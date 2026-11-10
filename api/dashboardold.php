<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
try {
    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'menunggu') AS menunggu,
            SUM(status = 'dipanggil') AS dipanggil,
            SUM(status = 'selesai') AS selesai
        FROM antrian
        WHERE tanggal = CURDATE()
    ");

    $summary = $stmt->fetch();

    $stmt = $pdo->query("
        SELECT
            kode_layanan,
            COUNT(*) AS total
        FROM antrian
        WHERE tanggal = CURDATE()
        GROUP BY kode_layanan
        ORDER BY kode_layanan
    ");

    $layanan = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT
            nomor_antrian,
            layanan,
            loket,
            waktu_panggil,
            status
        FROM panggilan
        WHERE DATE(waktu_panggil) = CURDATE()
        ORDER BY waktu_panggil DESC
        LIMIT 10
    ");

    $panggilan = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT
            pj.antrian_id,
            pj.status,
            pj.created_at,
            pj.processed_at
        FROM print_jobs pj
        ORDER BY pj.id DESC
        LIMIT 1
    ");

    $print = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total' => (int) ($summary['total'] ?? 0),
                'menunggu' => (int) ($summary['menunggu'] ?? 0),
                'dipanggil' => (int) ($summary['dipanggil'] ?? 0),
                'selesai' => (int) ($summary['selesai'] ?? 0)
            ],
            'layanan' => $layanan,
            'panggilan' => $panggilan,
            'print' => $print ?: null
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data dashboard.'
    ]);
}
