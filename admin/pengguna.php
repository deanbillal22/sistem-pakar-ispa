<?php
session_start();
require_once "../config/koneksi.php";

$page_title = "Pengguna";
include "../includes/sidebar.php"; 

// Penanganan variabel koneksi
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// ==========================================
// CONFIG PENGATURAN PAGINATION
// ==========================================
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($page < 1) { $page = 1; }
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Proses Pencarian Data
$keyword = "";
$where_clause = "";
if (isset($_GET['cari']) && !empty(trim($_GET['cari']))) {
    $keyword = mysqli_real_escape_string($conn, trim($_GET['cari']));
    $where_clause = " WHERE username LIKE '%$keyword%' OR nama LIKE '%$keyword%' OR email LIKE '%$keyword%'";
}

// Hitung Total Data Pengguna
$query_total = "SELECT COUNT(*) AS total FROM tb_user" . $where_clause;
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_data = $row_total['total'] ?? 0;
$total_pages = ceil($total_data / $limit);

// Query Data Pengguna dengan LIMIT
$query = "SELECT * FROM tb_user" . $where_clause . " ORDER BY id_user DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Menghitung rentang data yang ditampilkan
$from_data = $total_data > 0 ? $start + 1 : 0;
$to_data = min($start + $limit, $total_data);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna | Sistem Pakar ISPA</title>

    <!-- Google Font & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #F4F7FB; color: #2C3E50; overflow-x: hidden; }

        /* LAYOUT UTAMA */
        .main-content { 
            margin-left: 270px; 
            min-height: 100vh; 
            transition: all 0.3s ease;
        }

        /* TOPBAR */
        .topbar { 
            min-height: 80px; 
            background: #FFFFFF; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 30px; 
            border-bottom: 1px solid #EDF2F7;
            box-shadow: 0 3px 15px rgba(0, 0, 0, .03); 
            position: sticky; top: 0; z-index: 99;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-btn {
            display: none; background: none; border: none;
            font-size: 22px; color: #063154; cursor: pointer;
        }
        .topbar h2 { color: #063154; font-size: 24px; font-weight: 700; }
        .topbar p { margin-top: 2px; color: #7B8794; font-size: 13px; }

        .admin-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { 
            width: 42px; height: 42px; border-radius: 50%; background: #2F9D94; 
            display: flex; justify-content: center; align-items: center; color: #FFF; flex-shrink: 0; 
            font-size: 16px;
        }
        .admin-info h4 { color: #063154; font-size: 14px; margin: 0; font-weight: 600; }
        .admin-info span { color: #7B8794; font-size: 12px; }

        /* CONTENT CONTAINER */
        .content { padding: 25px; }
        .card { background: #FFF; border-radius: 18px; padding: 25px; box-shadow: 0 10px 25px rgba(0, 0, 0, .03); border: 1px solid #EEF2F7; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-header h3 { color: #063154; font-size: 22px; font-weight: 700; }

        /* TOOLBAR SEARCH */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; }
        .search-box { width: 340px; position: relative; }
        .search-box i { position: absolute; top: 50%; transform: translateY(-50%); left: 16px; color: #94A3B8; }
        .search-box input { 
            width: 100%; height: 44px; padding: 10px 18px 10px 45px; 
            border: 1px solid #E2E8F0; border-radius: 10px; outline: none; 
            transition: .3s; font-size: 14px; background: #FFF;
        }
        .search-box input:focus { border-color: #2F9D94; box-shadow: 0 0 0 4px rgba(47, 157, 148, .12); }

        /* TABEL RESPONSIF */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid #E9EEF3; border-radius: 12px; background: #FFF; }
        table { width: 100%; border-collapse: collapse; min-width: 650px; }
        thead { background: #F8FAFC; }
        thead th { padding: 15px 18px; text-align: left; font-size: 13px; color: #063154; font-weight: 600; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
        tbody td { padding: 16px 18px; border-bottom: 1px solid #EDF2F7; color: #475569; font-size: 13px; vertical-align: middle; }
        tbody tr:hover { background: #F8FBFD; }
        .aksi { width: 170px; text-align: center; }

        /* BADGES */
        .badge { display: inline-block; padding: 4px 12px; border-radius: 30px; font-size: 12px; font-weight: 600; }
        .badge-user { background: #EAF8F2; color: #2F9D94; }
        .badge-admin { background: #E8F0FE; color: #2563EB; }

        /* BUTTON AKSI */
        .action-group { display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-view { text-decoration: none; border: none; background: #E0F2FE; color: #0284C7; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: .3s; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-view:hover { background: #0284C7; color: #FFF; }
        .btn-delete { text-decoration: none; border: none; background: #FDECEC; color: #E74C3C; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: .3s; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-delete:hover { background: #E74C3C; color: #FFF; }

        .alert-success { background: #E6F5F4; color: #237A73; border: 1px solid #BCE5E1; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }

        /* FOOTER & PAGINATION */
        .table-footer { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .table-info { color: #7B8794; font-size: 13px; }

        .pagination { display: flex; gap: 6px; align-items: center; }
        .page-link { 
            min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; 
            background: #FFF; border: 1px solid #E2E8F0; color: #4A5568; font-weight: 600; 
            font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; 
            justify-content: center; transition: .3s; 
        }
        .page-link:hover { background: #EAF8F2; color: #2F9D94; border-color: #2F9D94; }
        .page-link.active { background: #2F9D94; color: #FFF; border-color: #2F9D94; }
        .page-link.disabled { background: #F7FAFC; color: #CBD5E0; border-color: #E2E8F0; cursor: not-allowed; pointer-events: none; }

        /* RESPONSIVE MEDIA QUERIES */
        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .toggle-btn { display: block; }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.active { transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .topbar { padding: 15px; height: 70px; }
            .admin-profile { display: none; }
            
            .content { padding: 15px; }
            .card { padding: 15px; border-radius: 12px; }

            .search-box { width: 100%; }

            .table-footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .topbar h2 { font-size: 18px; }
            .page-header h3 { font-size: 18px; }

            .action-group { flex-direction: column; width: 100%; }
            .btn-view, .btn-delete { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="toggle-btn" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h2>Pengguna</h2>
                    <p>Kelola seluruh data pengguna sistem pakar ISPA</p>
                </div>
            </div>

            <div class="admin-profile">
                <div class="avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="admin-info">
                    <h4>Administrator</h4>
                    <span>Admin Sistem</span>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">

            <?php if (isset($_GET['status'])) : ?>
                <?php if ($_GET['status'] == 'sukses_hapus') : ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check"></i> Data pengguna berhasil dihapus!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card">

                <div class="page-header">
                    <h3>Data Pengguna</h3>
                </div>

                <div class="toolbar">
                    <form action="" method="GET" class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari pengguna...">
                    </form>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th width="120">Peran</th>
                                <th class="aksi">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                                <?php 
                                $no = $start + 1;
                                while ($row = mysqli_fetch_assoc($result)) : 
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama'] ?? $row['username']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (($row['role'] ?? 'user') == 'admin'): ?>
                                            <span class="badge badge-admin">Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-user">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="../proses/pengguna/detail_user.php?id=<?= $row['id_user']; ?>" class="btn-view">
                                                <i class="fa-solid fa-eye"></i> Lihat
                                            </a>
                                            <a href="../proses/pengguna/hapus.php?id=<?= $row['id_user']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #8B98A7; padding: 30px;">
                                        Data pengguna tidak ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER & PAGINATION -->
                <?php if ($total_data > 0) : ?>
                <div class="table-footer">
                    <div class="table-info">
                        Menampilkan <strong><?= $from_data; ?> - <?= $to_data; ?></strong> dari <strong><?= $total_data; ?></strong> data
                    </div>

                    <div class="pagination">
                        <?php if ($page > 1) : ?>
                            <a href="?halaman=<?= $page - 1; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link">
                                <i class="fa-solid fa-angle-left"></i>
                            </a>
                        <?php else : ?>
                            <span class="page-link disabled"><i class="fa-solid fa-angle-left"></i></span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <a href="?halaman=<?= $i; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link <?= ($i == $page) ? 'active' : ''; ?>">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages) : ?>
                            <a href="?halaman=<?= $page + 1; ?><?= !empty($keyword) ? '&cari=' . urlencode($keyword) : ''; ?>" class="page-link">
                                <i class="fa-solid fa-angle-right"></i>
                            </a>
                        <?php else : ?>
                            <span class="page-link disabled"><i class="fa-solid fa-angle-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- SCRIPT TOGGLE SIDEBAR MOBILE -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.querySelector(".sidebar");

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener("click", function() {
                    sidebar.classList.toggle("active");
                });
            }
        });
    </script>

</body>
</html>