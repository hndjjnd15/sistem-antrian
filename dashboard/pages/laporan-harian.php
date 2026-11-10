<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$cabang=$pdo->query("
    SELECT id,nama
    FROM cabang
    WHERE status='aktif'
    ORDER BY nama
")->fetchAll();
?>

<div class="page-card">

<div class="page-header">

    <div>
        <h2>Laporan Harian</h2>
        <p>
            Lihat aktivitas antrian berdasarkan
            cabang dan tanggal.
        </p>
    </div>

    <div
        class="report-actions"
        id="harianActions"
        style="display:none">

        <button
            type="button"
            class="btn-secondary"
            id="btnPdfHarian">
            Cetak PDF
        </button>

        <button
            type="button"
            class="btn-primary"
            id="btnCsvHarian">
            Export CSV
        </button>
    </div>

</div>

    <div class="filter-grid">

        <div>
            <label>Cabang</label>
            <select id="laporanCabang">
                <option value="">Pilih Cabang</option>

                <?php foreach($cabang as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['nama']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div>
            <label>Tanggal</label>
            <input
                type="date"
                id="laporanTanggal"
                value="<?= date('Y-m-d') ?>">
        </div>

        <div class="filter-button">
            <button
                type="button"
                class="btn-primary"
                id="btnLaporanHarian">
                Tampilkan
            </button>
        </div>

    </div>

    <div id="laporanLoading" class="loading-box" style="display:none">
        Memuat laporan...
    </div>

    <div id="laporanHasil" style="display:none">

        <div class="report-info">
            <div>
                <span>Cabang</span>
                <strong id="reportCabang">-</strong>
            </div>

            <div>
                <span>Tanggal</span>
                <strong id="reportTanggal">-</strong>
            </div>
        </div>

        <div class="summary-grid">

            <div class="summary-card">
                <span>Total Pengunjung</span>
                <strong id="totalPengunjung">0</strong>
            </div>

            <div class="summary-card">
                <span>Teller</span>
                <strong id="totalTeller">0</strong>
            </div>

            <div class="summary-card">
                <span>Customer Service</span>
                <strong id="totalCs">0</strong>
            </div>

            <div class="summary-card">
                <span>Kredit</span>
                <strong id="totalKredit">0</strong>
            </div>

        </div>

        <div class="report-section">
            <h3>Detail Antrian</h3>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor</th>
                        <th>Layanan</th>
                        <th>Jam Ambil</th>
                        <th>Jam Panggil</th>
                        <th>Jam Selesai</th>
                        <th>Status</th>
                    </tr>
                    </thead>

                    <tbody id="detailLaporan">
                        <tr>
                            <td colspan="7" class="empty">
                                Belum ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>