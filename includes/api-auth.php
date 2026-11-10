<?php
/**
 * Guard untuk endpoint JSON panel petugas.
 * Endpoint publik (mis. pengambilan nomor di kiosk) tidak menggunakan helper ini.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function apiJsonError(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Memastikan endpoint panel hanya dapat diakses user pada cabang dan layanan
 * yang memang menjadi kewenangannya.
 */
function requirePanelAccess(int $requestedBranchId, ?int $requestedServiceId = null): void
{
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $role = (string) ($_SESSION['role'] ?? '');
    $sessionBranchId = (int) ($_SESSION['cabang_id'] ?? 0);

    if ($userId <= 0) {
        apiJsonError(401, 'Sesi login tidak valid. Silakan masuk kembali.');
    }

    if ($sessionBranchId <= 0 || $requestedBranchId !== $sessionBranchId) {
        apiJsonError(403, 'Anda tidak memiliki akses ke cabang tersebut.');
    }

    $serviceByRole = [
        'teller' => 1,
        'cs' => 2,
        'kredit' => 3
    ];

    if ($role === 'admin') {
        return;
    }

    if (!isset($serviceByRole[$role])) {
        apiJsonError(403, 'Role tidak diizinkan mengakses panel.');
    }

    if ($requestedServiceId !== null && $requestedServiceId !== $serviceByRole[$role]) {
        apiJsonError(403, 'Anda tidak memiliki akses ke layanan tersebut.');
    }
}
