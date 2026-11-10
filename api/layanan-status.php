<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../config/database.php';

if(($_SESSION['role']??'')!=='admin'){

    http_response_code(403);

    echo json_encode([
        'success'=>false,
        'message'=>'Akses tidak diizinkan.'
    ]);

    exit;
}

try{

    $id=(int)($_POST['id']??0);

    if($id<=0){

        throw new Exception(
            'Layanan tidak valid.'
        );
    }

    $stmt=$pdo->prepare("
        SELECT
            id,
            status
        FROM layanan
        WHERE id=?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $layanan=$stmt->fetch();

    if(!$layanan){

        throw new Exception(
            'Layanan tidak ditemukan.'
        );
    }

    $statusBaru=
        $layanan['status']==='aktif'
        ?'nonaktif'
        :'aktif';

    $stmt=$pdo->prepare("
        UPDATE layanan
        SET status=?
        WHERE id=?
    ");

    $stmt->execute([
        $statusBaru,
        $id
    ]);

    echo json_encode([
        'success'=>true,
        'status'=>$statusBaru,
        'message'=>
            $statusBaru==='aktif'
            ?'Layanan berhasil diaktifkan.'
            :'Layanan berhasil dinonaktifkan.'
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}