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
            <h2>Dashboard Antrian</h2>

            <p>
                Monitoring aktivitas antrian seluruh cabang Bank BBJ.
            </p>
        </div>

        <div class="dashboard-filter">

            <select id="dashboardCabang">

                <option value="0">
                    Semua Cabang
                </option>

                <?php foreach($cabang as $c): ?>

                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['nama']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

    </div>

    <div
        id="dashboardLoading"
        class="loading-box">

        Memuat dashboard...

    </div>

    <div
        id="dashboardContent"
        style="display:none">

        <!-- STATUS -->

        <div class="dashboard-summary">

            <div class="dash-card">
                <span>Total Hari Ini</span>
                <strong id="dashTotal">0</strong>
            </div>

            <div class="dash-card">
                <span>Menunggu</span>
                <strong id="dashMenunggu">0</strong>
            </div>

            <div class="dash-card">
                <span>Dipanggil</span>
                <strong id="dashDipanggil">0</strong>
            </div>

            <div class="dash-card">
                <span>Selesai</span>
                <strong id="dashSelesai">0</strong>
            </div>

        </div>

        <!-- LAYANAN -->

        <div class="report-section">

            <h3>Layanan Hari Ini</h3>

            <div class="dashboard-service">

                <div class="service-card">
                    <span>Teller</span>
                    <strong id="dashTeller">0</strong>
                </div>

                <div class="service-card">
                    <span>Customer Service</span>
                    <strong id="dashCs">0</strong>
                </div>

                <div class="service-card">
                    <span>Kredit</span>
                    <strong id="dashKredit">0</strong>
                </div>

            </div>

        </div>

        <!-- CABANG -->

        <div class="report-section">

            <h3>Aktivitas Per Cabang</h3>

            <div class="table-wrap">

                <table class="data-table">

                    <thead>

                    <tr>
                        <th>Cabang</th>
                        <th>Total</th>
                        <th>Menunggu</th>
                        <th>Selesai</th>
                    </tr>

                    </thead>

                    <tbody id="dashCabangTable">

                        <tr>
                            <td
                                colspan="4"
                                class="empty">

                                Belum ada data.

                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>