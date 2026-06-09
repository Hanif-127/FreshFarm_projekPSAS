<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../includes/koneksi.php';

[$script, $deviceUid, $pairingCode, $apiKey, $deviceName, $location] = array_pad($argv, 6, '');

$deviceUid = strtolower(trim($deviceUid));
$pairingCode = strtoupper(trim($pairingCode));
$apiKey = trim($apiKey);
$deviceName = trim($deviceName) ?: 'ESP32 FreshFarm';
$location = trim($location) ?: 'Lokasi belum diatur';

if (!preg_match('/^[a-z0-9][a-z0-9-]{2,79}$/', $deviceUid) || $pairingCode === '' || $apiKey === '') {
    fwrite(
        STDERR,
        "Penggunaan:\nphp iot/tools/provision_device.php <device_uid> <kode_pairing> <api_key> [nama] [lokasi]\n"
    );
    exit(1);
}

$pairingHash = password_hash($pairingCode, PASSWORD_DEFAULT);
$apiKeyHash = password_hash($apiKey, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $koneksi,
    'INSERT INTO iot_devices (
        user_id, device_uid, nama_perangkat, lokasi, api_key_hash, pairing_code_hash, status
     ) VALUES (
        NULL, ?, ?, ?, ?, ?, "active"
     )'
);
mysqli_stmt_bind_param($stmt, 'sssss', $deviceUid, $deviceName, $location, $apiKeyHash, $pairingHash);

try {
    mysqli_stmt_execute($stmt);
} catch (mysqli_sql_exception $exception) {
    fwrite(STDERR, "Gagal mendaftarkan perangkat: {$exception->getMessage()}\n");
    exit(1);
} finally {
    mysqli_stmt_close($stmt);
}

echo "Perangkat siap dipasangkan.\n";
echo "Device UID   : {$deviceUid}\n";
echo "Kode pairing: {$pairingCode}\n";
echo "Nama         : {$deviceName}\n";
echo "Lokasi       : {$location}\n";

