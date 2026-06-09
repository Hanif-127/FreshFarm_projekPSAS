<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/device_actions.php';

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
            <div class="iot-hero-copy">
                <span class="iot-hero-icon iot-hero-icon--device" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                <div>
                    <span class="iot-eyebrow">Manajemen Perangkat</span>
                    <h1>Perangkat IoT</h1>
                    <p>Pantau identitas, koneksi, dan pembacaan terakhir perangkat yang terpasang di lahan.</p>
                </div>
            </div>
            <div class="iot-hero-actions">
                <a class="iot-button iot-button--secondary" href="riwayat.php">
                    <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('history') ?></span>
                    Riwayat Data
                </a>
                <a class="iot-button" href="dashboard.php">
                    <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('summary') ?></span>
                    Lihat Monitoring
                </a>
            </div>
        </section>

        <?php if ($iot_device_action_message): ?>
            <div class="iot-message <?= $iot_device_action_message['type'] === 'danger' ? 'iot-message--danger' : 'iot-message--success' ?>">
                <?= htmlspecialchars($iot_device_action_message['text']) ?>
            </div>
        <?php endif; ?>

        <section class="iot-device-management-grid">
            <article class="iot-card iot-connected-devices-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-device" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                        <div>
                            <span class="iot-eyebrow">Milik Akun Ini</span>
                            <h2>Perangkat Terhubung</h2>
                        </div>
                    </div>
                    <span class="iot-refresh-label"><?= count($iot_user_devices) ?> perangkat</span>
                </div>

                <div class="iot-connected-device-list">
                    <?php if ($iot_user_devices === []): ?>
                        <div class="iot-device-list-empty">
                            <span aria-hidden="true"><?= iot_icon_svg('link') ?></span>
                            <p>Belum ada perangkat yang terhubung dengan akun ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($iot_user_devices as $owned_device): ?>
                            <?php $is_active_device = (int) $owned_device['id'] === (int) ($iot_device_row['id'] ?? 0); ?>
                            <form class="iot-connected-device <?= $is_active_device ? 'is-active' : '' ?>" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['iot_csrf_token']) ?>">
                                <input type="hidden" name="device_action" value="select">
                                <input type="hidden" name="device_id" value="<?= (int) $owned_device['id'] ?>">
                                <span class="iot-connected-device__icon" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                                <span class="iot-connected-device__copy">
                                    <strong><?= htmlspecialchars($owned_device['nama_perangkat']) ?></strong>
                                    <small><?= htmlspecialchars($owned_device['device_uid']) ?> &middot; <?= htmlspecialchars($owned_device['lokasi'] ?: 'Lokasi belum diatur') ?></small>
                                </span>
                                <?php if ($is_active_device): ?>
                                    <span class="iot-connected-device__active"><span aria-hidden="true"><?= iot_icon_svg('check') ?></span>Aktif</span>
                                <?php else: ?>
                                    <button class="iot-button iot-button--secondary" type="submit">Pilih</button>
                                <?php endif; ?>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="iot-card iot-pairing-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-live" aria-hidden="true"><?= iot_icon_svg('link') ?></span>
                        <div>
                            <span class="iot-eyebrow">Pairing Perangkat</span>
                            <h2>Hubungkan ESP32</h2>
                        </div>
                    </div>
                </div>

                <div class="iot-pairing-note">
                    <span aria-hidden="true"><?= iot_icon_svg('database') ?></span>
                    <p>Firebase dikelola otomatis oleh FreshFarm. Masukkan identitas perangkat dan kode pairing yang diberikan bersama ESP32.</p>
                </div>

                <form class="iot-pairing-form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['iot_csrf_token']) ?>">
                    <input type="hidden" name="device_action" value="pair">
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('device') ?></span>Device UID</span>
                        <input type="text" name="device_uid" placeholder="contoh: farm-esp32-02" required maxlength="80" pattern="[a-z0-9][a-z0-9-]{2,79}">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('key') ?></span>Kode Pairing</span>
                        <input type="password" name="pairing_code" placeholder="Masukkan kode pairing" required autocomplete="one-time-code">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('device') ?></span>Nama Perangkat</span>
                        <input type="text" name="device_name" placeholder="ESP32 Lahan Utama" maxlength="120">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('environment') ?></span>Lokasi</span>
                        <input type="text" name="device_location" placeholder="Bedeng Cabai - Blok Timur" maxlength="180">
                    </label>
                    <button class="iot-button" type="submit">
                        <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('link') ?></span>
                        Hubungkan Perangkat
                    </button>
                </form>
            </article>
        </section>

        <section class="iot-device-layout">
            <article class="iot-card iot-device-live-card" data-device-card-state>
                <div class="iot-device-card__head">
                    <div class="iot-device-identity">
                        <span class="iot-device-identity__icon" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                        <div>
                            <span class="iot-online-badge <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>" data-device-live-badge><span></span><?= ucfirst(htmlspecialchars($iot_device['status'])) ?></span>
                            <h2><?= htmlspecialchars($iot_device['name']) ?></h2>
                            <p><?= htmlspecialchars($iot_device['location']) ?></p>
                        </div>
                    </div>
                    <span class="iot-device-symbol" aria-hidden="true">ESP32</span>
                </div>

                <p class="iot-device-live-summary" data-device-live-summary>
                    Menunggu pembacaan terbaru untuk memastikan status perangkat.
                </p>

                <dl class="iot-device-stat-grid">
                    <div>
                        <dt><span class="iot-stat-icon" aria-hidden="true"><?= iot_icon_svg('device') ?></span>Device ID</dt>
                        <dd><?= htmlspecialchars($iot_device['uid']) ?></dd>
                    </div>
                    <div>
                        <dt><span class="iot-stat-icon" aria-hidden="true"><?= iot_icon_svg('clock') ?></span>Data Terakhir</dt>
                        <dd data-device-live-last><?= htmlspecialchars($iot_device['last_seen']) ?></dd>
                    </div>
                    <div>
                        <dt><span class="iot-stat-icon" aria-hidden="true"><?= iot_icon_svg('wifi') ?></span>Wi-Fi</dt>
                        <dd data-device-live-wifi><?= iot_format_value($iot_device['wifi_rssi']) ?> dBm</dd>
                    </div>
                    <div>
                        <dt><span class="iot-stat-icon" aria-hidden="true"><?= iot_icon_svg('environment') ?></span>Area Monitoring</dt>
                        <dd data-device-live-path>Lahan utama</dd>
                    </div>
                </dl>

                <div class="iot-device-actions">
                    <a class="iot-button iot-button--secondary" href="pengaturan.php">
                        <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('settings') ?></span>
                        Atur Ambang
                    </a>
                    <a class="iot-text-link" href="dashboard.php">Buka ringkasan sensor <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>

            <article class="iot-card iot-device-telemetry-card">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-live" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
                        <div>
                            <span class="iot-eyebrow">Pembacaan Live</span>
                            <h2>Data Terakhir Perangkat</h2>
                        </div>
                    </div>
                    <span class="iot-refresh-label">Diperbarui otomatis</span>
                </div>
                <div class="iot-device-telemetry-grid">
                    <div>
                        <span class="iot-metric-icon is-temperature" aria-hidden="true"><?= iot_icon_svg('temperature') ?></span>
                        <span>Suhu Tanah</span>
                        <strong data-device-live-soil-temp>-</strong>
                    </div>
                    <div>
                        <span class="iot-metric-icon is-moisture" aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>
                        <span>Kelembapan Tanah</span>
                        <strong data-device-live-soil-moisture>-</strong>
                    </div>
                    <div>
                        <span class="iot-metric-icon is-adc" aria-hidden="true"><?= iot_icon_svg('chart') ?></span>
                        <span>Nilai ADC</span>
                        <strong data-device-live-raw>-</strong>
                    </div>
                    <div>
                        <span class="iot-metric-icon is-uptime" aria-hidden="true"><?= iot_icon_svg('clock') ?></span>
                        <span>Uptime ESP32</span>
                        <strong data-device-live-uptime>-</strong>
                    </div>
                </div>
            </article>
        </section>

        <section class="iot-card iot-device-flow-card">
            <div class="iot-section-head">
                <div class="iot-section-title">
                    <span class="iot-section-title__icon is-settings" aria-hidden="true"><?= iot_icon_svg('settings') ?></span>
                    <div>
                        <span class="iot-eyebrow">Status Sistem</span>
                        <h2>Konfigurasi Monitoring</h2>
                    </div>
                </div>
                <span class="iot-state">Aktif</span>
            </div>
            <div class="iot-device-flow">
                <div>
                    <span class="iot-flow-icon is-sensor" aria-hidden="true"><?= iot_icon_svg('soil') ?></span>
                    <strong>Sensor Utama</strong>
                    <span>Suhu tanah, kelembapan tanah, dan sinyal perangkat sudah dipantau.</span>
                </div>
                <div>
                    <span class="iot-flow-icon is-live" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
                    <strong>Interval Card</strong>
                    <span>Nilai terbaru diperbarui cepat saat perangkat mengirim data.</span>
                </div>
                <div>
                    <span class="iot-flow-icon is-history" aria-hidden="true"><?= iot_icon_svg('history') ?></span>
                    <strong>Interval Tren</strong>
                    <span>Grafik dan riwayat menyimpan ringkasan berkala agar mudah dibaca.</span>
                </div>
                <div>
                    <span class="iot-flow-icon is-device" aria-hidden="true"><?= iot_icon_svg('device') ?></span>
                    <strong>Firmware</strong>
                    <span><?= htmlspecialchars($iot_device['firmware']) ?> terpasang pada <?= htmlspecialchars($iot_device['uid']) ?>.</span>
                </div>
            </div>
        </section>

        <section class="iot-card iot-device-note-card">
            <div class="iot-section-title">
                <span class="iot-section-title__icon is-alert" aria-hidden="true"><?= iot_icon_svg('alert') ?></span>
                <div>
                    <span class="iot-eyebrow">Catatan Operasional</span>
                    <h2>Perangkat dianggap online saat data baru diterima</h2>
                    <p>Jika perangkat tidak mengirim data dalam beberapa waktu, status akan berubah agar operator tahu koneksi atau daya perlu diperiksa.</p>
                </div>
            </div>
            <a class="iot-text-link" href="riwayat.php">Lihat data yang tersimpan setiap 5 menit</a>
        </section>
    </div>
</main>

<script>
    window.FRESHFARM_IOT_FIREBASE = {
        databaseURL: <?= json_encode($iot_firebase_database_url, JSON_UNESCAPED_SLASHES) ?>,
        deviceUid: <?= json_encode($iot_device_row ? $iot_device['uid'] : null, JSON_UNESCAPED_SLASHES) ?>,
        maxDataAgeMs: 900000
    };
</script>
<script src="../assets/js/iot_firebase_status.js?v=<?= (int) $iot_status_js_version ?>"></script>
</body>
</html>
