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
        COUNT(*) AS total,
        COUNT(DISTINCT tanggal) AS hari,
        SUM(CASE WHEN kode_layanan='A' THEN 1 ELSE 0 END) AS teller,
        SUM(CASE WHEN kode_layanan='B' THEN 1 ELSE 0 END) AS cs,
        SUM(CASE WHEN kode_layanan='C' THEN 1 ELSE 0 END) AS kredit
    FROM antrian
    WHERE cabang_id=?
    AND tanggal BETWEEN ? AND ?
");

$stmt->execute([
    $cabangId,
    $awal,
    $akhir
]);

$summary=$stmt->fetch();

$total=(int)$summary['total'];
$hari=(int)$summary['hari'];

$rata=$hari>0
    ?round($total/$hari,2)
    :0;

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
    $awal,
    $akhir
]);

$rekap=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<title>Laporan Periode</title>

<style>
*{box-sizing:border-box}

body{
    margin:0;
    padding:30px;
    font-family:Arial,sans-serif;
    font-size:12px;
    color:#111;
}

.header{
    text-align:center;
    border-bottom:2px solid #111;
    padding-bottom:15px;
    margin-bottom:20px;
}

.header h1{
    margin:0;
    font-size:21px;
}

.header h2{
    margin:6px 0 0;
    font-size:15px;
}

.info{
    margin-bottom:18px;
}

.info td{
    padding:3px;
}

.summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-bottom:20px;
}

.card{
    border:1px solid #888;
    padding:10px;
    text-align:center;
}

.card span{
    display:block;
    font-size:10px;
}

.card strong{
    display:block;
    margin-top:4px;
    font-size:19px;
}

table.data{
    width:100%;
    border-collapse:collapse;
}

.data th,
.data td{
    border:1px solid #777;
    padding:7px;
}

.data th{
    background:#eee;
}

.signature{
    margin-top:50px;
    display:flex;
    justify-content:flex-end;
}

.signature-box{
    width:230px;
    text-align:center;
}

.signature-space{
    height:70px;
}

.no-print{
    margin-bottom:20px;
}

.no-print button{
    border:0;
    padding:10px 16px;
    cursor:pointer;
}

@media print{

    body{
        padding:0;
    }

    .no-print{
        display:none;
    }

    @page{
        size:A4 portrait;
        margin:12mm;
    }
}
</style>
</head>

<body>

<div class="no-print">

    <button onclick="window.print()">
        Cetak / Simpan PDF
    </button>

</div>

<div class="header">

    <h1>
        PT BPR BOGOR JABAR (PERSERODA)
    </h1>

    <h2>
        LAPORAN PERIODE SISTEM ANTRIAN
    </h2>

</div>

<table class="info">

<tr>
    <td width="120">Cabang</td>

    <td>
        :
        <?= htmlspecialchars($cabang['nama']) ?>
    </td>
</tr>

<tr>
    <td>Periode</td>

    <td>
        :
        <?= date('d-m-Y',strtotime($awal)) ?>
        s/d
        <?= date('d-m-Y',strtotime($akhir)) ?>
    </td>
</tr>

</table>

<div class="summary">

    <div class="card">
        <span>Total Pengunjung</span>
        <strong><?= $total ?></strong>
    </div>

    <div class="card">
        <span>Hari Operasional</span>
        <strong><?= $hari ?></strong>
    </div>

    <div class="card">
        <span>Rata-rata / Hari</span>
        <strong><?= $rata ?></strong>
    </div>

    <div class="card">
        <span>Teller</span>
        <strong><?= (int)$summary['teller'] ?></strong>
    </div>

    <div class="card">
        <span>Customer Service</span>
        <strong><?= (int)$summary['cs'] ?></strong>
    </div>

    <div class="card">
        <span>Kredit</span>
        <strong><?= (int)$summary['kredit'] ?></strong>
    </div>

</div>

<h3>Rekap Harian</h3>

<table class="data">

<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Teller</th>
    <th>Customer Service</th>
    <th>Kredit</th>
    <th>Total</th>
</tr>
</thead>

<tbody>

<?php if(!$rekap): ?>

<tr>
    <td colspan="6" style="text-align:center">
        Tidak ada data.
    </td>
</tr>

<?php else: ?>

<?php foreach($rekap as $i=>$row): ?>

<tr>

    <td><?= $i+1 ?></td>

    <td>
        <?= date(
            'd-m-Y',
            strtotime($row['tanggal'])
        ) ?>
    </td>

    <td><?= (int)$row['teller'] ?></td>

    <td><?= (int)$row['cs'] ?></td>

    <td><?= (int)$row['kredit'] ?></td>

    <td>
        <strong>
            <?= (int)$row['total'] ?>
        </strong>
    </td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>

<div class="signature">

    <div class="signature-box">

        <div>
            <?= htmlspecialchars($cabang['nama']) ?>,
            <?= date('d-m-Y') ?>
        </div>

        <div>
            Mengetahui,
        </div>

        <div class="signature-space"></div>

        <strong>
            __________________________
        </strong>

    </div>

</div>

</body>
</html>