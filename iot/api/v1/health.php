<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

api_require_method('GET');

api_response(200, [
    'success' => true,
    'message' => 'FreshFarm IoT API berjalan.',
    'version' => 'v1',
    'database' => mysqli_ping($koneksi) ? 'connected' : 'disconnected',
    'server_time' => date('Y-m-d H:i:s'),
]);
