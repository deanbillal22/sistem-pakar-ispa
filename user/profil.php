<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Login User
if (!isset($_SESSION['login']) && !isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$idUser = $_SESSION['id_user'];

// Ambil Data User
$queryUser = mysqli_query($conn, "
    SELECT *
    FROM tb_user
    WHERE id_user = '$idUser'
    LIMIT 1
");

if (!$queryUser) {
    die("Query Error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($queryUser);

// Penentuan Foto Profil Default / Foto Upload User
$foto = "../assets/img/default-avatar.png"; // Default fallback

if (!empty($user['foto'])) {
    if (file_exists("../assets/img/" . $user['foto'])) {
        $foto = "../assets/img/" . $user['foto'];
    } elseif (file_exists("../assets/images/" . $user['foto'])) {
        $foto = "../assets/images/" . $user['foto'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Sistem Pakar ISPA</title>

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

        /* ====================================== 
           MAIN CONTAINER & CARD STYLES
           ====================================== */
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

        /* HEADER JUDUL */
        .info-header {
            margin-bottom: 30px;
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

        /* SECTION TOP PROFIL */
        .profile-top {
            display: flex;
            align-items: center;
            gap: 32px;
            padding-bottom: 30px;
            border-bottom: 1px dashed #E0E8E0;
            margin-bottom: 30px;
        }

        .profile-avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .profile-top img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #E8F5E9;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.15);
        }

        .profile-name h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1E5E20;
            margin: 0;
        }

        .profile-name .email-text {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
            margin-bottom: 16px;
            display: block;
        }

        /* FORM UPLOAD FOTO */
        .upload-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .custom-file-input {
            font-size: 13px;
            color: #555;
            max-width: 100%;
        }

        .custom-file-input::file-selector-button {
            border: 1px solid #C8E6C9;
            padding: 6px 14px;
            border-radius: 8px;
            background-color: #E8F5E9;
            color: #2E7D32;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
            margin-right: 10px;
        }

        .custom-file-input::file-selector-button:hover {
            background-color: #C8E6C9;
        }

        .btn-upload {
            padding: 7px 18px;
            border: none;
            background: #2E7D32;
            color: white;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btn-upload:hover {
            background: #1B5E20;
            box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);
        }

        /* GRID INFORMASI USER */
        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .info-item-box {
            background: #FAFDFB;
            border: 1px solid #EAEAEA;
            padding: 16px 20px;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .info-icon {
            width: 38px;
            height: 38px;
            background: #E8F5E9;
            color: #2E7D32;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .info-content label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 2px;
        }

        .info-content span {
            font-size: 15px;
            font-weight: 600;
            color: #1E5E20;
            word-break: break-word;
        }

        /* BUTTON GROUP */
        .button-group {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-action-primary,
        .btn-action-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all .2s ease;
        }

        .btn-action-primary {
            background: #2E7D32;
            color: white;
            border: 1px solid #2E7D32;
        }

        .btn-action-primary:hover {
            background: #1B5E20;
            color: white;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
        }

        .btn-action-outline {
            background: transparent;
            color: #2E7D32;
            border: 1px solid #2E7D32;
        }

        .btn-action-outline:hover {
            background: #E8F5E9;
            color: #1B5E20;
        }

        /* MEDIA QUERIES (RESPONSIVE) */
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

            .profile-top {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }

            .upload-form {
                justify-content: center;
                flex-direction: column;
            }

            .custom-file-input {
                width: 100%;
            }

            .btn-upload {
                width: 100%;
                justify-content: center;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-action-primary,
            .btn-action-outline {
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
                    <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profil.php">Profil</a></li>
                    <li class="nav-item d-none d-lg-block ms-2">
                        <a href="profil.php">
                            <?php if ($foto !== "../assets/img/default-avatar.png") : ?>
                                <img src="<?= htmlspecialchars($foto); ?>" alt="Foto Profil" class="profile-avatar-nav">
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

            <!-- Header Profil -->
            <div class="info-header">
                <span class="sub-title"><i class="bi bi-person-badge-fill me-1"></i> Akun Saya</span>
                <h2>Profil Pengguna</h2>
                <p>Informasi detail akun pengguna Sistem Pakar Diagnosis ISPA.</p>
            </div>

            <!-- Profil Top (Foto & Upload Form) -->
            <div class="profile-top">
                <div class="profile-avatar-wrapper">
                    <img src="<?= htmlspecialchars($foto); ?>" alt="Foto Profil">
                </div>

                <div class="profile-name">
                    <h3><?= htmlspecialchars($user['nama_lengkap'] ?? '-'); ?></h3>
                    <span class="email-text"><?= htmlspecialchars($user['email'] ?? '-'); ?></span>

                    <form action="../proses/upload_foto.php" method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="file" name="foto" class="form-control form-control-sm custom-file-input" accept=".jpg,.jpeg,.png" required>
                        <button type="submit" class="btn-upload">
                            <i class="bi bi-camera-fill"></i> Upload Foto
                        </button>
                    </form>
                </div>
            </div>

            <!-- Detail Informasi Profil -->
            <div class="profile-info-grid">

                <div class="info-item-box">
                    <div class="info-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="info-content">
                        <label>Username</label>
                        <span><?= htmlspecialchars($user['username'] ?? '-'); ?></span>
                    </div>
                </div>

                <div class="info-item-box">
                    <div class="info-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="info-content">
                        <label>Nomor HP</label>
                        <span><?= !empty($user['no_hp']) ? htmlspecialchars($user['no_hp']) : '-'; ?></span>
                    </div>
                </div>

                <div class="info-item-box">
                    <div class="info-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="info-content">
                        <label>Alamat</label>
                        <span><?= !empty($user['alamat']) ? htmlspecialchars($user['alamat']) : '-'; ?></span>
                    </div>
                </div>

                <div class="info-item-box">
                    <div class="info-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div class="info-content">
                        <label>Role</label>
                        <span><?= ucfirst(htmlspecialchars($user['role'] ?? 'User')); ?></span>
                    </div>
                </div>

                <div class="info-item-box">
                    <div class="info-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="info-content">
                        <label>Bergabung Sejak</label>
                        <span><?= !empty($user['created_at']) ? date('d F Y', strtotime($user['created_at'])) : '-'; ?></span>
                    </div>
                </div>

            </div>

            <!-- Tombol Aksi -->
            <div class="button-group">
                <a href="edit_profil.php" class="btn-action-primary">
                    <i class="bi bi-pencil-square"></i> Edit Profil
                </a>
                <a href="ubah_password.php" class="btn-action-outline">
                    <i class="bi bi-key-fill"></i> Ganti Password
                </a>
            </div>

        </div>

    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>