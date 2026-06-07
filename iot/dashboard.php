<?php
require __DIR__ . '/includes/bootstrap.php';

$chart_labels = array_column($iot_chart_readings, 'chart_label');
$chart_air_temperature = array_column($iot_chart_readings, 'air_temp');
$chart_soil_temperature = array_column($iot_chart_readings, 'soil_temp');
$chart_air_humidity = array_column($iot_chart_readings, 'air_humidity');
$chart_soil_moisture = array_column($iot_chart_readings, 'soil_moisture');
$soil_sensor = array_values(array_filter($iot_sensors, fn(array $sensor): bool => $sensor['key'] === 'soil_moisture'))[0] ?? null;
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
            <div>
                <span class="iot-eyebrow">Monitoring Lingkungan Kebun</span>
                <h1>Dashboard Monitoring IoT</h1>
                <p>Ringkasan kondisi lahan berdasarkan pembacaan sensor yang tersimpan di database.</p>
            </div>
            <div class="iot-device-status">
                <span class="iot-status-dot <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>" aria-hidden="true"></span>
                <div>
                    <strong><?= htmlspecialchars($iot_device['name']) ?></strong>
                    <span><?= ucfirst(htmlspecialchars($iot_device['status'])) ?>, data terakhir <?= htmlspecialchars($iot_device['last_seen']) ?></span>
                </div>
            </div>
        </section>

        <section class="iot-demo-banner">
            <strong>Database Terhubung</strong>
            <span><?= count($iot_history) ?> pembacaan terbaru berhasil dimuat dari perangkat <?= htmlspecialchars($iot_device['uid']) ?>.</span>
        </section>

        <div class="iot-section-intro">
            <div>
                <span class="iot-eyebrow">Kondisi Saat Ini</span>
                <h2>Pembacaan Sensor Terbaru</h2>
            </div>
            <span><?= count($iot_sensors) ?> sensor dipantau</span>
        </div>

        <section class="iot-sensor-grid" aria-label="Nilai sensor terkini">
            <?php foreach ($iot_sensors as $sensor): ?>
                <article class="iot-card iot-sensor-card state-<?= htmlspecialchars($sensor['state']) ?>" data-sensor="<?= htmlspecialchars($sensor['key']) ?>">
                    <div class="iot-card-head">
                        <span><?= htmlspecialchars($sensor['label']) ?></span>
                        <span class="iot-state"><?= htmlspecialchars(iot_state_label($sensor['state'])) ?></span>
                    </div>
                    <div class="iot-reading">
                        <strong data-sensor-value><?= iot_format_value($sensor['value']) ?></strong>
                        <span><?= htmlspecialchars($sensor['unit']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($sensor['note']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="iot-chart-grid" aria-label="Grafik tren sensor">
            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div>
                        <span class="iot-eyebrow">Suhu Udara</span>
                        <h2>Tren 60 Menit Terakhir</h2>
                    </div>
                    <span class="iot-chart-unit">&deg;C</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotTemperatureChart"
                        data-labels='<?= htmlspecialchars(json_encode($chart_labels), ENT_QUOTES, 'UTF-8') ?>'
                        data-temperature='<?= htmlspecialchars(json_encode($chart_air_temperature), ENT_QUOTES, 'UTF-8') ?>'
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div>
                        <span class="iot-eyebrow">Suhu Tanah</span>
                        <h2>Tren 60 Menit Terakhir</h2>
                    </div>
                    <span class="iot-chart-unit">&deg;C</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotSoilTemperatureChart"
                        data-labels='<?= htmlspecialchars(json_encode($chart_labels), ENT_QUOTES, 'UTF-8') ?>'
                        data-soil-temperature='<?= htmlspecialchars(json_encode($chart_soil_temperature), ENT_QUOTES, 'UTF-8') ?>'
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div>
                        <span class="iot-eyebrow">Kelembapan Udara</span>
                        <h2>Tren 60 Menit Terakhir</h2>
                    </div>
                    <span class="iot-chart-unit">%</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotAirHumidityChart"
                        data-labels='<?= htmlspecialchars(json_encode($chart_labels), ENT_QUOTES, 'UTF-8') ?>'
                        data-air-humidity='<?= htmlspecialchars(json_encode($chart_air_humidity), ENT_QUOTES, 'UTF-8') ?>'
                    ></canvas>
                </div>
            </article>

            <article class="iot-card iot-chart-card">
                <div class="iot-section-head">
                    <div>
                        <span class="iot-eyebrow">Kelembapan Tanah</span>
                        <h2>Tren 60 Menit Terakhir</h2>
                    </div>
                    <span class="iot-chart-unit">%</span>
                </div>
                <div class="iot-chart-wrap">
                    <canvas
                        id="iotMoistureChart"
                        data-labels='<?= htmlspecialchars(json_encode($chart_labels), ENT_QUOTES, 'UTF-8') ?>'
                        data-moisture='<?= htmlspecialchars(json_encode($chart_soil_moisture), ENT_QUOTES, 'UTF-8') ?>'
                    ></canvas>
                </div>
            </article>
        </section>

        <article class="iot-card iot-alert-card">
            <div class="iot-section-head">
                <div>
                    <span class="iot-eyebrow">Perlu Diperhatikan</span>
                    <h2>Peringatan Sensor</h2>
                </div>
                <span class="iot-refresh-label">Berdasarkan data terbaru</span>
            </div>
            <div class="iot-alert-grid">
                <div class="iot-alert state-<?= htmlspecialchars($soil_sensor['state'] ?? 'critical') ?>">
                    <strong><?= ($soil_sensor['state'] ?? 'critical') === 'safe' ? 'Kelembapan tanah dalam batas aman' : 'Kelembapan tanah perlu diperiksa' ?></strong>
                    <p>Nilai saat ini <?= iot_format_value($soil_sensor['value'] ?? null) ?>%. <?= htmlspecialchars($soil_sensor['note'] ?? 'Belum ada data pembacaan.') ?></p>
                </div>
                <div class="iot-alert state-<?= $iot_device['status'] === 'online' ? 'safe' : 'warning' ?>">
                    <strong>Perangkat <?= $iot_device['status'] === 'online' ? 'terhubung stabil' : 'sedang offline' ?></strong>
                    <p>Data terakhir diterima <?= htmlspecialchars($iot_device['last_seen']) ?>.</p>
                </div>
            </div>
            <a class="iot-text-link" href="pengaturan.php">Atur batas sensor</a>
        </article>

        <section class="iot-card iot-device-summary">
            <div>
                <span class="iot-eyebrow">Perangkat Aktif</span>
                <h2><?= htmlspecialchars($iot_device['name']) ?></h2>
                <p><?= htmlspecialchars($iot_device['location']) ?></p>
            </div>
            <dl>
                <div><dt>Device ID</dt><dd><?= htmlspecialchars($iot_device['uid']) ?></dd></div>
                <div><dt>Firmware</dt><dd><?= htmlspecialchars($iot_device['firmware']) ?></dd></div>
                <div><dt>Interval</dt><dd><?= (int) ($iot_device['interval'] / 60) ?> menit</dd></div>
                <div><dt>Wi-Fi</dt><dd><?= iot_format_value($iot_device['wifi_rssi']) ?> dBm</dd></div>
            </dl>
            <a class="iot-button" href="perangkat.php">Kelola Perangkat</a>
        </section>
    </div>
</main>

<script src="../assets/js/iot_monitoring.js?v=<?= (int) $iot_js_version ?>"></script>
</body>
</html>
