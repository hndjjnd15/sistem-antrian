<?php
require_once __DIR__ . '/../includes/auth.php';

$role=$_SESSION['role']??'';
$cabangId=(int)($_SESSION['cabang_id']??0);

$panelConfig=[
    'teller'=>['layanan_id'=>1,'loket'=>1],
    'cs'=>['layanan_id'=>2,'loket'=>2],
    'kredit'=>['layanan_id'=>3,'loket'=>3]
];

if(!$cabangId) exit('Cabang user tidak valid.');

if($role==='admin'){
    $layananId=(int)($_GET['layanan_id']??1);
    $loket=trim($_GET['loket']??'1');
}else{
    if(!isset($panelConfig[$role])){
        http_response_code(403);
        exit('Akses tidak diizinkan.');
    }
    $layananId=$panelConfig[$role]['layanan_id'];
    $loket=$panelConfig[$role]['loket'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Panel Antrian Bank BBJ</title>
<link rel="stylesheet" href="css/panel.css">
</head>
<body>

<div class="app">
<header class="header">
    <div>
        <h1>BANK BBJ</h1>
        <p><?= htmlspecialchars(strtoupper($_SESSION['nama_cabang']??'')) ?></p>
    </div>

    <div class="clock" id="clock">00:00:00</div>

    <div class="panel-user">
        <?= htmlspecialchars($_SESSION['nama']) ?> |
        <?= strtoupper(htmlspecialchars($_SESSION['role'])) ?> |
        <a href="../auth/logout.php">Keluar</a>
    </div>
</header>

<main class="panel">
    <section class="panel-head">
        <span>PANEL PEMANGGILAN</span>
        <h2 id="namaLayanan">-</h2>
        <div class="loket" id="namaLoket">LOKET -</div>
    </section>

    <section class="current-card">
        <div class="current-label">SEDANG DILAYANI</div>
        <div class="current-number" id="currentNumber">---</div>
        <div class="current-service" id="currentService">Belum ada panggilan</div>
    </section>

    <section class="waiting-card">
        <span>ANTRIAN MENUNGGU</span>
        <strong id="waitingCount">0</strong>
    </section>

    <section class="actions">
        <button type="button" id="btnNext" class="btn btn-primary">PANGGIL BERIKUTNYA</button>
        <button type="button" id="btnRecall" class="btn btn-secondary">PANGGIL ULANG</button>
        <button type="button" id="btnFinish" class="btn btn-success">SELESAI</button>
    </section>

    <div class="message" id="message"></div>
</main>
</div>

<script>
window.PANEL_CONFIG={
    layananId:<?= (int)$layananId ?>,
    loket:<?= json_encode((string)$loket) ?>,
    cabangId:<?= (int)$cabangId ?>
};
</script>
<script src="js/panel.js"></script>
</body>
</html>