<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

// Penanganan variabel koneksi
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

/* ==========================================================
   CEK LOGIN
========================================================== */
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ==========================================================
   VALIDASI PARAMETER
========================================================== */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$idKonsultasi = (int)$_GET['id'];
$idUser       = $_SESSION['id_user'] ?? null;

if (!$idUser) {
    header("Location: ../login.php");
    exit;
}

/* ==========================================================
   DATA USER (Prepared Statement)
========================================================== */
$stmtUser = mysqli_prepare($conn, "SELECT * FROM tb_user WHERE id_user = ? LIMIT 1");
mysqli_stmt_bind_param($stmtUser, "i", $idUser);
mysqli_stmt_execute($stmtUser);
$queryUser = mysqli_stmt_get_result($stmtUser);
$user      = mysqli_fetch_assoc($queryUser);

/* ==========================================================
   DATA KONSULTASI (Prepared Statement)
========================================================== */
$stmtKonsultasi = mysqli_prepare($conn, "SELECT * FROM tb_konsultasi WHERE id_konsultasi = ? AND id_user = ? LIMIT 1");
mysqli_stmt_bind_param($stmtKonsultasi, "ii", $idKonsultasi, $idUser);
mysqli_stmt_execute($stmtKonsultasi);
$queryKonsultasi = mysqli_stmt_get_result($stmtKonsultasi);

if (mysqli_num_rows($queryKonsultasi) == 0) {
    header("Location: riwayat.php");
    exit;
}

$konsultasi = mysqli_fetch_assoc($queryKonsultasi);

/* ==========================================================
   FORMAT TANGGAL 
========================================================== */
function formatTanggalIndo($datetime) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($datetime);
    $tgl   = date('d', $timestamp);
    $bln   = $bulan[(int)date('m', $timestamp)];
    $thn   = date('Y', $timestamp);
    $jam   = date('H:i', $timestamp);

    return "$tgl $bln $thn $jam";
}

$tanggalDiagnosa = formatTanggalIndo($konsultasi['tanggal']);

/* ==========================================================
   AMBIL SELURUH HASIL DIAGNOSA (Prepared Statement)
========================================================== */
$sqlHasil = "
    SELECT 
        h.*, 
        p.kode_penyakit, 
        p.nama_penyakit, 
        p.deskripsi, 
        p.gejala_umum, 
        p.penanganan, 
        p.gambar
    FROM tb_hasil_diagnosis h
    INNER JOIN tb_penyakit p ON h.id_penyakit = p.id_penyakit
    WHERE h.id_konsultasi = ?
    ORDER BY h.persentase DESC
";

$stmtHasil = mysqli_prepare($conn, $sqlHasil);
mysqli_stmt_bind_param($stmtHasil, "i", $idKonsultasi);
mysqli_stmt_execute($stmtHasil);
$queryHasil = mysqli_stmt_get_result($stmtHasil);

if (!$queryHasil) {
    die("Query Error : " . mysqli_error($conn));
}

$hasilDiagnosa = [];
while ($row = mysqli_fetch_assoc($queryHasil)) {
    $hasilDiagnosa[] = $row;
}

/* ==========================================================
   VALIDASI HASIL DIAGNOSA
========================================================== */
if (count($hasilDiagnosa) == 0) {
    header("Location: riwayat.php");
    exit;
}

/* ==========================================================
   HASIL DIAGNOSA TERBAIK & PENANGANAN GAMBAR
========================================================== */
$hasilUtama   = $hasilDiagnosa[0];
$namaPenyakit = $hasilUtama['nama_penyakit'];
$kodePenyakit = $hasilUtama['kode_penyakit'];
$persentase   = $hasilUtama['persentase'];
$nilaiCF      = $hasilUtama['nilai_cf'];
$deskripsi    = $hasilUtama['deskripsi'];
$gejalaUmum   = $hasilUtama['gejala_umum'];

// Logika Pengecekan Gambar Penyakit
$gambar = "../assets/images/penyakit/default.png";

if (!empty($hasilUtama['gambar'])) {
    $path1 = "../assets/images/penyakit/" . $hasilUtama['gambar'];
    $path2 = "../assets/images/" . $hasilUtama['gambar'];

    if (file_exists($path1)) {
        $gambar = $path1;
    } elseif (file_exists($path2)) {
        $gambar = $path2;
    } else {
        $gambar = $path1; 
    }
}

