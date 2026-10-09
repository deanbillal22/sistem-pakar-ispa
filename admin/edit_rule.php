<?php
ob_start(); // Mencegah error 
session_start();
require_once "../config/koneksi.php";

// Penanganan nama variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$error = "";
$id_rule = $_GET['id'] ?? null;

if (!$id_rule) {
    header("Location: rule.php");
    exit();
}

$id_rule = mysqli_real_escape_string($conn, $id_rule);

// Fetch data rule utama untuk inisialisasi awal
$q_rule = mysqli_query($conn, "SELECT * FROM tb_rule WHERE id_rule = '$id_rule'");
$rule = mysqli_fetch_assoc($q_rule);

if (!$rule) {
    header("Location: rule.php");
    exit();
}

// Fetch detail gejala terpilih awal
$q_selected_gejala = mysqli_query($conn, "SELECT id_gejala FROM tb_rule_detail WHERE id_rule = '$id_rule'");
$selected_gejala = [];
while ($row_g = mysqli_fetch_assoc($q_selected_gejala)) {
    $selected_gejala[] = $row_g['id_gejala'];
}

// Inisialisasi variabel tampilan form
$kode_rule_input   = $rule['kode_rule'];
$id_penyakit_input = $rule['id_penyakit'];
$cf_rule_input     = $rule['cf_rule'];

