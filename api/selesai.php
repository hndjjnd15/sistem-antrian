<?php
require_once __DIR__ . '/../includes/api-auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    apiJsonError(405, 'Metode tidak diizinkan.');
}

try {
    $panggilanId = (int) ($_POST['panggilan_id'] ?? 0);
    $cabangId = (int) ($_POST['cabang_id'] ?? 0);

    if ($panggilanId <= 0 || $cabangId <= 0) {
        apiJsonError(422, 'Data panggilan atau cabang tidak valid.');
    }

    requirePanelAccess($cabangId);
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT p.id,p.antrian_id,p.nomor_antrian,p.status,p.layanan_id
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE p.id=? AND a.cabang_id=? AND a.tanggal=CURDATE()
        LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$panggilanId, $cabangId]);
    $panggilan = $stmt->fetch();

    if (!$panggilan) {
        $pdo->rollBack();
        apiJsonError(404, 'Panggilan tidak ditemukan.');
    }
    if ($panggilan['status'] !== 'dipanggil') {
        $pdo->rollBack();
        apiJsonError(409, 'Nomor sudah selesai atau tidak aktif.');
    }

    $stmt = $pdo->prepare("UPDATE panggilan SET status='selesai',waktu_selesai=NOW() WHERE id=? AND status='dipanggil'");
    $stmt->execute([$panggilanId]);

    $stmt = $pdo->prepare("UPDATE antrian SET status='selesai',jam_selesai=NOW() WHERE id=? AND cabang_id=? AND status='dipanggil'");
    $stmt->execute([$panggilan['antrian_id'], $cabangId]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Status antrean berubah saat proses selesai.');
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Pelayanan nomor ' . $panggilan['nomor_antrian'] . ' selesai.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('selesai.php: ' . $e->getMessage());
    apiJsonError(500, 'Gagal menyelesaikan pelayanan.');
}
