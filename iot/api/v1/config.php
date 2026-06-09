<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

api_require_method('GET');

$deviceUid = trim((string) ($_GET['device_uid'] ?? ''));

if ($deviceUid === '') {
    api_error(422, 'Parameter device_uid wajib dikirim.');
}

$device = api_authenticate_device($koneksi, $deviceUid);
$deviceId = (int) $device['id'];

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT *
     FROM iot_thresholds
     WHERE device_id = ?
     LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'i', $deviceId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$threshold = mysqli_fetch_assoc($result) ?: [];
mysqli_stmt_close($stmt);

api_response(200, [
    'success' => true,
    'data' => [
        'device_uid' => $device['device_uid'],
        'firmware_version' => $device['firmware_version'],
        'sampling_interval_seconds' => (int) $device['sampling_interval_seconds'],
        'calibration' => [
            'soil_dry_adc' => (int) ($threshold['soil_dry_adc'] ?? 3200),
            'soil_wet_adc' => (int) ($threshold['soil_wet_adc'] ?? 1350),
        ],
        'thresholds' => [
            'air_temperature_min' => (float) ($threshold['air_temperature_min'] ?? 18),
            'air_temperature_max' => (float) ($threshold['air_temperature_max'] ?? 34),
            'air_humidity_min' => (float) ($threshold['air_humidity_min'] ?? 40),
            'air_humidity_max' => (float) ($threshold['air_humidity_max'] ?? 90),
            'light_lux_min' => (float) ($threshold['light_lux_min'] ?? 1000),
            'light_lux_max' => (float) ($threshold['light_lux_max'] ?? 80000),
            'soil_moisture_min' => (float) ($threshold['soil_moisture_min'] ?? 55),
            'soil_moisture_max' => (float) ($threshold['soil_moisture_max'] ?? 85),
            'soil_temperature_min' => (float) ($threshold['soil_temperature_min'] ?? 18),
            'soil_temperature_max' => (float) ($threshold['soil_temperature_max'] ?? 32),
        ],
        'notification_enabled' => (bool) ($threshold['notification_enabled'] ?? true),
    ],
]);
