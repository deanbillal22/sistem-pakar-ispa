<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

// Jika pengguna sudah login, alihkan langsung ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Pakar ISPA</title>

    <!-- Google Fonts & Bootstrap 5 -->
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
            background-color: #F7F6F2;
            color: #1E5E20;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 440px;
            border-radius: 18px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .auth-header {
            background: #1E5E20;
            color: #FFFFFF;
            padding: 30px 24px;
            text-align: center;
        }

        .auth-header i {
            font-size: 38px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .auth-header h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .auth-body {
            padding: 30px 28px;
        }

        .auth-body p {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1E5E20;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #D1D5DB;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #2E7D32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2E7D32;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #236628;
            color: #FFFFFF;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 11px;
            background: transparent;
            color: #2E7D32;
            border: 1.5px solid #2E7D32;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            margin-top: 12px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #EAF9EF;
            color: #1E5E20;
        }

        .alert {
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="auth-card">
        <div class="auth-header">
            <i class="bi bi-key-fill"></i>
            <h2>Lupa Password</h2>
        </div>

        <div class="auth-body">
            <p class="text-center">Masukkan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan instruksi untuk mereset password.</p>

            <?php if (isset($_SESSION['error'])) : ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])) : ?>
                <div class="alert alert-success text-center">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="../proses/pengguna/proses_lupa_password.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required>
                </div>

                <button type="submit" name="submit_lupa" class="btn-submit">
                    <i class="bi bi-send me-1"></i> Kirim Link Reset
                </button>

                <a href="../login.php" class="btn-back">
                    <i class="bi bi-arrow-left-short fs-5"></i> Kembali ke Login
                </a>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>