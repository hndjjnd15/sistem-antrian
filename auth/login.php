<?php

session_start();

require_once __DIR__ . '/../config/database.php';
function redirectByRole(string $role): void
{
    switch ($role) {

        case 'admin':
            header('Location: ../dashboard/');
            break;

        case 'teller':
            header('Location: ../panel/');
            break;

        case 'cs':
            header('Location: ../panel/');
            break;

        case 'kredit':
            header('Location: ../panel/');
            break;

        default:
            session_destroy();
            header('Location: login.php');
            break;
    }

    exit;
}
if (isset($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role'] ?? '');
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {

        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.username,
                u.password,
                u.nama,
                u.role,
                u.status,
                u.cabang_id,
                c.nama AS nama_cabang,
                c.kode AS kode_cabang
            FROM users u
            JOIN cabang c ON c.id = u.cabang_id
            WHERE u.username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (
            $user &&
            $user['status'] === 'aktif' &&
            password_verify($password, $user['password'])
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            $_SESSION['cabang_id'] = $user['cabang_id'];
            $_SESSION['nama_cabang'] = $user['nama_cabang'];
            $_SESSION['kode_cabang'] = $user['kode_cabang'];

            redirectByRole($user['role']);
            exit;

        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Antrian Bank BBJ</title>

    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#073d6b,#0876af);
        }
        .login-card{
            width:min(400px,90%);
            padding:32px;
            background:#fff;
            border-radius:16px;
            box-shadow:0 15px 40px rgba(0,0,0,.18);
        }
        .brand{text-align:center;margin-bottom:25px}
        .brand h1{margin:0;color:#073d6b;font-size:26px}
        .brand p{margin-top:6px;color:#71808d;font-size:13px}
        label{display:block;margin:14px 0 6px;font-size:12px;font-weight:700}
        input{
            width:100%;
            padding:12px;
            border:1px solid #ccd8e2;
            border-radius:8px;
            font-size:14px;
        }
        button{
            width:100%;
            margin-top:20px;
            padding:12px;
            border:0;
            border-radius:8px;
            background:#0871aa;
            color:white;
            font-weight:800;
            cursor:pointer;
        }
        .error{
            margin-bottom:15px;
            padding:10px;
            border-radius:7px;
            background:#fde7e7;
            color:#a62424;
            font-size:12px;
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="brand">
        <h1>BANK BBJ</h1>
        <p>Sistem Antrian</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Username</label>
        <input
            type="text"
            name="username"
            autocomplete="username"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <button type="submit">
            MASUK
        </button>

    </form>

</div>

</body>
</html>