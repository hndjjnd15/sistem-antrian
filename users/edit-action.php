<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

$id = (int) ($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$cabangId = (int)($_POST['cabang_id'] ?? 0);
$role = $_POST['role'] ?? '';

$allowed = ['admin','teller','cs','kredit'];

if (!$id || !$cabangId || $nama === '' || $username === '' || !in_array($role,$allowed,true)) {
    echo json_encode(['success'=>false,'message'=>'Data tidak valid.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username=? AND id<>?");
$stmt->execute([$username,$id]);

if ($stmt->fetch()) {
    echo json_encode(['success'=>false,'message'=>'Username sudah digunakan.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, cabang_id=?, role=? WHERE id=?");
$stmt->execute([$nama,$username,$cabangId,$role,$id]);

echo json_encode(['success'=>true,'message'=>'User berhasil diperbarui.']);