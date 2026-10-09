<?php
session_start();

// Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$idUser   = $_SESSION['id_user'];
$namaUser = $_SESSION['nama'] ?? "User";

// Ambil Data Gejala
$queryGejala = mysqli_query($conn, "
    SELECT *
    FROM tb_gejala
    ORDER BY kode_gejala ASC
");

// Ambil Data User
$queryUser = mysqli_query($conn, "
    SELECT *
    FROM tb_user
    WHERE id_user = '$idUser'
");

$user = mysqli_fetch_assoc($queryUser);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi Diagnosa - Sistem Pakar ISPA</title>

    <!-- Google Font & Bootstrap CSS -->
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
        }

        /* ====================================== 
           NAVBAR (RESPONSIF)
           ====================================== */
        .navbar-custom {
            background: #FFFFFF;
            padding: 12px 0;
            border-bottom: 1px solid #EAEAEA;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .navbar-custom .container {
            max-width: 1080px;
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

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-text strong {
            font-size: 16px;
            font-weight: 700;
            color: #1E5E20;
        }

        .brand-text small {
            font-size: 12px;
            color: #666;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-menu a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 15px;
            transition: .2s;
            position: relative;
            padding-bottom: 4px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #2E7D32;
            font-weight: 600;
        }

        .nav-menu a.active::after {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #2E7D32;
            border-radius: 3px 3px 0 0;
        }

        .profile-icon {
            font-size: 24px;
            color: #1E5E20;
            display: flex;
            align-items: center;
        }

        /* ====================================== 
           MAIN CONTAINER
           ====================================== */
        .main {
            max-width: 1080px;
            width: 100%;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .breadcrumb-custom {
            font-size: 14px;
            margin-bottom: 15px;
            color: #888;
        }

        .breadcrumb-custom span {
            color: #2E7D32;
            font-weight: 600;
        }

        .header {
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1E5E20;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 14.5px;
            line-height: 1.6;
            margin: 0;
        }

        .card-custom {
            background: #FFFFFF;
            padding: 32px;
            border-radius: 18px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .card-custom h3 {
            margin-bottom: 18px;
            font-size: 20px;
            font-weight: 700;
            color: #1E5E20;
        }

        .info-box {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 16px 20px;
            margin-bottom: 25px;
            background: #EAF9EF;
            border-left: 4px solid #2E7D32;
            border-radius: 10px;
        }

        .info-box i {
            font-size: 22px;
            color: #2E7D32;
            margin-top: 2px;
        }

        .info-box p {
            font-size: 14px;
            color: #1E5E20;
            margin: 0;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #1E5E20;
            font-size: 15px;
        }

        .form-control-custom {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #DDD;
            border-radius: 10px;
            font-size: 14px;
            color: #1E5E20;
            outline: none;
            transition: .2s;
            background-color: #FFF;
        }

        .form-control-custom:focus {
            border-color: #2E7D32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
        }

        .divider {
            border: 0;
            border-top: 1px solid #EAEAEA;
            margin: 30px 0;
        }

        /* ====================================== 
           TABLE GEJALA & BUTTONS
           ====================================== */
        .table-gejala {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 12px;
            border: 1px solid #EAEAEA;
        }

        .table-gejala table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .table-gejala th {
            background: #2E7D32;
            color: #FFFFFF;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }

        .table-gejala td {
            padding: 14px 16px;
            border-bottom: 1px solid #EAEAEA;
            vertical-align: middle;
            font-size: 14px;
            color: #333;
        }

        .table-gejala tr:last-child td {
            border-bottom: none;
        }

        .table-gejala tr:hover {
            background: #F4FBF5;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            justify-content: flex-end;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            background: #2E7D32;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14.5px;
            cursor: pointer;
            transition: .2s;
        }

        .btn-submit:hover {
            background: #236628;
            color: white;
        }

        .btn-secondary-custom {
            background: #E0E0E0;
            color: #333;
        }

        .btn-secondary-custom:hover {
            background: #D0D0D0;
            color: #111;
        }

        /* ====================================== 
           MEDIA QUERIES (RESPONSIVE)
           ====================================== */
        @media (max-width: 991px) {
            .nav-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 16px 0;
            }

            .nav-menu a.active::after {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .main {
                margin-top: 85px;
                padding: 0 15px;
            }

            .header h1 {
                font-size: 24px;
            }

            .card-custom {
                padding: 20px;
            }

            .action-buttons {
                flex-direction: column-reverse;
            }

            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand-custom">
                <i class="bi bi-lungs-fill"></i>
                <div class="brand-text">
                    <strong>Sistem Pakar ISPA</strong>
                    <small>pada Balita</small>
                </div>
            </a>

            <!-- Tombol Hamburger Mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu Navigation -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Beranda</a></li>
                    <li><a href="konsultasi.php" class="active">Konsultasi</a></li>
                    <li><a href="riwayat.php">Riwayat</a></li>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- Breadcrumb -->
        <div class="breadcrumb-custom">
            Beranda > <span>Konsultasi</span>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>Konsultasi Diagnosa ISPA</h1>
            <p>Pilih gejala yang dirasakan oleh balita beserta tingkat keyakinannya untuk menghitung estimasi penyakit.</p>
        </div>

        <!-- Card Form -->
        <div class="card-custom">
            <h3>Data Pengguna</h3>

            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <p>Silakan pilih gejala yang sedang dialami balita sesuai dengan kondisi sebenarnya di bawah ini.</p>
            </div>

            <form action="../proses/proses_diagnosa.php" method="POST" id="formDiagnosa">
                
                <div class="form-group">
                    <label>Nama Pengguna</label>
                    <input
                        type="text"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($user['nama_lengkap'] ?? $namaUser); ?>"
                        readonly>

                    <input
                        type="hidden"
                        name="id_user"
                        value="<?= $idUser; ?>">
                </div>

                <hr class="divider">

                <h3>Pilih Gejala Yang Dialami</h3>
                <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                    Pilih gejala yang sedang dialami beserta tingkat keyakinan Anda.
                </p>

                <!-- Tabel Gejala -->
                <div class="table-gejala">
                    <table>
                        <thead>
                            <tr>
                                <th width="8%">No</th>
                                <th width="15%">Kode</th>
                                <th>Gejala</th>
                                <th width="30%">Tingkat Keyakinan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while($gejala = mysqli_fetch_assoc($queryGejala)){
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td style="text-align: center; font-weight: 600; color: #2E7D32;"><?= htmlspecialchars($gejala['kode_gejala']); ?></td>
                                <td><?= htmlspecialchars($gejala['nama_gejala']); ?></td>
                                <td>
                                    <select name="cf_user[<?= $gejala['id_gejala']; ?>]" class="form-control-custom select-cf">
                                        <option value="0">Tidak Dipilih</option>
                                        <option value="0.2">Kurang Yakin</option>
                                        <option value="0.4">Sedikit Yakin</option>
                                        <option value="0.6">Cukup Yakin</option>
                                        <option value="0.8">Yakin</option>
                                        <option value="1">Sangat Yakin</option>
                                    </select>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tombol Aksi -->
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn-submit btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        Kembali
                    </a>
                    
                    <button type="reset" class="btn-submit btn-secondary-custom">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset Pilihan
                    </button>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-search"></i>
                        Proses Diagnosa
                    </button>
                </div>

            </form>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi Frontend agar user memilih minimal 1 gejala
        document.getElementById('formDiagnosa').addEventListener('submit', function(e) {
            const selects = document.querySelectorAll('.select-cf');
            let selectedCount = 0;

            selects.forEach(select => {
                if (parseFloat(select.value) > 0) {
                    selectedCount++;
                }
            });

            if (selectedCount === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 gejala yang dialami balita sebelum melanjutkan!');
            }
        });
    </script>
</body>
</html>