<?php
$page_title = "Riwayat Diagnosis";

// Hubungkan ke database & sidebar
include "../config/koneksi.php";
include "../includes/sidebar.php";

// Penanganan nama variabel koneksi
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

// Ambil parameter filter & pencarian dari URL
$tgl_mulai   = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($conn, $_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($conn, $_GET['tgl_selesai']) : '';
$penyakit    = isset($_GET['penyakit']) ? mysqli_real_escape_string($conn, $_GET['penyakit']) : '';
$keyword     = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, trim($_GET['keyword'])) : '';

// Susun Query Dinamis
$sql = "SELECT h.id_hasil, h.tanggal_diagnosis, h.nilai_cf, h.persentase,
               u.nama_lengkap AS nama_user,
               p.nama_penyakit
        FROM tb_hasil_diagnosis h
        LEFT JOIN tb_user u ON u.id_user = h.id_user
        LEFT JOIN tb_penyakit p ON h.id_penyakit = p.id_penyakit
        WHERE 1=1";

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql .= " AND DATE(h.tanggal_diagnosis) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

if (!empty($penyakit)) {
    $sql .= " AND p.nama_penyakit = '$penyakit'";
}

if (!empty($keyword)) {
    $sql .= " AND (u.nama_lengkap LIKE '%$keyword%' OR p.nama_penyakit LIKE '%$keyword%')";
}

$sql .= " ORDER BY h.tanggal_diagnosis DESC";
$query_riwayat = mysqli_query($conn, $sql);

if (!$query_riwayat) {
    die("Query error: " . mysqli_error($conn));
}

