<?php

require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Antrian Bank BBJ</title>
    <link rel="stylesheet" href="css/laporan.css">
</head>
<body>
<div class="app">
    <!-- =====================================================
         HEADER
    ====================================================== -->
    <header class="header">
        <div>
            <h1>BANK BBJ</h1>
            <p>KC PARUNGPANJANG</p>
        </div>
        <div class="header-title">
            <strong>LAPORAN ANTRIAN</strong>
            <span>Statistik Harian</span>
        </div>
    </header>

    <!-- =====================================================
         CONTENT
    ====================================================== -->
    <main class="container">
        <!-- =================================================
             FILTER
        ================================================== -->
        <section class="filter-card">
            <div>
                <label for="filterTanggal">Tanggal Laporan</label>
                <input type="date" id="filterTanggal">
            </div>
            <button type="button" id="btnRefresh">
                TAMPILKAN
            </button>
        </section>

        <!-- =================================================
             SUMMARY
        ================================================== -->
        <section class="summary-grid">
            <div class="summary-card">
                <span>TOTAL PENGUNJUNG</span>
                <strong id="totalAntrian">0</strong>
            </div>
            <div class="summary-card">
                <span>SELESAI</span>
                <strong id="totalSelesai">0</strong>
            </div>
            <div class="summary-card">
                <span>MENUNGGU</span>
                <strong id="totalMenunggu">0</strong>
            </div>
            <div class="summary-card">
                <span>SEDANG DILAYANI</span>
                <strong id="totalDipanggil">0</strong>
            </div>
        </section>

        <!-- =================================================
             SERVICE + BUSIEST
        ================================================== -->
        <section class="dashboard-grid">
            <!-- =============================================
                 PER LAYANAN
            ============================================== -->
            <div class="card">
                <div class="card-header">
                    <h2>Statistik Layanan</h2>
                </div>
                <div class="service-list" id="serviceList">
                </div>
            </div>

            <!-- =============================================
                 JAM TERSIBUK
            ============================================== -->
            <div class="card busiest-card">
                <span>JAM TERSIBUK</span>
                <strong id="jamTersibuk">-</strong>
                <p><b id="jumlahTersibuk">0</b>pengunjung</p>
            </div>
        </section>

        <!-- =================================================
             PER JAM
        ================================================== -->
        <section class="card">
            <div class="card-header">
                <h2>Pengunjung Per Jam</h2>
            </div>
            <div class="hour-chart" id="hourChart">
            </div>
        </section>

        <!-- =================================================
             PANGGILAN TERAKHIR
        ================================================== -->
        <section class="card">
            <div class="card-header">
                <h2>Aktivitas Panggilan</h2>
            </div>
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
                    <tbody id="callTable">
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="js/laporan.js"></script>
</body>
</html>