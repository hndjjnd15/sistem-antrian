<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

try{
    $layananId=(int)($_GET['layanan_id']??0);
    $cabangId=(int)($_GET['cabang_id']??0);

    if(!$layananId||!$cabangId){
        echo json_encode(['success'=>false,'message'=>'Layanan atau cabang tidak valid.']);
        exit;
    }

    $stmt=$pdo->prepare("SELECT id,kode,nama_layanan FROM layanan WHERE id=? AND status='aktif' LIMIT 1");
    $stmt->execute([$layananId]);
    $layanan=$stmt->fetch();

    if(!$layanan){
        echo json_encode(['success'=>false,'message'=>'Layanan tidak ditemukan.']);
        exit;
    }

    $stmt=$pdo->prepare("SELECT COUNT(*) FROM antrian WHERE cabang_id=? AND kode_layanan=? AND tanggal=CURDATE() AND status='menunggu'");
    $stmt->execute([$cabangId,$layanan['kode']]);
    $menunggu=(int)$stmt->fetchColumn();

    $stmt=$pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian,p.layanan,p.loket,p.waktu_panggil
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE a.cabang_id=? AND a.kode_layanan=? AND a.tanggal=CURDATE()
        AND a.status='dipanggil' AND p.status='dipanggil'
        ORDER BY p.waktu_panggil DESC LIMIT 1
    ");
    $stmt->execute([$cabangId,$layanan['kode']]);
    $aktif=$stmt->fetch();

    echo json_encode([
        'success'=>true,
        'data'=>[
            'layanan'=>[
                'id'=>(int)$layanan['id'],
                'kode'=>$layanan['kode'],
                'nama'=>$layanan['nama_layanan']
            ],
            'menunggu'=>$menunggu,
            'aktif'=>$aktif?:null
        ]
    ]);

}catch(Exception $e){
    echo json_encode(['success'=>false,'message'=>'Gagal mengambil data panel.']);
}