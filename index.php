<?php
// Pastikan koneksi/konfigurasi sistem dimuat
require_once 'config/koneksi.php';

class LandingPage {
    private $title;
    private $year;

    public function __construct($title = "Sistem Pakar Diagnosis ISPA Pada Balita") {
        $this->title = $title;
        $this->year = date('Y');
    }

    public function getTitle() {
        return $this->title;
    }

    public function getYear() {
        return $this->year;
    }
}

// Inisialisasi Objek LandingPage
$page = new LandingPage();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->getTitle(); ?></title>

    <!-- Google Font & Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /*==================================================
        RESET & GENERAL
        ==================================================*/
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background: #F7F6F2;
            color: #334155;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /*==================================================
        NAVBAR
        ==================================================*/
        header {
            width: 100%;
            background: #FFFFFF;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .navbar {
            height: 82px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo i {
            font-size: 32px;
            color: #2E7D32;
        }

        .logo h2 {
            font-size: 18px;
            color: #1E5E20;
            font-weight: 700;
            line-height: 1.2;
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            gap: 36px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .menu li a {
            color: #555;
            font-size: 15px;
            font-weight: 500;
            transition: .2s;
            position: relative;
            padding-bottom: 4px;
        }

        .menu li a:hover,
        .menu li a.active {
            color: #2E7D32;
            font-weight: 600;
        }

        .menu li a.active::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -18px;
            width: 100%;
            height: 3px;
            background: #2E7D32;
            border-radius: 3px 3px 0 0;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            background: #2E7D32;
            color: #FFF;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: .2s;
        }

        .btn-login:hover {
            background: #236628;
            color: #FFF;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            color: #1E5E20;
            cursor: pointer;
        }

        /*==================================================
        HERO
        ==================================================*/
        .hero {
            padding: 60px 0;
            background: #FFFFFF;
            border-bottom: 1px solid #EAEAEA;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 50px;
        }

        .hero-badge {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 30px;
            background: #EAF9EF;
            color: #2E7D32;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #C8E6C9;
        }

        .hero-left h1 {
            font-size: 42px;
            color: #1E5E20;
            line-height: 1.25;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .hero-left h1 span {
            color: #2E7D32;
        }

        .hero-left p {
            font-size: 16px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .hero-button {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #2E7D32;
            color: #FFF;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: .2s;
        }

        .btn-primary:hover {
            background: #236628;
            color: #FFF;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.25);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            border: 1.5px solid #2E7D32;
            color: #2E7D32;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: .2s;
        }

        .btn-outline:hover {
            background: #2E7D32;
            color: #FFF;
        }

        .hero-right {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-right img {
            width: 100%;
            max-width: 450px;
            border-radius: 18px;
            animation: floatImage 4s ease-in-out infinite;
        }

        @keyframes floatImage {
            0% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0); }
        }

        /*==================================================
        FEATURE
        ==================================================*/
        .feature {
            padding: 70px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .feature-card {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: .3s;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 18px;
            border-radius: 14px;
            background: #EAF9EF;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #2E7D32;
            font-size: 26px;
            transition: .3s;
        }

        .feature-card h3 {
            color: #1E5E20;
            margin-bottom: 8px;
            font-size: 18px;
            font-weight: 700;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            font-size: 13.5px;
        }

        /*==================================================
        ABOUT
        ==================================================*/
        .about {
            padding: 70px 0;
            background: #FFFFFF;
            border-top: 1px solid #EAEAEA;
            border-bottom: 1px solid #EAEAEA;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            align-items: center;
            gap: 50px;
        }

        .about-img-wrapper {
            display: flex;
            justify-content: center;
        }

        .about img {
            width: 100%;
            max-width: 380px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .about span {
            display: inline-block;
            background: #EAF9EF;
            color: #2E7D32;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            border: 1px solid #C8E6C9;
        }

        .about h2 {
            color: #1E5E20;
            font-size: 32px;
            margin-bottom: 16px;
            line-height: 1.3;
            font-weight: 700;
        }

        .about p {
            color: #666;
            line-height: 1.7;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .about ul {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .about li {
            color: #1E5E20;
            font-weight: 600;
            font-size: 14.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .about li i {
            color: #2E7D32;
            font-size: 18px;
        }

        /*==================================================
        CTA
        ==================================================*/
        .cta {
            padding: 70px 0;
        }

        .cta-box {
            background: #EAF9EF;
            border: 1px solid #C8E6C9;
            border-radius: 18px;
            padding: 50px 30px;
            text-align: center;
            color: #1E5E20;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .cta-box h2 {
            font-size: 32px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .cta-box p {
            font-size: 15px;
            color: #444;
            margin-bottom: 26px;
        }

        .cta-box a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            background: #2E7D32;
            color: #FFFFFF;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: .2s;
        }

        .cta-box a:hover {
            background: #236628;
            color: #FFFFFF;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.25);
        }

        /*==================================================
        FOOTER
        ==================================================*/
        footer {
            background: #1E5E20;
            padding: 35px 0;
            color: #FFFFFF;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 8px;
        }

        .footer-content h3 {
            color: #FFFFFF;
            margin-bottom: 4px;
            font-size: 18px;
            font-weight: 700;
        }

        .footer-content p {
            color: #E8F5E9;
            font-size: 13.5px;
            line-height: 1.6;
            margin: 0;
        }

        /*==================================================
        SCROLLBAR & HOVER
        ==================================================*/
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #2E7D32;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #F7F6F2;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.08);
        }

        .about img:hover,
        .hero-right img:hover {
            transform: scale(1.02);
            transition: .3s;
        }

        /*==================================================
        RESPONSIVE BREAKPOINTS
        ==================================================*/
        @media (max-width: 992px) {
            .hero-left h1 {
                font-size: 34px;
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .about ul {
                justify-content: center;
            }

            .about li {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .nav-wrapper {
                position: absolute;
                top: 82px;
                left: 0;
                width: 100%;
                background: #FFFFFF;
                flex-direction: column;
                padding: 24px;
                border-bottom: 1px solid #EAEAEA;
                box-shadow: 0 10px 15px rgba(0,0,0,0.05);
                display: none;
                gap: 20px;
            }

            .nav-wrapper.show {
                display: flex;
            }

            .menu {
                flex-direction: column;
                gap: 16px;
                width: 100%;
                text-align: center;
            }

            .menu li a.active::after {
                display: none;
            }

            .btn-login {
                width: 100%;
            }

            .hero {
                padding: 40px 0;
            }

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }

            .hero-right {
                order: -1;
            }

            .hero-right img {
                max-width: 280px;
            }

            .hero-button {
                justify-content: center;
            }

            .btn-primary, .btn-outline {
                width: 100%;
                justify-content: center;
            }

            .cta-box h2 {
                font-size: 24px;
            }

            .about h2 {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .about ul {
                grid-template-columns: 1fr;
            }

            .hero-left h1 {
                font-size: 28px;
            }

            .cta-box {
                padding: 35px 20px;
            }

            .cta-box a {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <i class="bi bi-lungs-fill"></i>
                    <div>
                        <h2>Sistem Pakar ISPA</h2>
                    </div>
                </div>

                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                    <i class="bi bi-list"></i>
                </button>

                <div class="nav-wrapper" id="navWrapper">
                    <ul class="menu">
                        <li><a class="active" href="#">Beranda</a></li>
                        <li><a href="login.php">Konsultasi</a></li>
                        <li><a href="login.php">Riwayat</a></li>
                        <li><a href="login.php">Profil</a></li>
                    </ul>

                    <div class="nav-button">
                        <a href="login.php" class="btn-login">Login</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-left">
                <span class="hero-badge">Sistem Pakar Infeksi Saluran Pernapasan Akut</span>
                <h1>Sistem Pakar <span>Diagnosis ISPA</span> Pada Balita</h1>
                <p>Membantu orang tua melakukan deteksi dini penyakit <strong>ISPA Saluran Pernapasan Bawah</strong> pada <strong>balita</strong> berdasarkan gejala yang dialami oleh buah hati Anda.</p>
                
                <div class="hero-button">
                    <a href="login.php" class="btn-primary">
                        Mulai Konsultasi <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#tentang" class="btn-outline">Pelajari Sistem</a>
                </div>
            </div>

            <div class="hero-right">
                <img src="./assets/images/hero.png" alt="Hero Image">
            </div>
        </div>
    </section>

    <!-- FEATURE SECTION -->
    <section class="feature">
        <div class="container">
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>Akurat</h3>
                    <p>Menggunakan metode Penalaran dan Tingkat Keyakinan.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                    <h3>Cepat</h3>
                    <p>Diagnosis dilakukan dalam hitungan detik.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-display"></i></div>
                    <h3>Mudah</h3>
                    <p>Tampilan sederhana sehingga mudah digunakan.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-journal-medical"></i></div>
                    <h3>Edukatif</h3>
                    <p>Menampilkan informasi penyakit dan saran penanganan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="tentang" class="about">
        <div class="container about-grid">
            <div class="about-img-wrapper">
                <img src="./assets/images/paruparu.jpg" alt="Tentang Sistem">
            </div>

            <div>
                <span>Tentang Sistem</span>
                <h2>Kenali Sistem Pakar Diagnosis ISPA</h2>
                <p>Sistem ini dirancang untuk membantu masyarakat dalam melakukan diagnosis awal penyakit ISPA berdasarkan gejala yang dipilih.</p>

                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Penalaran</li>
                    <li><i class="bi bi-check-circle-fill"></i> Tingkat Keyakinan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Informasi Penyakit</li>
                    <li><i class="bi bi-check-circle-fill"></i> Riwayat Konsultasi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Saran Penanganan</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta">
        <div class="container">
            <div class="cta-box">
                <h2>Mulai Diagnosis Sekarang</h2>
                <p>Temukan kemungkinan penyakit ISPA pada balita secara cepat.</p>
                <a href="login.php">Mulai Konsultasi</a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container footer-content">
            <h3>Sistem Pakar ISPA</h3>
            <p>© <?= $page->getYear(); ?> Sistem Pakar Diagnosis ISPA Pada Balita.</p>
        </div>
    </footer>

    <!-- SCRIPT MOBILE MENU TOGGLE -->
    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const navWrapper = document.getElementById('navWrapper');
        const toggleIcon = mobileToggle.querySelector('i');

        mobileToggle.addEventListener('click', () => {
            navWrapper.classList.toggle('show');
            if(navWrapper.classList.contains('show')) {
                toggleIcon.classList.remove('bi-list');
                toggleIcon.classList.add('bi-x-lg');
            } else {
                toggleIcon.classList.remove('bi-x-lg');
                toggleIcon.classList.add('bi-list');
            }
        });
    </script>
</body>

</html>