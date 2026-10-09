<?php
session_start();
require_once "../config/koneksi.php";

$page_title = "Rule";
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

// 1. Hitung Total Data Rule (untuk pagination) dengan Prepared Statement
if ($has_search) {
    $query_total = "SELECT COUNT(*) AS total FROM tb_rule r 
                    LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit 
                    WHERE r.kode_rule LIKE ? OR p.nama_penyakit LIKE ?";
    $stmt_total = mysqli_prepare($conn, $query_total);
    mysqli_stmt_bind_param($stmt_total, "ss", $param_keyword, $param_keyword);
} else {
    $query_total = "SELECT COUNT(*) AS total FROM tb_rule r 
                    LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit";
    $stmt_total = mysqli_prepare($conn, $query_total);
}

mysqli_stmt_execute($stmt_total);
$res_total = mysqli_stmt_get_result($stmt_total);
$row_total = mysqli_fetch_assoc($res_total);
$total_data = $row_total['total'] ?? 0;
mysqli_stmt_close($stmt_total);

$total_pages = ceil($total_data / $limit);

// 2. Query Data Rule Utama dengan Prepared Statement & LIMIT
if ($has_search) {
    $query = "SELECT r.*, p.nama_penyakit 
              FROM tb_rule r 
              LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit 
              WHERE r.kode_rule LIKE ? OR p.nama_penyakit LIKE ? 
              ORDER BY r.id_rule ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $param_keyword, $param_keyword, $start, $limit);
} else {
    $query = "SELECT r.*, p.nama_penyakit 
              FROM tb_rule r 
              LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit 
              ORDER BY r.id_rule ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Menghitung rentang data yang ditampilkan
$from_data = $total_data > 0 ? $start + 1 : 0;
$to_data = min($start + $limit, $total_data);

// 3. Stat Card Query Dinamis
$total_penyakit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_penyakit"))['total'] ?? 0;
$total_gejala = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_gejala"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rule | Sistem Pakar ISPA</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #F4F7FB; color: #2C3E50; overflow-x: hidden; }

        /* MAIN LAYOUT */
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
        .topbar-title p { margin-top: 4px; color: #7B8794; font-size: 13px; }

        .admin-profile { display: flex; align-items: center; gap: 12px; }
        .admin-avatar {
            width: 42px; height: 42px; border-radius: 50%; background: #2F9D94;
            color: #FFF; display: flex; justify-content: center; align-items: center; font-size: 16px;
        }
        .admin-info h4 { font-size: 14px; color: #063154; font-weight: 600; }
        .admin-info span { font-size: 12px; color: #94A3B8; }

        .content { padding: 30px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; }
        .page-header h3 { color: #063154; font-size: 24px; font-weight: 700; }
        .page-header p { color: #7B8794; font-size: 13px; margin-top: 4px; }

        .btn-add {
            border: none; background: #2F9D94; color: #FFF; padding: 11px 20px;
            border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: .25s;
            white-space: nowrap;
        }
        .btn-add:hover { background: #237A73; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(47, 157, 148, .25); }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 25px; }
        .stat-card { background: #FFF; border-radius: 14px; padding: 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, .03); border: 1px solid #EEF2F7; transition: .3s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card h2 { font-size: 30px; font-weight: 700; margin-bottom: 4px; }
        .stat-card span { font-size: 13px; font-weight: 500; }

        .teal { background: #E6F5F4; } .teal h2, .teal span { color: #2F9D94; }
        .blue { background: #F2F7FF; } .blue h2, .blue span { color: #2563EB; }
        .purple { background: #F8F2FF; } .purple h2, .purple span { color: #7C3AED; }
        .orange { background: #FFF8EE; } .orange h2, .orange span { color: #F97316; }

        /* CARD & TOOLBAR */
        .card { background: #FFF; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0, 0, 0, .03); border: 1px solid #EEF2F7; }
        .toolbar { margin-bottom: 22px; }
        .search-box { width: 340px; position: relative; }
        .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8; }
        .search-box input {
            width: 100%; height: 44px; border: 1px solid #E2E8F0; border-radius: 10px;
            padding-left: 45px; padding-right: 15px; outline: none; transition: .25s; font-size: 14px;
        }
        .search-box input:focus { border-color: #2F9D94; box-shadow: 0 0 0 4px rgba(47, 157, 148, .12); }

        /* TABEL RESPONSIF */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid #E9EEF3; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; background: #FFF; min-width: 700px; }
        thead { background: #F8FAFC; }
        thead th { padding: 16px 18px; text-align: left; color: #063154; font-size: 14px; font-weight: 600; border-bottom: 1px solid #E5E7EB; }
        tbody td { padding: 16px 18px; border-bottom: 1px solid #EDF2F7; font-size: 14px; color: #475569; vertical-align: middle; }
        tbody tr { transition: .25s; }
        tbody tr:hover { background: #F8FCFC; }

        tbody td:nth-child(1), tbody td:nth-child(2), tbody td:nth-child(5) { text-align: center; }

        /* BADGES & TAGS */
        .cf-badge { display: inline-block; min-width: 60px; padding: 6px 12px; border-radius: 8px; background: #E6F5F4; color: #2F9D94; text-align: center; font-weight: 600; font-size: 13px; }
        .gejala-tag {
            display: inline-block; background: #F1F5F9; color: #063154; padding: 4px 10px;
            border-radius: 6px; font-size: 12px; margin: 2px; font-weight: 500; border: 1px solid #E2E8F0;
        }

        /* AKSI */
        .action-group { display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-edit, .btn-delete {
            cursor: pointer; border-radius: 8px; padding: 8px 14px; font-size: 12px;
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: .25s;
        }
        .btn-edit { background: #E6F5F4; color: #2F9D94; border: 1px solid #BCE5E1; }
        .btn-edit:hover { background: #2F9D94; color: #FFF; }
        .btn-delete { background: #FFF5F5; color: #E53E3E; border: 1px solid #FED7D7; }
        .btn-delete:hover { background: #E53E3E; color: #FFF; }

        .alert-success { background: #E6F5F4; color: #237A73; border: 1px solid #BCE5E1; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }

        /* FOOTER & PAGINATION */
        .table-footer { margin-top: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .table-info { color: #64748B; font-size: 13px; }

        .pagination { display: flex; align-items: center; gap: 6px; }
        .page-link {
            min-width: 36px; height: 36px; padding: 0 10px; border: 1px solid #D9E2EC;
            background: #FFF; border-radius: 8px; transition: .25s; font-weight: 600;
            color: #475569; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;
        }
        .page-link:hover { background: #E6F5F4; color: #2F9D94; border-color: #2F9D94; }
        .page-link.active { background: #2F9D94; color: #FFF; border-color: #2F9D94; }
        .page-link.disabled { background: #F7FAFC; color: #CBD5E0; border-color: #E2E8F0; cursor: not-allowed; pointer-events: none; }

        /* MEDIA QUERIES */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0 !important; }
            .topbar { padding: 15px 20px 15px 75px !important; flex-direction: row; }
            .admin-profile { display: none; }
            .content { padding: 15px; }
            .page-header { flex-direction: column; align-items: stretch; gap: 12px; }
            .btn-add { justify-content: center; width: 100%; }
            .card { padding: 15px; }
            .search-box { width: 100%; }
            .table-footer { flex-direction: column; align-items: center; text-align: center; }
        }

        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
            .topbar-title h2 { font-size: 20px; }
            .page-header h3 { font-size: 20px; }
            .action-group { flex-direction: column; width: 100%; }
            .btn-edit, .btn-delete { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-title">
            <h2>Rule</h2>
            <p>Kelola basis pengetahuan Certainty Factor.</p>
        </div>
        <div class="admin-profile">
            <div class="admin-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="admin-info">
                <h4>Administrator</h4>
                <span>Admin Sistem</span>
            </div>
        </div>
    </div>

    <div class="content">
        <?php if (isset($_GET['status'])) : ?>
            <?php if ($_GET['status'] == 'sukses_tambah' || $_GET['status'] == 'sukses_edit') : ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i> Data rule berhasil disimpan/diperbarui!
                </div>
            <?php elseif ($_GET['status'] == 'sukses_hapus') : ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i> Data rule berhasil dihapus!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h3>Data Rule</h3>
                <p>Kelola hubungan gejala dengan penyakit menggunakan metode Certainty Factor.</p>
            </div>
            <a href="tambah_rule.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Tambah Rule
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card teal">
                <h2><?= $total_data; ?></h2>
                <span>Total Rule</span>
            </div>
            <div class="stat-card blue">
                <h2><?= $total_penyakit; ?></h2>
                <span>Penyakit</span>
            </div>
            <div class="stat-card purple">
                <h2><?= $total_gejala; ?></h2>
                <span>Gejala</span>
            </div>
            <div class="stat-card orange">
                <h2>Aktif</h2>
                <span>Status Rule</span>
            </div>
        </div>

        <div class="card">
            <div class="toolbar">
                <form action="" method="GET" class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari kode rule / nama penyakit...">
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th width="110">Kode Rule</th>
                            <th>Penyakit</th>
                            <th>Gejala Terkait</th>
                            <th width="100">CF Rule</th>
                            <th width="160" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                            <?php 
                            $no = $start + 1;
                            while ($row = mysqli_fetch_assoc($result)) : 
                                $id_rule = (int)$row['id_rule'];
                                
                                // Prepared Statement untuk relasi tb_rule_detail -> id_gejala
                                $q_detail = "SELECT g.nama_gejala FROM tb_rule_detail rd 
                                             LEFT JOIN tb_gejala g ON rd.id_gejala = g.id_gejala 
                                             WHERE rd.id_rule = ?";
                                $stmt_detail = mysqli_prepare($conn, $q_detail);
                                mysqli_stmt_bind_param($stmt_detail, "i", $id_rule);
                                mysqli_stmt_execute($stmt_detail);
                                $res_detail = mysqli_stmt_get_result($stmt_detail);
                                
                                // Fallback jika relasi menggunakan kode_gejala
                                if (!$res_detail || mysqli_num_rows($res_detail) == 0) {
                                    if ($stmt_detail) mysqli_stmt_close($stmt_detail);
                                    
                                    $q_detail_alt = "SELECT g.nama_gejala FROM tb_rule_detail rd 
                                                     LEFT JOIN tb_gejala g ON rd.kode_gejala = g.kode_gejala 
                                                     WHERE rd.id_rule = ?";
                                    $stmt_detail = mysqli_prepare($conn, $q_detail_alt);
                                    mysqli_stmt_bind_param($stmt_detail, "i", $id_rule);
                                    mysqli_stmt_execute($stmt_detail);
                                    $res_detail = mysqli_stmt_get_result($stmt_detail);
                                }
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($row['kode_rule']); ?></strong></td>
                                <td><strong><?= htmlspecialchars($row['nama_penyakit'] ?? 'Penyakit ID: ' . $row['id_penyakit']); ?></strong></td>
                                <td>
                                    <?php 
                                    if ($res_detail && mysqli_num_rows($res_detail) > 0) {
                                        while ($d = mysqli_fetch_assoc($res_detail)) {
                                            echo '<span class="gejala-tag">' . htmlspecialchars($d['nama_gejala'] ?? '-') . '</span>';
                                        }
                                    } else {
                                        echo '<span style="color: #A0AEC0; font-style: italic;">Tidak ada gejala</span>';
                                    }
                                    if ($stmt_detail) mysqli_stmt_close($stmt_detail);
                                    ?>
                                </td>
                                <td>
                                    <span class="cf-badge">
                                        <?= number_format((float)$row['cf_rule'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="edit_rule.php?id=<?= (int)$row['id_rule']; ?>" class="btn-edit">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="../proses/rule/hapus.php?id=<?= (int)$row['id_rule']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus rule ini?');">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #8B98A7; padding: 30px;">
                                    Data rule tidak ditemukan.
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

</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>