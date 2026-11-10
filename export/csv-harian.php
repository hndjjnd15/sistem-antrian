<?php
session_start();

require_once __DIR__.'/../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$cabangId=(int)($_GET['cabang_id']??0);
$tanggal=trim($_GET['tanggal']??'');

if(
    $cabangId<=0 ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/',$tanggal)
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

$stmt->execute([
    $cabangId,
    $tanggal
]);

$data=$stmt->fetchAll();

$filename=
    'laporan-harian-'.
    strtolower($cabang['kode']).'-'.
    $tanggal.'.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="'.$filename.'"'
);

// BOM agar Excel membaca UTF-8 dengan baik
echo "\xEF\xBB\xBF";

$output=fopen('php://output','w');

fputcsv($output,[
    'LAPORAN HARIAN SISTEM ANTRIAN BANK BBJ'
],';');

fputcsv($output,[
    'Cabang',
    $cabang['nama']
],';');

fputcsv($output,[
    'Tanggal',
    $tanggal
],';');

fputcsv($output,[], ';');

fputcsv($output,[
    'No',
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