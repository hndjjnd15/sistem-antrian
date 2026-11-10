<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

$id = (int)($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

if (!$id || strlen($password) < 6) {
    echo json_encode(['success'=>false,'message'=>'Password minimal 6 karakter.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->execute([
    password_hash($password,PASSWORD_DEFAULT),
    $id
]);

echo json_encode(['success'=>true,'message'=>'Password berhasil direset.']);