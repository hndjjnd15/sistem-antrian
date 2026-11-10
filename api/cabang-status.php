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
            'Cabang tidak valid.'
        );
    }

    $stmt=$pdo->prepare("
        SELECT status
        FROM cabang
        WHERE id=?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $cabang=$stmt->fetch();

    if(!$cabang){

        throw new Exception(
            'Cabang tidak ditemukan.'
        );
    }

    $statusBaru=
        $cabang['status']==='aktif'
        ?'nonaktif'
        :'aktif';

    $stmt=$pdo->prepare("
        UPDATE cabang
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
            ?'Cabang berhasil diaktifkan.'
            :'Cabang berhasil dinonaktifkan.'
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}