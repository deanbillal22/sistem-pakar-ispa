<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Cek Login User
if (!isset($_SESSION['login']) && !isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$idUser = $_SESSION['id_user'];

// Ambil Data User (untuk Foto Navbar)
$queryUser = mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = '$idUser'");
$user = mysqli_fetch_assoc($queryUser);

// Cek keberadaan foto profil user
$fotoUserPath = "";
if (!empty($user['foto'])) {
    if (file_exists("../assets/img/" . $user['foto'])) {
        $fotoUserPath = "../assets/img/" . $user['foto'];
    } elseif (file_exists("../assets/images/" . $user['foto'])) {
        $fotoUserPath = "../assets/images/" . $user['foto'];
    }
}

// ==============================
// Ambil Data Riwayat Diagnosis Spesifik User Ini
// ==============================
$queryRiwayat = mysqli_query($conn, "
    SELECT
        k.id_konsultasi,
        k.tanggal,
        h.id_hasil,
        h.id_penyakit,
        h.nilai_cf,
        h.persentase,
        p.nama_penyakit
    FROM tb_konsultasi k
    INNER JOIN tb_hasil_diagnosis h
        ON k.id_konsultasi = h.id_konsultasi
    INNER JOIN tb_penyakit p
        ON h.id_penyakit = p.id_penyakit
    WHERE k.id_user = '$idUser'
    ORDER BY
        k.tanggal DESC,
        h.persentase DESC
");

if (!$queryRiwayat) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosis - Sistem Pakar ISPA</title>

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

        .profile-avatar-nav {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2E7D32;
        }

        /* ====================================== 
           MAIN CONTENT & CARD 
           ====================================== */
        .card-custom {
            background: #FFFFFF;
            padding: 32px;
            border-radius: 18px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
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
            color: #1E5E20;
            margin: 0;
        }

        .info-header p {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
        }

        /* TABEL STYLING */
        .table-responsive {
            border-radius: 12px;
            border: 1px solid #E8F5E9;
        }

        .table-custom {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table-custom thead {
            background: #E8F5E9;
            color: #1E5E20;
        }

        .table-custom th {
            font-weight: 600;
            font-size: 14px;
            padding: 14px 16px;
            border: none;
            white-space: nowrap;
        }

        .table-custom td {
            padding: 14px 16px;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #F1F8E9;
            white-space: nowrap;
        }

        .table-custom tbody tr:hover {
            background-color: #FAFDFB;
        }

        /* BADGES */
        .badge-diagnosis {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
        }

        .badge-cf {
            background: #FFF3E0;
            color: #E65100;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12.5px;
        }

        /* TOMBOL AKSI USER */
        .btn-action-sm-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #2E7D32;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-action-sm-primary:hover {
            background: #1B5E20;
            color: white;
            transform: translateY(-1px);
        }

        .btn-action-sm-outline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6.5px 14px;
            border: 1.5px solid #2E7D32;
            background: #FFF;
            color: #2E7D32;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-action-sm-outline:hover {
            background: #2E7D32;
            color: white;
            transform: translateY(-1px);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #A5D6A7;
            margin-bottom: 12px;
        }

        .empty-state h4 {
            color: #1E5E20;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .empty-state p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-konsultasi-empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 18px;
            width: fit-content;
            white-space: nowrap;
            margin: 0 auto;
            background: #2E7D32;
            color: #FFFFFF;
            border-radius: 8px;
            font-weight: 500;
            font-size: 13.5px;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(46, 125, 50, 0.2);
            transition: all 0.2s ease-in-out;
        }

        .btn-konsultasi-empty:hover {
            background: #1B5E20;
            color: #FFFFFF;
            box-shadow: 0 4px 8px rgba(46, 125, 50, 0.3);
            transform: translateY(-1px);
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
                border-radius: 14px;
            }

            .info-header h2 {
                font-size: 22px;
            }

            .info-header p {
                font-size: 13.5px;
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
                        <a href="profil.php" class="profile-icon">
                            <?php if (!empty($fotoUserPath)) : ?>
                                <img src="<?= htmlspecialchars($fotoUserPath); ?>" alt="Foto Profil" class="profile-avatar-nav">
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
                <span class="sub-title"><i class="bi bi-clock-history me-1"></i> Rekam Diagnosis</span>
                <h2>Riwayat Diagnosis</h2>
                <p>Daftar seluruh hasil konsultasi diagnosis ISPA yang pernah Anda lakukan.</p>
            </div>

            <!-- Tabel Riwayat Diagnosis -->
            <?php if ($queryRiwayat && mysqli_num_rows($queryRiwayat) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th class="text-center" width="60">No</th>
                                <th>Tanggal Konsultasi</th>
                                <th>Nama</th>
                                <th>Hasil Diagnosis</th>
                                <th class="text-center">Tingkat Kepastian</th>
                                <th class="text-center" width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($queryRiwayat)) : 
                                $tanggal = date(
                                    'd M Y - H:i',
                                    strtotime($row['tanggal'])
                                );
                                $persentase = number_format(
                                    $row['persentase'],
                                    2,
                                    ",",
                                    "."
                                )." %";
                            ?>
                                <tr>
                                    <td class="text-center fw-medium"><?= $no++; ?></td>
                                    <td>
                                        <i class="bi bi-calendar3 text-success me-1"></i>
                                        <?= htmlspecialchars($tanggal); ?> WIB
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <span class="badge-diagnosis">
                                            <i class="bi bi-activity me-1"></i>
                                            <?= htmlspecialchars($row['nama_penyakit'] ?? 'Tidak Terdeteksi'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-cf"><?= htmlspecialchars($persentase); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="detail_riwayat.php?id=<?= $row['id_konsultasi']; ?>" class="btn-action-sm-outline" title="Lihat Detail Hasil">
                                                <i class="bi bi-eye-fill"></i> Detail
                                            </a>

                                            <a href="../proses/cetak_hasil.php?id=<?= $row['id_konsultasi']; ?>" target="_blank" class="btn-action-sm-primary" title="Cetak Hasil Diagnosis">
                                                <i class="bi bi-printer-fill"></i> Cetak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <!-- Tampilan Jika Belum Ada Riwayat -->
                <div class="empty-state">
                    <i class="bi bi-journal-x"></i>
                    <h4>Belum Ada Riwayat Diagnosis</h4>
                    <p>Anda belum pernah melakukan konsultasi diagnosis gejala ISPA untuk balita Anda.</p>
                    
                    <a href="konsultasi.php" class="btn-konsultasi-empty">
                        <i class="bi bi-plus-circle"></i> Mulai Konsultasi Baru
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>