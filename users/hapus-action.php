<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

$id = (int)($_POST['id'] ?? 0);

if (!$id || $id === (int)$_SESSION['user_id']) {
    echo json_encode(['success'=>false,'message'=>'User tidak dapat dihapus.']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

echo json_encode(['success'=>true,'message'=>'User berhasil dihapus.']);