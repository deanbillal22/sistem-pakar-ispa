<?php
session_start();
require_once "../config/koneksi.php";

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - Sistem Pakar ISPA</title>
    
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
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h2>Buat Password Baru</h2>
        </div>

        <div class="auth-body">
            <form action="../proses/pengguna/proses_reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru" required>
                </div>

                <div class="mb-4">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru" required>
                </div>

                <button type="submit" name="submit_reset" class="btn-submit">
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>

</body>
</html>