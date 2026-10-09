<?php
session_start();
require_once "../config/koneksi.php";

$page_title = "Data Penyakit";
include "../includes/sidebar.php";

// ==========================================
// CONFIG PENGATURAN PAGINATION
// ==========================================
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($page < 1) { $page = 1; }
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Proses Pencarian Data
$keyword = "";
$param_keyword = "";
$has_search = false;

if (isset($_GET['cari']) && !empty(trim($_GET['cari']))) {
    $keyword = trim($_GET['cari']);
    $param_keyword = "%" . $keyword . "%";
    $has_search = true;
}

// 1. Hitung Total Data (untuk pagination) dengan Prepared Statement
if ($has_search) {
    $query_total = "SELECT COUNT(*) AS total FROM tb_penyakit WHERE kode_penyakit LIKE ? OR nama_penyakit LIKE ? OR gejala_umum LIKE ?";
    $stmt_total = mysqli_prepare($conn, $query_total);
    mysqli_stmt_bind_param($stmt_total, "sss", $param_keyword, $param_keyword, $param_keyword);
} else {
    $query_total = "SELECT COUNT(*) AS total FROM tb_penyakit";
    $stmt_total = mysqli_prepare($conn, $query_total);
}

mysqli_stmt_execute($stmt_total);
$res_total = mysqli_stmt_get_result($stmt_total);
$row_total = mysqli_fetch_assoc($res_total);
$total_data = $row_total['total'] ?? 0;
mysqli_stmt_close($stmt_total);

$total_pages = ceil($total_data / $limit);

