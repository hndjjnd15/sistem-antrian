<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';

try{
    $cabangId=(int)($_GET['cabang_id']??0);
    $tanggal=trim($_GET['tanggal']??date('Y-m-d'));

    if(!$cabangId){
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak valid.'
        ]);
        exit;
    }

    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$tanggal)){
        echo json_encode([
            'success'=>false,
            'message'=>'Format tanggal tidak valid.'
        ]);
        exit;
    }

    // CABANG
    $stmt=$pdo->prepare("
        SELECT id,kode,nama
        FROM cabang
        WHERE id=?
        LIMIT 1
    ");
    $stmt->execute([$cabangId]);
    $cabang=$stmt->fetch();

    if(!$cabang){
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak ditemukan.'
        ]);
        exit;
    }

    // TOTAL PENGUNJUNG
    $stmt=$pdo->prepare("
        SELECT COUNT(*)
        FROM antrian
        WHERE cabang_id=?
        AND tanggal=?
    ");
    $stmt->execute([$cabangId,$tanggal]);
    $total=(int)$stmt->fetchColumn();

    // TOTAL PER LAYANAN
    $stmt=$pdo->prepare("
        SELECT
            l.id,
            l.kode,
            l.nama_layanan,
            COUNT(a.id) AS total
        FROM layanan l
        LEFT JOIN antrian a
            ON a.kode_layanan=l.kode
            AND a.cabang_id=?
            AND a.tanggal=?
        WHERE l.status='aktif'
        GROUP BY l.id,l.kode,l.nama_layanan
        ORDER BY l.id ASC
    ");
    $stmt->execute([$cabangId,$tanggal]);
    $layanan=$stmt->fetchAll();

    // DETAIL ANTRIAN
    $stmt=$pdo->prepare("
        SELECT
            a.id,
            a.nomor_antrian,
            a.kode_layanan,
            a.jam_ambil,
            a.jam_panggil,
            a.jam_selesai,
            a.status
        FROM antrian a
        WHERE a.cabang_id=?
        AND a.tanggal=?
        ORDER BY a.id ASC
    ");
    $stmt->execute([$cabangId,$tanggal]);
    $detail=$stmt->fetchAll();

    echo json_encode([
        'success'=>true,
        'data'=>[
            'cabang'=>[
                'id'=>(int)$cabang['id'],
                'kode'=>$cabang['kode'],
                'nama'=>$cabang['nama']
            ],
            'tanggal'=>$tanggal,
            'total'=>$total,
            'layanan'=>$layanan,
            'detail'=>$detail
        ]
    ]);

}catch(Exception $e){
    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil laporan harian.'
    ]);
}