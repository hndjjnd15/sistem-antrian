<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['antrian_id']) ||
        !is_numeric($_POST['antrian_id'])
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'ID antrian tidak valid.'
        ]);

        exit;
    }


    if (
        !isset($_POST['status'])
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Status print tidak diberikan.'
        ]);

        exit;
    }


    $antrian_id =
        (int) $_POST['antrian_id'];


    $status =
        trim($_POST['status']);


    /*
    |--------------------------------------------------------------------------
    | Status yang diperbolehkan
    |--------------------------------------------------------------------------
    */

    $allowedStatus = [
        'menunggu',
        'diproses',
        'berhasil',
        'gagal'
    ];


    if (
        !in_array(
            $status,
            $allowedStatus,
            true
        )
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Status print tidak valid.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cek print job
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            antrian_id,
            status
        FROM print_jobs
        WHERE antrian_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $antrian_id
    ]);


    $printJob =
        $stmt->fetch();


    if (!$printJob) {

        echo json_encode([
            'success' => false,
            'message' => 'Print job tidak ditemukan.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update status
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE print_jobs
        SET
            status = ?,
            processed_at = NOW()
        WHERE antrian_id = ?
    ");

    $stmt->execute([
        $status,
        $antrian_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' => true,

        'message' =>
            'Status print berhasil diperbarui.',

        'data' => [

            'antrian_id' =>
                $antrian_id,

            'status' =>
                $status

        ]

    ]);

} catch (Exception $e) {

    echo json_encode([

        'success' => false,

        'message' =>
            'Terjadi kesalahan server.',

        'error' =>
            $e->getMessage()

    ]);

}
