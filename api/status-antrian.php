<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../config/database.php';

try{

    $cabangId=(int)($_GET['cabang_id']??0);
    $tanggal=date('Y-m-d');

    $where="
        WHERE a.tanggal=?
    ";

    $params=[$tanggal];

    if($cabangId>0){

        $where.="
            AND a.cabang_id=?
        ";

        $params[]=$cabangId;
    }

    $stmt=$pdo->prepare("
        SELECT
            a.id,
            a.nomor_antrian,
            a.kode_layanan,
            a.jam_ambil,
            a.jam_panggil,
            a.jam_selesai,
            a.status,

            c.kode AS kode_cabang,
            c.nama AS nama_cabang

        FROM antrian a

        INNER JOIN cabang c
            ON c.id=a.cabang_id

        $where

        ORDER BY
            CASE a.status
                WHEN 'dipanggil' THEN 1
                WHEN 'menunggu' THEN 2
                WHEN 'selesai' THEN 3
                WHEN 'batal' THEN 4
                ELSE 5
            END,
            a.id DESC
    ");

    $stmt->execute($params);

    $data=$stmt->fetchAll();

    $summary=[
        'menunggu'=>0,
        'dipanggil'=>0,
        'selesai'=>0,
        'batal'=>0
    ];

    foreach($data as $row){

        if(isset($summary[$row['status']])){
            $summary[$row['status']]++;
        }
    }

    echo json_encode([
        'success'=>true,
        'data'=>[
            'tanggal'=>$tanggal,
            'summary'=>$summary,
            'antrian'=>$data
        ]
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil status antrian.'
    ]);
}