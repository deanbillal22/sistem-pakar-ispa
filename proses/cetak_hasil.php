<?php
session_start();
require_once "../config/koneksi.php";

/**
 * Class CetakHasilController
 * Untuk cetak hasil diagnosis
 */
class CetakHasilController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Memeriksa autentikasi user dan parameter ID
     */
    public function validateAccess($idKonsultasi)
    {
        if (!isset($_SESSION['id_user'])) {
            header("Location: ../login.php");
            exit();
        }

        if (empty($idKonsultasi)) {
            header("Location: ../user/detail_riwayat.php");
            exit();
        }
    }

    /**
     * Mengambil data profil user via Prepared Statement
     */
    public function getUserData($idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM tb_user WHERE id_user = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idUser);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $user;
    }

    /**
     * Mengambil data transaksi konsultasi
     */
    public function getKonsultasiData($idKonsultasi, $idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM tb_konsultasi WHERE id_konsultasi = ? AND id_user = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ii", $idKonsultasi, $idUser);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $konsultasi = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$konsultasi) {
            header("Location: ../user/detail_riwayat.php?id=" . $idKonsultasi);
            exit();
        }

        return $konsultasi;
    }

    /**
     * Mengambil daftar hasil diagnosis berdasarkan ID Konsultasi
     */
    public function getHasilDiagnosaData($idKonsultasi)
    {
        $query = "SELECT h.*, p.kode_penyakit, p.nama_penyakit, p.deskripsi, p.gejala_umum, p.penanganan 
                  FROM tb_hasil_diagnosis h 
                  INNER JOIN tb_penyakit p ON h.id_penyakit = p.id_penyakit 
                  WHERE h.id_konsultasi = ? 
                  ORDER BY h.persentase DESC";

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $idKonsultasi);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $hasilDiagnosa = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $hasilDiagnosa[] = $row;
        }
        mysqli_stmt_close($stmt);

        if (empty($hasilDiagnosa)) {
            die("Data hasil diagnosa tidak ditemukan.");
        }

        return $hasilDiagnosa;
    }
}

/* ==========================================================
   INITIALIZATION & DATA PROCESSING
========================================================== */

$idKonsultasi = (int)($_GET['id'] ?? 0);
$idUser       = $_SESSION['id_user'] ?? 0;

// Ambil koneksi database prosedural ($conn atau $koneksi)
$db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

if (!$db) {
    die("Koneksi database gagal!");
}

$controller = new CetakHasilController($db);

$controller->validateAccess($idKonsultasi);
$user          = $controller->getUserData($idUser);
$konsultasi    = $controller->getKonsultasiData($idKonsultasi, $idUser);
$hasilDiagnosa = $controller->getHasilDiagnosaData($idKonsultasi);

