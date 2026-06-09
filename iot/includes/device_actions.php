<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['iot_csrf_token'])) {
    $_SESSION['iot_csrf_token'] = bin2hex(random_bytes(24));
}

$iot_device_action_message = $_SESSION['iot_device_action_message'] ?? null;
unset($_SESSION['iot_device_action_message']);

function iot_device_action_redirect(string $type, string $message): never
{
    $_SESSION['iot_device_action_message'] = [
        'type' => $type,
        'text' => $message,
    ];

    header('Location: perangkat.php');
    exit;
}

function iot_verify_device_csrf(): void
{
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['iot_csrf_token'] ?? '');

    if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        iot_device_action_redirect('danger', 'Sesi formulir berakhir. Muat ulang halaman lalu coba kembali.');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    return;
}

iot_verify_device_csrf();

$action = (string) ($_POST['device_action'] ?? '');

if ($action === 'select') {
    $deviceId = filter_var($_POST['device_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$deviceId) {
        iot_device_action_redirect('danger', 'Perangkat yang dipilih tidak valid.');
    }

    $stmt = mysqli_prepare(
        $koneksi,
        'SELECT id FROM iot_devices WHERE id = ? AND user_id = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ii', $deviceId, $iot_user_id);
    mysqli_stmt_execute($stmt);
    $device = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);

    if (!$device) {
        iot_device_action_redirect('danger', 'Perangkat tidak ditemukan pada akunmu.');
    }

    $_SESSION['iot_active_device_id'] = (int) $device['id'];
    iot_device_action_redirect('success', 'Perangkat aktif berhasil diganti.');
}

if ($action !== 'pair') {
    iot_device_action_redirect('danger', 'Aksi perangkat tidak dikenali.');
}

$deviceUid = strtolower(trim((string) ($_POST['device_uid'] ?? '')));
$pairingCode = strtoupper(trim((string) ($_POST['pairing_code'] ?? '')));
$deviceName = trim((string) ($_POST['device_name'] ?? ''));
$deviceLocation = trim((string) ($_POST['device_location'] ?? ''));

if (!preg_match('/^[a-z0-9][a-z0-9-]{2,79}$/', $deviceUid)) {
    iot_device_action_redirect('danger', 'Device UID harus berisi 3-80 karakter berupa huruf kecil, angka, atau tanda hubung.');
}

if ($pairingCode === '') {
    iot_device_action_redirect('danger', 'Kode pairing wajib diisi.');
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT id, user_id, device_uid, nama_perangkat, lokasi, pairing_code_hash
     FROM iot_devices
     WHERE device_uid = ?
     LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 's', $deviceUid);
mysqli_stmt_execute($stmt);
$device = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
mysqli_stmt_close($stmt);

if (!$device || empty($device['pairing_code_hash']) || !password_verify($pairingCode, (string) $device['pairing_code_hash'])) {
    iot_device_action_redirect('danger', 'Device UID atau kode pairing tidak cocok.');
}

if ($device['user_id'] !== null && (int) $device['user_id'] !== $iot_user_id) {
    iot_device_action_redirect('danger', 'Perangkat sudah terhubung dengan akun lain.');
}

$resolvedName = $deviceName !== ''
    ? mb_substr($deviceName, 0, 120)
    : (string) ($device['nama_perangkat'] ?: 'ESP32 FreshFarm');
$resolvedLocation = $deviceLocation !== ''
    ? mb_substr($deviceLocation, 0, 180)
    : (string) ($device['lokasi'] ?: 'Lokasi belum diatur');
$deviceId = (int) $device['id'];

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare(
        $koneksi,
        'UPDATE iot_devices
         SET user_id = ?, nama_perangkat = ?, lokasi = ?,
             claimed_at = COALESCE(claimed_at, NOW()), pairing_code_hash = NULL
         WHERE id = ?'
    );
    mysqli_stmt_bind_param($stmt, 'issi', $iot_user_id, $resolvedName, $resolvedLocation, $deviceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $koneksi,
        'INSERT INTO iot_thresholds (device_id)
         VALUES (?)
         ON DUPLICATE KEY UPDATE device_id = VALUES(device_id)'
    );
    mysqli_stmt_bind_param($stmt, 'i', $deviceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);
} catch (Throwable $exception) {
    mysqli_rollback($koneksi);
    error_log('[FreshFarm IoT Pairing] ' . $exception->getMessage());
    iot_device_action_redirect('danger', 'Perangkat belum dapat dihubungkan. Coba kembali beberapa saat lagi.');
}

$_SESSION['iot_active_device_id'] = $deviceId;
iot_device_action_redirect('success', 'Perangkat berhasil dihubungkan dan sekarang menjadi perangkat aktif.');
