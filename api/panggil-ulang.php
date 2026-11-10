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

    $stmt = $pdo->prepare("
        SELECT p.id,p.nomor_antrian,p.layanan,p.loket,p.status
        FROM panggilan p
        INNER JOIN antrian a ON a.id=p.antrian_id
        WHERE p.id=? AND a.cabang_id=? AND a.tanggal=CURDATE()
        LIMIT 1
    ");
    $stmt->execute([$panggilanId, $cabangId]);
    $panggilan = $stmt->fetch();

    if (!$panggilan) {
        apiJsonError(404, 'Data panggilan tidak ditemukan.');
    }
    if ($panggilan['status'] !== 'dipanggil') {
        apiJsonError(409, 'Nomor sudah selesai.');
    }

    $stmt = $pdo->prepare("UPDATE panggilan SET waktu_panggil=NOW() WHERE id=? AND status='dipanggil'");
    $stmt->execute([$panggilanId]);

    echo json_encode([
        'success' => true,
        'message' => 'Nomor dipanggil ulang.',
        'data' => [
            'id' => (int) $panggilan['id'],
            'nomor_antrian' => $panggilan['nomor_antrian'],
            'layanan' => $panggilan['layanan'],
            'loket' => $panggilan['loket']
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('panggil-ulang.php: ' . $e->getMessage());
    apiJsonError(500, 'Gagal memanggil ulang.');
}
