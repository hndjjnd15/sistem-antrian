<?php

require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Periode Antrian - Bank BBJ</title>
    <link rel="stylesheet" href="css/periode.css">
</head>
<body>
<div class="app">
    <header class="header">
        <div><h1>BANK BBJ</h1><p>KC PARUNGPANJANG</p></div>
        <div class="header-title"><strong>LAPORAN ANTRIAN</strong><span>Rekap Periode</span></div>
    </header>

    <main class="container">
        <section class="filter-card no-print">
            <div class="filter-group">
                <div><label for="tanggalAwal">Tanggal Awal</label><input type="date" id="tanggalAwal"></div>
                <div><label for="tanggalAkhir">Tanggal Akhir</label><input type="date" id="tanggalAkhir"></div>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn-light" id="btnToday">HARI INI</button>
                <button type="button" class="btn-light" id="btnMonth">BULAN INI</button>
                <button type="button" class="btn-primary" id="btnLoad">TAMPILKAN</button>
            </div>
        </section>

        <section class="print-header">
            <h2>LAPORAN ANTRIAN NASABAH</h2>
            <strong>PT BPR BOGOR JABAR (PERSERODA)</strong>
            <p>KC Parungpanjang</p>
            <p id="printPeriode">-</p>
        </section>

        <section class="card report-section">
            <div class="card-title">Ringkasan Laporan</div>
            <table class="summary-table">
                <thead><tr><th>TOTAL PENGUNJUNG</th><th>HARI OPERASIONAL</th><th>RATA-RATA PENGUNJUNG</th></tr></thead>
                <tbody><tr>
                    <td><strong id="total">0</strong></td>
                    <td><strong id="hariAktif">0</strong><small>hari</small></td>
                    <td><strong id="rataRata">0</strong><small>per hari</small></td>
                </tr></tbody>
            </table>
        </section>

        <section class="card report-section">
            <div class="card-title">Statistik Per Layanan</div>
            <div class="table-wrapper">
                <table class="service-table">
                    <thead><tr><th>LAYANAN</th><th>TOTAL PENGUNJUNG</th></tr></thead>
                    <tbody id="serviceList"></tbody>
                    <tfoot><tr><td>TOTAL</td><td id="serviceTotal">0</td></tr></tfoot>
                </table>
            </div>
        </section>

        <section class="card report-section">
            <div class="card-head">
                <div class="card-title">Rekap Harian</div>
                <div class="report-actions no-print">
                    <button type="button" id="btnPrint">CETAK / PDF</button>
                    <button type="button" id="btnCsv">EXPORT CSV</button>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="daily-table">
                    <thead id="tableHead"></thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot"></tfoot>
                </table>
            </div>
        </section>

        <div class="print-footer">
            <div class="signature">
                <p>Parungpanjang, <span id="tanggalCetak"></span></p>
                <p>Petugas,</p>
                <div class="signature-space"></div>
                <p>_______________________</p>
            </div>
        </div>
    </main>
</div>
<script src="js/periode.js"></script>
</body>
</html>
