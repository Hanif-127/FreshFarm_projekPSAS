<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

api_require_method('POST');

$data = api_get_json_body();
$deviceUid = trim((string) ($data['device_uid'] ?? ''));

if ($deviceUid === '') {
    api_error(422, 'device_uid wajib dikirim.');
}

$device = api_authenticate_device($koneksi, $deviceUid);

$airTemperature = api_nullable_float($data, 'air_temperature_c', -50, 100);
$airHumidity = api_nullable_float($data, 'air_humidity_pct', 0, 100);
$lightLux = api_nullable_float($data, 'light_lux', 0, 200000);
$soilMoistureRaw = api_nullable_int($data, 'soil_moisture_raw', 0, 4095);
$soilMoisturePct = api_nullable_float($data, 'soil_moisture_pct', 0, 100);
$soilTemperature = api_nullable_float($data, 'soil_temperature_c', -50, 100);
$wifiRssi = api_nullable_int($data, 'wifi_rssi', -150, 0);

if (
    $airTemperature === null &&
    $airHumidity === null &&
    $lightLux === null &&
    $soilMoistureRaw === null &&
    $soilMoisturePct === null &&
    $soilTemperature === null
) {
    api_error(422, 'Minimal satu nilai sensor harus dikirim.');
}

$deviceId = (int) $device['id'];
$recordedAt = date('Y-m-d H:i:s');

try {
    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare(
        $koneksi,
        'INSERT INTO iot_readings (
            device_id,
            air_temperature_c,
            air_humidity_pct,
            light_lux,
            soil_moisture_raw,
            soil_moisture_pct,
            soil_temperature_c,
            wifi_rssi,
            recorded_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param(
        $stmt,
        'idddiddis',
        $deviceId,
        $airTemperature,
        $airHumidity,
        $lightLux,
        $soilMoistureRaw,
        $soilMoisturePct,
        $soilTemperature,
        $wifiRssi,
        $recordedAt
    );
    mysqli_stmt_execute($stmt);
    $readingId = (int) mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    $updateStmt = mysqli_prepare(
        $koneksi,
        'UPDATE iot_devices
         SET last_seen_at = ?, updated_at = CURRENT_TIMESTAMP
         WHERE id = ?'
    );
    mysqli_stmt_bind_param($updateStmt, 'si', $recordedAt, $deviceId);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    mysqli_commit($koneksi);
} catch (Throwable $exception) {
    mysqli_rollback($koneksi);
    error_log('[FreshFarm IoT readings] ' . $exception->getMessage());
    api_error(500, 'Data sensor gagal disimpan.');
}

api_response(201, [
    'success' => true,
    'message' => 'Data sensor berhasil disimpan.',
    'data' => [
        'reading_id' => $readingId,
        'device_uid' => $deviceUid,
        'recorded_at' => $recordedAt,
    ],
]);
