<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';

try{
    $panggilanId=(int)($_POST['panggilan_id']??0);
    $cabangId=(int)($_POST['cabang_id']??0);

    if(!$panggilanId||!$cabangId){
        echo json_encode(['success'=>false,'message'=>'Data panggilan atau cabang tidak valid.']); exit;
    }

    $pdo->beginTransaction();

    $stmt=$pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian,p.status
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE p.id=? AND a.cabang_id=? AND a.tanggal=CURDATE()
        LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$panggilanId,$cabangId]);
    $panggilan=$stmt->fetch();

    if(!$panggilan){
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Panggilan tidak ditemukan.']); exit;
    }

    if($panggilan['status']!=='dipanggil'){
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Nomor sudah selesai.']); exit;
    }

    $stmt=$pdo->prepare("UPDATE panggilan SET status='selesai',waktu_selesai=NOW() WHERE id=?");
    $stmt->execute([$panggilanId]);

    $stmt=$pdo->prepare("
        UPDATE antrian SET status='selesai',jam_selesai=NOW()
        WHERE id=? AND cabang_id=?
    ");
    $stmt->execute([$panggilan['antrian_id'],$cabangId]);

    $pdo->commit();

    echo json_encode([
        'success'=>true,
        'message'=>'Pelayanan nomor '.$panggilan['nomor_antrian'].' selesai.'
    ]);
}catch(Exception $e){
    if($pdo->inTransaction())$pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Gagal menyelesaikan pelayanan.']);
}