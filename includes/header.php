<style>
.sidebar-header{
    display:flex;
    align-items:center;
    gap:2px;

    padding-bottom:22px;
    margin-bottom:20px;

    border-bottom:1px solid rgba(255,255,255,.08);
}

.logo{

    width:52px;
    height:52px;

    border-radius:12px;

    background:#2ECC71;

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;

    flex-shrink:0;

}

.logo img{

    width:42px;
    height:42px;

    object-fit:contain;

}

/* Jika menggunakan icon */

.logo-icon{

    color:white;
    font-size:24px;

}

.logo-text h2{

    color:#ffffff;

    font-size:16px;
    font-weight:700;

    line-height:20px;

}

.logo-text span{

    color:#B8C7D3;

    font-size:12px;

}
</style>

<div class="sidebar-header">

    <div class="logo">

        <!-- Jika menggunakan gambar logo -->
        <!-- <img src="../assets/images/logo.png" alt="Logo ISPA"> -->

        <!-- Jika belum punya logo, gunakan icon -->
        
        <div class="logo-icon">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
       

    </div>

    <div class="logo-text">
        <h2>Sistem Pakar ISPA</h2>
        <span>Pada Balita</span>
    </div>

</div>