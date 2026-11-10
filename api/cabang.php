<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';

try{
    $stmt=$pdo->query("SELECT id,kode,nama FROM cabang WHERE status='aktif' ORDER BY nama ASC");
    $data=$stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'=>true,
        'data'=>$data
    ]);
}catch(Exception $e){
    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil data cabang.'
    ]);
}