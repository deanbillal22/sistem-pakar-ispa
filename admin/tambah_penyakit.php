<?php
ob_start(); // Mencegah error 
session_start();
require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$error = "";

// Inisialisasi variabel input untuk preserving data jika terjadi error
$kode_penyakit_input = "";
$nama_penyakit_input = "";
$deskripsi_input     = "";
$gejala_umum_input   = "";
$penanganan_input    = "";

// Auto-Generate Kode Penyakit Otomatis (Opsional)
$query_next_code = mysqli_query($conn, "SELECT MAX(id_penyakit) as max_id FROM tb_penyakit");
$row_next = mysqli_fetch_assoc($query_next_code);
$next_id = ($row_next['max_id'] ?? 0) + 1;
$auto_kode = "P" . str_pad($next_id, 3, "0", STR_PAD_LEFT);

// PROSES INSERT DATA HARUS DI SINI 
if (isset($_POST['simpan'])) {
    $kode_penyakit_input = mysqli_real_escape_string($conn, trim($_POST['kode_penyakit']));
    $nama_penyakit_input = mysqli_real_escape_string($conn, trim($_POST['nama_penyakit']));
    $deskripsi_input     = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $gejala_umum_input   = mysqli_real_escape_string($conn, trim($_POST['gejala_umum']));
    $penanganan_input    = mysqli_real_escape_string($conn, trim($_POST['penanganan']));

    // Validasi: Cek apakah kode penyakit sudah pernah dipakai
    $check_duplicate = mysqli_query($conn, "SELECT id_penyakit FROM tb_penyakit WHERE kode_penyakit = '$kode_penyakit_input'");
    
    if (mysqli_num_rows($check_duplicate) > 0) {
        $error = "Kode Penyakit '$kode_penyakit_input' sudah terdaftar. Silakan gunakan kode lain!";
    } else {
        // Handling Upload Gambar
        $gambar_nama = 'default.png';
        $upload_ok = true;

        if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
            $filename   = $_FILES['gambar']['name'];
            $ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_ext = array('jpg', 'jpeg', 'png', 'webp');

            // Cek Format Ekstensi Gambar
            if (!in_array($ext, $allowed_ext)) {
                $error = "Format gambar tidak didukung! Gunakan format JPG, JPEG, PNG, atau WEBP.";
                $upload_ok = false;
            } else {
                $gambar_nama = time() . "_" . uniqid() . "." . $ext;
                $target_dir = "../assets/images/penyakit/";

                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar_nama)) {
                    $error = "Gagal mengunggah berkas gambar.";
                    $upload_ok = false;
                }
            }
        }

        // Jalankan Insert jika tidak ada error upload
        if ($upload_ok) {
            $query = "INSERT INTO tb_penyakit (kode_penyakit, nama_penyakit, deskripsi, gejala_umum, penanganan, gambar) 
                      VALUES ('$kode_penyakit_input', '$nama_penyakit_input', '$deskripsi_input', '$gejala_umum_input', '$penanganan_input', '$gambar_nama')";

            if (mysqli_query($conn, $query)) {
                header("Location: penyakit.php?status=sukses");
                exit();
            } else {
                $error = "Gagal menyimpan data ke database: " . mysqli_error($conn);
            }
        }
    }
}

