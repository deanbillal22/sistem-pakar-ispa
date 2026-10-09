<?php
session_start();

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$idUser = $_SESSION['id_user'];
$nama   = $_SESSION['nama'] ?? "User";

// Fungsi pembantu untuk format tanggal 
function tanggalIndonesia($dateString) {
    if (!$dateString) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($dateString);
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return "$tgl $bln $thn, $jam WIB";
}

/* =====================================================
    AMBIL DATA DASHBOARD
===================================================== */

// Total Konsultasi
$queryTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM tb_hasil_diagnosis
    WHERE id_user = '$idUser'
");

$totalKonsultasi = 0;
if ($queryTotal && mysqli_num_rows($queryTotal) > 0) {
    $totalKonsultasi = mysqli_fetch_assoc($queryTotal)['total'];
}

// Riwayat Diagnosis Terakhir
$queryTerakhir = mysqli_query($conn, "
    SELECT
        hd.id_hasil,
        hd.id_user,
        hd.id_penyakit,
        hd.nilai_cf,
        hd.persentase,
        hd.tanggal_diagnosis,
        p.nama_penyakit
    FROM tb_hasil_diagnosis hd
    INNER JOIN tb_penyakit p ON hd.id_penyakit = p.id_penyakit
    WHERE hd.id_user = '$idUser'
    ORDER BY hd.tanggal_diagnosis DESC
    LIMIT 1
");

$dataTerakhir = null;
if ($queryTerakhir && mysqli_num_rows($queryTerakhir) > 0) {
    $dataTerakhir = mysqli_fetch_assoc($queryTerakhir);
}

// Persentase Diagnosis Tertinggi
$queryTertinggi = mysqli_query($conn, "
    SELECT
        hd.id_hasil,
        hd.id_user,
        hd.id_penyakit,
        hd.nilai_cf,
        hd.persentase,
        hd.tanggal_diagnosis,
        p.nama_penyakit
    FROM tb_hasil_diagnosis hd
    INNER JOIN tb_penyakit p ON hd.id_penyakit = p.id_penyakit
    WHERE hd.id_user = '$idUser'
    ORDER BY hd.persentase DESC
    LIMIT 1
");

$dataTertinggi = null;
if ($queryTertinggi && mysqli_num_rows($queryTertinggi) > 0) {
    $dataTertinggi = mysqli_fetch_assoc($queryTertinggi);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Pakar ISPA</title>

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
        }

        /* ====================================== 
           NAVBAR (RESPONSIF)
           ====================================== */
        .navbar-custom {
            background: #FFFFFF;
            padding: 12px 0;
            border-bottom: 1px solid #EAEAEA;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .navbar-custom .container {
            max-width: 1080px;
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

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-text strong {
            font-size: 16px;
            font-weight: 700;
            color: #1E5E20;
        }

        .brand-text small {
            font-size: 12px;
            color: #666;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-menu a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 15px;
            transition: .2s;
            position: relative;
            padding-bottom: 4px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #2E7D32;
            font-weight: 600;
        }

        .nav-menu a.active::after {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #2E7D32;
            border-radius: 3px 3px 0 0;
        }

        /* ====================================== 
           MAIN CONTAINER
           ====================================== */
        .main {
            max-width: 1080px;
            width: 100%;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1E5E20;
            margin-bottom: 6px;
        }

        .header p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        /* CARD CUSTOM */
        .card-custom {
            background: #FFFFFF;
            padding: 28px;
            border-radius: 18px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }

        .card-custom h3 {
            margin-bottom: 18px;
            font-size: 20px;
            font-weight: 700;
            color: #1E5E20;
        }

        /* BANNER MULAI KONSULTASI */
        .banner-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #EAF9EF;
            border: 1px solid #C8E6C9;
            padding: 24px;
            border-radius: 14px;
            gap: 16px;
        }

        .banner-text h4 {
            font-size: 18px;
            font-weight: 700;
            color: #1E5E20;
            margin-bottom: 6px;
        }

        .banner-text p {
            font-size: 13.5px;
            color: #444;
            margin: 0;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            background: #2E7D32;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14.5px;
            cursor: pointer;
            transition: .2s;
            white-space: nowrap;
        }

        .btn-submit:hover {
            background: #236628;
            color: white;
        }

        /* RIWAYAT TERAKHIR BOX */
        .riwayat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            background: #FAFAFA;
            border: 1px solid #EAEAEA;
            border-radius: 14px;
            gap: 16px;
        }

        .riwayat-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .riwayat-icon-box {
            width: 48px;
            height: 48px;
            min-width: 48px;
            background: #EAF9EF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .riwayat-icon-box i {
            font-size: 24px;
            color: #2E7D32;
        }

        .riwayat-details strong {
            display: block;
            font-size: 15.5px;
            color: #1E5E20;
        }

        .riwayat-details small {
            font-size: 13px;
            color: #777;
        }

        .riwayat-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .riwayat-percent {
            font-size: 24px;
            font-weight: 700;
            color: #2E7D32;
        }

        /* STATISTIK GRID */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: #FFFFFF;
            border: 1px solid #EAEAEA;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            text-align: left;
        }

        .stat-card .title {
            font-size: 13.5px;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 26px;
            font-weight: 700;
            color: #1E5E20;
            line-height: 1.2;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .stat-card .sub {
            font-size: 13px;
            color: #2E7D32;
            font-weight: 500;
        }

        /* ====================================== 
           MEDIA QUERIES (RESPONSIVE)
           ====================================== */
        @media (max-width: 991px) {
            .nav-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 16px 0;
            }

            .nav-menu a.active::after {
                display: none;
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main {
                margin-top: 85px;
                padding: 0 15px;
            }

            .header h1 {
                font-size: 24px;
            }

            .card-custom {
                padding: 20px;
            }

            .banner-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-submit {
                width: 100%;
            }

            .riwayat-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .riwayat-right {
                width: 100%;
                justify-content: space-between;
                border-top: 1px dashed #EAEAEA;
                padding-top: 12px;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand-custom">
                <i class="bi bi-lungs-fill"></i>
                <div class="brand-text">
                    <strong>Sistem Pakar ISPA</strong>
                    <small>pada Balita</small>
                </div>
            </a>

            <!-- Tombol Hamburger Mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu Item -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="active">Beranda</a></li>
                    <li><a href="konsultasi.php">Konsultasi</a></li>
                    <li><a href="riwayat.php">Riwayat</a></li>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- Header -->
        <div class="header">
            <h1>Halo, <?= htmlspecialchars($nama); ?> 👋</h1>
            <p>Selamat datang di Sistem Pakar Diagnosis ISPA pada Balita.</p>
        </div>

        <!-- Card Banner Mulai Konsultasi -->
        <div class="card-custom" style="padding: 20px;">
            <div class="banner-box">
                <div class="banner-text">
                    <h4>Mulai Diagnosa Penyakit</h4>
                    <p>Lakukan konsultasi awal gejala ISPA balita secara akurat dan cepat.</p>
                </div>
                <a href="konsultasi.php" class="btn-submit">
                    <i class="bi bi-stethoscope"></i>
                    Mulai Konsultasi
                </a>
            </div>
        </div>

        <!-- Card Riwayat Terakhir -->
        <div class="card-custom">
            <h3>Riwayat Diagnosis Terakhir</h3>
            <div class="riwayat-content">
                <div class="riwayat-info">
                    <div class="riwayat-icon-box">
                        <i class="bi bi-lungs-fill"></i>
                    </div>
                    <div class="riwayat-details">
                        <strong><?= $dataTerakhir ? htmlspecialchars($dataTerakhir['nama_penyakit']) : 'Belum Ada Diagnosa'; ?></strong>
                        <small>
                            <?= $dataTerakhir ? tanggalIndonesia($dataTerakhir['tanggal_diagnosis']) : '-'; ?>
                        </small>
                    </div>
                </div>

                <div class="riwayat-right">
                    <div class="riwayat-percent">
                        <?= $dataTerakhir ? number_format($dataTerakhir['persentase'], 0) : '0'; ?>%
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Grid -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="title">Total Konsultasi</div>
                <div class="value"><?= $totalKonsultasi; ?></div>
                <div class="sub">Kali Dilakukan</div>
            </div>

            <div class="stat-card">
                <div class="title">Persentase Tertinggi</div>
                <div class="value"><?= $dataTertinggi ? number_format($dataTertinggi['persentase'], 0) : '0'; ?>%</div>
                <div class="sub"><?= $dataTertinggi ? htmlspecialchars($dataTertinggi['nama_penyakit']) : '-'; ?></div>
            </div>

            <div class="stat-card">
                <div class="title">Konsultasi Terakhir</div>
                <div class="value">
                    <?= $dataTerakhir ? date('d M Y', strtotime($dataTerakhir['tanggal_diagnosis'])) : '-'; ?>
                </div>
                <div class="sub">
                    <?= $dataTerakhir ? date('H:i', strtotime($dataTerakhir['tanggal_diagnosis'])) . ' WIB' : '-'; ?>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>