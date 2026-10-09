<?php
session_start();

class LoginView {
    private $errorMessage;

    public function __construct() {
        // Mengambil pesan error dari session jika ada
        if (isset($_SESSION['error'])) {
            $this->errorMessage = $_SESSION['error'];
            unset($_SESSION['error']); // Hapus session error setelah diambil
        }
    }

    // Method untuk mengecek apakah ada error
    public function hasError() {
        return !empty($this->errorMessage);
    }

    // Method untuk mengambil pesan error secara aman (htmlspecialchars)
    public function getErrorMessage() {
        return htmlspecialchars($this->errorMessage);
    }
}

// Inisialisasi Objek LoginView
$view = new LoginView();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Pakar ISPA</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="login-page">

        <div class="login-card">

            <!-- LEFT: Gambar Ilustrasi -->
            <div class="login-left">
                <img src="assets/images/register.png" alt="Login Illustration">
            </div>

            <!-- RIGHT: Form Login -->
            <div class="login-right">

                <!-- Logo -->
                <div class="logo-area">
                    <i class="bi bi-lungs-fill"></i>
                    <div>
                        <h5>Sistem Pakar ISPA</h5>
                        <span>pada Balita</span>
                    </div>
                </div>

                <h2>Selamat Datang!</h2>
                <p>Silakan login untuk melanjutkan ke sistem.</p>

                <!-- Menampilkan Notifikasi Error via Method Objek OOP -->
                <?php if ($view->hasError()) : ?>
                    <div class="alert alert-danger alert-dismissible fade show text-start mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $view->getErrorMessage(); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="proses/proses_login.php" method="POST">

                    <div class="mb-3 text-start">
                        <label for="username" class="form-label">Email atau Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan email atau username" required autocomplete="username" onkeypress="if(event.which === 32) return false;" oninput="this.value = this.value.replace(/\s/g, '');">
                    </div>

                    <div class="mb-2 text-start">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" class="btn-toggle-password" id="togglePassword" aria-label="Tampilkan Password">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-end mb-4">
                        <a href="user/lupa_password.php" class="forgot-link">Lupa Password?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Login
                    </button>

                    <div class="register-link">
                        Belum punya akun? <a href="register.php">Daftar di sini</a>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Toggle Password -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (togglePasswordBtn && passwordInput && toggleIcon) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    
                    // Toggle tipe input
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    // Toggle icon
                    toggleIcon.classList.toggle('bi-eye', !isPassword);
                    toggleIcon.classList.toggle('bi-eye-slash', isPassword);
                });
            }
        });
    </script>

</body>

</html>