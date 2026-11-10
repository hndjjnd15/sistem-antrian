<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$stmt=$pdo->query("
    SELECT
        id,
        kode,
        nama_layanan,
        status,
        created_at
    FROM layanan
    ORDER BY id ASC
");

$data=$stmt->fetchAll();
?>

<div class="page-card">
    <div class="page-header">
        <div>
            <h2>Master Layanan</h2>

            <p>
                Kelola nama dan status layanan sistem antrian.
            </p>
        </div>
    </div>

    <div class="info-box">
        Kode layanan A, B dan C merupakan kode sistem dan
        tidak dapat diubah.
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Layanan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

            <?php if(!$data): ?>

                <tr>
                    <td colspan="5" class="empty">
                        Belum ada layanan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($data as $i=>$row): ?>
                    <tr>

                        <td>
                            <?= $i+1 ?>
                        </td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($row['kode']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama_layanan']) ?>
                        </td>

                        <td>

                            <?php if($row['status']==='aktif'): ?>
                                <span class="badge aktif">
                                    AKTIF
                                </span>
                            <?php else: ?>
                                <span class="badge nonaktif">
                                    NONAKTIF
                                </span>
                            <?php endif; ?>

                        </td>

                        <td>
                            <div class="action-group">
                                <button
                                    type="button"
                                    class="btn-small btn-edit-layanan"
                                    data-id="<?= (int)$row['id'] ?>"
                                    data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_layanan']) ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>">
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="btn-small btn-status-layanan"
                                    data-id="<?= (int)$row['id'] ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>">

                                    <?= $row['status']==='aktif'
                                        ? 'Nonaktifkan'
                                        : 'Aktifkan'
                                    ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- MODAL EDIT LAYANAN -->

<div
    class="modal"
    id="modalLayanan">

    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Layanan</h3>
            <button type="button" class="modal-close" id="closeModalLayanan">×</button>
        </div>

        <form id="formLayanan">
            <input type="hidden" name="id" id="layananId">
            <div class="form-group">
                <label>Kode Layanan</label>
                <input type="text" id="layananKode" readonly>
            </div>

            <div class="form-group">
                <label>Nama Layanan</label>
                <input type="text" name="nama_layanan" id="layananNama" maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="layananStatus">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>

            <div class="modal-actions">
                <button
                    type="submit"
                    class="btn-primary">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>