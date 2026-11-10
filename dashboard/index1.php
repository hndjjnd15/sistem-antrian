<?php

require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sistem Antrian</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<header class="topbar">

    <div>
        <strong>BANK BBJ</strong>
        <span>KC Parungpanjang</span>
    </div>
    <a href="../users/">
        Manajemen User
    </a>
    <div class="user">
        <?= htmlspecialchars($_SESSION['nama']) ?>
        |
        <a href="../auth/logout.php">Keluar</a>
    </div>

</header>

<main class="container">
    <div class="title">
        <h1>Dashboard Monitoring</h1>
        <p id="tanggal">-</p>
    </div>

    <section class="summary-grid">

        <div class="card">
            <span>TOTAL PENGUNJUNG</span>
            <strong id="total">0</strong>
        </div>

        <div class="card">
            <span>MENUNGGU</span>
            <strong id="menunggu">0</strong>
        </div>

        <div class="card">
            <span>DIPANGGIL</span>
            <strong id="dipanggil">0</strong>
        </div>

        <div class="card">
            <span>SELESAI</span>
            <strong id="selesai">0</strong>
        </div>

    </section>

    <section class="content-grid">
        <div class="box">
            <h2>Pengunjung Per Layanan</h2>
            <div id="layananList"></div>
        </div>
        <div class="box">
            <h2>Status Print Terakhir</h2>
            <div
                id="printStatus"
                class="status-box"
            >
                -
            </div>
        </div>

    </section>

    <section class="box">

        <h2>Panggilan Terakhir</h2>

        <div class="table-wrapper">

            <table>
                <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Layanan</th>
                    <th>Loket</th>
                    <th>Waktu</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody id="callTable"></tbody>

            </table>

        </div>

    </section>

</main>

<script src="js/dashboard.js"></script>

</body>
</html>