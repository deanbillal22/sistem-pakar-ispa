<?php
$page_title = "Detail Riwayat Diagnosis";

// Path ke file koneksi database
require_once "../../config/koneksi.php";

// Penanganan variabel koneksi 
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

/**
 * Class DetailRiwayat
 * Kelola pengambilan data detail riwayat diagnosis 
 */
class DetailRiwayat
{
    private $db;
    private $idRiwayat;
    private $data = null;

    /**
     * Constructor
     * @param mysqli $dbConnection
     * @param string|int $id
     */
    public function __construct($dbConnection, $id)
    {
        $this->db = $dbConnection;
        $this->idRiwayat = trim($id);
    }

    /**
     * Validasi ketersediaan ID Riwayat
     * @return bool
     */
    public function isValidInput(): bool
    {
        return !empty($this->idRiwayat);
    }

    /**
     * Mengambil data riwayat diagnosis dari database
     * @return bool
     */
    public function loadDetailData(): bool
    {
        if (!$this->isValidInput()) {
            return false;
        }

        // Coba query dari tb_hasil_diagnosis
        $sql1 = "SELECT r.*, u.nama_lengkap AS nama_user, u.email, p.nama_penyakit, p.deskripsi, p.penanganan
                 FROM tb_hasil_diagnosis r
                 LEFT JOIN tb_user u ON r.id_user = u.id_user
                 LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit
                 WHERE r.id_hasil = ? LIMIT 1";

        if ($stmt = mysqli_prepare($this->db, $sql1)) {
            mysqli_stmt_bind_param($stmt, "s", $this->idRiwayat);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $this->data = $row;
                mysqli_stmt_close($stmt);
                return true;
            }
            mysqli_stmt_close($stmt);
        }

        // Fallback query ke tb_riwayat_diagnosis jika query pertama tidak menghasilkan data
        $sql2 = "SELECT r.*, u.nama_lengkap AS nama_user, u.email, p.nama_penyakit, p.deskripsi, p.penanganan
                 FROM tb_riwayat_diagnosis r
                 LEFT JOIN tb_user u ON r.id_user = u.id_user
                 LEFT JOIN tb_penyakit p ON r.id_penyakit = p.id_penyakit
                 WHERE r.id_riwayat = ? LIMIT 1";

        if ($stmt = mysqli_prepare($this->db, $sql2)) {
            mysqli_stmt_bind_param($stmt, "s", $this->idRiwayat);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $this->data = $row;
                mysqli_stmt_close($stmt);
                return true;
            }
            mysqli_stmt_close($stmt);
        }

        return false;
    }

    /**
     * Getter seluruh data riwayat
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Mengambil nilai Certainty Factor (CF)
     * @return float
     */
    public function getNilaiCF(): float
    {
        return (float) ($this->data['nilai_cf'] ?? $this->data['cf'] ?? 0);
    }

    /**
     * Mengambil persentase kepastian
     * @return float
     */
    public function getPersentase(): float
    {
        if (isset($this->data['persentase'])) {
            return (float) $this->data['persentase'];
        }
        return $this->getNilaiCF() * 100;
    }

    /**
     * Format tanggal diagnosis
     * @return string
     */
    public function getFormattedTanggal(): string
    {
        $rawDate = $this->data['tanggal_diagnosa'] ?? $this->data['tanggal'] ?? date('Y-m-d H:i:s');
        return date('d F Y - H:i', strtotime($rawDate)) . ' WIB';
    }
}

// ==========================================================
// INISIALISASI & INSEPSI REQUEST
// ==========================================================
$idInput = $_GET['id_hasil'] ?? $_GET['id'] ?? '';
$detailModel = new DetailRiwayat($conn, $idInput);

if (!$detailModel->isValidInput() || !$detailModel->loadDetailData()) {
    echo "<script>
            alert('Data riwayat tidak ditemukan!'); 
            window.location.href='../../admin/riwayat.php';
          </script>";
    exit();
}

$data = $detailModel->getData();

