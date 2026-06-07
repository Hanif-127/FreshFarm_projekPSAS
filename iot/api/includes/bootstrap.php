<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

date_default_timezone_set('Asia/Jakarta');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function api_response(int $statusCode, array $payload): never
{
    http_response_code($statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function api_error(int $statusCode, string $message, array $errors = []): never
{
    $payload = [
        'success' => false,
        'message' => $message,
    ];

    if ($errors !== []) {
        $payload['errors'] = $errors;
    }

    api_response($statusCode, $payload);
}

function api_require_method(string $method): void
{
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $allowedMethod = strtoupper($method);

    if ($requestMethod !== $allowedMethod) {
        header('Allow: ' . $allowedMethod);
        api_error(405, 'Metode request tidak diizinkan.');
    }
}

function api_get_json_body(): array
{
    $rawBody = file_get_contents('php://input');

    if ($rawBody === false || trim($rawBody) === '') {
        api_error(400, 'Body request JSON tidak boleh kosong.');
    }

    try {
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_error(400, 'Body request harus berupa JSON yang valid.');
    }

    if (!is_array($data)) {
        api_error(400, 'Body request harus berupa objek JSON.');
    }

    return $data;
}

function api_get_key(): string
{
    return trim((string) ($_SERVER['HTTP_X_API_KEY'] ?? ''));
}

function api_authenticate_device(mysqli $koneksi, string $deviceUid): array
{
    $apiKey = api_get_key();

    if ($apiKey === '') {
        api_error(401, 'Header X-API-Key wajib dikirim.');
    }

    $stmt = mysqli_prepare(
        $koneksi,
        'SELECT id, user_id, device_uid, nama_perangkat, lokasi, api_key_hash,
                firmware_version, sampling_interval_seconds, status, last_seen_at
         FROM iot_devices
         WHERE device_uid = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $deviceUid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $device = mysqli_fetch_assoc($result) ?: null;
    mysqli_stmt_close($stmt);

    if (!$device || !password_verify($apiKey, (string) $device['api_key_hash'])) {
        api_error(401, 'Device UID atau API key tidak valid.');
    }

    if ($device['status'] !== 'active') {
        api_error(403, 'Perangkat sedang dinonaktifkan.');
    }

    return $device;
}

function api_nullable_float(
    array $data,
    string $key,
    ?float $minimum = null,
    ?float $maximum = null
): ?float {
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        return null;
    }

    if (!is_numeric($data[$key])) {
        api_error(422, "Nilai {$key} harus berupa angka.");
    }

    $value = (float) $data[$key];

    if (($minimum !== null && $value < $minimum) || ($maximum !== null && $value > $maximum)) {
        api_error(422, "Nilai {$key} berada di luar rentang yang diizinkan.");
    }

    return $value;
}

function api_nullable_int(
    array $data,
    string $key,
    ?int $minimum = null,
    ?int $maximum = null
): ?int {
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        return null;
    }

    if (filter_var($data[$key], FILTER_VALIDATE_INT) === false) {
        api_error(422, "Nilai {$key} harus berupa bilangan bulat.");
    }

    $value = (int) $data[$key];

    if (($minimum !== null && $value < $minimum) || ($maximum !== null && $value > $maximum)) {
        api_error(422, "Nilai {$key} berada di luar rentang yang diizinkan.");
    }

    return $value;
}

set_exception_handler(static function (Throwable $exception): never {
    error_log('[FreshFarm IoT API] ' . $exception->getMessage());
    api_error(500, 'Terjadi kesalahan pada server API.');
});

require_once __DIR__ . '/../../../includes/koneksi.php';
