<?php
require __DIR__ . '/includes/bootstrap.php';

$iot_status_js_version = filemtime(__DIR__ . '/../assets/js/iot_firebase_status.js');
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
<body class="module-page iot-page" data-iot-page="perangkat">
<?php include '../includes/header.php'; ?>

<main class="iot-main">
    <div class="iot-container">
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <section class="iot-hero iot-hero--compact">
            <div>
                <span class="iot-eyebrow">Manajemen Perangkat</span>
                <h1>Perangkat IoT</h1>
                <p>Pantau identitas, koneksi, dan pembacaan terakhir perangkat yang terpasang di lahan.</p>
            </div>
            <div class="iot-hero-actions">
                <a class="iot-button iot-button--secondary" href="riwayat.php">Riwayat Data</a>
                <a class="iot-button" href="dashboard.php">Lihat Monitoring</a>
            </div>
        </section>

        <div class="iot-message" data-demo-message hidden></div>

        <section class="iot-device-layout">
            <article class="iot-card iot-device-live-card" data-device-card-state>
                <div class="iot-device-card__head">
                    <div>
                        <span class="iot-online-badge <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>" data-device-live-badge><span></span><?= ucfirst(htmlspecialchars($iot_device['status'])) ?></span>
                        <h2><?= htmlspecialchars($iot_device['name']) ?></h2>
                        <p><?= htmlspecialchars($iot_device['location']) ?></p>
                    </div>
                    <span class="iot-device-symbol" aria-hidden="true">ESP32</span>
                </div>

                <p class="iot-device-live-summary" data-device-live-summary>
                    Menunggu pembacaan terbaru untuk memastikan status perangkat.
                </p>

                <dl class="iot-device-stat-grid">
                    <div>
                        <dt>Device ID</dt>
                        <dd><?= htmlspecialchars($iot_device['uid']) ?></dd>
                    </div>
                    <div>
                        <dt>Data Terakhir</dt>
                        <dd data-device-live-last><?= htmlspecialchars($iot_device['last_seen']) ?></dd>
                    </div>
                    <div>
                        <dt>Wi-Fi</dt>
                        <dd data-device-live-wifi><?= iot_format_value($iot_device['wifi_rssi']) ?> dBm</dd>
                    </div>
                    <div>
                        <dt>Area Monitoring</dt>
                        <dd data-device-live-path>Lahan utama</dd>
                    </div>
                </dl>

                <div class="iot-device-actions">
                    <a class="iot-button iot-button--secondary" href="pengaturan.php">Atur Ambang</a>
                    <a class="iot-text-link" href="dashboard.php">Buka ringkasan sensor</a>
                </div>
            </article>

            <article class="iot-card iot-device-telemetry-card">
                <div class="iot-section-head">
                    <div>
                        <span class="iot-eyebrow">Pembacaan Live</span>
                        <h2>Data Terakhir Perangkat</h2>
                    </div>
                    <span class="iot-refresh-label">Diperbarui otomatis</span>
                </div>
                <div class="iot-device-telemetry-grid">
                    <div>
                        <span>Suhu Tanah</span>
                        <strong data-device-live-soil-temp>-</strong>
                    </div>
                    <div>
                        <span>Kelembapan Tanah</span>
                        <strong data-device-live-soil-moisture>-</strong>
                    </div>
                    <div>
                        <span>Nilai ADC</span>
                        <strong data-device-live-raw>-</strong>
                    </div>
                    <div>
                        <span>Uptime ESP32</span>
                        <strong data-device-live-uptime>-</strong>
                    </div>
                </div>
            </article>
        </section>

        <section class="iot-card iot-device-flow-card">
            <div class="iot-section-head">
                <div>
                    <span class="iot-eyebrow">Status Sistem</span>
                    <h2>Konfigurasi monitoring</h2>
                </div>
                <span class="iot-state">Aktif</span>
            </div>
            <div class="iot-device-flow">
                <div>
                    <strong>Sensor Utama</strong>
                    <span>Suhu tanah, kelembapan tanah, dan sinyal perangkat sudah dipantau.</span>
                </div>
                <div>
                    <strong>Interval Card</strong>
                    <span>Nilai terbaru diperbarui cepat saat perangkat mengirim data.</span>
                </div>
                <div>
                    <strong>Interval Tren</strong>
                    <span>Grafik dan riwayat menyimpan ringkasan berkala agar mudah dibaca.</span>
                </div>
                <div>
                    <strong>Firmware</strong>
                    <span><?= htmlspecialchars($iot_device['firmware']) ?> terpasang pada <?= htmlspecialchars($iot_device['uid']) ?>.</span>
                </div>
            </div>
        </section>

        <section class="iot-card iot-device-note-card">
            <div>
                <span class="iot-eyebrow">Catatan Operasional</span>
                <h2>Perangkat dianggap online saat data baru diterima</h2>
                <p>Jika perangkat tidak mengirim data dalam beberapa waktu, status akan berubah agar operator tahu koneksi atau daya perlu diperiksa.</p>
            </div>
            <a class="iot-text-link" href="riwayat.php">Lihat data yang tersimpan setiap 5 menit</a>
        </section>
    </div>
</main>

<script>
    window.FRESHFARM_IOT_FIREBASE = {
        databaseURL: 'https://freshfarm-iot-default-rtdb.asia-southeast1.firebasedatabase.app',
        deviceUid: <?= json_encode($iot_device['uid'], JSON_UNESCAPED_SLASHES) ?>,
        maxDataAgeMs: 900000
    };
</script>
<script src="../assets/js/iot_firebase_status.js?v=<?= (int) $iot_status_js_version ?>"></script>
</body>
</html>