// BARU PANGGIL SIDEBAR & RENDER TAMPILAN
$page_title = "Tambah Penyakit";
include "../includes/sidebar.php";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penyakit | Sistem Pakar ISPA</title>

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
            justify-content: space-between; align-items: center; padding: 0 35px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
            position: sticky; top: 0; z-index: 99;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-btn {
            display: none; background: none; border: none;
            font-size: 22px; color: #063154; cursor: pointer;
        }
        .topbar-title h2 { color: #063154; font-size: 24px; font-weight: 700; }
        .topbar-title p { margin-top: 2px; color: #8B98A7; font-size: 13px; }
        
        /* CONTENT AREA */
        .content { padding: 30px; }
        .card { 
            background: #FFF; border-radius: 18px; padding: 30px; 
            border: 1px solid #EEF2F7; box-shadow: 0 8px 25px rgba(0, 0, 0, .04); 
            max-width: 900px; margin: 0 auto;
        }

        /* GRID DUA KOLOM UNTUK FIELD PENDEK */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; font-weight: 600; color: #063154; margin-bottom: 8px; font-size: 14px; }
        .form-control {
            width: 100%; padding: 12px 16px; border: 1px solid #E2E8F0;
            border-radius: 10px; outline: none; font-size: 14px; transition: .3s;
            background: #FFF; color: #334155;
        }
        .form-control:focus { border-color: #2F9D94; box-shadow: 0 0 0 4px rgba(47, 157, 148, .12); }
        textarea.form-control { resize: vertical; min-height: 110px; }
        
        /* FILE UPLOAD PREVIEW */
        .preview-wrapper { margin-top: 10px; display: none; }
        .preview-wrapper img { max-width: 150px; max-height: 150px; border-radius: 10px; border: 1px solid #E2E8F0; object-fit: cover; }

        /* BUTTON ACTIONS */
        .form-actions { display: flex; gap: 12px; margin-top: 30px; align-items: center; }
        .btn-submit { 
            background: #2F9D94; color: #FFF; border: none; padding: 12px 28px; 
            border-radius: 10px; font-weight: 600; cursor: pointer; transition: .25s; 
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;
        }
        .btn-submit:hover { background: #237E77; }
        .btn-cancel { 
            background: #E2E8F0; color: #475569; padding: 12px 24px; border-radius: 10px; 
            font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; 
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
            .topbar-title h2 { font-size: 20px; }
            .topbar-title p { display: none; }
            
            .content { padding: 15px; }
            .card { padding: 20px; border-radius: 14px; }
            
            .form-row { grid-template-columns: 1fr; gap: 0; }
            
            .form-actions { flex-direction: column; width: 100%; }
            .btn-submit, .btn-cancel { width: 100%; text-align: center; }
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
                <div class="topbar-title">
                    <h2>Tambah Penyakit</h2>
                    <p>Menambahkan data penyakit ISPA baru ke sistem</p>
                </div>
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

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kode Penyakit <span style="color:red;">*</span></label>
                            <input type="text" name="kode_penyakit" class="form-control" placeholder="Contoh: P001" value="<?= htmlspecialchars(!empty($kode_penyakit_input) ? $kode_penyakit_input : $auto_kode); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Nama Penyakit <span style="color:red;">*</span></label>
                            <input type="text" name="nama_penyakit" class="form-control" placeholder="Masukkan nama penyakit" value="<?= htmlspecialchars($nama_penyakit_input); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi <span style="color:red;">*</span></label>
                        <textarea name="deskripsi" class="form-control" placeholder="Penjelasan mengenai penyakit..." required><?= htmlspecialchars($deskripsi_input); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gejala Umum</label>
                        <textarea name="gejala_umum" class="form-control" placeholder="Sebutkan gejala umum (opsional)..."><?= htmlspecialchars($gejala_umum_input); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Penanganan <span style="color:red;">*</span></label>
                        <textarea name="penanganan" class="form-control" placeholder="Penanganan medis/pertolongan pertama..." required><?= htmlspecialchars($penanganan_input); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Penyakit</label>
                        <input type="file" name="gambar" id="imgInput" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="preview-wrapper" id="previewWrapper">
                            <img id="imgPreview" src="#" alt="Preview Gambar">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="simpan" class="btn-submit">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Data
                        </button>
                        <a href="penyakit.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK RESPONSIVE SIDEBAR & IMAGE PREVIEW -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Toggle Sidebar di Layar Kecil / Mobile
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.querySelector(".sidebar");

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }

            // 2. Preview Gambar sebelum Diupload
            const imgInput = document.getElementById("imgInput");
            const previewWrapper = document.getElementById("previewWrapper");
            const imgPreview = document.getElementById("imgPreview");

            if (imgInput) {
                imgInput.addEventListener("change", function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            imgPreview.setAttribute("src", e.target.result);
                            previewWrapper.style.display = "block";
                        }
                        reader.readAsDataURL(file);
                    } else {
                        previewWrapper.style.display = "none";
                    }
                });
            }
        });
    </script>
</body>

</html>