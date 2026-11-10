<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../config/database.php';
if(session_status()===PHP_SESSION_NONE) session_start();

$userId=(int)($_SESSION['user_id']??0);


try{
    $layananId=(int)($_POST['layanan_id']??0);
    $loket=trim($_POST['loket']??'');
    $cabangId=(int)($_POST['cabang_id']??0);

    if(!$layananId||$loket===''||!$cabangId){
        echo json_encode(['success'=>false,'message'=>'Data layanan, loket atau cabang tidak valid.']); exit;
    }
    if(!$userId){
        echo json_encode([
            'success'=>false,
            'message'=>'Sesi user tidak valid.'
        ]);
        exit;
    }
    $stmt=$pdo->prepare("SELECT id,kode,nama_layanan FROM layanan WHERE id=? AND status='aktif' LIMIT 1");
    $stmt->execute([$layananId]);
    $layanan=$stmt->fetch();

    if(!$layanan){
        echo json_encode(['success'=>false,'message'=>'Layanan tidak ditemukan.']); exit;
    }

    $pdo->beginTransaction();

    $stmt=$pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE a.cabang_id=? AND p.loket=? AND p.status='dipanggil'
        AND a.tanggal=CURDATE()
        ORDER BY p.waktu_panggil DESC LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$cabangId,$loket]);
    $aktif=$stmt->fetch();

    if($aktif){
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Selesaikan nomor '.$aktif['nomor_antrian'].' terlebih dahulu.']); exit;
    }

    $stmt=$pdo->prepare("
        SELECT id,nomor,nomor_antrian
        FROM antrian
        WHERE cabang_id=? AND kode_layanan=? AND tanggal=CURDATE()
        AND status='menunggu'
        ORDER BY nomor ASC LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$cabangId,$layanan['kode']]);
    $antrian=$stmt->fetch();

    if(!$antrian){
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Tidak ada antrian yang menunggu.']); exit;
    }

    $stmt=$pdo->prepare("UPDATE antrian SET status='dipanggil',jam_panggil=NOW() WHERE id=?");
    $stmt->execute([$antrian['id']]);

    $stmt=$pdo->prepare("
        INSERT INTO panggilan(
            antrian_id,user_id,nomor_antrian,layanan,loket,
            waktu_panggil_awal,waktu_panggil,status
        )
        VALUES(?,?,?,?,?,NOW(),NOW(),'dipanggil')
    ");

    $stmt->execute([
        $antrian['id'],
        $userId,
        $antrian['nomor_antrian'],
        $layanan['nama_layanan'],
        $loket
    ]);

    $panggilanId=(int)$pdo->lastInsertId();
    $pdo->commit();

    echo json_encode([
        'success'=>true,
        'message'=>'Nomor berhasil dipanggil.',
        'data'=>[
            'id'=>$panggilanId,
            'panggilan_id'=>$panggilanId,
            'antrian_id'=>(int)$antrian['id'],
            'nomor_antrian'=>$antrian['nomor_antrian'],
            'layanan'=>$layanan['nama_layanan'],
            'loket'=>$loket
        ]
    ]);
}catch(Exception $e){
    if($pdo->inTransaction())$pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Terjadi kesalahan saat memanggil antrian.']);
}