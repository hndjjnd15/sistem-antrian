<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

function requireRole(array $roles): void
{
    $role = $_SESSION['role'] ?? '';

    if (!in_array($role, $roles, true)) {
        http_response_code(403);

        exit('Akses tidak diizinkan.');
    }
}