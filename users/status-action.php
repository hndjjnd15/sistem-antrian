<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status,['aktif','nonaktif'],true)) {
    echo json_encode(['success'=>false,'message'=>'Data tidak valid.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET status=? WHERE id=?");
$stmt->execute([$status,$id]);

echo json_encode(['success'=>true,'message'=>'Status user berhasil diubah.']);