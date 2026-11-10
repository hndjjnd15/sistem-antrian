<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../config/database.php';

try{

    $cabangId=(int)($_GET['cabang_id']??0);
    $tanggalAwal=trim($_GET['tanggal_awal']??'');
    $tanggalAkhir=trim($_GET['tanggal_akhir']??'');

    if($cabangId<=0){
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak valid.'
        ]);
        exit;
    }

    if(
        !preg_match('/^\d{4}-\d{2}-\d{2}$/',$tanggalAwal) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/',$tanggalAkhir)
    ){
        echo json_encode([
            'success'=>false,
            'message'=>'Format tanggal tidak valid.'
        ]);
        exit;
    }

    if($tanggalAwal>$tanggalAkhir){
        echo json_encode([
            'success'=>false,
            'message'=>'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'
        ]);
        exit;
    }

    // =====================================================
    // CABANG
    // =====================================================

    $stmt=$pdo->prepare("
        SELECT
            id,
            kode,
            nama
        FROM cabang
        WHERE id=?
        LIMIT 1
    ");

    $stmt->execute([
        $cabangId
    ]);

    $cabang=$stmt->fetch();

    if(!$cabang){
        echo json_encode([
            'success'=>false,
            'message'=>'Cabang tidak ditemukan.'
        ]);
        exit;
    }

    // =====================================================
    // TOTAL SELURUH ANTRIAN
    // =====================================================

    $stmt=$pdo->prepare("
        SELECT COUNT(*)
        FROM antrian
        WHERE cabang_id=?
        AND tanggal BETWEEN ? AND ?
    ");

    $stmt->execute([
        $cabangId,
        $tanggalAwal,
        $tanggalAkhir
    ]);

    $total=(int)$stmt->fetchColumn();

    // =====================================================
    // HARI OPERASIONAL
    // Hari dihitung jika ada antrian pada tanggal tersebut
    // =====================================================

    $stmt=$pdo->prepare("
        SELECT COUNT(DISTINCT tanggal)
        FROM antrian
        WHERE cabang_id=?
        AND tanggal BETWEEN ? AND ?
    ");

    $stmt->execute([
        $cabangId,
        $tanggalAwal,
        $tanggalAkhir
    ]);

    $hariOperasional=(int)$stmt->fetchColumn();

    // =====================================================
    // RATA-RATA PENGUNJUNG
    // =====================================================

    $rataRata=
        $hariOperasional>0
        ? round($total/$hariOperasional,2)
        : 0;

    // =====================================================
    // TOTAL PER LAYANAN
    // =====================================================

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
            AND a.tanggal BETWEEN ? AND ?
        WHERE l.status='aktif'
        GROUP BY
            l.id,
            l.kode,
            l.nama_layanan
        ORDER BY l.id ASC
    ");

    $stmt->execute([
        $cabangId,
        $tanggalAwal,
        $tanggalAkhir
    ]);

    $layanan=$stmt->fetchAll();

    // =====================================================
    // REKAP PER TANGGAL
    // =====================================================

    $stmt=$pdo->prepare("
        SELECT
            tanggal,
            COUNT(*) AS total,
            SUM(CASE WHEN kode_layanan='A' THEN 1 ELSE 0 END) AS teller,
            SUM(CASE WHEN kode_layanan='B' THEN 1 ELSE 0 END) AS cs,
            SUM(CASE WHEN kode_layanan='C' THEN 1 ELSE 0 END) AS kredit
        FROM antrian
        WHERE cabang_id=?
        AND tanggal BETWEEN ? AND ?
        GROUP BY tanggal
        ORDER BY tanggal ASC
    ");

    $stmt->execute([
        $cabangId,
        $tanggalAwal,
        $tanggalAkhir
    ]);

    $rekap=$stmt->fetchAll();

    // =====================================================
    // DETAIL ANTRIAN
    // =====================================================

    $stmt=$pdo->prepare("
        SELECT
            a.id,
            a.tanggal,
            a.nomor_antrian,
            a.kode_layanan,
            a.jam_ambil,
            a.jam_panggil,
            a.jam_selesai,
            a.status
        FROM antrian a
        WHERE a.cabang_id=?
        AND a.tanggal BETWEEN ? AND ?
        ORDER BY
            a.tanggal ASC,
            a.id ASC
    ");

    $stmt->execute([
        $cabangId,
        $tanggalAwal,
        $tanggalAkhir
    ]);

    $detail=$stmt->fetchAll();

    // =====================================================
    // RESPONSE
    // =====================================================

    echo json_encode([
        'success'=>true,
        'data'=>[
            'cabang'=>[
                'id'=>(int)$cabang['id'],
                'kode'=>$cabang['kode'],
                'nama'=>$cabang['nama']
            ],
            'periode'=>[
                'tanggal_awal'=>$tanggalAwal,
                'tanggal_akhir'=>$tanggalAkhir
            ],
            'ringkasan'=>[
                'total'=>$total,
                'hari_operasional'=>$hariOperasional,
                'rata_rata'=>$rataRata
            ],
            'layanan'=>$layanan,
            'rekap'=>$rekap,
            'detail'=>$detail
        ]
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>'Gagal mengambil laporan periode.'
    ]);
}