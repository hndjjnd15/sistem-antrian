<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {

    $pdo->query("SELECT 1");

    echo json_encode([
        'success' => true,
        'server' => 'online',
        'database' => 'online',
        'message' => 'Sistem Antrian Bank BBJ aktif',
        'time' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'server' => 'online',
        'database' => 'offline',
        'message' => 'Database tidak tersedia.'
    ]);
}