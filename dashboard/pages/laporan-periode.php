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

$awal=date('Y-m-01');
$akhir=date('Y-m-d');
?>

<div class="page-card">

    <div class="page-header">

        <div>
            <h2>Laporan Periode</h2>
            <p>
                Lihat rekap antrian berdasarkan
                cabang dan rentang tanggal.
            </p>
        </div>

        <div
            class="report-actions"
            id="periodeActions"
            style="display:none">

            <button
                type="button"
                class="btn-secondary"
                id="btnPdfPeriode">
                Cetak PDF
            </button>

            <button
                type="button"
                class="btn-primary"
                id="btnCsvPeriode">
                Export CSV
            </button>

        </div>

    </div>

    <div class="filter-grid periode-filter">

        <div>
            <label>Cabang</label>
            <select id="periodeCabang">
                <option value="">Pilih Cabang</option>

                <?php foreach($cabang as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['nama']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div>
            <label>Tanggal Awal</label>
            <input
                type="date"
                id="tanggalAwal"
                value="<?= $awal ?>">
        </div>

        <div>
            <label>Tanggal Akhir</label>
            <input
                type="date"
                id="tanggalAkhir"
                value="<?= $akhir ?>">
        </div>

        <div class="filter-button">
            <button
                type="button"
                class="btn-primary"
                id="btnLaporanPeriode">
                Tampilkan
            </button>
        </div>

    </div>

    <div
        id="periodeLoading"
        class="loading-box"
        style="display:none">
        Memuat laporan...
    </div>

    <div id="periodeHasil" style="display:none">

        <div class="report-info">
            <div>
                <span>Cabang</span>
                <strong id="periodeNamaCabang">-</strong>
            </div>

            <div>
                <span>Periode</span>
                <strong id="periodeTanggal">-</strong>
            </div>
        </div>

        <div class="summary-grid periode-summary">

            <div class="summary-card">
                <span>Total Pengunjung</span>
                <strong id="periodeTotal">0</strong>
            </div>

            <div class="summary-card">
                <span>Hari Operasional</span>
                <strong id="periodeHari">0</strong>
            </div>

            <div class="summary-card">
                <span>Rata-rata / Hari</span>
                <strong id="periodeRata">0</strong>
            </div>

            <div class="summary-card">
                <span>Teller</span>
                <strong id="periodeTeller">0</strong>
            </div>

            <div class="summary-card">
                <span>Customer Service</span>
                <strong id="periodeCs">0</strong>
            </div>

            <div class="summary-card">
                <span>Kredit</span>
                <strong id="periodeKredit">0</strong>
            </div>

        </div>

        <div class="report-section">
            <h3>Rekap Harian</h3>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Teller</th>
                        <th>Customer Service</th>
                        <th>Kredit</th>
                        <th>Total</th>
                    </tr>
                    </thead>

                    <tbody id="rekapPeriode">
                        <tr>
                            <td colspan="6" class="empty">
                                Belum ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <h3>Detail Antrian</h3>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Layanan</th>
                        <th>Jam Ambil</th>
                        <th>Jam Panggil</th>
                        <th>Jam Selesai</th>
                        <th>Status</th>
                    </tr>
                    </thead>

                    <tbody id="detailPeriode">
                        <tr>
                            <td colspan="8" class="empty">
                                Belum ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>