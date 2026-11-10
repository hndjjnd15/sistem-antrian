<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../config/database.php';

try{

    $cabangId=(int)($_GET['cabang_id']??0);
    $tanggal=date('Y-m-d');

    $where="WHERE a.tanggal=?";
    $params=[$tanggal];

    if($cabangId>0){
        $where.=" AND a.cabang_id=?";
        $params[]=$cabangId;
    }

    // =========================
    // TOTAL & STATUS
    // =========================

    $stmt=$pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN a.status='menunggu' THEN 1 ELSE 0 END) AS menunggu,
            SUM(CASE WHEN a.status='dipanggil' THEN 1 ELSE 0 END) AS dipanggil,
            SUM(CASE WHEN a.status='selesai' THEN 1 ELSE 0 END) AS selesai,
            SUM(CASE WHEN a.status='batal' THEN 1 ELSE 0 END) AS batal
        FROM antrian a
        $where
    ");

    $stmt->execute($params);
    $summary=$stmt->fetch();

    // =========================
    // PER LAYANAN
    // =========================

    $stmt=$pdo->prepare("
        SELECT
            SUM(CASE WHEN a.kode_layanan='A' THEN 1 ELSE 0 END) AS teller,
            SUM(CASE WHEN a.kode_layanan='B' THEN 1 ELSE 0 END) AS cs,
            SUM(CASE WHEN a.kode_layanan='C' THEN 1 ELSE 0 END) AS kredit
        FROM antrian a
        $where
    ");

    $stmt->execute($params);
    $layanan=$stmt->fetch();

    // =========================
    // PER CABANG
    // =========================

    $stmt=$pdo->prepare("
        SELECT
            c.id,
            c.kode,
            c.nama,
            COUNT(a.id) AS total,
            SUM(CASE WHEN a.status='menunggu' THEN 1 ELSE 0 END) AS menunggu,
            SUM(CASE WHEN a.status='selesai' THEN 1 ELSE 0 END) AS selesai
        FROM cabang c
        LEFT JOIN antrian a
            ON a.cabang_id=c.id
            AND a.tanggal=?
        WHERE c.status='aktif'
        GROUP BY
            c.id,
            c.kode,
            c.nama
        ORDER BY c.nama
    ");

    $stmt->execute([$tanggal]);
    $cabang=$stmt->fetchAll();

    echo json_encode([
        'success'=>true,
        'data'=>[
            'tanggal'=>$tanggal,

            'summary'=>[
                'total'=>(int)($summary['total']??0),
                'menunggu'=>(int)($summary['menunggu']??0),
                'dipanggil'=>(int)($summary['dipanggil']??0),
                'selesai'=>(int)($summary['selesai']??0),
                'batal'=>(int)($summary['batal']??0)
            ],

            'layanan'=>[
                'teller'=>(int)($layanan['teller']??0),
                'cs'=>(int)($layanan['cs']??0),
                'kredit'=>(int)($layanan['kredit']??0)
            ],

            'cabang'=>$cabang
        ]
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil data dashboard.'
    ]);
}