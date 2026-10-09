<?php
$page_title = "Penanganan";
include "../includes/sidebar.php";
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Penanganan | Sistem Pakar ISPA</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
    /*==================================================
        RESET
        ==================================================*/
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }
        body{
            background:#F4F7FB;
            color:#2C3E50;
        }
        /*==================================================
        MAIN CONTENT
        ==================================================*/
        .main-content{
            margin-left:250px;
            min-height:100vh;
            transition:.3s;
        }
        /*==================================================
        TOPBAR
        ==================================================*/
        .topbar{
            height:90px;
            background:#FFFFFF;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:0 65px;
            box-shadow:0 3px 15px rgba(0,0,0,.05);
            border-bottom:1px solid #EEF2F7;
        }
        .topbar-title h2{
            color:#063154;
            font-size:28px;
            font-weight:700;
        }
        .topbar-title p{
            color:#8A97A5;
            margin-top:4px;
            font-size:14px;
        }
        .admin-profile{
            display:flex;
            align-items:center;
            gap:14px;
        }
        .admin-avatar{
            width:45px;
            height:45px;
            border-radius:50%;
            background:#2F9D94;
            color:#FFF;
            display:flex;
            justify-content:center;
            align-items:center;
            font-size:18px;
        }
        .admin-info h4{
            color:#063154;
            font-size:15px;
            font-weight:600;
        }
        .admin-info span{
            font-size:12px;
            color:#95A5A6;
        }
        /*==================================================
        CONTENT
        ==================================================*/
        .content{
            padding:30px;
        }
        .card{
            background:#FFFFFF;
            border-radius:18px;
            padding:28px;
            box-shadow:0 10px 30px rgba(0,0,0,.05);
            border:1px solid #ECF0F3;
            transition:.3s;
        }
        .card:hover{
            transform:translateY(-2px);
            box-shadow:0 18px 35px rgba(0,0,0,.07);
        }
        /*==================================================
        HEADER
        ==================================================*/
        .page-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:28px;
        }
        .page-header h3{
            font-size:28px;
            color:#063154;
            font-weight:700;
        }
        .page-header p{
            margin-top:6px;
            color:#7F8C8D;
            font-size:14px;
        }
        /*==================================================
        BUTTON
        ==================================================*/
        .btn-add{
            border:none;
            outline:none;
            cursor:pointer;
            background:#28A745;
            color:#FFF;
            padding:12px 20px;
            border-radius:10px;
            font-size:14px;
            font-weight:600;
            transition:.3s;
            display:flex;
            align-items:center;
            gap:8px;
        }
        .btn-add:hover{
            background:#23913D;
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(40,167,69,.25);
        }
        /*==================================================
        TOOLBAR
        ==================================================*/
        .toolbar{
            margin-bottom:25px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        /*==================================================
        SEARCH
        ==================================================*/
        .search-box{
            width:380px;
            position:relative;
        }
        .search-box i{
            position:absolute;
            left:16px;
            top:50%;
            transform:translateY(-50%);
            color:#94A3B8;
            font-size:14px;
        }
        .search-box input{
            width:100%;
            height:46px;
            padding-left:45px;
            padding-right:15px;
            border:1px solid #E2E8F0;
            border-radius:10px;
            outline:none;
            font-size:14px;
            transition:.3s;
            background:#FFF;
        }
        .search-box input:focus{
            border-color:#2F9D94;
            box-shadow:0 0 0 4px rgba(47,157,148,.12);
        }
        /*==================================================
        ANIMATION
        ==================================================*/
        button{
            transition:.25s;
        }
        button:active{
            transform:scale(.97);
        }
        /*==================================================
        TABLE
        ==================================================*/
        .table-responsive{
            width:100%;
            overflow-x:auto;
            border:1px solid #E9EEF3;
            border-radius:12px;
        }
        table{
            width:100%;
            border-collapse:collapse;
            background:#FFF;
        }
        thead{
            background:#F8FAFC;
        }
        thead th{
            padding:16px 18px;
            text-align:left;
            color:#063154;
            font-size:14px;
            font-weight:600;
            border-bottom:1px solid #E5E7EB;
        }
        tbody td{
            padding:18px;
            border-bottom:1px solid #EDF2F7;
            vertical-align:top;
            font-size:14px;
            color:#475569;
        }
        tbody tr{
            transition:.25s;
        }
        tbody tr:hover{
            background:#F8FCFC;
        }
        /*==================================================
        NUMBER
        ==================================================*/
        tbody td:first-child{
            font-weight:600;
            color:#063154;
        }
        /*==================================================
        PENYAKIT
        ==================================================*/
        tbody td:nth-child(2){
            font-weight:600;
            color:#063154;
        }
        /*==================================================
        LIST PENANGANAN
        ==================================================*/
        .penanganan-list{
            margin-left:18px;
            line-height:1.9;
            color:#475569;
        }
        .penanganan-list li{
            margin-bottom:6px;
        }
        .penanganan-list li:last-child{
            margin-bottom:0;
        }
        /*==================================================
        ACTION
        ==================================================*/
        .action-group{
            display:flex;
            align-items:center;
            gap:8px;
        }
        .action-group button{
            border:none;
            padding:7px 14px;
            border-radius:7px;
            cursor:pointer;
            font-size:12px;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:5px;
            transition:.25s;
        }
        .btn-edit{
            background:#EAF8F2;
            color:#28A745;
            border:1px solid #BFE9CF;
        }
        .btn-edit:hover{
            background:#28A745;
            color:#FFF;
        }
        .btn-delete{
            background:#FFF0F0;
            color:#DC3545;
            border:1px solid #F4C2C7;
        }
        .btn-delete:hover{
            background:#DC3545;
            color:#FFF;
        }
        /*==================================================
        TABLE FOOTER
        ==================================================*/
        .table-footer{
            margin-top:22px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:15px;
        }
        .table-info{
            color:#64748B;
            font-size:13px;
        }
        /*==================================================
        PAGINATION
        ==================================================*/
        .pagination{
            display:flex;
            gap:8px;
        }
        .page{
            width:36px;
            height:36px;
            border:1px solid #D9E2EC;
            background:#FFF;
            border-radius:9px;
            cursor:pointer;
            font-weight:600;
            color:#475569;
            transition:.25s;
        }
        .page:hover{
            background:#2F9D94;
            color:#FFF;
            border-color:#2F9D94;
        }
        .page.active{
            background:#2F9D94;
            color:#FFF;
            border-color:#2F9D94;
        }
        /*==================================================
        SCROLLBAR
        ==================================================*/
        .table-responsive::-webkit-scrollbar{
            height:8px;
        }
        .table-responsive::-webkit-scrollbar-track{
            background:#F1F5F9;
        }
        .table-responsive::-webkit-scrollbar-thumb{
            background:#CBD5E1;
            border-radius:20px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover{
            background:#94A3B8;
        }
        /*==================================================
        RESPONSIVE
        ==================================================*/
        @media(max-width:992px){
            .main-content{
                margin-left:220px;
            }
            .search-box{
                width:300px;
            }
        }
        @media(max-width:768px){
            .main-content{
                margin-left:0;
            }
            .content{
                padding:18px;
            }
            .topbar{
                flex-direction:column;
                align-items:flex-start;
                height:auto;
                padding:18px;
                gap:15px;
            }
            .page-header{
                flex-direction:column;
                align-items:flex-start;
                gap:18px;
            }
            .toolbar{
                flex-direction:column;
                align-items:flex-start;
            }
            .search-box{
                width:100%;
            }
            .action-group{
                flex-direction:column;
                width:100%;
            }
            .action-group button{
                width:100%;
                justify-content:center;
            }
            .table-footer{
                flex-direction:column;
                align-items:flex-start;
            }
        }
        /*=========================================
        ROW ANIMATION
        =========================================*/
        tbody tr{
            transition:.25s ease;
        }
        tbody tr:hover{
            background:#F7FCFC;
        }
        /*=========================================
        LIST STYLE
        =========================================*/
        .penanganan-list{
            padding-left:20px;
        }
        .penanganan-list li{
            margin-bottom:8px;
        }
        .penanganan-list li:last-child{
            margin-bottom:0;
        }
        /*=========================================
        BUTTON EFFECT
        =========================================*/
        .btn-add,
        .btn-edit,
        .btn-delete{
            transition:.25s;
        }
        .btn-add:hover,
        .btn-edit:hover,
        .btn-delete:hover{
            transform:translateY(-2px);
        }
        /*=========================================
        CARD EFFECT
        =========================================*/
        .card{
            transition:.3s;
        }
        .card:hover{
            box-shadow:0 18px 40px rgba(0,0,0,.08);
        }
    </style>
</head>
<body>

<div class="main-content">
    <!-- =========================
            TOPBAR
    ========================== -->
    <div class="topbar">
        <div class="topbar-title">
            <h2>Penanganan</h2>
            <p>
                Kelola seluruh data saran penanganan penyakit ISPA.
            </p>
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
    <!-- =========================
            CONTENT
    ========================== -->
    <div class="content">

        <div class="card">
            <!-- HEADER -->
            <div class="page-header">
                <div>
                    <h3>Data Penanganan</h3>
                    <p>
                        Daftar seluruh saran penanganan penyakit ISPA.
                    </p>
                </div>
            </div>
            <!-- SEARCH -->
            <div class="toolbar">

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        placeholder="Cari penanganan...">
                </div>
                <button class="btn-add">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Penanganan
                </button>
            </div>
            <!-- TABLE -->

            <div class="table-responsive">

                <table>
                    <thead>
                        <tr>
                            <th width="70">
                                No
                            </th>
                            <th width="180">
                                Penyakit
                            </th>
                            <th>
                                Saran Penanganan
                            </th>
                            <th width="180">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                   <tbody>

                        <?php
                        $penanganan = [
                            [
                                "id" => 1,
                                "penyakit" => "Laringitis",
                                "saran" => [
                                    "Istirahat dan hindari berbicara terlalu keras.",
                                    "Perbanyak minum air hangat.",
                                    "Gunakan humidifier atau uap hangat.",
                                    "Konsultasi ke dokter jika suara hilang lebih dari satu minggu."
                                ]
                            ],

                            [
                                "id" => 2,
                                "penyakit" => "Bronkitis",
                                "saran" => [
                                    "Perbanyak minum air putih.",
                                    "Istirahat yang cukup.",
                                    "Hindari asap rokok.",
                                    "Minum obat sesuai anjuran dokter."
                                ]
                            ],

                            [
                                "id" => 3,
                                "penyakit" => "Bronkiolitis",
                                "saran" => [
                                    "Bersihkan hidung menggunakan saline.",
                                    "Pastikan kebutuhan cairan tercukupi.",
                                    "Pantau suhu tubuh.",
                                    "Segera ke dokter bila sesak bertambah."
                                ]
                            ],

                            [
                                "id" => 4,
                                "penyakit" => "Pneumonia",
                                "saran" => [
                                    "Minum antibiotik sesuai resep.",
                                    "Istirahat total.",
                                    "Perbanyak konsumsi air.",
                                    "Segera ke rumah sakit bila sesak berat."
                                ]
                            ]
                        ];
                        foreach($penanganan as $row){

                        ?>
                        <tr>
                            <td>
                                <strong><?= $row['id']; ?></strong>
                            </td>
                            <td>
                                <strong><?= $row['penyakit']; ?></strong>
                            </td>
                            <td>
                                <ul class="penanganan-list">
                                    <?php foreach($row['saran'] as $item){ ?>
                                        <li><?= $item; ?></li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td>

                                <div class="action-group">
                                    <button class="btn-edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Edit
                                    </button>
                                    <button class="btn-delete">
                                        <i class="fa-solid fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <?php
                        }
                        ?>
                        </tbody>
                </table>
            </div>
           <div class="table-footer">

                <div class="table-info">
                    Menampilkan
                    <strong>1 - <?= count($penanganan); ?></strong>
                    dari
                    <strong><?= count($penanganan); ?></strong>
                    data penanganan
                </div>
                <div class="pagination">
                    <button class="page active">
                        1
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>