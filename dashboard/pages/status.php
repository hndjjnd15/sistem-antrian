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
            <h2>Status Antrian</h2>

            <p>
                Monitoring kondisi antrian secara real-time.
            </p>
        </div>

        <div class="dashboard-filter">

            <select id="statusCabang">

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

    <div class="dashboard-summary">

        <div class="dash-card">
            <span>Menunggu</span>
            <strong id="statusMenunggu">0</strong>
        </div>

        <div class="dash-card">
            <span>Dipanggil</span>
            <strong id="statusDipanggil">0</strong>
        </div>

        <div class="dash-card">
            <span>Selesai</span>
            <strong id="statusSelesai">0</strong>
        </div>

        <div class="dash-card">
            <span>Batal</span>
            <strong id="statusBatal">0</strong>
        </div>

    </div>

    <div class="report-section">

        <h3>Antrian Hari Ini</h3>

        <div class="table-wrap">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Cabang</th>
                        <th>Nomor</th>
                        <th>Layanan</th>
                        <th>Jam Ambil</th>
                        <th>Jam Panggil</th>
                        <th>Jam Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody id="statusTable">

                    <tr>
                        <td colspan="8" class="empty">
                            Memuat data...
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>