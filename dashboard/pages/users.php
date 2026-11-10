<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$stmt=$pdo->query("
    SELECT
        u.id,
        u.username,
        u.nama,
        u.role,
        u.status,
        u.cabang_id,
        u.created_at,
        c.nama AS nama_cabang
    FROM users u
    JOIN cabang c ON c.id=u.cabang_id
    ORDER BY u.id DESC
");

$users=$stmt->fetchAll();

$cabang=$pdo->query("
    SELECT id,nama
    FROM cabang
    WHERE status='aktif'
    ORDER BY nama
")->fetchAll();

$userLogin=(int)($_SESSION['user_id']??0);
?>

<div class="page-card">

    <div class="page-header">
        <div>
            <h2>Manajemen User</h2>
            <p>Kelola akun dan hak akses pengguna sistem antrian.</p>
        </div>

        <button class="btn-primary" id="btnTambahUser">
            + Tambah User
        </button>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Cabang</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>

            <tbody>

            <?php if(!$users): ?>

                <tr>
                    <td colspan="6" class="empty">
                        Belum ada data user.
                    </td>
                </tr>

            <?php endif; ?>

            <?php foreach($users as $user): ?>

                <tr>
                    <td>
                        <strong>
                            <?= htmlspecialchars($user['nama']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['username']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['nama_cabang']) ?>
                    </td>

                    <td>
                        <span class="badge role">
                            <?= strtoupper(htmlspecialchars($user['role'])) ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge <?= $user['status']==='aktif'?'aktif':'nonaktif' ?>">
                            <?= strtoupper($user['status']) ?>
                        </span>
                    </td>

                    <td>
                        <div class="action-group">

                            <button
                                class="btn-action edit-user"
                                data-id="<?= $user['id'] ?>"
                                data-nama="<?= htmlspecialchars($user['nama'],ENT_QUOTES) ?>"
                                data-username="<?= htmlspecialchars($user['username'],ENT_QUOTES) ?>"
                                data-role="<?= htmlspecialchars($user['role']) ?>"
                                data-cabang="<?= $user['cabang_id'] ?>">
                                Edit
                            </button>

                            <button
                                class="btn-action reset-user"
                                data-id="<?= $user['id'] ?>"
                                data-nama="<?= htmlspecialchars($user['nama'],ENT_QUOTES) ?>">
                                Password
                            </button>

                            <?php if((int)$user['id']!==$userLogin): ?>

                                <button
                                    class="btn-action status-user"
                                    data-id="<?= $user['id'] ?>"
                                    data-status="<?= $user['status'] ?>">
                                    <?= $user['status']==='aktif'?'Nonaktifkan':'Aktifkan' ?>
                                </button>

                                <button
                                    class="btn-action danger delete-user"
                                    data-id="<?= $user['id'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama'],ENT_QUOTES) ?>">
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- TAMBAH USER -->
<div class="modal" id="modalTambah">
<div class="modal-box">

    <div class="modal-header">
        <h3>Tambah User</h3>
        <button class="modal-close">×</button>
    </div>

    <form id="formTambah">
        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required minlength="6">

        <label>Cabang</label>
        <select name="cabang_id" required>
            <option value="">Pilih Cabang</option>

            <?php foreach($cabang as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['nama']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Role</label>
        <select name="role" required>
            <option value="">Pilih Role</option>
            <option value="admin">Admin</option>
            <option value="teller">Teller</option>
            <option value="cs">Customer Service</option>
            <option value="kredit">Kredit</option>
        </select>

        <button type="submit" class="btn-primary">
            Simpan User
        </button>
    </form>
</div>
</div>

<!-- EDIT USER -->
<div class="modal" id="modalEdit">
<div class="modal-box">

    <div class="modal-header">
        <h3>Edit User</h3>
        <button class="modal-close">×</button>
    </div>

    <form id="formEdit">
        <input type="hidden" name="id" id="editId">

        <label>Nama</label>
        <input type="text" name="nama" id="editNama" required>

        <label>Username</label>
        <input type="text" name="username" id="editUsername" required>

        <label>Cabang</label>
        <select name="cabang_id" id="editCabang" required>

            <?php foreach($cabang as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['nama']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Role</label>
        <select name="role" id="editRole" required>
            <option value="admin">Admin</option>
            <option value="teller">Teller</option>
            <option value="cs">Customer Service</option>
            <option value="kredit">Kredit</option>
        </select>

        <button type="submit" class="btn-primary">
            Simpan Perubahan
        </button>
    </form>
</div>
</div>

<!-- RESET PASSWORD -->
<div class="modal" id="modalReset">
<div class="modal-box">

    <div class="modal-header">
        <h3>Reset Password</h3>
        <button class="modal-close">×</button>
    </div>

    <form id="formReset">
        <input type="hidden" name="id" id="resetId">
        <p id="resetNama"></p>
        <label>Password Baru</label>
        <input
            type="password"
            name="password"
            required
            minlength="6">
        <button type="submit" class="btn-primary">
            Reset Password
        </button>
    </form>
</div>
</div>

<!-- KONFIRMASI -->
<div class="modal" id="modalConfirm">
<div class="modal-box modal-small">

    <h3>Konfirmasi</h3>
    <p id="confirmText"></p>
    <div class="confirm-actions">

        <button
            type="button"
            class="btn-secondary"
            id="btnBatalConfirm">
            Batal
        </button>

        <button
            type="button"
            class="btn-danger"
            id="btnConfirm">
            Ya, Lanjutkan
        </button>

    </div>

</div>
</div>

<div class="toast" id="toast"></div>