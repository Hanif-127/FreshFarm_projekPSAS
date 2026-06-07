<?php
require __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sensor - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/dashboard_base.css">
    <link rel="stylesheet" href="../assets/css/iot_monitoring.css?v=<?= (int) $iot_css_version ?>">
</head>
<body class="module-page iot-page" data-iot-page="history">
<?php include '../includes/header.php'; ?>

<main class="iot-main">
    <div class="iot-container">
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <section class="iot-hero iot-hero--compact">
            <div>
                <span class="iot-eyebrow">Data Sensor</span>
                <h1>Riwayat Pembacaan</h1>
                <p>Lihat perubahan kondisi lahan dari waktu ke waktu.</p>
            </div>
            <div class="iot-filter-group">
                <label for="history-period">Periode</label>
                <select id="history-period">
                    <option>24 jam terakhir</option>
                    <option>7 hari terakhir</option>
                    <option>30 hari terakhir</option>
                </select>
            </div>
        </section>

        <section class="iot-card">
            <div class="iot-section-head">
                <div>
                    <span class="iot-eyebrow">Database Sensor</span>
                    <h2>Data Terbaru</h2>
                </div>
                <span class="iot-refresh-label"><?= count($iot_history) ?> pembacaan ditampilkan</span>
            </div>
            <div class="iot-table-wrap">
                <table class="iot-table">
                    <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu Udara</th>
                        <th>Kelembapan Udara</th>
                        <th>Cahaya</th>
                        <th>Kelembapan Tanah</th>
                        <th>Suhu Tanah</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($iot_history as $reading): ?>
                        <tr>
                            <td><?= htmlspecialchars($reading['time']) ?></td>
                            <td><?= iot_format_value($reading['air_temp']) ?> &deg;C</td>
                            <td><?= iot_format_value($reading['air_humidity']) ?>%</td>
                            <td><?= iot_format_value($reading['light']) ?> lux</td>
                            <td><?= iot_format_value($reading['soil_moisture']) ?>%</td>
                            <td><?= iot_format_value($reading['soil_temp']) ?> &deg;C</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>
</html>