// Ambil opsi daftar penyakit untuk Filter Dropdown
$query_penyakit = mysqli_query($conn, "SELECT nama_penyakit FROM tb_penyakit ORDER BY nama_penyakit ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosis | Sistem Pakar ISPA</title>

    <!-- Google Font & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #F4F7FB; color: #2C3E50; overflow-x: hidden; }

        /* MAIN CONTENT RESPONSIVE */
        .main-content { 
            margin-left: 270px; 
            min-height: 100vh; 
            transition: all 0.3s ease; 
        }

        /* TOPBAR */
        .topbar {
            height: 80px; background: #FFF; display: flex;
            justify-content: space-between; align-items: center; padding: 0 30px;
            border-bottom: 1px solid #EDF2F7; box-shadow: 0 3px 15px rgba(0, 0, 0, .03);
            position: sticky; top: 0; z-index: 99;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-btn {
            display: none; background: none; border: none;
            font-size: 22px; color: #063154; cursor: pointer;
        }
        .topbar-title h2 { font-size: 24px; color: #063154; font-weight: 700; }
        .topbar-title p { font-size: 13px; color: #7B8794; }

        .admin-profile { display: flex; align-items: center; gap: 12px; }
        .admin-avatar { width: 42px; height: 42px; border-radius: 50%; background: #2F9D94; display: flex; justify-content: center; align-items: center; color: #FFF; font-size: 16px; flex-shrink: 0; }
        .admin-info h4 { font-size: 14px; color: #063154; }
        .admin-info span { font-size: 11px; color: #94A3B8; }

        /* CONTENT CONTAINER */
        .content { padding: 25px; }
        .card { background: #FFF; border-radius: 16px; padding: 25px; border: 1px solid #EEF2F7; box-shadow: 0 10px 25px rgba(0, 0, 0, .03); }

        .page-header { margin-bottom: 20px; }
        .page-header h3 { color: #063154; font-size: 22px; font-weight: 700; }
        .page-header p { margin-top: 2px; font-size: 13px; color: #7B8794; }

        /* FORM FILTER & PENCARIAN */
        .filter-section {
            display: flex; align-items: flex-end; gap: 15px;
            flex-wrap: wrap; margin-bottom: 25px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; flex: 1 1 180px; }
        .filter-group label { font-size: 13px; font-weight: 600; color: #64748B; }
        .filter-group input, .filter-group select {
            width: 100%; height: 44px; padding: 0 14px;
            border: 1px solid #E2E8F0; border-radius: 10px; outline: none; transition: .25s; background: #FFF;
        }
        .filter-group input:focus, .filter-group select:focus {
            border-color: #2F9D94; box-shadow: 0 0 0 4px rgba(47, 157, 148, .10);
        }
        
        .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; width: auto; }
        .btn-filter {
            height: 44px; border: none; padding: 0 20px; border-radius: 10px;
            background: #2F9D94; color: #FFF; cursor: pointer; font-size: 14px;
            font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: .25s; text-decoration: none;
        }
        .btn-filter:hover { background: #247D76; }
        .btn-reset { background: #6C757D; }
        .btn-reset:hover { background: #5A6268; }

        /* TABLE RESPONSIVE */
        .table-responsive { width: 100%; overflow-x: auto; border: 1px solid #E9EEF3; border-radius: 12px; background: #FFF; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        thead { background: #F8FAFC; }
        thead th { padding: 14px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #063154; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
        tbody td { padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #EDF2F7; vertical-align: middle; }
        tbody tr:hover { background: #F8FCFC; }

        tbody td:first-child, tbody td:nth-child(5), tbody td:nth-child(6) { text-align: center; }
        tbody td:first-child { font-weight: 600; }

        .cf-badge, .percent-badge {
            display: inline-block; min-width: 60px; padding: 4px 10px;
            border-radius: 8px; font-size: 12px; font-weight: 600; text-align: center;
        }
        .cf-badge { background: #EEF8F3; color: #28A745; }
        .percent-badge { background: #EEF4FF; color: #2563EB; }

        .action-group { display: flex; justify-content: center; align-items: center; gap: 6px; }
        .btn-action {
            text-decoration: none; padding: 6px 10px; border-radius: 6px;
            font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: .25s; border: none; cursor: pointer;
            white-space: nowrap;
        }
        .btn-detail { background: #EAF8F2; color: #28A745; border: 1px solid #BFE9CF; }
        .btn-detail:hover { background: #28A745; color: #FFF; }
        .btn-delete { background: #FFF1F2; color: #DC3545; border: 1px solid #F5C2C7; }
        .btn-delete:hover { background: #DC3545; color: #FFF; }

        .table-footer { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .table-info { font-size: 13px; color: #64748B; }

        /* BREAKPOINTS RESPONSIVE */
        @media screen and (max-width: 992px) {
            .main-content { margin-left: 0; }
            .toggle-btn { display: block; }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.active { transform: translateX(0); }
        }

        @media screen and (max-width: 768px) {
            .topbar { padding: 0 15px; height: 70px; }
            .topbar-title h2 { font-size: 18px; }
            .topbar-title p, .admin-info { display: none; }
            
            .content { padding: 15px; }
            .card { padding: 15px; border-radius: 12px; }

            .filter-group { flex: 1 1 100%; }
            .filter-buttons { width: 100%; }
            .btn-filter { flex: 1; text-align: center; }
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
                <div class="topbar-title">
                    <h2>Riwayat</h2>
                    <p>Kelola seluruh riwayat hasil diagnosis pengguna.</p>
                </div>
            </div>
            <div class="admin-profile">
                <div class="admin-avatar"><i class="fa-solid fa-user"></i></div>
                <div class="admin-info">
                    <h4>Administrator</h4>
                    <span>Admin Sistem</span>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <div class="card">
                <div class="page-header">
                    <h3>Riwayat Diagnosis</h3>
                    <p>Data hasil diagnosis seluruh pengguna.</p>
                </div>

                <!-- FORM FILTER & PENCARIAN TERPADU -->
                <form method="GET" action="" class="filter-section">
                    <div class="filter-group">
                        <label>Pencarian</label>
                        <input type="text" name="keyword" placeholder="Cari nama user / penyakit..." value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Penyakit</label>
                        <select name="penyakit">
                            <option value="">Semua Penyakit</option>
                            <?php if ($query_penyakit) : ?>
                                <?php while ($p = mysqli_fetch_assoc($query_penyakit)) : ?>
                                    <option value="<?= htmlspecialchars($p['nama_penyakit']); ?>" <?= ($penyakit == $p['nama_penyakit']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_penyakit']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        <?php if (!empty($tgl_mulai) || !empty($tgl_selesai) || !empty($penyakit) || !empty($keyword)): ?>
                            <a href="riwayat.php" class="btn-filter btn-reset">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th width="150">Tanggal</th>
                                <th width="160">User</th>
                                <th>Penyakit Terdiagnosis</th>
                                <th width="90">Nilai CF</th>
                                <th width="100">Persentase</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $total_data = 0;
                            if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0) :
                                $total_data = mysqli_num_rows($query_riwayat);
                                while ($row = mysqli_fetch_assoc($query_riwayat)) : 
                                    $id_hasil   = $row['id_hasil'];
                                    $nilai_cf   = $row['nilai_cf'] ?? 0;
                                    $persentase = $row['persentase'] ?? ($nilai_cf * 100);
                                    $tgl_format = isset($row['tanggal_diagnosis']) ? date('d M Y H:i', strtotime($row['tanggal_diagnosis'])) : '-';
                                    $nama_user_display = $row['nama_user'] ?? 'Umum';
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $tgl_format; ?></td>
                                    <td><strong><?= htmlspecialchars($nama_user_display); ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_penyakit'] ?? 'Tidak Diketahui'); ?></td>
                                    <td>
                                        <span class="cf-badge">
                                            <?= number_format((float)$nilai_cf, 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="percent-badge">
                                            <?= round($persentase); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="../proses/riwayat/detail.php?id_hasil=<?= $id_hasil; ?>" class="btn-action btn-detail">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                            <a href="../proses/riwayat/hapus.php?id_hasil=<?= $id_hasil; ?>" 
                                            class="btn-action btn-delete" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data riwayat ini?');">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else :
                            ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #94A3B8; padding: 25px;">
                                        Tidak ada data riwayat diagnosis yang ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER -->
                <div class="table-footer">
                    <div class="table-info">
                        Menampilkan <strong><?= $total_data; ?></strong> data riwayat.
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPT TOGGLE SIDEBAR -->
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