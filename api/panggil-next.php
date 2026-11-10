<?php
require_once __DIR__ . '/../includes/api-auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    apiJsonError(405, 'Metode tidak diizinkan.');
}

try {
    $layananId = (int) ($_POST['layanan_id'] ?? 0);
    $loket = trim((string) ($_POST['loket'] ?? ''));
    $cabangId = (int) ($_POST['cabang_id'] ?? 0);

    if ($layananId <= 0 || $cabangId <= 0 || $loket === '' || strlen($loket) > 30) {
        apiJsonError(422, 'Data layanan, loket, atau cabang tidak valid.');
    }

    requirePanelAccess($cabangId, $layananId);
    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id,kode,nama_layanan FROM layanan WHERE id=? AND status='aktif' LIMIT 1");
    $stmt->execute([$layananId]);
    $layanan = $stmt->fetch();

    if (!$layanan) {
        apiJsonError(404, 'Layanan tidak ditemukan atau tidak aktif.');
    }

    $pdo->beginTransaction();

    // Lock cabang sebagai mutex agar dua petugas tidak mengambil antrean yang sama.
    $stmt = $pdo->prepare("SELECT id FROM cabang WHERE id=? AND status='aktif' FOR UPDATE");
    $stmt->execute([$cabangId]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        apiJsonError(404, 'Cabang tidak ditemukan atau tidak aktif.');
    }

    $stmt = $pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE a.cabang_id=? AND p.loket=? AND p.status='dipanggil'
          AND a.tanggal=CURDATE()
        ORDER BY p.waktu_panggil DESC LIMIT 1
    ");
    $stmt->execute([$cabangId, $loket]);
    $aktif = $stmt->fetch();

    if ($aktif) {
        $pdo->rollBack();
        apiJsonError(409, 'Selesaikan nomor ' . $aktif['nomor_antrian'] . ' terlebih dahulu.');
    }

    $stmt = $pdo->prepare("
        SELECT id,nomor,nomor_antrian
        FROM antrian
        WHERE cabang_id=? AND kode_layanan=? AND tanggal=CURDATE()
          AND status='menunggu'
        ORDER BY nomor ASC LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$cabangId, $layanan['kode']]);
    $antrian = $stmt->fetch();

    if (!$antrian) {
        $pdo->rollBack();
        apiJsonError(404, 'Tidak ada antrean yang menunggu.');
    }

    $stmt = $pdo->prepare("UPDATE antrian SET status='dipanggil',jam_panggil=NOW() WHERE id=? AND status='menunggu'");
    $stmt->execute([$antrian['id']]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();
        apiJsonError(409, 'Antrean sudah diproses petugas lain. Silakan coba lagi.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO panggilan(
            antrian_id,user_id,nomor_antrian,layanan,loket,
            waktu_panggil_awal,waktu_panggil,status
        ) VALUES(?,?,?,?,?,NOW(),NOW(),'dipanggil')
    ");
    $stmt->execute([
        $antrian['id'], $userId, $antrian['nomor_antrian'],
        $layanan['nama_layanan'], $loket
    ]);

    $panggilanId = (int) $pdo->lastInsertId();
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Nomor berhasil dipanggil.',
        'data' => [
            'id' => $panggilanId,
            'panggilan_id' => $panggilanId,
            'antrian_id' => (int) $antrian['id'],
            'nomor_antrian' => $antrian['nomor_antrian'],
            'layanan' => $layanan['nama_layanan'],
            'loket' => $loket
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('panggil-next.php: ' . $e->getMessage());
    apiJsonError(500, 'Terjadi kesalahan saat memanggil antrean.');
}
