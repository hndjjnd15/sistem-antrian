<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

try {

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $cabangId = (int)($_POST['cabang_id'] ?? 0);
    $role = $_POST['role'] ?? '';

    $allowedRole = ['admin','teller','cs','kredit'];

    if (
        $nama === '' ||
        $username === '' ||
        $password === '' ||
        !$cabangId ||
        !in_array($role, $allowedRole, true)
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Data belum lengkap.'
        ]);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 6 karakter.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM cabang
        WHERE id=? AND status='aktif'
        LIMIT 1
    ");
    $stmt->execute([$cabangId]);

    if (!$stmt->fetch()) {
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak valid.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO users
        (cabang_id,username,password,nama,role,status)
        VALUES (?,?,?,?,?,'aktif')
    ");

    $stmt->execute([
        $cabangId,
        $username,
        password_hash($password,PASSWORD_DEFAULT),
        $nama,
        $role
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'User berhasil ditambahkan.'
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan user.'
    ]);
}