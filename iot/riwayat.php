<?php
require __DIR__ . '/includes/bootstrap.php';

$iot_history_js_version = filemtime(__DIR__ . '/../assets/js/iot_firebase_history.js');
$iot_status_js_version = filemtime(__DIR__ . '/../assets/js/iot_firebase_status.js');
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
                <p>Lihat perubahan kondisi lahan dari pembacaan berkala perangkat.</p>
            </div>
            <div class="iot-history-actions">
                <div class="iot-filter-group">
                    <label for="history-period">Periode</label>
                    <select id="history-period" data-history-period>
                        <option value="86400">24 jam terakhir</option>
                        <option value="604800">7 hari terakhir</option>
                        <option value="2592000">30 hari terakhir</option>
                        <option value="all">Semua data</option>
                    </select>
                </div>
                <button class="iot-button iot-button--danger" type="button" data-clear-history>Bersihkan Riwayat</button>
            </div>
        </section>

        <div class="iot-message" data-history-status hidden></div>

        <section class="iot-card">
            <div class="iot-section-head">
                <div>
                    <span class="iot-eyebrow">Data Sensor</span>
                    <h2>Data Terbaru</h2>
                </div>
                <span class="iot-refresh-label" data-history-count>Memuat riwayat...</span>
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
                        <th>Wi-Fi</th>
                    </tr>
                    </thead>
                    <tbody data-history-body>
                        <tr data-history-empty>
                            <td colspan="7" class="iot-empty-row">Memuat data riwayat perangkat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<script>
    window.FRESHFARM_IOT_FIREBASE = {
        databaseURL: 'https://freshfarm-iot-default-rtdb.asia-southeast1.firebasedatabase.app',
        deviceUid: <?= json_encode($iot_device['uid'], JSON_UNESCAPED_SLASHES) ?>,
        historyLimit: 500,
        historyRefreshMs: 300000
    };
</script>
<script src="../assets/js/iot_firebase_history.js?v=<?= (int) $iot_history_js_version ?>"></script>
<script src="../assets/js/iot_firebase_status.js?v=<?= (int) $iot_status_js_version ?>"></script>
</body>
</html>
