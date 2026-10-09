<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

// Penanganan variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil ID User 
$idUser = $_SESSION['id_user'] ?? null;

if (!$idUser) {
    header("Location: ../login.php");
    exit;
}

// Ambil Data User menggunakan Prepared Statement
$stmt = mysqli_prepare($conn, "SELECT * FROM tb_user WHERE id_user = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$queryUser = mysqli_stmt_get_result($stmt);

if (!$queryUser) {
    die("Query Error: " . mysqli_error($conn));
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
    <title>Edit Profil - Sistem Pakar ISPA</title>

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

        /* FORM STYLING RESPONSIVE */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-control {
            width: 100%;
            padding: 12px 16px;
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

        textarea.form-control {
            resize: vertical;
        }

        /* BUTTON GROUP */
        .button-group {
            display: flex;
            gap: 14px;
            margin-top: 10px;
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

            <!-- Header Edit Profil -->
            <div class="info-header">
                <span class="sub-title"><i class="bi bi-pencil-square me-1"></i> Pengaturan Akun</span>
                <h2>Edit Profil</h2>
                <p>Perbarui informasi biodata akun Anda di bawah ini.</p>
            </div>

            <!-- Form Edit Profil -->
            <form action="../proses/update_profil.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="id_user" value="<?= htmlspecialchars($user['id_user']); ?>">

                <div class="form-grid">

                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label><i class="bi bi-person-vcard-fill"></i> Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label><i class="bi bi-person-fill"></i> Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label><i class="bi bi-envelope-fill"></i> Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <!-- Nomor HP -->
                    <div class="form-group">
                        <label for="no_hp">Nomor HP</label>
                        <input 
                            type="tel" 
                            name="no_hp" 
                            id="no_hp" 
                            class="form-control" 
                            value="<?= htmlspecialchars($user['no_hp']); ?>" 
                            pattern="[0-9]+" 
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="Contoh: 08123456789"
                            required>
                    </div>

                    <!-- Foto Profil -->
                    <div class="form-group full-width">
                        <label><i class="bi bi-image-fill"></i> Foto Profil (Opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        <small class="text-muted d-block mt-1">Format: JPG, JPEG, PNG. Maksimal 500KB. Kosongkan jika tidak ingin mengubah foto.</small>
                    </div>

                    <!-- Alamat -->
                    <div class="form-group full-width">
                        <label><i class="bi bi-geo-alt-fill"></i> Alamat</label>
                        <textarea name="alamat" rows="3" class="form-control"><?= htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
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

</body>

</html>