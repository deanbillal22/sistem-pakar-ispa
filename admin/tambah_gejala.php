<?php
ob_start(); // Mencegah error "headers already sent"
session_start();
require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi ($conn / $koneksi)
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$error = "";
$kode_gejala_input = "";
$nama_gejala_input = "";

// Auto-Generate Kode Gejala Otomatis (Opsional)
$query_next_code = mysqli_query($conn, "SELECT MAX(id_gejala) as max_id FROM tb_gejala");
$row_next = mysqli_fetch_assoc($query_next_code);
$next_id = ($row_next['max_id'] ?? 0) + 1;
$auto_kode = "G" . str_pad($next_id, 2, "0", STR_PAD_LEFT);

// Proses Form Submit HARUS sebelum ada output HTML
if (isset($_POST['submit'])) {
    $kode_gejala_input = mysqli_real_escape_string($conn, trim($_POST['kode_gejala']));
    $nama_gejala_input = mysqli_real_escape_string($conn, trim($_POST['nama_gejala']));
    
    // Validasi: Cek apakah kode gejala sudah ada di database
    $check_duplicate = mysqli_query($conn, "SELECT id_gejala FROM tb_gejala WHERE kode_gejala = '$kode_gejala_input'");
    
    if (mysqli_num_rows($check_duplicate) > 0) {
        $error = "Kode Gejala '$kode_gejala_input' sudah digunakan. Silakan gunakan kode lain!";
    } else {
        $query = "INSERT INTO tb_gejala (kode_gejala, nama_gejala) VALUES ('$kode_gejala_input', '$nama_gejala_input')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: gejala.php?status=sukses_tambah");
            exit();
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}

$page_title = "Tambah Gejala";
include "../includes/sidebar.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Gejala | Sistem Pakar ISPA</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #F4F7FB; color: #2C3E50; overflow-x: hidden; }
        
        /* MAIN CONTENT LAYOUT */
        .main-content { 
            margin-left: 270px; 
            min-height: 100vh; 
            transition: all 0.3s ease;
        }
        
        /* TOPBAR & HAMBURGER BUTTON */
        .topbar { 
            height: 80px; background: #FFF; display: flex; 
            align-items: center; padding: 0 35px; 
            box-shadow: 0 2px 12px rgba(0,0,0,.04); 
            position: sticky; top: 0; z-index: 99;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-btn {
            display: none; background: none; border: none;
            font-size: 22px; color: #063154; cursor: pointer;
        }
        .topbar h2 { color: #063154; font-size: 24px; font-weight: 700; }
        
        /* CONTENT AREA */
        .content { padding: 30px; }
        .card { 
            background: #FFF; border-radius: 18px; padding: 30px; 
            max-width: 750px; margin: 0 auto;
            border: 1px solid #EEF2F7; box-shadow: 0 8px 25px rgba(0,0,0,.04); 
        }
        
        /* GRID DUA KOLOM UNTUK KODE & NAMA GEJALA */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #063154; margin-bottom: 8px; }
        .form-control {
            width: 100%; padding: 12px 16px; border: 1px solid #E2E8F0; 
            border-radius: 10px; font-size: 14px; outline: none; transition: .3s;
            background: #FFF; color: #334155;
        }
        .form-control:focus { border-color: #2F9D94; box-shadow: 0 0 0 4px rgba(47, 157, 148, .12); }
        
        /* BUTTON ACTIONS */
        .btn-group { display: flex; gap: 12px; margin-top: 10px; align-items: center; }
        .btn-save { 
            background: #2F9D94; color: #FFF; border: none; padding: 12px 28px; 
            border-radius: 10px; font-weight: 600; cursor: pointer; transition: .25s; 
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;
        }
        .btn-save:hover { background: #237E77; }
        .btn-cancel { 
            background: #E2E8F0; color: #4A5568; text-decoration: none; padding: 12px 24px; 
            border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; 
            justify-content: center; transition: .25s; font-size: 14px;
        }
        .btn-cancel:hover { background: #CBD5E1; }

        .alert-danger { 
            background: #FADBD8; color: #C0392B; border: 1px solid #F5C6CB; 
            padding: 14px 18px; border-radius: 10px; margin-bottom: 22px; font-size: 14px; 
            display: flex; align-items: center; gap: 10px;
        }

        /* MEDIA QUERIES (RESPONSIVE BREAKPOINTS) */
        @media screen and (max-width: 992px) {
            .main-content { margin-left: 0; }
            .toggle-btn { display: block; }
            .topbar { padding: 0 20px; }
            
            /* Sidebar responsive state */
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.active { transform: translateX(0); }
        }

        @media screen and (max-width: 768px) {
            .topbar { height: 70px; }
            .topbar h2 { font-size: 20px; }
            
            .content { padding: 15px; }
            .card { padding: 20px; border-radius: 14px; }
            
            .form-row { grid-template-columns: 1fr; gap: 0; }
            
            .btn-group { flex-direction: column; width: 100%; }
            .btn-save, .btn-cancel { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h2>Tambah Data Gejala</h2>
        </div>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="content">
        <div class="card">
            <?php if (!empty($error)) : ?>
                <div class="alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Kode Gejala <span style="color:red;">*</span></label>
                        <input type="text" name="kode_gejala" class="form-control" placeholder="Contoh: G01" value="<?= htmlspecialchars(!empty($kode_gejala_input) ? $kode_gejala_input : $auto_kode); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Gejala <span style="color:red;">*</span></label>
                        <input type="text" name="nama_gejala" class="form-control" placeholder="Contoh: Batuk Berkelanjutan" value="<?= htmlspecialchars($nama_gejala_input); ?>" required>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Data
                    </button>
                    <a href="gejala.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JAVASCRIPT UNTUK RESPONSIVE SIDEBAR TOGGLE -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById("sidebarToggle");
        const sidebar = document.querySelector(".sidebar");

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener("click", function () {
                sidebar.classList.toggle("active");
            });
        }
    });
</script>
</body>
</html>