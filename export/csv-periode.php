<?php
session_start();

require_once __DIR__.'/../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$cabangId=(int)($_GET['cabang_id']??0);
$awal=trim($_GET['tanggal_awal']??'');
$akhir=trim($_GET['tanggal_akhir']??'');

if(
    $cabangId<=0 ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/',$awal) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/',$akhir) ||
    $awal>$akhir
){
    exit('Parameter tidak valid.');
}

$stmt=$pdo->prepare("
    SELECT kode,nama
    FROM cabang
    WHERE id=?
    LIMIT 1
");

$stmt->execute([$cabangId]);

$cabang=$stmt->fetch();

if(!$cabang){
    exit('Cabang tidak ditemukan.');
}

$stmt=$pdo->prepare("
    SELECT
        tanggal,
        nomor_antrian,
        kode_layanan,
        jam_ambil,
        jam_panggil,
        jam_selesai,
        status
    FROM antrian
    WHERE cabang_id=?
    AND tanggal BETWEEN ? AND ?
    ORDER BY tanggal ASC,id ASC
");

$stmt->execute([
    $cabangId,
    $awal,
    $akhir
]);

$data=$stmt->fetchAll();

$filename=
    'laporan-periode-'.
    strtolower($cabang['kode']).'-'.
    $awal.'-'.$akhir.'.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="'.$filename.'"'
);

echo "\xEF\xBB\xBF";

$output=fopen('php://output','w');

fputcsv($output,[
    'LAPORAN PERIODE SISTEM ANTRIAN BANK BBJ'
],';');

fputcsv($output,[
    'Cabang',
    $cabang['nama']
],';');

fputcsv($output,[
    'Periode',
    $awal.' s/d '.$akhir
],';');

fputcsv($output,[], ';');

fputcsv($output,[
    'No',
    'Tanggal',
    'Nomor Antrian',
    'Layanan',
    'Jam Ambil',
    'Jam Panggil',
    'Jam Selesai',
    'Status'
],';');

$no=1;

foreach($data as $row){

    $layanan=match($row['kode_layanan']){
        'A'=>'Teller',
        'B'=>'Customer Service',
        'C'=>'Kredit',
        default=>$row['kode_layanan']
    };

    fputcsv($output,[
        $no++,
        $row['tanggal'],
        $row['nomor_antrian'],
        $layanan,
        $row['jam_ambil'],
        $row['jam_panggil'],
        $row['jam_selesai'],
        strtoupper($row['status'])
    ],';');
}

fclose($output);
exit;