$hasilUtama    = $hasilDiagnosa[0];
$tanggal       = date("d F Y H:i", strtotime($konsultasi['tanggal']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Hasil Diagnosis - ID #<?= $idKonsultasi; ?></title>

    <!-- Google Font & Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #EFEFEF;
            color: #222;
            font-size: 13px;
            line-height: 1.6;
            padding: 20px 0;
        }

        /* CONTAINER UTAMA (PRINTOUT A4) */
        .page-container {
            width: 100%;
            max-width: 800px;
            background: #FFFFFF;
            margin: 0 auto;
            padding: 35px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        /* ACTIONS BAR */
        .action-bar {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-back {
            background: #757575;
            color: #FFF;
        }

        .btn-back:hover {
            background: #616161;
        }

        .btn-print {
            background: #2E7D32;
            color: #FFF;
        }

        .btn-print:hover {
            background: #1B5E20;
        }

        /* HEADER & KOP */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #2E7D32;
            padding-bottom: 15px;
            margin-bottom: 20px;
            gap: 15px;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-box img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .logo-text h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1E5E20;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .logo-text p {
            color: #555;
            font-size: 11.5px;
            font-weight: 400;
        }

        .tanggal-cetak {
            text-align: right;
            font-size: 11px;
            color: #666;
            white-space: nowrap;
        }

        /* SECTION TIAP BAGIAN */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #2E7D32;
            color: #FFF;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* TABEL METADATA / INFORMASI */
        .table-info {
            width: 100%;
            border-collapse: collapse;
        }

        .table-info td {
            padding: 5px 8px;
            vertical-align: top;
        }

        .table-info td.label {
            width: 180px;
            font-weight: 600;
            color: #1E5E20;
        }

        .table-info td.separator {
            width: 10px;
        }

        /* TABEL KESELURUHAN HASIL */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .table-hasil {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .table-hasil th {
            background: #E8F5E9;
            color: #1E5E20;
            border: 1px solid #C8E6C9;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }

        .table-hasil td {
            border: 1px solid #E0E0E0;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }

        .table-hasil td.text-left {
            text-align: left;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            background: #E8F5E9;
            color: #2E7D32;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid #A5D6A7;
        }

        /* LIST SARAN PENANGANAN */
        .penanganan-list {
            padding-left: 20px;
            margin: 0;
        }

        .penanganan-list li {
            margin-bottom: 6px;
            text-align: justify;
        }

        /* FOOTER & TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .ttd-box {
            text-align: center;
            width: 220px;
        }

        .ttd-space {
            height: 60px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #DDD;
            padding-top: 10px;
        }

        /* RESPONSIVE LAYAR HP / TABLET */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }

            .page-container {
                padding: 20px 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .tanggal-cetak {
                text-align: left;
            }

            .table-info td.label {
                width: 130px;
            }
        }

        /* PRESET UNTUK HASIL PRINT / CETAK KERTAS */
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 15mm;
            }

            body {
                background: #FFF;
                padding: 0;
                font-size: 11pt;
            }

            .action-bar {
                display: none !important;
            }

            .page-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }

            .section {
                page-break-inside: avoid;
                margin-bottom: 16px;
            }

            .section-title {
                background: #2E7D32 !important;
                color: #FFF !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-hasil th {
                background: #E8F5E9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge {
                border: 1px solid #2E7D32;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <!-- TOMBOL AKSI CETAK / KEMBALI -->
    <div class="action-bar">
        <a href="../user/detail_riwayat.php?id=<?= $idKonsultasi; ?>" class="btn-action btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button type="button" onclick="window.print()" class="btn-action btn-print">
            <i class="bi bi-printer"></i> Cetak Dokumen
        </button>
    </div>

    <div class="page-container">

        <!-- HEADER / KOP -->
        <div class="header">
            <div class="logo-box">
                <img src="../assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                <div class="logo-text">
                    <h2>Sistem Pakar Diagnosis ISPA</h2>
                    <p>Saluran Pernapasan Bawah Pada Balita</p>
                </div>
            </div>
            <div class="tanggal-cetak">
                Dicetak pada:<br>
                <strong><?= date("d F Y H:i"); ?></strong>
            </div>
        </div>

        <!-- DATA KONSULTASI -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-person-lines-fill"></i> Data Konsultasi
            </div>
            <table class="table-info">
                <tr>
                    <td class="label">ID Konsultasi</td>
                    <td class="separator">:</td>
                    <td>#<?= $idKonsultasi; ?></td>
                </tr>
                <tr>
                    <td class="label">Nama Pengguna</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($user['email'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">Tanggal Konsultasi</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($tanggal); ?></td>
                </tr>
            </table>
        </div>

        <!-- HASIL DIAGNOSA UTAMA -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-check-circle-fill"></i> Hasil Diagnosis Utama
            </div>
            <table class="table-info">
                <tr>
                    <td class="label">Kode Penyakit</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($hasilUtama['kode_penyakit']); ?></td>
                </tr>
                <tr>
                    <td class="label">Nama Penyakit</td>
                    <td class="separator">:</td>
                    <td>
                        <strong><?= htmlspecialchars($hasilUtama['nama_penyakit']); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="label">Nilai CF</td>
                    <td class="separator">:</td>
                    <td><?= number_format($hasilUtama['nilai_cf'], 4, ",", "."); ?></td>
                </tr>
                <tr>
                    <td class="label">Tingkat Keyakinan</td>
                    <td class="separator">:</td>
                    <td>
                        <span class="badge">
                            <?= number_format($hasilUtama['persentase'], 2, ",", "."); ?>%
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- HASIL DIAGNOSA KESELURUHAN -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-list-stars"></i> Hasil Diagnosis Keseluruhan
            </div>
            <div class="table-responsive">
                <table class="table-hasil">
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="18%">Kode</th>
                            <th>Nama Penyakit</th>
                            <th width="18%">Nilai CF</th>
                            <th width="18%">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($hasilDiagnosa as $hasil) :
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($hasil['kode_penyakit']); ?></td>
                                <td class="text-left"><?= htmlspecialchars($hasil['nama_penyakit']); ?></td>
                                <td><?= number_format($hasil['nilai_cf'], 4, ",", "."); ?></td>
                                <td>
                                    <strong><?= number_format($hasil['persentase'], 2, ",", "."); ?>%</strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DESKRIPSI PENYAKIT -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-info-circle-fill"></i> Deskripsi Penyakit
            </div>
            <p style="text-align:justify;">
                <?= nl2br(htmlspecialchars($hasilUtama['deskripsi'])); ?>
            </p>
        </div>

        <!-- GEJALA UMUM -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-activity"></i> Gejala Umum
            </div>
            <p style="text-align:justify;">
                <?= nl2br(htmlspecialchars($hasilUtama['gejala_umum'])); ?>
            </p>
        </div>

        <!-- PENANGANAN -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-shield-plus"></i> Saran Penanganan
            </div>
            <?php if (!empty($hasilUtama['penanganan'])) : ?>
                <ol class="penanganan-list">
                    <?php
                    $listPenanganan = preg_split("/\r\n|\n|\r/", trim($hasilUtama['penanganan']));
                    foreach ($listPenanganan as $item) :
                        if (trim($item) == "") continue;
                    ?>
                        <li><?= htmlspecialchars($item); ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else : ?>
                <p>Belum tersedia informasi penanganan.</p>
            <?php endif; ?>
        </div>

        <!-- CATATAN / DISCLAIMER -->
        <div class="section">
            <div class="section-title">
                <i class="bi bi-exclamation-triangle-fill"></i> Catatan
            </div>
            <p style="text-align:justify;">
                Jika dalam waktu lebih dari 3 hari gejala belum juga mereda atau kondisi balita memburuk, disarankan untuk segera berkonsultasi langsung dengan dokter atau fasilitas kesehatan terdekat.
            </p>
        </div>

        <!-- TANDA TANGAN -->
        <div class="ttd-container">
            <div class="ttd-box">
                <p>Jakarta, <?= date("d F Y"); ?></p>
                <div style="font-size: 11px; color: #555; margin-top: 2px;">Pemeriksa / Pakar Sistem</div>
                <div class="ttd-space"></div>
                <p><strong>dr. Kartika Sari Widuri, Sp.A</strong></p>
            </div>
        </div>
        <!-- FOOTER -->
        <div class="footer">
            Dokumen ini dicetak secara otomatis oleh Sistem Pakar Diagnosis ISPA pada Balita.
        </div>

    </div>

    <!-- SCRIPT AUTO PRINT & AUTO REDIRECT -->
    <script>
        const targetUrl = "../user/detail_riwayat.php?id=<?= $idKonsultasi; ?>";

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };

        window.onafterprint = function() {
            window.location.href = targetUrl;
        };
    </script>
</body>

</html>