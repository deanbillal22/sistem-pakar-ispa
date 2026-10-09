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
   AMBIL ID USER DARI SESSION
========================================================== */
$idUser = $_SESSION['id_user'] ?? null;

if (!$idUser) {
    header("Location: ../login.php");
    exit;
}

/* ==========================================================
   AMBIL DATA USER (Prepared Statement)
========================================================== */
$stmtUser = mysqli_prepare($conn, "SELECT * FROM tb_user WHERE id_user = ? LIMIT 1");
mysqli_stmt_bind_param($stmtUser, "i", $idUser);
mysqli_stmt_execute($stmtUser);
$queryUser = mysqli_stmt_get_result($stmtUser);

if (!$queryUser) {
    die("Query Error : " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($queryUser);

if (!$user) {
    die("Data user tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - Sistem Pakar ISPA</title>

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
            max-width: 650px;
            margin: 0 auto;
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
            margin-bottom: 28px;
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
            font-size: 26px;
            font-weight: 700;
            color: #1E5E20;
            margin: 0;
        }

        .info-header p {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
        }

        /* FORM STYLING RESPONSIVE */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #1E5E20;
        }

        label i {
            color: #2E7D32;
            font-size: 15px;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            padding: 12px 42px 12px 16px;
            border: 1px solid #C8E6C9;
            background: #FAFDFB;
            border-radius: 10px;
            font-size: 14.5px;
            color: #333;
            outline: none;
            transition: all .2s ease;
        }

        .form-control:focus {
            border-color: #2E7D32;
            background: #FFF;
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            cursor: pointer;
            color: #666;
            font-size: 18px;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #2E7D32;
        }

        /* BUTTON GROUP */
        .button-group {
            display: flex;
            gap: 14px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-action-primary,
        .btn-action-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14.5px;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btn-action-primary {
            background: #2E7D32;
            color: white;
            border: none;
        }

        .btn-action-primary:hover {
            background: #1B5E20;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
            transform: translateY(-1px);
        }

        .btn-action-outline {
            background: transparent;
            color: #2E7D32;
            border: 1px solid #2E7D32;
        }

        .btn-action-outline:hover {
            background: #E8F5E9;
            color: #1B5E20;
            transform: translateY(-1px);
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

            <!-- Header Ubah Password -->
            <div class="info-header">
                <span class="sub-title"><i class="bi bi-shield-lock-fill me-1"></i> Keamanan Akun</span>
                <h2>Ganti Password</h2>
                <p>Silakan masukkan password lama Anda dan buat password baru yang aman.</p>
            </div>

            <!-- Pesan Notifikasi -->
            <?php if (isset($_SESSION['error'])) : ?>
                <div class="alert alert-danger alert-dismissible fade show text-center mb-4" role="alert">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])) : ?>
                <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Ganti Password -->
            <form action="../proses/update_password.php" method="POST">

                <!-- Password Lama -->
                <div class="form-group">
                    <label><i class="bi bi-key-fill"></i> Password Lama</label>
                    <div class="input-group-custom">
                        <input type="password" name="password_lama" id="pwd_lama" class="form-control" placeholder="Masukkan password lama" required>
                        <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('pwd_lama', this)"></i>
                    </div>
                </div>

                <!-- Password Baru -->
                <div class="form-group">
                    <label><i class="bi bi-lock-fill"></i> Password Baru</label>
                    <div class="input-group-custom">
                        <input type="password" name="password_baru" id="pwd_baru" class="form-control" placeholder="Masukkan password baru" required>
                        <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('pwd_baru', this)"></i>
                    </div>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="form-group">
                    <label><i class="bi bi-check2-square"></i> Konfirmasi Password Baru</label>
                    <div class="input-group-custom">
                        <input type="password" name="konfirmasi_password" id="pwd_konfirmasi" class="form-control" placeholder="Ulangi password baru" required>
                        <i class="bi bi-eye-slash toggle-password" onclick="toggleVisibility('pwd_konfirmasi', this)"></i>
                    </div>
                </div>

                <!-- Button Group -->
                <div class="button-group">
                    <button type="submit" class="btn-action-primary">
                        <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
                    </button>

                    <a href="profil.php" class="btn-action-outline">
                        <i class="bi bi-x-circle-fill"></i> Batal
                    </a>
                </div>

            </form>

        </div>

    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Toggle View/Hide Password -->
    <script>
        function toggleVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>

</body>

</html>