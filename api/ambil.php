<?php

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';

try {

    $layananId = (int)($_POST['layanan_id'] ?? 0);
    $cabangId  = (int)($_POST['cabang_id'] ?? 0);

    if ($layananId <= 0 || $cabangId <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Data layanan atau cabang tidak valid.'
        ]);

        exit;
    }

    // =====================================================
    // CEK LAYANAN AKTIF
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            kode,
            nama_layanan
        FROM layanan
        WHERE id=?
        AND status='aktif'
        LIMIT 1
    ");

    $stmt->execute([
        $layananId
    ]);

    $dataLayanan = $stmt->fetch();

    if (!$dataLayanan) {

        echo json_encode([
            'success' => false,
            'message' => 'Layanan sedang tidak tersedia.'
        ]);

        exit;
    }

    $kode = $dataLayanan['kode'];
    $layanan = $dataLayanan['nama_layanan'];

    // =====================================================
    // CEK CABANG
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            kode,
            nama
        FROM cabang
        WHERE id=?
        AND status='aktif'
        LIMIT 1
    ");

    $stmt->execute([
        $cabangId
    ]);

    $dataCabang = $stmt->fetch();

    if (!$dataCabang) {

        echo json_encode([
            'success' => false,
            'message' => 'Cabang tidak aktif atau tidak ditemukan.'
        ]);

        exit;
    }

    $tanggal = date('Y-m-d');

    // =====================================================
    // MULAI TRANSAKSI
    // =====================================================

    $pdo->beginTransaction();

    // =====================================================
    // AMBIL NOMOR TERAKHIR
    // BERDASARKAN:
    // CABANG + LAYANAN + TANGGAL
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT nomor
        FROM antrian
        WHERE cabang_id=?
        AND kode_layanan=?
        AND tanggal=?
        ORDER BY nomor DESC
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        $cabangId,
        $kode,
        $tanggal
    ]);

    $row = $stmt->fetch();

    $nomor =
        ((int)($row['nomor'] ?? 0)) + 1;

    $nomorAntrian =
        $kode . '-' .
        str_pad(
            $nomor,
            3,
            '0',
            STR_PAD_LEFT
        );

    // =====================================================
    // INSERT ANTRIAN
    // =====================================================

    $stmt = $pdo->prepare("
        INSERT INTO antrian (
            cabang_id,
            nomor,
            kode_layanan,
            nomor_antrian,
            tanggal,
            jam_ambil,
            status
        )
        VALUES (
            ?,?,?,?,?,
            NOW(),
            'menunggu'
        )
    ");

    $stmt->execute([
        $cabangId,
        $nomor,
        $kode,
        $nomorAntrian,
        $tanggal
    ]);

    $antrianId =
        (int)$pdo->lastInsertId();

    // =====================================================
    // INSERT PRINT JOB
    // =====================================================

    $stmt = $pdo->prepare("
        INSERT INTO print_jobs (
            antrian_id,
            status
        )
        VALUES (
            ?,
            'menunggu'
        )
    ");

    $stmt->execute([
        $antrianId
    ]);

    // =====================================================
    // COMMIT
    // =====================================================

    $pdo->commit();

    // =====================================================
    // RESPONSE
    // =====================================================

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $antrianId,
            'nomor' => $nomorAntrian,
            'layanan' => $layanan,
            'kode_layanan' => $kode,

            'cabang' => [
                'id' => (int)$dataCabang['id'],
                'kode' => $dataCabang['kode'],
                'nama' => $dataCabang['nama']
            ],

            'tanggal' => date('d-m-Y'),
            'jam' => date('H:i:s')
        ]
    ]);

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'ambil.php: ' . $e->getMessage()
    );

    echo json_encode([
        'success' => false,
        'message' => 'Gagal membuat nomor antrian.'
    ]);
}