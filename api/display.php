<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';

try{
    $cabangId=(int)($_GET['cabang_id']??0);

    if(!$cabangId){
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak valid.'
        ]);
        exit;
    }

    $stmt=$pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian,p.layanan,p.loket,p.waktu_panggil
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE a.cabang_id=?
        AND p.status='dipanggil'
        AND a.status='dipanggil'
        AND a.tanggal=CURDATE()
        ORDER BY p.waktu_panggil DESC
        LIMIT 1
    ");
    $stmt->execute([$cabangId]);
    $aktif=$stmt->fetch();

    $stmt=$pdo->prepare("
        SELECT p.nomor_antrian,p.layanan,p.loket,p.waktu_panggil
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE a.cabang_id=?
        AND a.tanggal=CURDATE()
        ORDER BY p.waktu_panggil DESC
        LIMIT 5
    ");
    $stmt->execute([$cabangId]);
    $riwayat=$stmt->fetchAll();

    $stmt=$pdo->prepare("
        SELECT id,kode,nama
        FROM cabang
        WHERE id=? AND status='aktif'
        LIMIT 1
    ");
    $stmt->execute([$cabangId]);
    $cabang=$stmt->fetch();

    echo json_encode([
        'success'=>true,
        'data'=>[
            'cabang'=>$cabang?:null,
            'aktif'=>$aktif?:null,
            'riwayat'=>$riwayat
        ]
    ]);

}catch(Exception $e){
    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil data display.'
    ]);
}