<?php
session_start();

require_once "../config/koneksi.php";

/**
 * Class DiagnosaEngine
 * Untuk seluruh kalkulasi mesin inferensi.
 */
class DiagnosaEngine
{
    private $db;
    private $idUser;
    private $cfUser = [];
    private $gejalaDipilih = [];

    public function __construct($dbConnection, $idUser, array $inputCF)
    {
        $this->db = $dbConnection;
        $this->idUser = (int)$idUser;
        $this->sanitizeInputCF($inputCF);
    }

    /**
     * Filter input CF user agar hanya mengambil nilai > 0
     */
    private function sanitizeInputCF(array $inputCF)
    {
        foreach ($inputCF as $idGejala => $cf) {
            $nilaiCF = (float)$cf;
            if ($nilaiCF > 0) {
                $this->cfUser[(int)$idGejala] = $nilaiCF;
            }
        }
    }

    /**
     * Jalankan proses eksekusi utama diagnosa
     */
    public function execute()
    {
        // 1. Validasi minimal 1 gejala dipilih
        if (empty($this->cfUser)) {
            $_SESSION['hasil_diagnosa'] = [
                "status" => false,
                "pesan"  => [
                    "judul" => "Diagnosa Gagal",
                    "isi"   => "Silakan pilih minimal satu gejala sebelum melakukan proses diagnosa."
                ]
            ];
            header("Location: ../user/konsultasi.php");
            exit;
        }

        // 2. Simpan Data Konsultasi
        $idKonsultasi = $this->simpanKonsultasi();

        // 3. Ambil Detail Gejala yang Dipilih
        $this->loadDetailGejalaUser();

        // 4. Proses Forward Chaining
        $ruleAktif = $this->processForwardChaining();

        // Jika tidak ada rule yang terpenuhi
        if (empty($ruleAktif)) {
            $_SESSION['hasil_diagnosa'] = [
                "status"        => false,
                "id_konsultasi" => $idKonsultasi,
                "tanggal"       => date("Y-m-d H:i:s"),
                "gejala_user"   => $this->gejalaDipilih,
                "hasil"         => [],
                "pesan"         => [
                    "judul" => "Belum Dapat Memberikan Kesimpulan",
                    "isi"   => "Berdasarkan gejala yang dipilih, sistem belum dapat memberikan kesimpulan karena kombinasi gejala belum memenuhi basis pengetahuan (rule) yang tersedia. Disarankan untuk berkonsultasi dengan tenaga kesehatan atau mengunjungi fasilitas kesehatan terdekat agar memperoleh pemeriksaan lebih lanjut."
                ]
            ];
            header("Location: ../user/hasil.php");
            exit;
        }

        // 5. Perhitungan Certainty Factor (CF) per Rule & Pengelompokan Penyakit
        $hasilPenyakit = $this->calculateCFPerRule($ruleAktif);

        // 6. Combine CF & Tentukan Kategori Keyakinan
        $hasilPenyakit = $this->combineCF($hasilPenyakit);

        // 7. Urutkan Hasil Diagnosa Berdasarkan Persentase Tertinggi
        uasort($hasilPenyakit, function ($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });

        $hasilTerbaik = reset($hasilPenyakit);

        // 8. Simpan Hasil Diagnosa ke Database
        $this->simpanHasilDiagnosa($idKonsultasi, $hasilPenyakit);

        // 9. Simpan Ke Session untuk Tampilan
        $_SESSION['hasil_diagnosa'] = [
            "status"        => true,
            "id_konsultasi" => $idKonsultasi,
            "id_user"       => $this->idUser,
            "tanggal"       => date("Y-m-d H:i:s"),
            "jumlah_gejala" => count($this->cfUser),
            "gejala_user"   => $this->gejalaDipilih,
            "hasil_terbaik" => $hasilTerbaik,
            "hasil"         => $hasilPenyakit
        ];

        header("Location: ../user/hasil.php");
        exit;
    }

