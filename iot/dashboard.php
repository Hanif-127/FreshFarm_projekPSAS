<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/icons.php';

$iot_firebase_js_version = filemtime(__DIR__ . '/../assets/js/iot_firebase_dashboard.js');
$soil_sensor = array_values(array_filter($iot_sensors, fn(array $sensor): bool => $sensor['key'] === 'soil_moisture'))[0] ?? null;
$iot_sensor_by_key = array_column($iot_sensors, null, 'key');
$iot_sensor_icons = [
    'air_temperature' => 'temperature',
    'air_humidity' => 'humidity',
    'soil_moisture' => 'humidity',
    'soil_temperature' => 'temperature',
    'light' => 'light',
    'wifi' => 'wifi',
];
$iot_sensor_groups = [
    [
        'key' => 'air',
        'icon' => 'air',
        'eyebrow' => 'Mikroklimat',
        'title' => 'Kondisi Udara',
        'description' => 'Suhu dan kelembapan di sekitar tanaman.',
        'sensors' => ['air_temperature', 'air_humidity'],
    ],
    [
        'key' => 'soil',
        'icon' => 'soil',
        'eyebrow' => 'Media Tanam',
        'title' => 'Kondisi Tanah',
        'description' => 'Kelembapan dan suhu pada area akar.',
        'sensors' => ['soil_moisture', 'soil_temperature'],
    ],
    [
        'key' => 'system',
        'icon' => 'environment',
        'eyebrow' => 'Lingkungan & Sistem',
        'title' => 'Cahaya dan Perangkat',
        'description' => 'Intensitas cahaya serta kualitas koneksi perangkat.',
        'sensors' => ['light', 'wifi'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring IoT - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/dashboard_base.css">
    <link rel="stylesheet" href="../assets/css/iot_monitoring.css?v=<?= (int) $iot_css_version ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="module-page iot-page" data-iot-page="dashboard">
<?php include '../includes/header.php'; ?>

<main class="iot-main">
    <div class="iot-container">
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <section class="iot-hero">
            <div class="iot-hero-copy">
                <span class="iot-hero-icon" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
                <div>
                    <span class="iot-eyebrow">Monitoring Lingkungan Kebun</span>
                    <h1>Dashboard Monitoring IoT</h1>
                    <p>Ringkasan kondisi lahan berdasarkan pembacaan sensor yang diperbarui otomatis.</p>
                </div>
            </div>
            <div class="iot-device-status">
                <span class="iot-device-status__icon" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                <div>
                    <strong><?= htmlspecialchars($iot_device['name']) ?></strong>
                    <span class="iot-device-status__meta">
                        <span class="iot-status-dot <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>" data-iot-status-dot aria-hidden="true"></span>
                        <span data-iot-device-status-text><?= ucfirst(htmlspecialchars($iot_device['status'])) ?>, data terakhir <?= htmlspecialchars($iot_device['last_seen']) ?></span>
                    </span>
                </div>
            </div>
        </section>

        <section class="iot-demo-banner">
            <span class="iot-banner-icon" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
            <strong>Realtime Aktif</strong>
            <span data-iot-firebase-summary>Menunggu data realtime dari perangkat <?= htmlspecialchars($iot_device['uid']) ?>.</span>
        </section>

        <section class="iot-summary-strip" aria-label="Ringkasan alur data IoT">
            <div>
                <span class="iot-summary-icon is-live" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
                <span>Pembaruan Sensor</span>
                <strong>15 detik</strong>
                <small>data terbaru dari perangkat</small>
            </div>
            <div>
                <span class="iot-summary-icon is-chart" aria-hidden="true"><?= iot_icon_svg('chart') ?></span>
                <span>Tren Grafik</span>
                <strong>5 menit</strong>
                <small>ringkasan kondisi berkala</small>
            </div>
            <div>
                <span class="iot-summary-icon is-history" aria-hidden="true"><?= iot_icon_svg('history') ?></span>
                <span>Riwayat</span>
                <strong>Otomatis</strong>
                <small>tersimpan saat perangkat aktif</small>
            </div>
            <div>
                <span class="iot-summary-icon is-device" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                <span>Perangkat</span>
                <strong><?= htmlspecialchars($iot_device['uid']) ?></strong>
                <small>terhubung ke lahan utama</small>
            </div>
        </section>

        <div class="iot-section-intro">
            <div>
                <span class="iot-eyebrow">Kondisi Saat Ini</span>
                <h2>Pembacaan Sensor Terbaru</h2>
            </div>
            <span><?= count($iot_sensors) ?> sensor dipantau</span>
        </div>

        <section class="iot-live-groups" aria-label="Nilai sensor terkini">
            <?php foreach ($iot_sensor_groups as $group): ?>
                <article class="iot-live-group iot-live-group--<?= htmlspecialchars($group['key']) ?>">
                    <header class="iot-live-group__header">
                        <span class="iot-live-group__icon" aria-hidden="true"><?= iot_icon_svg($group['icon']) ?></span>
                        <div>
                            <span class="iot-eyebrow"><?= htmlspecialchars($group['eyebrow']) ?></span>
                            <h3><?= htmlspecialchars($group['title']) ?></h3>
                            <p><?= htmlspecialchars($group['description']) ?></p>
                        </div>
                        <span class="iot-live-group__count"><?= count($group['sensors']) ?> indikator</span>
                    </header>
                    <div class="iot-live-group__cards">
                        <?php foreach ($group['sensors'] as $sensor_key): ?>
                            <?php $sensor = $iot_sensor_by_key[$sensor_key] ?? null; ?>
                            <?php if (!$sensor) continue; ?>
                            <section class="iot-card iot-sensor-card state-<?= htmlspecialchars($sensor['state']) ?>" data-sensor="<?= htmlspecialchars($sensor['key']) ?>">
                                <div class="iot-card-head">
                                    <span class="iot-sensor-label">
                                        <span class="iot-sensor-icon" aria-hidden="true"><?= iot_icon_svg($iot_sensor_icons[$sensor['key']] ?? 'activity') ?></span>
                                        <span><?= htmlspecialchars($sensor['label']) ?></span>
                                    </span>
                                    <span class="iot-state" data-sensor-state><?= htmlspecialchars(iot_state_label($sensor['state'])) ?></span>
                                </div>
                                <div class="iot-reading">
                                    <strong data-sensor-value><?= iot_format_value($sensor['value']) ?></strong>
                                    <span><?= htmlspecialchars($sensor['unit']) ?></span>
                                </div>
                                <p data-sensor-note><?= htmlspecialchars($sensor['note']) ?></p>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="iot-chart-grid" aria-label="Grafik tren sensor">
            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-temperature" aria-hidden="true"><?= iot_icon_svg('temperature') ?></span>
                        <div>
                            <span class="iot-eyebrow">Suhu Udara</span>
                            <h2>Tren 60 Menit Terakhir</h2>
                        </div>
                    </div>
                    <span class="iot-chart-unit">&deg;C</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotTemperatureChart"
                        data-labels="[]"
                        data-temperature="[]"
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-soil" aria-hidden="true"><?= iot_icon_svg('soil') ?></span>
                        <div>
                            <span class="iot-eyebrow">Suhu Tanah</span>
                            <h2>Tren 60 Menit Terakhir</h2>
                        </div>
                    </div>
                    <span class="iot-chart-unit">&deg;C</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotSoilTemperatureChart"
                        data-labels="[]"
                        data-soil-temperature="[]"
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-humidity" aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>
                        <div>
                            <span class="iot-eyebrow">Kelembapan Udara</span>
                            <h2>Tren 60 Menit Terakhir</h2>
                        </div>
                    </div>
                    <span class="iot-chart-unit">%</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotAirHumidityChart"
                        data-labels="[]"
                        data-air-humidity="[]"
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-soil-moisture" aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>
                        <div>
                            <span class="iot-eyebrow">Kelembapan Tanah</span>
                            <h2>Tren 60 Menit Terakhir</h2>
                        </div>
                    </div>
                    <span class="iot-chart-unit">%</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotMoistureChart"
                        data-labels="[]"
                        data-moisture="[]"
                    ></canvas>
                </div>
            </article>
        </section>

        <article class="iot-card iot-alert-card">
            <div class="iot-section-head">
                <div class="iot-section-title">
                    <span class="iot-section-title__icon is-alert" aria-hidden="true"><?= iot_icon_svg('alert') ?></span>
                    <div>
                        <span class="iot-eyebrow">Perlu Diperhatikan</span>
                        <h2>Peringatan Sensor</h2>
                    </div>
                </div>
                <span class="iot-refresh-label">Berdasarkan data terbaru</span>
            </div>
            <div class="iot-alert-grid">
                <div class="iot-alert state-<?= htmlspecialchars($soil_sensor['state'] ?? 'critical') ?>">
                    <strong data-iot-soil-alert-title><?= ($soil_sensor['state'] ?? 'critical') === 'safe' ? 'Kelembapan tanah dalam batas aman' : 'Kelembapan tanah perlu diperiksa' ?></strong>
                    <p data-iot-soil-alert-text>Nilai saat ini <?= iot_format_value($soil_sensor['value'] ?? null) ?>%. <?= htmlspecialchars($soil_sensor['note'] ?? 'Belum ada data pembacaan.') ?></p>
                </div>
                <div class="iot-alert state-<?= $iot_device['status'] === 'online' ? 'safe' : 'warning' ?>">
                    <strong data-iot-device-alert-title>Perangkat <?= $iot_device['status'] === 'online' ? 'terhubung stabil' : 'sedang offline' ?></strong>
                    <p data-iot-device-alert-text>Data terakhir diterima <?= htmlspecialchars($iot_device['last_seen']) ?>.</p>
                </div>
            </div>
            <a class="iot-text-link" href="pengaturan.php">Atur batas sensor</a>
        </article>

        <section class="iot-card iot-device-summary">
            <div class="iot-device-summary__title">
                <span class="iot-section-title__icon is-device" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                <div>
                    <span class="iot-eyebrow">Perangkat Aktif</span>
                    <h2><?= htmlspecialchars($iot_device['name']) ?></h2>
                    <p><?= htmlspecialchars($iot_device['location']) ?></p>
                </div>
            </div>
            <dl>
                <div><dt>Device ID</dt><dd><?= htmlspecialchars($iot_device['uid']) ?></dd></div>
                <div><dt>Firmware</dt><dd><?= htmlspecialchars($iot_device['firmware']) ?></dd></div>
                <div><dt>Latest</dt><dd>15 detik</dd></div>
                <div><dt>History</dt><dd>5 menit</dd></div>
                <div><dt>Wi-Fi</dt><dd data-iot-device-wifi><?= iot_format_value($iot_device['wifi_rssi']) ?> dBm</dd></div>
            </dl>
            <a class="iot-button" href="perangkat.php">
                Kelola Perangkat
                <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('arrow') ?></span>
            </a>
        </section>
    </div>
</main>

<script src="../assets/js/iot_monitoring.js?v=<?= (int) $iot_js_version ?>"></script>
<script>
    window.FRESHFARM_IOT_FIREBASE = {
        databaseURL: <?= json_encode($iot_firebase_database_url, JSON_UNESCAPED_SLASHES) ?>,
        deviceUid: <?= json_encode($iot_device_row ? $iot_device['uid'] : null, JSON_UNESCAPED_SLASHES) ?>,
        maxDataAgeMs: 900000,
        historyLimit: 12,
        chartRefreshMs: 300000
    };
</script>
<script src="../assets/js/iot_firebase_dashboard.js?v=<?= (int) $iot_firebase_js_version ?>"></script>
</body>
</html>
