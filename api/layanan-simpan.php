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

    $nama=trim(
        $_POST['nama_layanan']??''
    );

    $status=trim(
        $_POST['status']??'aktif'
    );

    if($id<=0){

        throw new Exception(
            'Layanan tidak valid.'
        );
    }

    if($nama===''){

        throw new Exception(
            'Nama layanan wajib diisi.'
        );
    }

    if(
        !in_array(
            $status,
            ['aktif','nonaktif'],
            true
        )
    ){

        throw new Exception(
            'Status layanan tidak valid.'
        );
    }

    // Pastikan layanan ada
    $stmt=$pdo->prepare("
        SELECT
            id,
            kode
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

    // Kode tidak diubah
    $stmt=$pdo->prepare("
        UPDATE layanan
        SET
            nama_layanan=?,
            status=?
        WHERE id=?
    ");

    $stmt->execute([
        $nama,
        $status,
        $id
    ]);

    echo json_encode([
        'success'=>true,
        'message'=>'Layanan berhasil diperbarui.'
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}