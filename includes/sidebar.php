<!-- Tombol Toggle (Hamburger) - Hanya muncul di Mobile -->
<button class="toggle-sidebar-btn" id="btnToggleSidebar">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- Layer Gelap (Overlay) saat sidebar terbuka di HP -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <!-- Header Sidebar -->
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-lungs"></i>
            </div>
            <div class="brand-text">
                <h3>Sistem Pakar ISPA</h3>
                <span>Pada Balita</span>
            </div>
        </div>
        <!-- Tombol Close Sidebar (Khusus HP) -->
        <button class="close-sidebar-btn" id="btnCloseSidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Menu Navigation -->
    <ul class="sidebar-menu">
        <li class="<?= ($page_title == 'Dashboard') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-label">MASTER DATA</li>
        <li class="<?= ($page_title == 'Data Penyakit') ? 'active' : ''; ?>">
            <a href="penyakit.php">
                <i class="fa-solid fa-virus"></i>
                <span>Penyakit</span>
            </a>
        </li>
        <li class="<?= ($page_title == 'Data Gejala') ? 'active' : ''; ?>">
            <a href="gejala.php">
                <i class="fa-solid fa-stethoscope"></i>
                <span>Gejala</span>
            </a>
        </li>
        <li class="<?= ($page_title == 'Data Rule') ? 'active' : ''; ?>">
            <a href="rule.php">
                <i class="fa-solid fa-code-branch"></i>
                <span>Rule</span>
            </a>
        </li>

        <li class="menu-label">DATA</li>
        <li class="<?= ($page_title == 'Data Pengguna') ? 'active' : ''; ?>">
            <a href="pengguna.php">
                <i class="fa-solid fa-users"></i>
                <span>Pengguna</span>
            </a>
        </li>
        <li class="<?= ($page_title == 'Riwayat Diagnosa') ? 'active' : ''; ?>">
            <a href="riwayat.php">
                <i class="fa-solid fa-file-medical"></i>
                <span>Riwayat Diagnosa</span>
            </a>
        </li>

        <li class="logout-item">
            <a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<style>
/* CSS DASAR SIDEBAR */
.sidebar {
    width: 270px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #003354; /* Warna sesuai desain Anda */
    color: white;
    z-index: 1050;
    transition: all 0.3s ease;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    width: 40px;
    height: 40px;
    background: #2F9D94;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.brand-text h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.brand-text span {
    font-size: 12px;
    opacity: 0.7;
}

.sidebar-menu {
    list-style: none;
    padding: 15px;
    margin: 0;
    flex: 1;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu li.menu-label {
    font-size: 11px;
    font-weight: 700;
    color: #8A97A8;
    margin: 20px 0 8px 12px;
    letter-spacing: 0.5px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #C2D1D9;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.sidebar-menu a:hover,
.sidebar-menu li.active a {
    background: #2F9D94;
    color: #FFFFFF;
}

.close-sidebar-btn,
.toggle-sidebar-btn {
    background: transparent;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
    display: none; /* Default sembunyi di desktop */
}

.toggle-sidebar-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;
    background: #003354;
    color: white;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    display: none;
    backdrop-filter: blur(2px);
}

/* RESPONSIVE DESAIN FOR MOBILE (768px kebawah) */
@media (max-width: 768px) {
    .toggle-sidebar-btn {
        display: flex; /* Tampilkan tombol hamburger di HP */
    }

    .close-sidebar-btn {
        display: block; /* Tampilkan tombol X di header sidebar */
    }

    .sidebar {
        left: -270px; /* Sembunyikan sidebar ke kiri luar layar */
    }

    .sidebar.active {
        left: 0; /* Munculkan sidebar saat kelas .active ditambah */
    }

    .sidebar-overlay.active {
        display: block; /* Tampilkan layer gelap */
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const btnToggle = document.getElementById("btnToggleSidebar");
    const btnClose = document.getElementById("btnCloseSidebar");

    // Buka Sidebar
    if(btnToggle) {
        btnToggle.addEventListener("click", function() {
            sidebar.classList.add("active");
            overlay.classList.add("active");
        });
    }

    // Tutup Sidebar via tombol X
    if(btnClose) {
        btnClose.addEventListener("click", function() {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    }

    // Tutup Sidebar jika klik di luar area sidebar (overlay)
    if(overlay) {
        overlay.addEventListener("click", function() {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    }
});
</script>