    /**
     * Insert data konsultasi baru
     */
    private function simpanKonsultasi()
    {
        $stmt = mysqli_prepare($this->db, "INSERT INTO tb_konsultasi (id_user) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "i", $this->idUser);
        
        if (!mysqli_stmt_execute($stmt)) {
            die("Gagal menyimpan data konsultasi: " . mysqli_error($this->db));
        }

        $idKonsultasi = mysqli_insert_id($this->db);
        mysqli_stmt_close($stmt);

        return $idKonsultasi;
    }

    /**
     * Mengambil detail gejala yang diinputkan oleh user
     */
    private function loadDetailGejalaUser()
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_gejala, kode_gejala, nama_gejala FROM tb_gejala WHERE id_gejala = ? LIMIT 1");

        foreach ($this->cfUser as $idGejala => $nilaiCF) {
            mysqli_stmt_bind_param($stmt, "i", $idGejala);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $row['cf_user'] = $nilaiCF;
                $this->gejalaDipilih[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }

    /**
     * Algoritma Forward Chaining untuk memfilter rule yang aktif
     */
    private function processForwardChaining()
    {
        $ruleAktif = [];
        $queryRule = mysqli_query($this->db, "SELECT * FROM tb_rule ORDER BY id_rule ASC");

        while ($rule = mysqli_fetch_assoc($queryRule)) {
            $idRule = $rule['id_rule'];

            $stmtDetail = mysqli_prepare($this->db, "
                SELECT rd.id_gejala, g.kode_gejala, g.nama_gejala 
                FROM tb_rule_detail rd
                INNER JOIN tb_gejala g ON rd.id_gejala = g.id_gejala
                WHERE rd.id_rule = ? 
                ORDER BY g.kode_gejala ASC
            ");
            mysqli_stmt_bind_param($stmtDetail, "i", $idRule);
            mysqli_stmt_execute($stmtDetail);
            $queryDetail = mysqli_stmt_get_result($stmtDetail);

            $semuaTerpenuhi = true;
            $daftarGejala = [];

            while ($detail = mysqli_fetch_assoc($queryDetail)) {
                $daftarGejala[] = $detail;
                if (!isset($this->cfUser[$detail['id_gejala']])) {
                    $semuaTerpenuhi = false;
                }
            }
            mysqli_stmt_close($stmtDetail);

            if ($semuaTerpenuhi) {
                $stmtPenyakit = mysqli_prepare($this->db, "SELECT id_penyakit, kode_penyakit, nama_penyakit FROM tb_penyakit WHERE id_penyakit = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtPenyakit, "i", $rule['id_penyakit']);
                mysqli_stmt_execute($stmtPenyakit);
                $resPenyakit = mysqli_stmt_get_result($stmtPenyakit);

                if ($penyakit = mysqli_fetch_assoc($resPenyakit)) {
                    $ruleAktif[] = [
                        "id_rule"       => $rule['id_rule'],
                        "kode_rule"     => $rule['kode_rule'],
                        "id_penyakit"   => $penyakit['id_penyakit'],
                        "kode_penyakit" => $penyakit['kode_penyakit'],
                        "nama_penyakit" => $penyakit['nama_penyakit'],
                        "cf_rule"       => (float)$rule['cf_rule'],
                        "gejala"        => $daftarGejala
                    ];
                }
                mysqli_stmt_close($stmtPenyakit);
            }
        }

        return $ruleAktif;
    }

    /**
     * Menghitung nilai CF tiap Rule yang aktif
     */
    private function calculateCFPerRule(array $ruleAktif)
    {
        $hasilPenyakit = [];

        foreach ($ruleAktif as $rule) {
            $cfEvidenceInput = [];
            $detailGejala = [];

            foreach ($rule['gejala'] as $gejala) {
                $idGejala = $gejala['id_gejala'];
                $nilaiCFUser = (float)$this->cfUser[$idGejala];

                $cfEvidenceInput[] = $nilaiCFUser;
                $detailGejala[] = [
                    "id_gejala"   => $idGejala,
                    "kode_gejala" => $gejala['kode_gejala'],
                    "nama_gejala" => $gejala['nama_gejala'],
                    "cf_user"     => $nilaiCFUser
                ];
            }

            $cfEvidence = min($cfEvidenceInput);
            $cfRule = round($cfEvidence * (float)$rule['cf_rule'], 5);
            $idPenyakit = $rule['id_penyakit'];

            if (!isset($hasilPenyakit[$idPenyakit])) {
                $hasilPenyakit[$idPenyakit] = [
                    "id_penyakit"   => $idPenyakit,
                    "kode_penyakit" => $rule['kode_penyakit'],
                    "nama_penyakit" => $rule['nama_penyakit'],
                    "cf_rule_list"  => [],
                    "detail_rule"   => []
                ];
            }

            $hasilPenyakit[$idPenyakit]['cf_rule_list'][] = $cfRule;
            $hasilPenyakit[$idPenyakit]['detail_rule'][] = [
                "id_rule"     => $rule['id_rule'],
                "kode_rule"   => $rule['kode_rule'],
                "gejala"      => $detailGejala,
                "cf_evidence" => $cfEvidence,
                "bobot_rule"  => (float)$rule['cf_rule'],
                "cf_rule"     => $cfRule
            ];
        }

        return $hasilPenyakit;
    }

    /**
     * Melakukan penggabungan (combine) CF per penyakit dan menghitung persentase
     */
    private function combineCF(array $hasilPenyakit)
    {
        foreach ($hasilPenyakit as &$penyakit) {
            $cfList = $penyakit['cf_rule_list'];
            $prosesCombine = [];
            $hasilCF = $cfList[0];

            $prosesCombine[] = [
                "langkah" => 1,
                "cf_awal" => $hasilCF,
                "cf_baru" => null,
                "hasil"   => $hasilCF
            ];

            for ($i = 1; $i < count($cfList); $i++) {
                $cfAwal = $hasilCF;
                $cfBaru = $cfList[$i];

                $hasilCF = round($cfAwal + ($cfBaru * (1 - $cfAwal)), 5);

                $prosesCombine[] = [
                    "langkah" => $i + 1,
                    "cf_awal" => $cfAwal,
                    "cf_baru" => $cfBaru,
                    "hasil"   => $hasilCF
                ];
            }

            $persentase = round($hasilCF * 100, 2);

            if ($persentase >= 90) {
                $kategori = "Sangat Tinggi";
            } elseif ($persentase >= 70) {
                $kategori = "Tinggi";
            } elseif ($persentase >= 50) {
                $kategori = "Sedang";
            } elseif ($persentase >= 30) {
                $kategori = "Rendah";
            } else {
                $kategori = "Sangat Rendah";
            }

            $penyakit['cf_combine']      = $hasilCF;
            $penyakit['persentase']      = $persentase;
            $penyakit['kategori']        = $kategori;
            $penyakit['combine_process'] = $prosesCombine;
        }

        return $hasilPenyakit;
    }

    /**
     * Menyimpan hasil ke database tb_hasil_diagnosis
     */
    private function simpanHasilDiagnosa($idKonsultasi, array $hasilPenyakit)
    {
        $stmtSimpan = mysqli_prepare($this->db, "
            INSERT INTO tb_hasil_diagnosis (id_konsultasi, id_user, id_penyakit, nilai_cf, persentase, tanggal_diagnosis)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        foreach ($hasilPenyakit as $hasil) {
            mysqli_stmt_bind_param(
                $stmtSimpan,
                "iiidd",
                $idKonsultasi,
                $this->idUser,
                $hasil['id_penyakit'],
                $hasil['cf_combine'],
                $hasil['persentase']
            );

            if (!mysqli_stmt_execute($stmtSimpan)) {
                die("Gagal menyimpan hasil diagnosa : " . mysqli_error($this->db));
            }
        }
        mysqli_stmt_close($stmtSimpan);
    }
}

/* ==========================================================
   INITIALIZATION & EXECUTION
========================================================== */

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user/konsultasi.php");
    exit;
}

// Instansiasi Diagnosa Engine & Jalankan Proses menggunakan $conn bawaan koneksi.php
$engine = new DiagnosaEngine($conn, $_SESSION['id_user'], $_POST['cf_user'] ?? []);
$engine->execute();