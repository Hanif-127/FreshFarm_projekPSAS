<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/icons.php';

$iot_status_js_version = filemtime(__DIR__ . '/../assets/js/iot_firebase_status.js');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan IoT - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/dashboard_base.css">
    <link rel="stylesheet" href="../assets/css/iot_monitoring.css?v=<?= (int) $iot_css_version ?>">
</head>
<body class="module-page iot-page" data-iot-page="pengaturan">
<?php include '../includes/header.php'; ?>

<main class="iot-main">
    <div class="iot-container">
        <?php include __DIR__ . '/includes/navigation.php'; ?>

        <section class="iot-hero iot-hero--compact">
            <div class="iot-hero-copy">
                <span class="iot-hero-icon iot-hero-icon--settings" aria-hidden="true"><?= iot_icon_svg('settings') ?></span>
                <div>
                    <span class="iot-eyebrow">Konfigurasi Monitoring</span>
                    <h1>Pengaturan IoT</h1>
                    <p>Atur batas aman sensor dan catat nilai kalibrasi yang dipakai firmware ESP32.</p>
                </div>
            </div>
        </section>

        <div class="iot-message" data-demo-message hidden></div>

        <section class="iot-settings-overview" aria-label="Ringkasan pengaturan IoT">
            <div>
                <span class="iot-summary-icon is-alert" aria-hidden="true"><?= iot_icon_svg('alert') ?></span>
                <span>Ambang Sensor</span>
                <strong>Batas aman</strong>
                <small>menentukan status setiap pembacaan</small>
            </div>
            <div>
                <span class="iot-summary-icon is-calibrate" aria-hidden="true"><?= iot_icon_svg('calibrate') ?></span>
                <span>Kalibrasi Tanah</span>
                <strong>Nilai ADC</strong>
                <small>menyesuaikan kondisi kering dan basah</small>
            </div>
            <div>
                <span class="iot-summary-icon is-device" aria-hidden="true"><?= iot_icon_svg('clock') ?></span>
                <span>Pengiriman Data</span>
                <strong>5 menit</strong>
                <small>interval riwayat perangkat</small>
            </div>
        </section>

        <form class="iot-settings-grid" data-demo-form>
            <section class="iot-card iot-settings-card iot-settings-card--threshold">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-alert" aria-hidden="true"><?= iot_icon_svg('alert') ?></span>
                        <div>
                            <span class="iot-eyebrow">Ambang Kondisi</span>
                            <h2>Batas Aman Sensor</h2>
                        </div>
                    </div>
                </div>
                <div class="iot-form-grid">
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('temperature') ?></span>Suhu udara minimum (&deg;C)</span>
                        <input type="number" name="air_temp_min" value="18" step="0.1">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('temperature') ?></span>Suhu udara maksimum (&deg;C)</span>
                        <input type="number" name="air_temp_max" value="34" step="0.1">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>Kelembapan tanah minimum (%)</span>
                        <input type="number" name="soil_moisture_min" value="55" step="0.1">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>Kelembapan tanah maksimum (%)</span>
                        <input type="number" name="soil_moisture_max" value="85" step="0.1">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('soil') ?></span>Suhu tanah minimum (&deg;C)</span>
                        <input type="number" name="soil_temp_min" value="18" step="0.1">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('soil') ?></span>Suhu tanah maksimum (&deg;C)</span>
                        <input type="number" name="soil_temp_max" value="32" step="0.1">
                    </label>
                </div>
            </section>

            <section class="iot-card iot-settings-card iot-settings-card--calibration">
                <div class="iot-section-head">
                    <div class="iot-section-title">
                        <span class="iot-section-title__icon is-calibrate" aria-hidden="true"><?= iot_icon_svg('calibrate') ?></span>
                        <div>
                            <span class="iot-eyebrow">Kalibrasi Analog</span>
                            <h2>Sensor Kelembapan Tanah</h2>
                        </div>
                    </div>
                </div>
                <div class="iot-help-text">
                    <span class="iot-help-icon" aria-hidden="true"><?= iot_icon_svg('calibrate') ?></span>
                    <p>Catat nilai mentah ketika probe berada pada tanah benar-benar kering dan tanah basah. Nilai ini digunakan ESP32 untuk menghitung persentase.</p>
                </div>
                <div class="iot-form-grid">
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('soil') ?></span>Nilai ADC tanah kering</span>
                        <input type="number" name="soil_dry_adc" value="3200">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('humidity') ?></span>Nilai ADC tanah basah</span>
                        <input type="number" name="soil_wet_adc" value="1350">
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('clock') ?></span>Interval pengiriman</span>
                        <select name="interval">
                            <option value="60">1 menit</option>
                            <option value="300" selected>5 menit</option>
                            <option value="600">10 menit</option>
                            <option value="1800">30 menit</option>
                        </select>
                    </label>
                    <label>
                        <span class="iot-form-label"><span aria-hidden="true"><?= iot_icon_svg('alert') ?></span>Status notifikasi</span>
                        <select name="notification">
                            <option value="on" selected>Aktif</option>
                            <option value="off">Nonaktif</option>
                        </select>
                    </label>
                </div>
                <div class="iot-form-actions">
                    <button class="iot-button" type="submit">
                        <span class="iot-button__icon" aria-hidden="true"><?= iot_icon_svg('save') ?></span>
                        Simpan Catatan Pengaturan
                    </button>
                </div>
            </section>
        </form>
    </div>
</main>

<script src="../assets/js/iot_monitoring.js?v=<?= (int) $iot_js_version ?>"></script>
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
