<?php
session_start();
require_once "../config/koneksi.php";

$page_title = "Dashboard";
include "../includes/sidebar.php";

/**
 * Helper function untuk menghitung jumlah baris data aman dengan prepared statement
 */
function getTotalCount($conn, $query, $params = [], $types = "")
{
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        return 0;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $data['total'] ?? 0;
}

/* ===============================
   TOTAL DATA
================================ */
$totalPenyakit = getTotalCount($conn, "SELECT COUNT(*) AS total FROM tb_penyakit");
$totalGejala   = getTotalCount($conn, "SELECT COUNT(*) AS total FROM tb_gejala");
$totalRule     = getTotalCount($conn, "SELECT COUNT(*) AS total FROM tb_rule");
$totalUser     = getTotalCount($conn, "SELECT COUNT(*) AS total FROM tb_user WHERE role = ?", ['user'], 's');
$totalDiagnosa = getTotalCount($conn, "SELECT COUNT(*) AS total FROM tb_hasil_diagnosis");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Pakar ISPA</title>

    <!-- Font & Icons -->
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
            background: #F3F6FA;
            overflow-x: hidden;
        }

        /* MAIN LAYOUT RESPONSIVE */
        .main-content {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* TOPBAR & PROFIL */
        .topbar {
            min-height: 90px;
            background: #FFFFFF;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 35px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            flex-wrap: wrap;
            gap: 15px;
        }

        .topbar-left h2 {
            color: #063154;
            font-size: 24px;
            font-weight: 700;
        }

        .topbar-left p {
            margin-top: 4px;
            color: #8A97A8;
            font-size: 14px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-image {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #2F9D94;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .profile-info h4 {
            color: #063154;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        .profile-info span {
            color: #8A97A8;
            font-size: 13px;
            display: block;
            margin-top: 3px;
            font-weight: 400;
        }

        /* DASHBOARD BODY */
        .dashboard {
            padding: 25px;
        }

        /* CARDS SUMMARY GRID */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }

        .card:hover {
            transform: translateY(-6px);
        }

        .icon {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 22px;
            flex-shrink: 0;
        }

        .penyakit { background: #E74C3C; }
        .gejala   { background: #3498DB; }
        .rule     { background: #9B59B6; }
        .user     { background: #F39C12; }
        .diagnosa { background: #2F9D94; }

        .card-text h4 {
            color: #7F8C8D;
            font-size: 13px;
            font-weight: 500;
        }

        .card-text h2 {
            margin-top: 5px;
            color: #063154;
            font-size: 26px;
        }

        /* FORM DIAGNOSA SPARKLINE CARD */
        .diagnosa-card {
            margin-top: 25px;
            background: #FFFFFF;
            border-radius: 18px;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
            gap: 20px;
        }

        .diagnosa-left {
            min-width: 160px;
        }

        .diagnosa-left h4 {
            color: #7F8C8D;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .diagnosa-left h1 {
            color: #2F9D94;
            font-size: 44px;
            font-weight: 700;
        }

        .diagnosa-right {
            flex: 1;
            width: 100%;
            position: relative;
            height: 120px;
        }

        /* CHARTS & SUMMARY ROW GRID */
        .dashboard-row {
            margin-top: 25px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .chart-card,
        .summary-card {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h3,
        .summary-card h3 {
            color: #063154;
            font-size: 18px;
        }

        .card-header span {
            color: #8B98A7;
            font-size: 13px;
        }

        .summary-card h3 {
            margin-bottom: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #EDF2F7;
        }

        .summary-item:last-child {
            border: none;
        }

        .summary-item span {
            color: #7F8C8D;
            font-size: 14px;
        }

        .summary-item strong {
            color: #063154;
            font-size: 15px;
        }

        /* CONTAINER CANVAS RESPONSIF */
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        /* MEDIA QUERIES */
        @media (max-width: 1024px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }

            .topbar {
                padding: 15px 20px 15px 75px !important;
            }

            .dashboard {
                padding: 15px;
            }

            .diagnosa-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .diagnosa-right {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .topbar-left h2 {
                font-size: 20px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .card-text h2 {
                font-size: 22px;
            }

            .diagnosa-left h1 {
                font-size: 36px;
            }

            .chart-container {
                height: 230px;
            }
        }
    </style>
</head>

<body>

    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h2>Dashboard</h2>
                <p>Selamat Datang di Sistem Pakar ISPA Pada Balita</p>
            </div>
            <div class="profile">
                <div class="profile-image">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="profile-info">
                    <h4><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></h4>
                    <span>Admin Sistem</span>
                </div>
            </div>
        </div>

        <!-- DASHBOARD BODY -->
        <div class="dashboard">
            <!-- CARDS SUMMARY -->
            <div class="cards">
                <div class="card">
                    <div class="icon penyakit">
                        <i class="fa-solid fa-virus"></i>
                    </div>
                    <div class="card-text">
                        <h4>Total Penyakit</h4>
                        <h2><?= $totalPenyakit; ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="icon gejala">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                    <div class="card-text">
                        <h4>Total Gejala</h4>
                        <h2><?= $totalGejala; ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="icon rule">
                        <i class="fa-solid fa-code-branch"></i>
                    </div>
                    <div class="card-text">
                        <h4>Total Rule</h4>
                        <h2><?= $totalRule; ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="icon user">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="card-text">
                        <h4>Total Pengguna</h4>
                        <h2><?= $totalUser; ?></h2>
                    </div>
                </div>
            </div>

            <!-- HIGHLIGHT DIAGNOSA -->
            <div class="diagnosa-card">
                <div class="diagnosa-left">
                    <h4>Total Diagnosa</h4>
                    <h1><?= $totalDiagnosa; ?></h1>
                </div>
                <div class="diagnosa-right">
                    <canvas id="totalDiagnosaChart"></canvas>
                </div>
            </div>

            <!-- GRAFIK & RINGKASAN -->
            <div class="dashboard-row">
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Grafik Diagnosa ISPA</h3>
                        <span>Tahun 2026</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="diagnosaChart"></canvas>
                    </div>
                </div>

                <div class="summary-card">
                    <h3>Ringkasan Data</h3>
                    <div class="summary-item">
                        <span>Penyakit</span>
                        <strong><?= $totalPenyakit; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Gejala</span>
                        <strong><?= $totalGejala; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Rule</span>
                        <strong><?= $totalRule; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Pengguna</span>
                        <strong><?= $totalUser; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Diagnosa</span>
                        <strong><?= $totalDiagnosa; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Bar Chart
        const ctx = document.getElementById('diagnosaChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Diagnosa',
                    data: [12, 18, 10, 25, 17, 30],
                    backgroundColor: '#2F9D94',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 }
                    }
                }
            }
        });

        // Sparkline Chart
        const total = document.getElementById("totalDiagnosaChart");
        new Chart(total, {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', '', '', '', '', ''],
                datasets: [{
                    data: [220, 260, 240, 320, 290, 340, 300, 370, 350, 410],
                    borderColor: '#2F9D94',
                    borderWidth: 3,
                    pointRadius: 0,
                    tension: .45,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    </script>
</body>

</html>