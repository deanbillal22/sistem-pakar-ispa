<?php
session_start();
require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// ======================================================
// CEK LOGIN
// ======================================================
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// ======================================================
// DATA USER LOGIN
// ======================================================
$idUser = $_SESSION['id_user'];
$queryUser = mysqli_query($conn, "
    SELECT *
    FROM tb_user
    WHERE id_user='$idUser'
    LIMIT 1
");
$user = mysqli_fetch_assoc($queryUser);

// ======================================================
// CEK HASIL DIAGNOSA
// ======================================================
if (!isset($_SESSION['hasil_diagnosa'])) {
    header("Location: konsultasi.php");
    exit;
}

$data = $_SESSION['hasil_diagnosa'];

// ======================================================
// DATA UMUM
// ======================================================
$status          = $data['status'];
$idKonsultasi    = $data['id_konsultasi'];
$tanggalDiagnosa = date("d F Y H:i", strtotime($data['tanggal']));
$gejalaDipilih   = $data['gejala_user'];

// ======================================================
// NILAI DEFAULT
// ======================================================
$penyakit       = "Belum dapat disimpulkan";
$cf              = 0;
$persentase     = 0;
$kategori       = "-";
$warnaKategori  = "#D32F2F";
$gambarPenyakit = "../assets/images/penyakit/default.png"; // Fallback default
$deskripsi      = "";
$gejalaUmum     = "";
$penanganan     = [];
$note           = "";

// ======================================================
// JIKA TIDAK ADA RULE YANG AKTIF
// ======================================================
if (!$status) {
    $note       = $data['pesan']['isi'];
    $penanganan = [
        "Segera berkonsultasi dengan dokter atau tenaga kesehatan.",
        "Lakukan pemeriksaan lebih lanjut di fasilitas kesehatan terdekat."
    ];
} else {
    // HASIL TERBAIK
    $hasilTerbaik = $data['hasil_terbaik'];
    $penyakit     = $hasilTerbaik['nama_penyakit'];
    $cf           = $hasilTerbaik['cf_combine'];
    $persentase   = $hasilTerbaik['persentase'];
    $kategori     = $hasilTerbaik['kategori'];

    // WARNA KATEGORI
    switch ($kategori) {
        case "Sangat Tinggi":
            $warnaKategori = "#2E7D32";
            break;
        case "Tinggi":
            $warnaKategori = "#388E3C";
            break;
        case "Sedang":
            $warnaKategori = "#F57C00";
            break;
        case "Rendah":
            $warnaKategori = "#EF6C00";
            break;
        default:
            $warnaKategori = "#D32F2F";
            break;
    }

    // AMBIL DATA PENYAKIT DARI DATABASE
    $queryPenyakit = mysqli_query($conn, "
        SELECT *
        FROM tb_penyakit
        WHERE id_penyakit='{$hasilTerbaik['id_penyakit']}'
        LIMIT 1
    ");

    if ($dataPenyakit = mysqli_fetch_assoc($queryPenyakit)) {
        if (!empty($dataPenyakit['gambar'])) {
            // Pengecekan path tempat penyimpanan gambar
            $path1 = "../assets/images/penyakit/" . $dataPenyakit['gambar'];
            $path2 = "../assets/images/" . $dataPenyakit['gambar'];

            if (file_exists($path1)) {
                $gambarPenyakit = $path1;
            } elseif (file_exists($path2)) {
                $gambarPenyakit = $path2;
            } else {
                $gambarPenyakit = "../assets/images/penyakit/" . $dataPenyakit['gambar'];
            }
        }
        $deskripsi  = $dataPenyakit['deskripsi'];
        $gejalaUmum = $dataPenyakit['gejala_umum'];
        $penanganan = preg_split("/\r\n|\n|\r/", trim($dataPenyakit['penanganan']));
    }

    $note = "Jika sudah lebih dari 3 hari gejala tidak hilang atau balita anda belum juga membaik, maka disarankan untuk segera membawa balita anda ke fasilitas kesehatan terdekat untuk berkonsultasi dengan tenaga kesehatan.";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa - Sistem Pakar ISPA</title>

    <!-- Google Font & Bootstrap CSS -->
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
            background-color: #f4f7f6;
            color: #2c3e50;
            padding-top: 85px;
        }

        /* ====================================== 
           NAVBAR RESPONSIVE
           ====================================== */
        .navbar-custom {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 12px 0;
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
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
           STATUS BOX
           ====================================== */
        .status-box {
            padding: 16px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 15px;
        }

        .status-box.success {
            background-color: #EAF9EF;
            border: 1px solid #CFEEDB;
            color: #2E7D32;
        }

        .status-box.danger {
            background-color: #FFEBEE;
            border: 1px solid #FFCDD2;
            color: #C62828;
        }

        .status-box i {
            font-size: 22px;
            flex-shrink: 0;
        }

        /* ====================================== 
           CARD CUSTOM
           ====================================== */
        .card-custom {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eef2f5;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
            height: 100%;
        }

        .result-img-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 12px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .result-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .percentage-badge {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2E7D32;
            line-height: 1.2;
        }

        .category-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            color: #FFF;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* ====================================== 
           GEJALA & PENANGANAN
           ====================================== */
        .badge-gejala {
            display: inline-block;
            background: #EAF9EF;
            color: #2E7D32;
            border: 1px solid #CFEEDB;
            padding: 6px 14px;
            border-radius: 20px;
            margin-right: 6px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 500;
            word-break: break-word;
        }

        .penanganan-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .penanganan-list li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 12px;
            color: #444;
            font-size: 14px;
            line-height: 1.5;
        }

        .penanganan-list li:last-child {
            margin-bottom: 0;
        }

        .penanganan-list li i {
            color: #2E7D32;
            font-size: 18px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ====================================== 
           BUTTONS & NOTES
           ====================================== */
        .btn-green {
            background-color: #2E7D32;
            color: #ffffff;
            border: 2px solid #2E7D32;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-green:hover {
            background-color: #1E5E20;
            border-color: #1E5E20;
            color: #ffffff;
        }

        .btn-outline-green {
            background-color: transparent;
            color: #2E7D32;
            border: 2px solid #2E7D32;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-green:hover {
            background-color: #2E7D32;
            color: #ffffff;
        }

        .note-card {
            background: #ffffff;
            border-left: 4px solid #2E7D32;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        /* ====================================== 
           MEDIA QUERIES (RESPONSIVE)
           ====================================== */
        @media (max-width: 768px) {
            body {
                padding-top: 75px;
            }

            .card-custom {
                padding: 18px;
            }

            .percentage-badge {
                font-size: 1.8rem;
            }

            .result-img-wrapper {
                width: 75px;
                height: 75px;
            }

            .status-box {
                font-size: 14px;
                padding: 12px 16px;
            }
        }

        /* Print Media Style */
        @media print {
            body {
                padding-top: 0;
                background-color: #fff;
            }

            .navbar-custom,
            .action-buttons {
                display: none !important;
            }

            .card-custom {
                box-shadow: none;
                border: 1px solid #ddd;
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

        <!-- STATUS -->
        <div class="status-box <?= $status ? 'success' : 'danger'; ?>">
            <i class="bi <?= $status ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
            <span><?= $status ? 'Diagnosis berhasil dilakukan' : 'Sistem belum dapat memberikan Diagnosis'; ?></span>
        </div>

        <!-- HASIL DIAGNOSA -->
        <div class="row g-3 g-md-4 mb-4">
            <!-- KOTAK PENYAKIT TERIDENTIFIKASI -->
            <div class="col-12 col-md-7">
                <div class="card-custom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="result-img-wrapper">
                            <img src="<?= htmlspecialchars($gambarPenyakit); ?>" alt="<?= htmlspecialchars($penyakit); ?>">
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Penyakit Teridentifikasi</span>
                            <h2 class="h4 h3-md fw-bold text-success m-0"><?= htmlspecialchars($penyakit); ?></h2>
                            <?php if ($status): ?>
                                <span class="category-pill" style="background-color: <?= $warnaKategori; ?>;">
                                    Kategori: <?= htmlspecialchars($kategori); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOTAK PERSENTASE KEYAKINAN -->
            <div class="col-12 col-md-5">
                <div class="card-custom d-flex flex-column justify-content-center">
                    <span class="text-muted small d-block mb-1">Persentase Keyakinan</span>
                    <div class="percentage-badge">
                        <?= number_format($persentase, 2, ",", "."); ?>%
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMASI PENGGUNA & TANGGAL -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6">
                <div class="card-custom py-3">
                    <span class="text-muted small d-block mb-1">Nama Pengguna</span>
                    <strong class="fs-6 text-dark"><?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?></strong>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="card-custom py-3">
                    <span class="text-muted small d-block mb-1">Tanggal Diagnosa</span>
                    <strong class="fs-6 text-dark"><?= htmlspecialchars($tanggalDiagnosa); ?></strong>
                </div>
            </div>
        </div>

        <!-- GEJALA & PENANGANAN -->
        <div class="row g-3 g-md-4 mb-4">
            <!-- GEJALA -->
            <div class="col-12 col-lg-6">
                <div class="card-custom">
                    <h3 class="h5 fw-bold text-success mb-3">Gejala yang Dipilih</h3>
                    <div>
                        <?php if (!empty($gejalaDipilih)) : ?>
                            <?php foreach ($gejalaDipilih as $gejala) : ?>
                                <span class="badge-gejala">
                                    <?= htmlspecialchars($gejala['kode_gejala']); ?> - <?= htmlspecialchars($gejala['nama_gejala']); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span class="text-muted small">Tidak ada gejala dipilih.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PENANGANAN -->
            <div class="col-12 col-lg-6">
                <div class="card-custom">
                    <h3 class="h5 fw-bold text-success mb-3">Saran Penanganan</h3>
                    <ul class="penanganan-list">
                        <?php foreach ($penanganan as $item) : ?>
                            <?php if (!empty(trim($item))): ?>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span><?= htmlspecialchars($item); ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- BUTTONS ACTION -->
        <div class="row g-3 action-buttons mb-4">
            <div class="col-12 col-sm-6">
                <a href="informasi.php" class="btn btn-outline-green w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-info-circle"></i> Informasi Penyakit
                </a>
            </div>
            <div class="col-12 col-sm-6">
                <button onclick="window.print()" class="btn btn-green w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-printer"></i> Cetak Hasil
                </button>
            </div>
        </div>

        <!-- NOTE -->
        <div class="note-card">
            <h4 class="h6 fw-bold text-success mb-2"><i class="bi bi-exclamation-circle me-1"></i> Catatan</h4>
            <p class="small text-secondary mb-0">
                <?= htmlspecialchars($note); ?>
            </p>
        </div>

    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>