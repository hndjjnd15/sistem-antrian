<?php
session_start();

require_once __DIR__.'/../config/database.php';

if(($_SESSION['role']??'')!=='admin'){
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$cabangId=(int)($_GET['cabang_id']??0);
$tanggal=trim($_GET['tanggal']??'');

if($cabangId<=0){
    exit('Cabang tidak valid.');
}

if(!$tanggal){
    exit('Tanggal tidak valid.');
}

$date=DateTime::createFromFormat('Y-m-d',$tanggal);

if(!$date || $date->format('Y-m-d')!==$tanggal){
    exit('Format tanggal tidak valid.');
}

/* =========================
   CABANG
========================= */

$stmt=$pdo->prepare("
    SELECT id,kode,nama
    FROM cabang
    WHERE id=?
    LIMIT 1
");

$stmt->execute([$cabangId]);

$cabang=$stmt->fetch();

if(!$cabang){
    exit('Cabang tidak ditemukan.');
}

/* =========================
   RINGKASAN
========================= */

$stmt=$pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN kode_layanan='A' THEN 1 ELSE 0 END) AS teller,
        SUM(CASE WHEN kode_layanan='B' THEN 1 ELSE 0 END) AS cs,
        SUM(CASE WHEN kode_layanan='C' THEN 1 ELSE 0 END) AS kredit
    FROM antrian
    WHERE cabang_id=?
    AND tanggal=?
");

$stmt->execute([
    $cabangId,
    $tanggal
]);

$summary=$stmt->fetch();

/* =========================
   DETAIL
========================= */

$stmt=$pdo->prepare("
    SELECT
        nomor_antrian,
        kode_layanan,
        jam_ambil,
        jam_panggil,
        jam_selesai,
        status
    FROM antrian
    WHERE cabang_id=?
    AND tanggal=?
    ORDER BY id ASC
");

$stmt->execute([
    $cabangId,
    $tanggal
]);

$data=$stmt->fetchAll();

function namaLayanan($kode){
    return match($kode){
        'A'=>'Teller',
        'B'=>'Customer Service',
        'C'=>'Kredit',
        default=>$kode
    };
}

function formatJam($jam){
    if(!$jam){
        return '-';
    }

    return substr($jam,0,8);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1.0">

<title>
    Laporan Harian Sistem Antrian
</title>

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    padding:30px;
    font-family:Arial,sans-serif;
    color:#111;
    font-size:12px;
    background:#f4f4f4;
}

/* =========================
   DOCUMENT
========================= */

.document{
    max-width:900px;
    margin:auto;
    padding:35px;
    background:#fff;
    box-shadow:0 3px 15px rgba(0,0,0,.1);
}

/* =========================
   BUTTON
========================= */

.toolbar{
    max-width:900px;
    margin:0 auto 15px;
    display:flex;
    gap:10px;
}

.btn{
    border:0;
    border-radius:6px;
    padding:10px 16px;
    font-weight:700;
    cursor:pointer;
}

.btn-print{
    background:#075985;
    color:#fff;
}

.btn-back{
    background:#e5e7eb;
    color:#333;
}

/* =========================
   HEADER
========================= */

.report-header{
    text-align:center;
    border-bottom:2px solid #222;
    padding-bottom:15px;
    margin-bottom:20px;
}

.report-header h1{
    margin:0;
    font-size:20px;
}

.report-header h2{
    margin:6px 0 0;
    font-size:15px;
}

/* =========================
   INFO
========================= */

.info{
    width:100%;
    margin-bottom:20px;
}

.info td{
    padding:4px 0;
}

/* =========================
   SUMMARY
========================= */

.summary{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:20px;
}

.summary-card{
    border:1px solid #aaa;
    padding:12px;
    text-align:center;
}

.summary-card span{
    display:block;
    font-size:10px;
}

.summary-card strong{
    display:block;
    margin-top:6px;
    font-size:20px;
}

/* =========================
   TABLE
========================= */

table.data{
    width:100%;
    border-collapse:collapse;
}

.data th,
.data td{
    border:1px solid #777;
    padding:6px;
}

.data th{
    background:#eee;
    text-align:center;
}

/* =========================
   SIGNATURE
========================= */

.signature{
    display:flex;
    justify-content:flex-end;
    margin-top:45px;
}

.signature-box{
    width:230px;
    text-align:center;
}

.signature-space{
    height:65px;
}

/* =========================
   PRINT
========================= */

@media print{

    body{
        padding:0;
        background:#fff;
    }

    .toolbar{
        display:none;
    }

    .document{
        max-width:none;
        padding:0;
        box-shadow:none;
    }

    @page{
        size:A4 portrait;
        margin:12mm;
    }
}

</style>

</head>

<body>

<div class="toolbar">

    <button
        type="button"
        class="btn btn-print"
        onclick="window.print()">
        Cetak / Simpan PDF
    </button>

    <button
        type="button"
        class="btn btn-back"
        onclick="window.close()">
        Tutup
    </button>

</div>

<div class="document">

    <div class="report-header">

        <h1>
            PT BPR BOGOR JABAR (PERSERODA)
        </h1>

        <h2>
            LAPORAN HARIAN SISTEM ANTRIAN
        </h2>

    </div>

    <table class="info">

        <tr>
            <td width="110">
                Cabang
            </td>

            <td>
                :
                <?= htmlspecialchars($cabang['nama']) ?>
            </td>
        </tr>

        <tr>
            <td>
                Tanggal
            </td>

            <td>
                :
                <?= date(
                    'd-m-Y',
                    strtotime($tanggal)
                ) ?>
            </td>
        </tr>

    </table>

    <div class="summary">

        <div class="summary-card">
            <span>
                Total Pengunjung
            </span>

            <strong>
                <?= (int)($summary['total']??0) ?>
            </strong>
        </div>

        <div class="summary-card">
            <span>
                Teller
            </span>

            <strong>
                <?= (int)($summary['teller']??0) ?>
            </strong>
        </div>

        <div class="summary-card">
            <span>
                Customer Service
            </span>

            <strong>
                <?= (int)($summary['cs']??0) ?>
            </strong>
        </div>

        <div class="summary-card">
            <span>
                Kredit
            </span>

            <strong>
                <?= (int)($summary['kredit']??0) ?>
            </strong>
        </div>

    </div>

    <table class="data">

        <thead>

        <tr>
            <th>No</th>
            <th>Nomor</th>
            <th>Layanan</th>
            <th>Jam Ambil</th>
            <th>Jam Panggil</th>
            <th>Jam Selesai</th>
            <th>Status</th>
        </tr>

        </thead>

        <tbody>

        <?php if(!$data): ?>

            <tr>

                <td
                    colspan="7"
                    style="text-align:center">

                    Tidak ada data.

                </td>

            </tr>

        <?php else: ?>

            <?php foreach($data as $i=>$row): ?>

                <tr>

                    <td>
                        <?= $i+1 ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $row['nomor_antrian']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            namaLayanan(
                                $row['kode_layanan']
                            )
                        ) ?>
                    </td>

                    <td>
                        <?= formatJam(
                            $row['jam_ambil']
                        ) ?>
                    </td>

                    <td>
                        <?= formatJam(
                            $row['jam_panggil']
                        ) ?>
                    </td>

                    <td>
                        <?= formatJam(
                            $row['jam_selesai']
                        ) ?>
                    </td>

                    <td>
                        <?= strtoupper(
                            htmlspecialchars(
                                $row['status']
                            )
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

    <div class="signature">

        <div class="signature-box">

            <div>
                <?= htmlspecialchars(
                    $cabang['nama']
                ) ?>,
                <?= date(
                    'd-m-Y',
                    strtotime($tanggal)
                ) ?>
            </div>

            <div>
                Mengetahui,
            </div>

            <div class="signature-space">
            </div>

            <strong>
                ______________________
            </strong>

        </div>

    </div>

</div>

</body>
</html>