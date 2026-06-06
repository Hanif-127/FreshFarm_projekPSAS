<?php
require __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perangkat IoT - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/dashboard_base.css">
    <link rel="stylesheet" href="../assets/css/iot_monitoring.css?v=<?= (int) $iot_css_version ?>">
</head>
<body class="module-page iot-page">
<?php include '../includes/header.php'; ?>

<main class="iot-main">
    <div class="iot-container">
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <section class="iot-hero iot-hero--compact">
            <div>
                <span class="iot-eyebrow">Manajemen Perangkat</span>
                <h1>Perangkat IoT</h1>
                <p>Kelola identitas, lokasi, dan kondisi ESP32 yang mengirim data sensor.</p>
            </div>
            <button class="iot-button" type="button" data-demo-action="add-device">Tambah Perangkat</button>
        </section>

        <div class="iot-message" data-demo-message hidden></div>

        <section class="iot-device-grid">
            <article class="iot-card iot-device-card">
                <div class="iot-device-card__head">
                    <div>
                        <span class="iot-online-badge <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>"><span></span><?= ucfirst(htmlspecialchars($iot_device['status'])) ?></span>
                        <h2><?= htmlspecialchars($iot_device['name']) ?></h2>
                        <p><?= htmlspecialchars($iot_device['location']) ?></p>
                    </div>
                    <span class="iot-device-symbol" aria-hidden="true">ESP32</span>
                </div>
                <dl class="iot-device-detail-list">
                    <div><dt>Device ID</dt><dd><?= htmlspecialchars($iot_device['uid']) ?></dd></div>
                    <div><dt>Data Terakhir</dt><dd><?= htmlspecialchars($iot_device['last_seen']) ?></dd></div>
                    <div><dt>Versi Firmware</dt><dd><?= htmlspecialchars($iot_device['firmware']) ?></dd></div>
                    <div><dt>Interval Kirim</dt><dd><?= (int) ($iot_device['interval'] / 60) ?> menit</dd></div>
                </dl>
                <div class="iot-device-actions">
                    <button class="iot-button iot-button--secondary" type="button" data-demo-action="edit-device">Ubah Perangkat</button>
                    <a class="iot-text-link" href="dashboard.php">Lihat Monitoring</a>
                </div>
            </article>

            <article class="iot-card iot-empty-device-card">
                <span>+</span>
                <h2>Tambahkan area monitoring</h2>
                <p>Daftarkan ESP32 berikutnya ketika ingin memantau petak atau greenhouse lain.</p>
                <button class="iot-text-link iot-link-button" type="button" data-demo-action="add-device">Tambah perangkat</button>
            </article>
        </section>
    </div>
</main>

<script src="../assets/js/iot_monitoring.js?v=<?= (int) $iot_js_version ?>"></script>
</body>
</html>
