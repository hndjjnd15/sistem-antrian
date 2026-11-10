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
        nama,
        status,
        created_at
    FROM cabang
    ORDER BY nama ASC
");

$data=$stmt->fetchAll();
?>

<div class="page-card">

    <div class="page-header">

        <div>
            <h2>Master Cabang</h2>

            <p>
                Kelola data cabang Bank BBJ.
            </p>
        </div>

        <button
            type="button"
            class="btn-primary"
            id="btnTambahCabang">
            + Tambah Cabang
        </button>

    </div>

    <div class="table-wrap">

        <table class="data-table">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Cabang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

            <?php if(!$data): ?>

                <tr>
                    <td colspan="5" class="empty">
                        Belum ada data cabang.
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
                            <?= htmlspecialchars($row['nama']) ?>
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
                                    class="btn-small btn-edit-cabang"
                                    data-id="<?= $row['id'] ?>"
                                    data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>">
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="btn-small btn-status-cabang"
                                    data-id="<?= $row['id'] ?>"
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

<!-- =========================
     MODAL CABANG
========================= -->

<div
    class="modal"
    id="modalCabang">

    <div class="modal-box">

        <div class="modal-header">

            <h3 id="modalCabangTitle">
                Tambah Cabang
            </h3>

            <button
                type="button"
                class="modal-close"
                id="closeModalCabang">
                ×
            </button>

        </div>

        <form id="formCabang">

            <input
                type="hidden"
                name="id"
                id="cabangId">

            <div class="form-group">
                <label>Kode Cabang</label>
                <input type="text" name="kode" id="cabangKode" maxlength="20" required>
            </div>

            <div class="form-group">

                <label>
                    Nama Cabang
                </label>

                <input
                    type="text"
                    name="nama"
                    id="cabangNama"
                    maxlength="100"
                    required>

            </div>

            <div
                class="form-group"
                id="groupStatusCabang">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    id="cabangStatus">

                    <option value="aktif">
                        Aktif
                    </option>

                    <option value="nonaktif">
                        Nonaktif
                    </option>

                </select>

            </div>

            <div class="modal-actions">
                
                <button type="submit" class="btn-primary">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>