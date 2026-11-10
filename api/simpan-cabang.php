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
    $kode=strtoupper(trim($_POST['kode']??''));
    $nama=trim($_POST['nama']??'');
    $status=trim($_POST['status']??'aktif');

    if($kode===''){

        throw new Exception(
            'Kode cabang wajib diisi.'
        );
    }

    if($nama===''){

        throw new Exception(
            'Nama cabang wajib diisi.'
        );
    }

    if(
        !in_array(
            $status,
            ['aktif','nonaktif'],
            true
        )
    ){
        $status='aktif';
    }

    // =========================================
    // CEK KODE DUPLIKAT
    // =========================================

    if($id>0){

        $stmt=$pdo->prepare("
            SELECT id
            FROM cabang
            WHERE kode=?
            AND id<>?
            LIMIT 1
        ");

        $stmt->execute([
            $kode,
            $id
        ]);

    }else{

        $stmt=$pdo->prepare("
            SELECT id
            FROM cabang
            WHERE kode=?
            LIMIT 1
        ");

        $stmt->execute([
            $kode
        ]);
    }

    if($stmt->fetch()){

        throw new Exception(
            'Kode cabang sudah digunakan.'
        );
    }

    // =========================================
    // UPDATE
    // =========================================

    if($id>0){

        $stmt=$pdo->prepare("
            UPDATE cabang
            SET
                kode=?,
                nama=?,
                status=?
            WHERE id=?
        ");

        $stmt->execute([
            $kode,
            $nama,
            $status,
            $id
        ]);

        echo json_encode([
            'success'=>true,
            'message'=>'Data cabang berhasil diperbarui.'
        ]);

        exit;
    }

    // =========================================
    // INSERT
    // =========================================

    $stmt=$pdo->prepare("
        INSERT INTO cabang(
            kode,
            nama,
            status
        )
        VALUES(
            ?,?,?
        )
    ");

    $stmt->execute([
        $kode,
        $nama,
        $status
    ]);

    echo json_encode([
        'success'=>true,
        'message'=>'Cabang berhasil ditambahkan.'
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}