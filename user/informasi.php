<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Cek Hasil Diagnosis
if (!isset($_SESSION['hasil_diagnosa'])) {
    header("Location: konsultasi.php");
    exit;
}

$data = $_SESSION['hasil_diagnosa'];
$status = $data['status'] ?? false;

// Default jika diagnosis tidak/belum menghasilkan penyakit
$penyakitName   = "Tidak Diketahui";
$deskripsi      = "Data deskripsi penyakit tidak ditemukan.";
$gejalaUmum     = [];
$penangananList = [];

if ($status && isset($data['hasil_terbaik'])) {
    $hasilTerbaik = $data['hasil_terbaik'];
    $idPenyakit   = $hasilTerbaik['id_penyakit'];

    // Query untuk mengambil detail penyakit berdasarkan id_penyakit hasil diagnosis
    $queryPenyakit = mysqli_query($conn, "
        SELECT * 
        FROM tb_penyakit 
        WHERE id_penyakit = '$idPenyakit' 
        LIMIT 1
    ");

    if ($row = mysqli_fetch_assoc($queryPenyakit)) {
        $penyakitName = $row['nama_penyakit'];
        
        if (!empty($row['deskripsi'])) {
            $deskripsi = $row['deskripsi'];
        }

        // Memecah gejala umum (pisahkan berdasarkan koma jika disimpan berderet)
        if (!empty($row['gejala_umum'])) {
            $gejalaUmum = array_values(array_filter(array_map('trim', explode(',', $row['gejala_umum']))));
        }

        // Memecah saran penanganan per baris
        if (!empty($row['penanganan'])) {
            $penangananList = array_values(array_filter(preg_split("/\r\n|\n|\r/", trim($row['penanganan']))));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Penyakit - <?= htmlspecialchars($penyakitName); ?></title>

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
            background: #F4F6F4;
            color: #1E5E20;
            padding-top: 85px;
        }

        /* ====================================== 
           NAVBAR RESPONSIVE 
           ====================================== */
        .navbar-custom {
            background: #FFFFFF;
            padding: 12px 0;
            border-bottom: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
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

        /* ====================================== 
           CARD UTAMA 
           ====================================== */
        .card-custom {
            background: #FFFFFF;
            padding: 32px;
            border-radius: 20px;
            border: 1px solid #E2E8E2;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
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

        /* HEADER JUDUL */
        .info-header {
            margin-bottom: 24px;
        }

        .info-header .sub-title {
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
            color: #1B5E20;
            margin: 0;
            word-break: break-word;
        }

        /* SECTION TIAP BAGIAN */
        .info-section {
            margin-bottom: 24px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 12px;
        }

        .section-title i {
            color: #2E7D32;
            font-size: 18px;
        }

        /* DESKRIPSI & PENYEBAB BOX */
        .desc-box {
            background: #F4F8F4;
            border-left: 4px solid #2E7D32;
            padding: 18px 20px;
            border-radius: 0 12px 12px 0;
            color: #333;
            font-size: 14.5px;
            line-height: 1.7;
            word-break: break-word;
        }

        /* BADGES GEJALA UMUM */
        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .badge-item {
            background: #E8F5E9;
            border: 1px solid #C8E6C9;
            color: #2E7D32;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13.5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            word-break: break-word;
        }

        /* LIST SARAN PENANGANAN */
        .penanganan-card {
            background: #FAFDFB;
            border: 1px solid #E0E8E0;
            border-radius: 14px;
            padding: 16px 20px;
        }

        .penanganan-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .penanganan-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14.5px;
            color: #333;
            padding: 10px 0;
            border-bottom: 1px dashed #E0E8E0;
            line-height: 1.5;
        }

        .penanganan-list li:last-child {
            border-bottom: none;
        }

        .icon-check-wrapper {
            width: 22px;
            height: 22px;
            background: #2E7D32;
            color: #FFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 12px;
            margin-top: 2px;
        }

        /* TOMBOL KEMBALI */
        .btn-back-bottom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border: 1.5px solid #2E7D32;
            background: #FFF;
            color: #2E7D32;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            margin-top: 10px;
            transition: all .2s ease;
        }

        .btn-back-bottom:hover {
            background: #2E7D32;
            color: #FFF;
        }

        /* ====================================== 
           MEDIA QUERIES (RESPONSIVE) 
           ====================================== */
        @media (max-width: 768px) {
            body {
                padding-top: 75px;
            }

            .card-custom {
                padding: 20px 16px;
                border-radius: 16px;
            }

            .info-header h2 {
                font-size: 22px;
            }

            .desc-box {
                padding: 14px 16px;
                font-size: 14px;
            }

            .penanganan-card {
                padding: 12px 14px;
            }

            .penanganan-list li {
                font-size: 14px;
            }

            .btn-back-bottom {
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
                    <li class="nav-item"><a class="nav-link active" href="konsultasi.php">Konsultasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
                    <li class="nav-item d-none d-lg-block ms-2">
                        <a href="profil.php" class="text-success fs-4"><i class="bi bi-person-circle"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="container my-4">

        <div class="card-custom">

            <!-- Header Informasi Penyakit -->
            <div class="info-header">
                <span class="sub-title"><i class="bi bi-info-circle-fill me-1"></i> Informasi Detail Penyakit</span>
                <h2><?= htmlspecialchars($penyakitName); ?></h2>
            </div>

            <!-- Deskripsi -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-file-text-fill"></i>
                    <span>Deskripsi</span>
                </div>
                <div class="desc-box">
                    <?= htmlspecialchars($deskripsi); ?>
                </div>
            </div>
            
            <!-- Gejala Umum -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-activity"></i>
                    <span>Gejala Umum</span>
                </div>
                <div class="badge-list">
                    <?php if (!empty($gejalaUmum)) : ?>
                        <?php foreach ($gejalaUmum as $gejala) : ?>
                            <span class="badge-item"><i class="bi bi-check2"></i> <?= htmlspecialchars($gejala); ?></span>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <span class="text-muted small">Informasi gejala umum tidak tersedia.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Saran Penanganan -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-shield-check"></i>
                    <span>Saran Penanganan</span>
                </div>
                <div class="penanganan-card">
                    <ul class="penanganan-list">
                        <?php if (!empty($penangananList)) : ?>
                            <?php foreach ($penangananList as $item) : ?>
                                <li>
                                    <div class="icon-check-wrapper">
                                        <i class="bi bi-check"></i>
                                    </div>
                                    <span><?= htmlspecialchars($item); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>
                                <div class="icon-check-wrapper">
                                    <i class="bi bi-check"></i>
                                </div>
                                <span>Segera hubungi fasilitas kesehatan terdekat untuk konsultasi lebih lanjut.</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- TOMBOL KEMBALI DI BAWAH -->
            <a href="hasil.php" class="btn-back-bottom">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>

        </div>

    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>