// Include Sidebar
if (file_exists("../../includes/sidebar.php")) {
    include "../../includes/sidebar.php";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Diagnosis | Sistem Pakar</title>

    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #F4F7FB;
            color: #2C3E50;
            overflow-x: hidden;
        }

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
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: #063154;
            cursor: pointer;
        }

        .topbar h2 {
            color: #063154;
            font-size: 24px;
            font-weight: 700;
        }

        /* CONTENT AREA */
        .content {
            padding: 30px;
        }

        /* CARD STYLING */
        .card {
            background: #FFF;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .04);
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #EEF2F7;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #F1F5F9;
            padding-bottom: 20px;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .card-header h3 {
            color: #063154;
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            background: #6C757D;
            color: #FFF;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: .25s;
        }

        .btn-back:hover {
            background: #5A6268;
        }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .info-box {
            background: #F8FAFC;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
        }

        .info-box label {
            font-size: 12px;
            color: #64748B;
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box p {
            font-size: 15px;
            color: #063154;
            font-weight: 600;
            word-break: break-word;
            margin: 0;
        }

        /* RESULT BOX */
        .result-box {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .result-box h4 {
            color: #166534;
            font-size: 18px;
            margin-bottom: 10px;
            word-break: break-word;
        }

        .result-box p {
            color: #15803D;
            font-size: 14px;
            line-height: 1.6;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge-cf {
            background: #28A745;
            color: #FFF;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-percent {
            background: #2563EB;
            color: #FFF;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        /* SECTION DETAILS */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #063154;
            margin-bottom: 12px;
            border-left: 4px solid #2F9D94;
            padding-left: 12px;
        }

        .content-block {
            background: #FAFAFA;
            border: 1px solid #E2E8F0;
            padding: 18px;
            border-radius: 12px;
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 22px;
            word-break: break-word;
        }

        /* MEDIA QUERIES (RESPONSIVE BREAKPOINTS) */
        @media screen and (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }

            .toggle-btn {
                display: block;
            }

            .topbar {
                padding: 0 20px;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }

        @media screen and (max-width: 768px) {
            .topbar {
                height: 70px;
            }

            .topbar h2 {
                font-size: 20px;
            }

            .content {
                padding: 15px;
            }

            .card {
                padding: 20px;
                border-radius: 14px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header h3 {
                font-size: 18px;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .result-box {
                padding: 16px;
            }

            .result-box h4 {
                font-size: 16px;
            }
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
                <h2>Detail Riwayat</h2>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-file-medical" style="color: #2F9D94;"></i> Detail Riwayat Diagnosis</h3>
                    <a href="../../admin/riwayat.php" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <!-- Informasi Pengguna & Tanggal -->
                <div class="info-grid">
                    <div class="info-box">
                        <label><i class="fa-solid fa-user me-1"></i> Nama Pengguna</label>
                        <p><?= htmlspecialchars($data['nama_user'] ?? $data['nama'] ?? 'Umum'); ?></p>
                    </div>
                    <div class="info-box">
                        <label><i class="fa-solid fa-envelope me-1"></i> Email Pengguna</label>
                        <p><?= htmlspecialchars($data['email'] ?? '-'); ?></p>
                    </div>
                    <div class="info-box">
                        <label><i class="fa-solid fa-calendar-days me-1"></i> Tanggal Diagnosis</label>
                        <p><?= htmlspecialchars($detailModel->getFormattedTanggal()); ?></p>
                    </div>
                </div>

                <!-- Hasil Diagnosis -->
                <div class="result-box">
                    <h4>Penyakit Terdiagnosa: <u><?= htmlspecialchars($data['nama_penyakit'] ?? 'Tidak Diketahui'); ?></u></h4>
                    <p>
                        <span>Tingkat Kepastian:</span>
                        <span class="badge-cf">CF <?= number_format($detailModel->getNilaiCF(), 2); ?></span>
                        <span>atau setara dengan</span>
                        <span class="badge-percent"><?= round($detailModel->getPersentase()); ?>%</span>
                    </p>
                </div>

                <!-- Deskripsi Penyakit -->
                <?php if (!empty($data['deskripsi'])) : ?>
                    <div class="section-title">Deskripsi Penyakit</div>
                    <div class="content-block">
                        <?= nl2br(htmlspecialchars($data['deskripsi'])); ?>
                    </div>
                <?php endif; ?>

                <!-- Penanganan -->
                <?php if (!empty($data['penanganan'])) : ?>
                    <div class="section-title">Solusi & Penanganan</div>
                    <div class="content-block">
                        <?= nl2br(htmlspecialchars($data['penanganan'])); ?>
                    </div>
                <?php endif; ?>

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