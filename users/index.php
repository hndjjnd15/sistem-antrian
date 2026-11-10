<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

$stmt = $pdo->query("
    SELECT
        u.id,
        u.username,
        u.nama,
        u.role,
        u.status,
        u.created_at,
        u.cabang_id,
        c.nama AS nama_cabang
    FROM users u
    JOIN cabang c ON c.id = u.cabang_id
    ORDER BY u.id DESC
");

$users = $stmt->fetchAll();

$cabang = $pdo->query("
    SELECT id,nama
    FROM cabang
    WHERE status='aktif'
    ORDER BY nama
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Bank BBJ</title>
    <link rel="stylesheet" href="css/users.css">
</head>
<body>

<div class="modal" id="confirmModal">
    <div class="modal-box confirm-box">
        <h2 id="confirmTitle">Konfirmasi</h2>
        <p id="confirmMessage">Apakah Anda yakin?</p>

        <div class="modal-actions center">
            <button type="button" class="btn btn-back" id="confirmCancel">
                BATAL
            </button>

            <button type="button" class="btn btn-primary" id="confirmOk">
                YA, LANJUTKAN
            </button>
        </div>
    </div>
</div>

<div class="modal" id="resetModal">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeReset">&times;</button>
        <h2>Reset Password</h2>
        <form id="formResetPassword">
            <input type="hidden" name="id" id="resetId">
            <label>Password Baru</label>
            <input type="password" name="password" minlength="6" required>

            <div class="modal-actions">
                <button type="button" class="btn btn-back" id="batalReset">BATAL</button>
                <button type="submit" class="btn btn-primary">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editModal">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeEdit">&times;</button>
        <h2>Edit User</h2>

        <form id="formEditUser">
            <input type="hidden" name="id" id="editId">

            <label>Nama</label>
            <input type="text" name="nama" id="editNama" required>

            <label>Username</label>
            <input type="text" name="username" id="editUsername" required>

            <label>Cabang</label>
            <select name="cabang_id" id="editCabang" required>
                <?php foreach ($cabang as $cb): ?>
                    <option value="<?= $cb['id'] ?>">
                        <?= htmlspecialchars($cb['nama']) ?>
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

            <div class="modal-actions">
                <button type="button" class="btn btn-back" id="batalEdit">BATAL</button>
                <button type="submit" class="btn btn-primary">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="userModal">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeModal">&times;</button>
        <h2>Tambah User</h2>
        <form id="formTambahUser">
            <label>Nama</label>
            <input type="text" name="nama" required>

            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required minlength="6">

            <label>Cabang</label>
            <select name="cabang_id" required>
                <option value="">Pilih Cabang</option>

                <?php foreach ($cabang as $cb): ?>
                    <option value="<?= $cb['id'] ?>">
                        <?= htmlspecialchars($cb['nama']) ?>
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

            <div class="modal-actions">
                <button type="button" class="btn btn-back" id="btnBatal">BATAL</button>
                <button type="submit" class="btn btn-primary" id="btnSimpan">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

<div class="toast" id="toast"></div>
<header class="topbar">
    <div class="brand">
        <div class="brand-logo">BBJ</div>
        <div>
            <strong>Manajemen User</strong>
            <span>Sistem Antrian Bank BBJ</span>
        </div>
    </div>
    <span>
        <?= htmlspecialchars($_SESSION['nama_cabang']) ?>
    </span>
    <div class="topbar-right">
        <div class="admin-info">
            <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
            <span>Administrator</span>
        </div>

        <a href="../dashboard/" class="btn-back">Dashboard</a>
    </div>
</header>

<main class="container">

    <div class="page-heading">
        <div>
            <h1>Manajemen User</h1>
            <p>Kelola akun dan hak akses pengguna sistem antrian.</p>
        </div>

        <button class="btn-primary" id="btnTambahUser">
            + Tambah User
        </button>
    </div>

    <section class="stats">
        <div class="stat-card">
            <span>Total User</span>
            <strong><?= count($users) ?></strong>
        </div>

        <div class="stat-card">
            <span>User Aktif</span>
            <strong>
                <?= count(array_filter($users, fn($u) => $u['status'] === 'aktif')) ?>
            </strong>
        </div>

        <div class="stat-card">
            <span>Nonaktif</span>
            <strong>
                <?= count(array_filter($users, fn($u) => $u['status'] === 'nonaktif')) ?>
            </strong>
        </div>
    </section>

    <section class="table-card">

        <div class="table-header">
            <div>
                <h2>Daftar User</h2>
                <span>Semua pengguna sistem</span>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>User</th>
                    <th>Username</th>
                    <th>Cabang</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($users as $user): ?>
                <tr>

                    <td>
                        <div class="user-cell">
                            <div class="avatar">
                                <?= strtoupper(substr($user['nama'],0,1)) ?>
                            </div>

                            <strong>
                                <?= htmlspecialchars($user['nama']) ?>
                            </strong>
                        </div>
                    </td>

                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['nama_cabang']) ?></td>
                    <td>
                        <span class="role-badge">
                            <?= strtoupper(htmlspecialchars($user['role'])) ?>
                        </span>
                    </td>

                    <td>
                        <span class="status-badge <?= $user['status'] ?>">
                            <?= strtoupper($user['status']) ?>
                        </span>
                    </td>

                    <td>
                        <?= date('d-m-Y', strtotime($user['created_at'])) ?>
                    </td>

                    <td>
                        <div class="actions">

                            <button
                                class="action-btn btnEdit"
                                data-id="<?= $user['id'] ?>"
                                data-nama="<?= htmlspecialchars($user['nama']) ?>"
                                data-username="<?= htmlspecialchars($user['username']) ?>"
                                data-cabang="<?= $user['cabang_id'] ?>"
                                data-role="<?= htmlspecialchars($user['role']) ?>"
                            >
                                Edit
                            </button>

                            <button
                                class="action-btn reset btnReset"
                                data-id="<?= $user['id'] ?>"
                            >
                                Password
                            </button>

                            <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>

                            <button
                                class="action-btn status-btn btnStatus"
                                data-id="<?= $user['id'] ?>"
                                data-status="<?= $user['status'] ?>"
                            >
                                <?= $user['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </button>

                            <button
                                class="action-btn danger btnHapus"
                                data-id="<?= $user['id'] ?>"
                            >
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

    </section>

</main>
<script src="js/users.js"></script>
<script src="js/konfirmasi.js"></script>
<script src="js/edit.js"></script>
<script src="js/tambah.js"></script>
<script src="js/status.js"></script>
<script src="js/reset.js"></script>
<script src="js/hapus.js"></script>
</body>
</html>