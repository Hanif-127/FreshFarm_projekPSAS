<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$iot_active_page = basename($_SERVER['SCRIPT_NAME'] ?? 'dashboard.php', '.php');
$iot_css_version = filemtime(__DIR__ . '/../../assets/css/iot_monitoring.css');
$iot_js_version = filemtime(__DIR__ . '/../../assets/js/iot_monitoring.js');
$iot_user_id = (int) $_SESSION['user_id'];

function iot_relative_time(?string $date): string
{
    if (!$date) {
        return 'belum pernah mengirim data';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'waktu tidak diketahui';
    }

    $seconds = max(0, time() - $timestamp);
    if ($seconds < 60) {
        return 'baru saja';
    }

    $minutes = (int) floor($seconds / 60);
    if ($minutes < 60) {
        return $minutes . ' menit lalu';
    }

    $hours = (int) floor($minutes / 60);
    if ($hours < 24) {
        return $hours . ' jam lalu';
    }

    return (int) floor($hours / 24) . ' hari lalu';
}

function iot_threshold_state(?float $value, ?float $min, ?float $max): string
{
    if ($value === null) {
        return 'critical';
    }

    if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
        return 'warning';
    }

    return 'safe';
}

function iot_sensor_note(string $state, bool $hasValue): string
{
    if (!$hasValue) {
        return 'Belum ada data';
    }

    return $state === 'safe' ? 'Dalam batas aman' : 'Di luar batas aman';
}

function iot_state_label(string $state): string
{
    return match ($state) {
        'critical' => 'Belum Ada Data',
        'warning' => 'Perhatian',
        default => 'Aman',
    };
}

function iot_format_value(float|int|null $value): string
{
    if ($value === null) {
        return '-';
    }

    if (is_float($value)) {
        return number_format($value, 1, ',', '.');
    }

    return number_format($value, 0, ',', '.');
}

function iot_nullable_float(mixed $value): ?float
{
    return $value === null ? null : (float) $value;
}

$iot_device_row = null;
$device_stmt = mysqli_prepare(
    $koneksi,
    "SELECT d.*, t.air_temperature_min, t.air_temperature_max,
            t.air_humidity_min, t.air_humidity_max,
            t.light_lux_min, t.light_lux_max,
            t.soil_moisture_min, t.soil_moisture_max,
            t.soil_temperature_min, t.soil_temperature_max
     FROM iot_devices d
     LEFT JOIN iot_thresholds t ON t.device_id = d.id
     WHERE d.user_id = ?
     ORDER BY (d.status = 'active') DESC, d.updated_at DESC
     LIMIT 1"
);

if ($device_stmt) {
    mysqli_stmt_bind_param($device_stmt, 'i', $iot_user_id);
    mysqli_stmt_execute($device_stmt);
    $device_result = mysqli_stmt_get_result($device_stmt);
    $iot_device_row = mysqli_fetch_assoc($device_result) ?: null;
    mysqli_stmt_close($device_stmt);
}

$iot_latest_reading = null;

$last_seen_at = $iot_device_row['last_seen_at'] ?? null;
$interval_seconds = (int) ($iot_device_row['sampling_interval_seconds'] ?? 300);
$online_window = max(900, $interval_seconds * 3);
$iot_is_online = $iot_device_row !== null
    && ($iot_device_row['status'] ?? '') === 'active'
    && $last_seen_at !== null
    && strtotime($last_seen_at) >= time() - $online_window;

$iot_device = [
    'name' => $iot_device_row['nama_perangkat'] ?? 'Belum ada perangkat',
    'uid' => $iot_device_row['device_uid'] ?? '-',
    'location' => $iot_device_row['lokasi'] ?? 'Lokasi belum diatur',
    'status' => $iot_is_online ? 'online' : 'offline',
    'last_seen' => iot_relative_time($last_seen_at),
    'firmware' => ($iot_device_row['firmware_version'] ?? '') ?: '-',
    'wifi_rssi' => $iot_latest_reading['wifi_rssi'] ?? null,
    'interval' => $interval_seconds,
];

$sensor_definitions = [
    [
        'key' => 'air_temperature',
        'label' => 'Suhu Udara',
        'value_field' => 'air_temperature_c',
        'min_field' => 'air_temperature_min',
        'max_field' => 'air_temperature_max',
        'unit' => "\u{00B0}C",
    ],
    [
        'key' => 'air_humidity',
        'label' => 'Kelembapan Udara',
        'value_field' => 'air_humidity_pct',
        'min_field' => 'air_humidity_min',
        'max_field' => 'air_humidity_max',
        'unit' => '%',
    ],
    [
        'key' => 'light',
        'label' => 'Intensitas Cahaya',
        'value_field' => 'light_lux',
        'min_field' => 'light_lux_min',
        'max_field' => 'light_lux_max',
        'unit' => 'lux',
    ],
    [
        'key' => 'soil_moisture',
        'label' => 'Kelembapan Tanah',
        'value_field' => 'soil_moisture_pct',
        'min_field' => 'soil_moisture_min',
        'max_field' => 'soil_moisture_max',
        'unit' => '%',
    ],
    [
        'key' => 'soil_temperature',
        'label' => 'Suhu Tanah',
        'value_field' => 'soil_temperature_c',
        'min_field' => 'soil_temperature_min',
        'max_field' => 'soil_temperature_max',
        'unit' => "\u{00B0}C",
    ],
];

$iot_sensors = [];
foreach ($sensor_definitions as $definition) {
    $value = iot_nullable_float($iot_latest_reading[$definition['value_field']] ?? null);
    $minimum = iot_nullable_float($iot_device_row[$definition['min_field']] ?? null);
    $maximum = iot_nullable_float($iot_device_row[$definition['max_field']] ?? null);
    $state = iot_threshold_state($value, $minimum, $maximum);

    $iot_sensors[] = [
        'key' => $definition['key'],
        'label' => $definition['label'],
        'value' => $value,
        'unit' => $definition['unit'],
        'state' => $state,
        'note' => iot_sensor_note($state, $value !== null),
    ];
}

$wifi_value = $iot_latest_reading['wifi_rssi'] ?? null;
$wifi_state = $wifi_value === null ? 'critical' : ((int) $wifi_value >= -70 ? 'safe' : 'warning');
$iot_sensors[] = [
    'key' => 'wifi',
    'label' => 'Sinyal Perangkat',
    'value' => $wifi_value === null ? null : (int) $wifi_value,
    'unit' => 'dBm',
    'state' => $wifi_state,
    'note' => $wifi_value === null ? 'Belum ada data' : ($wifi_state === 'safe' ? 'Koneksi stabil' : 'Sinyal lemah'),
];
