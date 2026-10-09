<?php
// Sertakan file koneksi & sidebar
include "../../config/koneksi.php"; 
include "../../includes/sidebar.php";

// ==========================================
// CLASS USER CONTROLLER 
// ==========================================
class UserController {
    private $db;

    // Dependency Injection koneksi database melalui Constructor
    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    /**
     * Mengambil detail pengguna berdasarkan ID
     * @param string|int $id
     * @return array|null
     */
    public function getUserById($id) {
        if (empty($id)) {
            return null;
        }

        $cleanId = mysqli_real_escape_string($this->db, $id);
        $query   = mysqli_query($this->db, "SELECT * FROM tb_user WHERE id_user = '$cleanId'");

        if ($query && mysqli_num_rows($query) > 0) {
            return mysqli_fetch_assoc($query);
        }

        return null;
    }

    /**
     * Memformat tampilan role badge CSS class
     * @param string $role
     * @return string
     */
    public function getRoleBadgeClass($role) {
        return (strtolower($role) === 'admin') ? 'role-admin' : 'role-user';
    }
}

// ==========================================
// INISIALISASI & EKSEKUSI OBJEK
// ==========================================

// Handle fallback koneksi ($conn / $koneksi)
$dbConnection = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

if (!$dbConnection) {
    die("Koneksi database gagal!");
}

// Inisialisasi Objek Controller
$userController = new UserController($dbConnection);

// Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil Data Pengguna melalui Method Objek
$data = $userController->getUserById($id);

// Validasi jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data pengguna tidak ditemukan atau ID tidak valid!'); window.location.href='../../admin/pengguna.php';</script>";
    exit();
}

$page_title = "Detail Pengguna";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengguna | Sistem Pakar ISPA</title>

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
            height: 80px; 
            background: #FFF; 
            display: flex; 
            align-items: center; 
            padding: 0 35px; 
            box-shadow: 0 2px 12px rgba(0,0,0,.04); 
            position: sticky; 
            top: 0; 
            z-index: 99;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .toggle-btn {
            display: none; 
            background: none; 
            border: none;
            font-size: 22px; 
            color: #063154; 
            cursor: pointer;
        }
        .topbar h2 { color: #063154; font-size: 24px; font-weight: 700; }

        /* CONTENT AREA */
        .content { padding: 30px; }
        
        /* CARD STYLING */
        .card { 
            background: #FFF; 
            border-radius: 18px; 
            padding: 30px; 
            box-shadow: 0 8px 25px rgba(0,0,0,.04); 
            max-width: 700px; 
            margin: 0 auto; 
            border: 1px solid #EEF2F7;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #EDF2F7;
        }
        .card-header i { font-size: 28px; color: #2F9D94; }
        .card-header h3 { color: #063154; font-size: 22px; font-weight: 700; margin: 0; }

        /* GRID DUA KOLOM UNTUK INFORMASI SINGKAT */
        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .detail-group { margin-bottom: 18px; }
        .detail-group label { 
            display: block; 
            font-size: 13px; 
            color: #7B8794; 
            margin-bottom: 6px; 
            font-weight: 500; 
        }
        .detail-group p { 
            font-size: 15px; 
            color: #063154; 
            font-weight: 600; 
            background: #F8FAFC; 
            padding: 12px 16px; 
            border-radius: 10px; 
            border: 1px solid #E2E8F0; 
            word-break: break-word;
            margin: 0;
        }

        /* BADGE UNTUK ROLE */
        .role-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .role-admin { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; }
        .role-user { background: #E6F4EA; color: #137333; border: 1px solid #CEEAD6; }

        /* BUTTON BACK */
        .btn-group { margin-top: 10px; }
        .btn-back { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            gap: 8px; 
            padding: 12px 24px; 
            background: #6C757D; 
            color: #FFF; 
            text-decoration: none; 
            border-radius: 10px; 
            font-size: 14px; 
            font-weight: 600; 
            transition: .25s; 
        }
        .btn-back:hover { background: #5A6268; }

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
            .card-header h3 { font-size: 18px; }
            .card-header i { font-size: 22px; }

            /* Grid 2 kolom berubah jadi 1 kolom di layar HP */
            .detail-row { grid-template-columns: 1fr; gap: 0; }

            .btn-back { width: 100%; text-align: center; }
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
            <h2>Detail Pengguna</h2>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-id-card"></i>
                <h3>Informasi Pengguna</h3>
            </div>

            <div class="detail-group">
                <label><i class="fa-solid fa-user me-1"></i> Nama Lengkap</label>
                <p><?= htmlspecialchars($data['nama_lengkap'] ?? $data['username']); ?></p>
            </div>

            <div class="detail-row">
                <div class="detail-group">
                    <label><i class="fa-solid fa-at me-1"></i> Username</label>
                    <p><?= htmlspecialchars($data['username']); ?></p>
                </div>

                <div class="detail-group">
                    <label><i class="fa-solid fa-user-shield me-1"></i> Peran / Role</label>
                    <p>
                        <?php 
                            $role       = $data['role'] ?? 'user';
                            $badgeClass = $userController->getRoleBadgeClass($role);
                        ?>
                        <span class="role-badge <?= $badgeClass; ?>">
                            <?= ucfirst(htmlspecialchars($role)); ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-group">
                    <label><i class="fa-solid fa-envelope me-1"></i> Email</label>
                    <p><?= htmlspecialchars(!empty($data['email']) ? $data['email'] : '-'); ?></p>
                </div>

                <div class="detail-group">
                    <label><i class="fa-solid fa-phone me-1"></i> No. HP</label>
                    <p><?= htmlspecialchars(!empty($data['no_hp']) ? $data['no_hp'] : '-'); ?></p>
                </div>
            </div>

            <div class="detail-group">
                <label><i class="fa-solid fa-location-dot me-1"></i> Alamat</label>
                <p><?= htmlspecialchars(!empty($data['alamat']) ? $data['alamat'] : '-'); ?></p>
            </div>

            <div class="btn-group">
                <a href="../../admin/pengguna.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
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