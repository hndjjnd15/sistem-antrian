<?php
require_once __DIR__.'/config/database.php';

$cabang=$pdo->query("SELECT id,nama FROM cabang WHERE status='aktif' ORDER BY nama")->fetchAll();
$layanan=$pdo->query("SELECT id,nama_layanan FROM layanan WHERE status='aktif' ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Test Ambil Tiket</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial;background:#f3f6f9;color:#17354c}
.wrap{width:min(500px,92%);margin:50px auto}.card{background:#fff;padding:25px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
h1{margin:0 0 6px;font-size:24px}p{margin:0 0 20px;color:#7a8b99;font-size:13px}
label{display:block;margin:14px 0 6px;font-size:12px;font-weight:700}select,button{width:100%;padding:12px;border-radius:8px;font-size:14px}
select{border:1px solid #d8e1e7;background:#fff}button{margin-top:20px;border:0;background:#0284c7;color:#fff;font-weight:800;cursor:pointer}
.result{display:none;margin-top:20px;padding:18px;border-radius:10px;background:#eef6fb;text-align:center}
.result strong{display:block;font-size:38px;color:#075985;margin:8px 0}.error{background:#fee2e2;color:#b42318}
</style>
</head>
<body>
<div class="wrap">
<div class="card">
<h1>Test Ambil Tiket</h1>
<p>Uji pembuatan nomor antrian multi-cabang.</p>

<form id="formAmbil">
<label>Cabang</label>
<select name="cabang_id" required>
<option value="">Pilih Cabang</option>
<?php foreach($cabang as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
<?php endforeach; ?>
</select>

<label>Layanan</label>
<select name="layanan_id" required>
<option value="">Pilih Layanan</option>
<?php foreach($layanan as $l): ?>
<option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_layanan']) ?></option>
<?php endforeach; ?>
</select>

<button type="submit">AMBIL NOMOR</button>
</form>

<div class="result" id="result"></div>
</div>
</div>

<script>
const form=document.getElementById("formAmbil");
const result=document.getElementById("result");

form.addEventListener("submit",async e=>{
    e.preventDefault();
    result.style.display="block";
    result.className="result";
    result.innerHTML="Memproses...";

    try{
        const response=await fetch("api/ambil.php",{
            method:"POST",
            body:new FormData(form)
        });

        const data=await response.json();

        if(!data.success){
            result.classList.add("error");
            result.textContent=data.message;
            return;
        }

        result.innerHTML=`
            <span>${data.data.layanan}</span>
            <strong>${data.data.nomor}</strong>
            <small>${data.data.tanggal} • ${data.data.jam}</small>
        `;
    }catch(e){
        result.classList.add("error");
        result.textContent="Gagal terhubung ke server.";
    }
});
</script>
</body>
</html>