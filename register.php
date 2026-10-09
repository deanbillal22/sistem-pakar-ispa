<?php
session_start();

class RegisterView {
    private $errorMessage;

    public function __construct() {
        // Mengambil pesan error dari session jika ada
        if (isset($_SESSION['error'])) {
            $this->errorMessage = $_SESSION['error'];
            unset($_SESSION['error']); // Hapus session error setelah dibaca
        }
    }

    // Method mengecek keberadaan error
    public function hasError() {
        return !empty($this->errorMessage);
    }

    // Method mengambil pesan error dengan sanitasi HTML
    public function getErrorMessage() {
        return htmlspecialchars($this->errorMessage);
    }
}

// Inisialisasi Objek RegisterView
$view = new RegisterView();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi | Sistem Pakar ISPA</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/register1.css">
</head>

<body>

    <div class="register-page">

        <div class="register-card">

            <!-- LEFT: Ilustrasi Gambar -->
            <div class="register-left">
                <img src="assets/images/register.png" alt="Register Illustration">
            </div>

            <!-- RIGHT: Form Registrasi -->
            <div class="register-right">

                <!-- Logo Area -->
                <div class="logo-area">
                    <i class="bi bi-lungs-fill"></i>
                    <div>
                        <h5>Sistem Pakar ISPA</h5>
                        <span>pada Balita</span>
                    </div>
                </div>

                <h2>Buat Akun Baru</h2>
                <p>Silakan lengkapi data diri Anda untuk membuat akun.</p>

                <!-- Menampilkan Pesan Error -->
                <?php if ($view->hasError()) : ?>
                    <div class="alert alert-danger alert-dismissible fade show text-start mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $view->getErrorMessage(); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="proses/daftar.php" method="POST">

                    <div class="form-group mb-2 text-start">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group mb-2 text-start">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
                    </div>

                    <div class="form-group mb-2 text-start">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="username" onkeypress="if(event.which === 32) return false;" oninput="this.value = this.value.replace(/\s/g, '');">
                    </div>

                    <div class="form-group mb-2 text-start">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="new-password">
                            <button type="button" class="btn-toggle-password" id="togglePassword1" aria-label="Tampilkan Password">
                                <i class="bi bi-eye" id="iconPassword1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi password" required autocomplete="new-password">
                            <button type="button" class="btn-toggle-password" id="togglePassword2" aria-label="Tampilkan Konfirmasi Password">
                                <i class="bi bi-eye" id="iconPassword2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        Daftar
                    </button>

                    <div class="login-link">
                        Sudah punya akun? <a href="login.php">Login di sini</a>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- Bootstrap JS & Toggle Password Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupPasswordToggle(toggleId, inputId, iconId) {
                const toggleBtn = document.getElementById(toggleId);
                const passwordInput = document.getElementById(inputId);
                const icon = document.getElementById(iconId);

                if (toggleBtn && passwordInput && icon) {
                    toggleBtn.addEventListener('click', function() {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        
                        icon.classList.toggle('bi-eye', !isPassword);
                        icon.classList.toggle('bi-eye-slash', isPassword);
                    });
                }
            }

            setupPasswordToggle('togglePassword1', 'password', 'iconPassword1');
            setupPasswordToggle('togglePassword2', 'konfirmasi_password', 'iconPassword2');
        });
    </script>

</body>

</html>