// PROSES UPDATE DATA 
if (isset($_POST['submit'])) {
    $kode_rule_input   = mysqli_real_escape_string($conn, trim($_POST['kode_rule'] ?? ''));
    $id_penyakit_input = mysqli_real_escape_string($conn, trim($_POST['id_penyakit'] ?? ''));
    $cf_rule_input     = mysqli_real_escape_string($conn, trim($_POST['cf_rule'] ?? ''));
    $gejala_ids        = $_POST['gejala'] ?? [];

    // Preservasi checkbox pilihan user jika terjadi error
    $selected_gejala   = $gejala_ids;

    if (!empty($kode_rule_input) && !empty($id_penyakit_input) && $cf_rule_input !== '' && !empty($gejala_ids)) {
        // Cek duplikasi kode_rule dengan ID lain
        $check_duplicate = mysqli_query($conn, "SELECT id_rule FROM tb_rule WHERE kode_rule = '$kode_rule_input' AND id_rule != '$id_rule'");
        
        if (mysqli_num_rows($check_duplicate) > 0) {
            $error = "Kode Rule '$kode_rule_input' sudah digunakan oleh rule lain!";
        } else {
            $query_update = "UPDATE tb_rule SET 
                             kode_rule = '$kode_rule_input', 
                             id_penyakit = '$id_penyakit_input', 
                             cf_rule = '$cf_rule_input', 
                             updated_at = NOW() 
                             WHERE id_rule = '$id_rule'";

            if (mysqli_query($conn, $query_update)) {
                // Hapus detail lama, masukkan detail baru
                mysqli_query($conn, "DELETE FROM tb_rule_detail WHERE id_rule = '$id_rule'");

                foreach ($gejala_ids as $id_gejala) {
                    $id_gejala = mysqli_real_escape_string($conn, $id_gejala);
                    mysqli_query($conn, "INSERT INTO tb_rule_detail (id_rule, id_gejala) VALUES ('$id_rule', '$id_gejala')");
                }

                header("Location: rule.php?status=sukses_edit");
                exit();
            } else {
                $error = "Gagal memperbarui rule: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Mohon lengkapi semua bidang dan pilih minimal 1 gejala!";
    }
}

// QUERY OPTION PENYAKIT & GEJALA
$query_penyakit = mysqli_query($conn, "SELECT * FROM tb_penyakit ORDER BY nama_penyakit ASC");
$query_gejala   = mysqli_query($conn, "SELECT * FROM tb_gejala ORDER BY kode_gejala ASC");

// INCLUDE SIDEBAR & RENDER HTML
$page_title = "Edit Rule";
include "../includes/sidebar.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Rule | Sistem Pakar ISPA</title>

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
            max-width: 800px; margin: 0 auto;
            border: 1px solid #EEF2F7; box-shadow: 0 8px 25px rgba(0,0,0,.04); 
        }

        /* GRID DUA KOLOM UNTUK KODE & CF RULE */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
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

        /* BOX GEJALA CHECKBOX */
        .gejala-box { 
            border: 1px solid #E2E8F0; border-radius: 10px; padding: 15px; 
            max-height: 220px; overflow-y: auto; background: #F8FAFC; 
        }
        .gejala-item { 
            display: flex; align-items: flex-start; gap: 12px; 
            padding: 8px; border-radius: 6px; transition: background 0.2s;
        }
        .gejala-item:hover { background: #EDF2F7; }
        .gejala-item input[type="checkbox"] { 
            width: 18px; height: 18px; margin-top: 2px;
            cursor: pointer; accent-color: #2F9D94; flex-shrink: 0; 
        }
        .gejala-item label { 
            font-size: 14px; font-weight: 400; color: #334155; 
            margin: 0; cursor: pointer; line-height: 1.4; 
        }

        /* BUTTON ACTIONS */
        .btn-group { display: flex; gap: 12px; margin-top: 10px; align-items: center; }
        .btn-submit { 
            background: #28A745; color: #FFF; border: none; padding: 12px 28px; 
            border-radius: 10px; font-weight: 600; cursor: pointer; transition: .25s; 
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;
        }
        .btn-submit:hover { background: #218838; }
        .btn-back { 
            background: #6C757D; color: #FFF; text-decoration: none; padding: 12px 24px; 
            border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; 
            justify-content: center; transition: .25s; font-size: 14px;
        }
        .btn-back:hover { background: #5A6268; }

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

            .btn-group { flex-direction: column-reverse; width: 100%; }
            .btn-submit, .btn-back { width: 100%; text-align: center; }
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
            <h2>Edit Rule</h2>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="card">
            <?php if (!empty($error)) : ?>
                <div class="alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Kode Rule <span style="color:red;">*</span></label>
                        <input type="text" name="kode_rule" class="form-control" value="<?= htmlspecialchars($kode_rule_input); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Certainty Factor (CF Rule) <span style="color:red;">*</span></label>
                        <input type="number" step="0.01" min="0" max="1" name="cf_rule" class="form-control" value="<?= htmlspecialchars($cf_rule_input); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Penyakit <span style="color:red;">*</span></label>
                    <select name="id_penyakit" class="form-control" required>
                        <option value="">-- Pilih Penyakit --</option>
                        <?php while ($p = mysqli_fetch_assoc($query_penyakit)) : ?>
                            <option value="<?= $p['id_penyakit']; ?>" <?= ($p['id_penyakit'] == $id_penyakit_input) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p['nama_penyakit']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Gejala Terkait <span style="color:red;">*</span></label>
                    <div class="gejala-box">
                        <?php while ($g = mysqli_fetch_assoc($query_gejala)) : ?>
                            <?php $is_checked = in_array($g['id_gejala'], $selected_gejala) ? 'checked' : ''; ?>
                            <div class="gejala-item">
                                <input type="checkbox" name="gejala[]" value="<?= $g['id_gejala']; ?>" id="g_<?= $g['id_gejala']; ?>" <?= $is_checked; ?>>
                                <label for="g_<?= $g['id_gejala']; ?>">
                                    <strong>[<?= htmlspecialchars($g['kode_gejala']); ?>]</strong> <?= htmlspecialchars($g['nama_gejala']); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="rule.php" class="btn-back">Batal</a>
                    <button type="submit" name="submit" class="btn-submit">
                        <i class="fa-solid fa-floppy-disk"></i> Update Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- UNTUK RESPONSIVE SIDEBAR TOGGLE -->
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