// 2. Query Data Penyakit dengan LIMIT & Prepared Statement
if ($has_search) {
    $query = "SELECT * FROM tb_penyakit WHERE kode_penyakit LIKE ? OR nama_penyakit LIKE ? OR gejala_umum LIKE ? ORDER BY kode_penyakit ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssii", $param_keyword, $param_keyword, $param_keyword, $start, $limit);
} else {
    $query = "SELECT * FROM tb_penyakit ORDER BY kode_penyakit ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Menghitung rentang data yang ditampilkan
$from_data = $total_data > 0 ? $start + 1 : 0;
$to_data = min($start + $limit, $total_data);

// Fungsi pembantu untuk memotong teks
function limit_text($text, $limit = 50) {
    if (empty($text)) return "-";
    if (strlen($text) > $limit) {
        return htmlspecialchars(substr($text, 0, $limit)) . '...';
    }
    return htmlspecialchars($text);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penyakit | Sistem Pakar ISPA</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #F4F7FB; overflow-x: hidden; }
        
        /* LAYOUT UTAMA */
        .main-content { 
            margin-left: 270px; 
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            min-height: 90px; 
            background: #FFF; 
            display: flex;
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 40px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
        }
        .topbar-title h2 { color: #063154; font-size: 26px; font-weight: 700; }
        .topbar-title p { margin-top: 4px; color: #8B98A7; font-size: 13px; }

        .content { padding: 30px; }
        .card { background: #FFF; border-radius: 18px; padding: 25px; border: 1px solid #EEF2F7; box-shadow: 0 8px 25px rgba(0, 0, 0, .05); }

        /* TOOLBAR (SEARCH & TOMBOL TAMBAH) */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; }
        .search-box { position: relative; width: 320px; }
        .search-box input {
            width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #E2E8F0;
            border-radius: 10px; outline: none; font-size: 14px;
        }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #A0AEC0; }

        .btn-add {
            background: #2F9D94; color: #FFF; padding: 10px 20px; border-radius: 10px;
            text-decoration: none; font-weight: 600; font-size: 14px; transition: .25s;
            display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
        }
        .btn-add:hover { background: #237E77; }

        /* TABEL RESPONSIF */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; text-align: left; min-width: 900px; }
        th, td { padding: 14px 16px; border-bottom: 1px solid #EDF2F7; font-size: 13px; vertical-align: middle; }
        th { 
            background: #FAFCFE; 
            color: #063154; 
            font-weight: 600; 
            font-size: 13px; 
            white-space: nowrap;
        }
        
        /* PEMBAGIAN LEBAR KOLOM */
        th:nth-child(1), td:nth-child(1) { width: 8%; text-align: center; }  /* Gambar */
        th:nth-child(2), td:nth-child(2) { width: 8%; }                      /* Kode */
        th:nth-child(3), td:nth-child(3) { width: 15%; }                     /* Nama Penyakit */
        th:nth-child(4), td:nth-child(4) { width: 25%; }                     /* Deskripsi */
        th:nth-child(5), td:nth-child(5) { width: 18%; }                     /* Gejala Umum */
        th:nth-child(6), td:nth-child(6) { width: 18%; }                     /* Penanganan */
        th:nth-child(7), td:nth-child(7) { width: 8%; text-align: center; }  /* Aksi */

        .disease-img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
        }

        .badge-code {
            background: #E6F4F1; color: #2F9D94; font-weight: 600;
            padding: 6px 10px; border-radius: 6px; display: inline-block;
            font-size: 12px;
        }

        /* TOMBOL AKSI */
        .actions { display: flex; gap: 6px; justify-content: center; }
        .btn-action {
            width: 34px; height: 34px; border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center; text-decoration: none; font-size: 13px; transition: .2s;
        }
        .btn-edit { background: #E6F4F1; color: #2F9D94; }
        .btn-edit:hover { background: #2F9D94; color: #FFF; }
        .btn-delete { background: #FADBD8; color: #E74C3C; }
        .btn-delete:hover { background: #E74C3C; color: #FFF; }

        .alert-success {
            background: #D4EDDA; color: #155724; border: 1px solid #C3E6CB;
            padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;
        }

        /* PAGINATION BAWAH */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .pagination-info { color: #718096; font-size: 14px; }
        .pagination-info strong { color: #2D3748; }
        .pagination-nav { display: flex; gap: 8px; align-items: center; }
        .page-link {
            min-width: 38px; height: 38px; padding: 0 10px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 10px; border: 1px solid #E2E8F0; background: #FFF;
            color: #4A5568; font-weight: 600; font-size: 14px; text-decoration: none;
            transition: all 0.2s ease;
        }
        .page-link:hover { border-color: #2F9D94; color: #2F9D94; }
        .page-link.active { background: #2F9D94; color: #FFF; border-color: #2F9D94; }
        .page-link.disabled { background: #F7FAFC; color: #CBD5E0; border-color: #E2E8F0; cursor: not-allowed; pointer-events: none; }

        /* RESPONSIVE MEDIA QUERIES */
        @media (max-width: 768px) {
            .main-content { margin-left: 0 !important; }
            .topbar { padding: 15px 20px 15px 75px !important; }
            .topbar-title h2 { font-size: 20px; }
            .content { padding: 15px; }
            .card { padding: 15px; }
            .toolbar { flex-direction: column-reverse; align-items: stretch; }
            .search-box { width: 100%; }
            .btn-add { justify-content: center; width: 100%; }
            .pagination-container { flex-direction: column; align-items: center; text-align: center; }
        }

        @media (max-width: 480px) {
            .topbar-title h2 { font-size: 18px; }
            .topbar-title p { font-size: 12px; }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <h2>Data Penyakit</h2>
                <p>Daftar seluruh jenis penyakit ISPA yang terdaftar pada sistem.</p>
            </div>
        </div>

        <div class="content">
            <?php if (isset($_GET['status'])) : ?>
                <?php if ($_GET['status'] == 'sukses' || $_GET['status'] == 'sukses_tambah' || $_GET['status'] == 'sukses_edit') : ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check"></i> Data penyakit berhasil disimpan/diperbarui!
                    </div>
                <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check"></i> Data penyakit berhasil dihapus!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card">
                <div class="toolbar">
                    <form action="" method="GET" class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari kode, nama, atau gejala...">
                    </form>
                    <a href="tambah_penyakit.php" class="btn-add">
                        <i class="fa-solid fa-plus"></i> Tambah Penyakit
                    </a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center;">Gambar</th>
                                <th>Kode</th>
                                <th>Nama Penyakit</th>
                                <th>Deskripsi</th>
                                <th>Gejala Umum</th>
                                <th>Penanganan</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) : ?>
                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                    <tr>
                                        <td style="text-align: center;">
                                        <?php 
                                            $path_utama = "../assets/images/penyakit/";
                                            $path_alt   = "../assets/img/";

                                            if (!empty($row['gambar']) && file_exists($path_utama . $row['gambar'])) {
                                                $gambar = $path_utama . $row['gambar'];
                                            } elseif (!empty($row['gambar']) && file_exists($path_alt . $row['gambar'])) {
                                                $gambar = $path_alt . $row['gambar'];
                                            } else {
                                                $gambar = "../assets/images/penyakit/default.png";
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($gambar); ?>" alt="<?= htmlspecialchars($row['nama_penyakit']); ?>" class="disease-img">
                                        </td>

                                        <td>
                                            <span class="badge-code"><?= htmlspecialchars($row['kode_penyakit']); ?></span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($row['nama_penyakit']); ?></strong></td>
                                        <td><?= limit_text($row['deskripsi'], 50); ?></td>
                                        <td><?= limit_text($row['gejala_umum'], 40); ?></td>
                                        <td><?= limit_text($row['penanganan'], 40); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="edit_penyakit.php?id=<?= (int)$row['id_penyakit']; ?>" class="btn-action btn-edit" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="../proses/penyakit/hapus.php?id=<?= (int)$row['id_penyakit']; ?>" class="btn-action btn-delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #8B98A7; padding: 30px;">
                                        Data penyakit tidak ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- BOTTOM PAGINATION -->
                <?php if ($total_data > 0) : ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Menampilkan <strong><?= $from_data; ?> - <?= $to_data; ?></strong> dari <strong><?= $total_data; ?></strong> data
                    </div>
                    
                    <div class="pagination-nav">
                        <!-- Prev Button -->
                        <?php if ($page > 1) : ?>
                            <a href="?halaman=<?= $page - 1; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        <?php else : ?>
                            <span class="page-link disabled"><i class="fa-solid fa-chevron-left"></i></span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <a href="?halaman=<?= $i; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link <?= ($i == $page) ? 'active' : ''; ?>">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages) : ?>
                            <a href="?halaman=<?= $page + 1; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php else : ?>
                            <span class="page-link disabled"><i class="fa-solid fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>

</html>
<?php
mysqli_stmt_close($stmt);
?>