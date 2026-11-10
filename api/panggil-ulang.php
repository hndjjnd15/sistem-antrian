<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';

try{
    $panggilanId=(int)($_POST['panggilan_id']??0);
    $cabangId=(int)($_POST['cabang_id']??0);

    if(!$panggilanId||!$cabangId){
        echo json_encode(['success'=>false,'message'=>'Data panggilan atau cabang tidak valid.']); exit;
    }

    $stmt=$pdo->prepare("
        SELECT p.id,p.nomor_antrian,p.layanan,p.loket,p.status
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE p.id=? AND a.cabang_id=? AND a.tanggal=CURDATE()
        LIMIT 1
    ");
    $stmt->execute([$panggilanId,$cabangId]);
    $panggilan=$stmt->fetch();

    if(!$panggilan){
        echo json_encode(['success'=>false,'message'=>'Data panggilan tidak ditemukan.']); exit;
    }

    if($panggilan['status']!=='dipanggil'){
        echo json_encode(['success'=>false,'message'=>'Nomor sudah selesai.']); exit;
    }

    $stmt=$pdo->prepare("UPDATE panggilan SET waktu_panggil=NOW() WHERE id=?");
    $stmt->execute([$panggilanId]);

    echo json_encode([
        'success'=>true,
        'message'=>'Nomor dipanggil ulang.',
        'data'=>[
            'id'=>(int)$panggilan['id'],
            'nomor_antrian'=>$panggilan['nomor_antrian'],
            'layanan'=>$panggilan['layanan'],
            'loket'=>$panggilan['loket']
        ]
    ]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Gagal memanggil ulang.']);
}