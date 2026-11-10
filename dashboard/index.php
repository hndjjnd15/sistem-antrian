<?php
require_once __DIR__.'/../includes/auth.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard Admin - Bank BBJ</title>
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<!-- =====================================================
     GLOBAL CONFIRM POPUP
===================================================== -->

<div class="modal" id="globalConfirmModal">

    <div class="modal-box modal-small">

        <div class="modal-header">

            <h3 id="globalConfirmTitle">
                Konfirmasi
            </h3>

            <button
                type="button"
                class="modal-close"
                id="globalConfirmClose">
                ×
            </button>

        </div>

        <div class="confirm-content">

            <div class="confirm-icon">
                ?
            </div>

            <p id="globalConfirmMessage">
                Apakah Anda yakin?
            </p>

        </div>

        <div class="modal-actions">

            <button
                type="button"
                class="btn-secondary"
                id="globalConfirmCancel">
                Batal
            </button>

            <button
                type="button"
                class="btn-primary"
                id="globalConfirmOk">
                Ya, Lanjutkan
            </button>

        </div>

    </div>

</div>


<!-- =====================================================
     GLOBAL TOAST
===================================================== -->

<div
    id="globalToast"
    class="global-toast">

    <div
        class="toast-icon"
        id="globalToastIcon">
        ✓
    </div>

    <div class="toast-content">

        <strong id="globalToastTitle">
            Berhasil
        </strong>

        <span id="globalToastMessage">
            Data berhasil disimpan.
        </span>

    </div>

</div>
<div class="app">

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <strong>BANK BBJ</strong>
            <span>Sistem Antrian</span>
        </div>

        <nav class="menu">
            <a href="#" class="menu-item active" data-page="home">
                Dashboard
            </a>

            <a href="#" class="menu-item" data-page="laporan-harian">
                Laporan Harian
            </a>

            <a href="#" class="menu-item" data-page="laporan-periode">
                Laporan Periode
            </a>

            <a href="#" class="menu-item" data-page="users">
                Manajemen User
            </a>

            <a href="#" class="menu-item" data-page="cabang">
                Master Cabang
            </a>

            <a href="#" class="menu-item" data-page="layanan">
                Master Layanan
            </a>

            <a href="#" class="menu-item" data-page="status">
                Status Antrian
            </a>
        </nav>

        <div class="sidebar-footer">
            <a class="btn-logout" href="../auth/logout.php">Keluar</a>
        </div>
    </aside>
    <div id="sidebarOverlay" class="sidebar-overlay"></div>
    <div class="main">

        <header class="header">
            <button id="btnSidebar">☰</button>

            <div>
                <h1 id="pageTitle">Dashboard</h1>
                <small>
                    <?= htmlspecialchars($_SESSION['nama']??'Admin') ?>
                </small>
            </div>

            <div class="header-right">
                <?= htmlspecialchars($_SESSION['nama_cabang']??'') ?>
            </div>
        </header>

        <main class="content" id="mainContent">
            <div class="loading">Memuat...</div>
        </main>

        <footer class="footer">
            © <?= date('Y') ?> Bank BBJ
        </footer>

    </div>

</div>
<script src="js/popup.js"></script>
<script src="js/home.js"></script>
<script src="js/users.js"></script>
<script src="js/cabang.js"></script>
<script src="js/layanan.js"></script>
<script src="js/laporan-harian.js"></script>
<script src="js/laporan-periode.js"></script>
<script src="js/status.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>