/* ==========================================================
   EXTRACT PENANGANAN 
========================================================== */
$penanganan = [];
if (!empty($hasilUtama['penanganan'])) {
    $penanganan = preg_split(
        "/\r\n|\n|\r/",
        trim($hasilUtama['penanganan'])
    );
}

/* ==========================================================
   KATEGORI KEYAKINAN
========================================================== */
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Diagnosis - Sistem Pakar ISPA</title>

    <!-- Google Font & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #F7F6F2;
            color: #1E5E20;
            padding-top: 85px;
        }

        /* NAVBAR RESPONSIVE */
        .navbar-custom {
            background: #FFFFFF;
            padding: 12px 0;
            border-bottom: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #1E5E20;
        }

        .navbar-brand-custom i {
            font-size: 32px;
            color: #2E7D32;
        }

        .brand-text strong {
            font-size: 16px;
            font-weight: 700;
            color: #1E5E20;
            display: block;
            line-height: 1.2;
        }

        .brand-text small {
            font-size: 12px;
            color: #666;
        }

        .nav-link {
            font-weight: 500;
            color: #555 !important;
            padding: 8px 16px !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #2E7D32 !important;
            font-weight: 600;
        }

        .profile-avatar-nav {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2E7D32;
        }

        /* MAIN CONTENT & CARD */
        .card-custom {
            background: #FFFFFF;
            padding: 32px;
            border-radius: 18px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }

        .card-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2E7D32, #66BB6A);
        }

        .info-header {
            margin-bottom: 24px;
        }

        .sub-title {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #388E3C;
            margin-bottom: 4px;
            display: block;
        }

        .info-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1E5E20;
            margin: 0;
        }

        .info-header p {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
        }

        .info-card {
            background: #FFFFFF;
            border: 1px solid #E8F5E9;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .03);
            height: 100%;
        }

        .info-card h5 {
            color: #2E7D32;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .info-card p {
            color: #555;
            line-height: 1.7;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .image-diagnosa {
            width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 8px;
        }

        .badge-persentase {
            display: inline-block;
            margin-top: 14px;
            background: #E8F5E9;
            color: #2E7D32;
            padding: 8px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 15px;
        }

        .nilai-box {
            margin-top: 16px;
            background: #F8F9FA;
            border-radius: 12px;
            padding: 16px;
        }

        .table-responsive {
            border-radius: 12px;
            border: 1px solid #E8F5E9;
        }

        .table th,
        .table td {
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 75px;
            }

            .card-custom {
                padding: 20px 16px;
                border-radius: 14px;
            }

            .info-header h2 {
                font-size: 22px;
            }

            .info-header p {
                font-size: 13.5px;
            }

            .info-card {
                padding: 16px;
            }

            .btn-action-mobile {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR RESPONSIVE -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand-custom">
                <i class="bi bi-lungs-fill"></i>
                <div class="brand-text">
                    <strong>Sistem Pakar ISPA</strong>
                    <small>pada Balita</small>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1 my-2 my-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="konsultasi.php">Konsultasi</a></li>
                    <li class="nav-item"><a class="nav-link active" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
                    <li class="nav-item d-none d-lg-block ms-2">
                        <a href="profil.php">
                            <?php if (!empty($user['foto']) && file_exists("../assets/img/" . $user['foto'])) : ?>
                                <img src="../assets/img/<?= htmlspecialchars($user['foto']); ?>" alt="Foto Profil" class="profile-avatar-nav">
                            <?php else : ?>
                                <i class="bi bi-person-circle fs-4 text-success"></i>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="container my-4">
        <div class="card-custom">

            <!-- Header Riwayat -->
            <div class="info-header">
                <span class="sub-title">
                    <i class="bi bi-clock-history me-1"></i> Detail Riwayat Diagnosis
                </span>
                <h2><?= htmlspecialchars($namaPenyakit); ?></h2>
                <p>Hasil diagnosis berdasarkan riwayat konsultasi yang telah dilakukan.</p>
            </div>

            <!-- INFORMASI KONSULTASI & HASIL UTAMA -->
            <div class="row g-4 mb-4">
                <!-- Card Informasi Konsultasi -->
                <div class="col-lg-5">
                    <div class="info-card">
                        <h5>
                            <i class="bi bi-info-circle-fill me-2"></i> Informasi Konsultasi
                        </h5>
                        <hr>
                        <p>
                            <strong>ID Konsultasi</strong><br>
                            #<?= htmlspecialchars($idKonsultasi); ?>
                        </p>
                        <p>
                            <strong>Tanggal Konsultasi</strong><br>
                            <?= htmlspecialchars($tanggalDiagnosa); ?> WIB
                        </p>
                        <p>
                            <strong>Nama Pengguna</strong><br>
                            <?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Email</strong><br>
                            <?= htmlspecialchars($user['email'] ?? ''); ?>
                        </p>
                    </div>
                </div>

                <!-- Card Hasil Diagnosis -->
                <div class="col-lg-7">
                    <div class="info-card text-center">
                        <!-- GAMBAR PENYAKIT -->
                        <img src="<?= htmlspecialchars($gambar); ?>" alt="<?= htmlspecialchars($namaPenyakit); ?>" class="image-diagnosa">

                        <h4 class="mt-3 fw-bold text-success fs-5">
                            <?= htmlspecialchars($namaPenyakit); ?>
                        </h4>

                        <span class="badge-persentase">
                            <?= number_format($persentase, 2, ",", "."); ?>%
                        </span>

                        <div class="nilai-box">
                            <div class="row text-center g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block fw-semibold">Nilai Certainty Factor</small>
                                    <strong class="text-dark fs-6"><?= number_format($nilaiCF, 4, ",", "."); ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block fw-semibold">Kategori Keyakinan</small>
                                    <strong class="text-dark fs-6"><?= htmlspecialchars($kategori); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESKRIPSI & GEJALA UMUM -->
            <div class="row g-4">
                <!-- Deskripsi -->
                <div class="col-lg-6">
                    <div class="info-card">
                        <h5>
                            <i class="bi bi-file-earmark-medical-fill me-2"></i> Deskripsi Penyakit
                        </h5>
                        <hr>
                        <p style="text-align:justify;" class="mb-0">
                            <?= nl2br(htmlspecialchars($deskripsi)); ?>
                        </p>
                    </div>
                </div>

                <!-- Gejala Umum -->
                <div class="col-lg-6">
                    <div class="info-card">
                        <h5>
                            <i class="bi bi-clipboard2-pulse-fill me-2"></i> Gejala Umum
                        </h5>
                        <hr>
                        <p style="text-align:justify;" class="mb-0">
                            <?= nl2br(htmlspecialchars($gejalaUmum)); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- PENANGANAN -->
            <div class="info-card mt-4">
                <h5>
                    <i class="bi bi-heart-pulse-fill me-2"></i> Penanganan
                </h5>
                <hr>
                <?php if (!empty($penanganan)) : ?>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($penanganan as $item) : ?>
                            <?php if (trim($item) != "") : ?>
                                <li class="mb-2 text-dark" style="font-size: 14px;">
                                    <?= htmlspecialchars($item); ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="text-muted mb-0">Belum tersedia informasi penanganan.</p>
                <?php endif; ?>
            </div>

            <!-- HASIL DIAGNOSA KESELURUHAN -->
            <div class="info-card mt-4">
                <h5>
                    <i class="bi bi-bar-chart-fill me-2"></i> Hasil Diagnosis Keseluruhan
                </h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th width="60" class="text-center">No</th>
                                <th>Penyakit</th>
                                <th class="text-center">CF</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($hasilDiagnosa as $item) :
                            ?>
                                <tr>
                                    <td class="text-center fw-medium"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($item['nama_penyakit']); ?></td>
                                    <td class="text-center"><?= number_format($item['nilai_cf'], 4, ",", "."); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success fw-medium">
                                            <?= number_format($item['persentase'], 2, ",", "."); ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-4">
                <a href="riwayat.php" class="btn btn-outline-success btn-action-mobile">
                    <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Riwayat
                </a>

                <a href="../proses/cetak_hasil.php?id=<?= $idKonsultasi; ?>" target="_blank" class="btn btn-success btn-action-mobile">
                    <i class="bi bi-printer-fill me-2"></i> Cetak Hasil
                </a>
            </div>

        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>