<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);

// Foto default
$foto = "../assets/images/.png";

if (
    isset($user['foto']) &&
    !empty($user['foto']) &&
    file_exists("../assets/images/profile/" . $user['foto'])
) {
    $foto = "../assets/images/profile/" . $user['foto'];
}

?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">

    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center fw-bold text-success"
           href="../user/dashboard.php">

            <img src="../assets/images/logo.png"
                 alt="Logo"
                 width="42"
                 class="me-2">

            ISPA Expert

        </a>

        <!-- Toggle -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active fw-bold text-success' : ''; ?>"
                       href="../user/dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'konsultasi.php') ? 'active fw-bold text-success' : ''; ?>"
                       href="../user/konsultasi.php">
                        Konsultasi
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'riwayat.php') ? 'active fw-bold text-success' : ''; ?>"
                       href="../user/riwayat.php">
                        Riwayat
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentPage, ['profil.php','edit_profil.php','ganti_password.php']) ? 'active fw-bold text-success' : ''; ?>"
                       href="../user/profil.php">
                        Profil
                    </a>
                </li>

                <li class="nav-item ms-lg-3">
                    <a href="../user/profil.php">

                        <img src="<?= $foto; ?>"
                             alt="Foto Profil"
                             class="rounded-circle"
                             style="width:45px;height:45px;object-fit:cover;border:2px solid #2F9D94;">

                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>