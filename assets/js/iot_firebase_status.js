(function () {
    'use strict';

    var config = window.FRESHFARM_IOT_FIREBASE || {};
    var databaseURL = (config.databaseURL || '').replace(/\/$/, '');
    var deviceUid = config.deviceUid || 'farm-esp32-01';
    var maxDataAgeMs = Number(config.maxDataAgeMs || 900000);
    var latestData = null;
    var pollingStarted = false;

    function latestUrl() {
        return databaseURL + '/iot/devices/' + encodeURIComponent(deviceUid) + '/latest.json';
    }

    function setText(selector, text) {
        document.querySelectorAll(selector).forEach(function (element) {
            element.textContent = text;
        });
    }

    function formatNumber(value, decimals, unit) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '-';
        }

        var formatted = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(Number(value));

        return unit ? formatted + ' ' + unit : formatted;
    }

    function readTimestamp(data) {
        if (!data) {
            return null;
        }

        if (data.recorded_at_unix && !String(data.recorded_at_unix).startsWith('millis-')) {
            return Number(data.recorded_at_unix) * 1000;
        }

        if (data.recorded_at) {
            var parsed = Date.parse(String(data.recorded_at).replace(' ', 'T') + '+07:00');
            return Number.isNaN(parsed) ? null : parsed;
        }

        return null;
    }

    function relativeTime(timestampMs) {
        if (!timestampMs) {
            return 'waktu belum tersedia';
        }

        var seconds = Math.max(0, Math.floor((Date.now() - timestampMs) / 1000));
        if (seconds < 60) {
            return 'baru saja';
        }

        var minutes = Math.floor(seconds / 60);
        if (minutes < 60) {
            return minutes + ' menit lalu';
        }

        var hours = Math.floor(minutes / 60);
        if (hours < 24) {
            return hours + ' jam lalu';
        }

        return Math.floor(hours / 24) + ' hari lalu';
    }

    function formatUptime(value) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '-';
        }

        var seconds = Math.floor(Number(value) / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);

        if (hours > 0) {
            return hours + ' jam ' + (minutes % 60) + ' menit';
        }

        if (minutes > 0) {
            return minutes + ' menit';
        }

        return seconds + ' detik';
    }

    function updateDots(isOnline) {
        document.querySelectorAll('[data-iot-status-dot]').forEach(function (dot) {
            dot.classList.toggle('is-offline', !isOnline);
        });
    }

    function updateBadge(isOnline) {
        document.querySelectorAll('[data-device-live-badge]').forEach(function (badge) {
            badge.classList.toggle('is-offline', !isOnline);
            badge.lastChild.textContent = isOnline ? 'Online' : 'Offline';
        });
    }

    function updateDeviceCard(isOnline) {
        document.querySelectorAll('[data-device-card-state]').forEach(function (card) {
            card.classList.toggle('is-online', isOnline);
            card.classList.toggle('is-offline', !isOnline);
        });
    }

    function applyLatest(data) {
        latestData = data || null;

        var timestamp = readTimestamp(latestData);
        var isOnline = Boolean(timestamp && Date.now() - timestamp <= maxDataAgeMs);
        var relative = relativeTime(timestamp);
        var status = isOnline ? 'Online' : 'Offline';

        updateDots(isOnline);
        updateBadge(isOnline);
        updateDeviceCard(isOnline);

        setText('[data-iot-workspace-status-text]', status + ', ' + relative);
        setText('[data-iot-device-status-text]', status + ', data terakhir ' + relative);
        setText('[data-device-live-last]', relative);
        setText('[data-device-live-wifi]', formatNumber(latestData && latestData.wifi_rssi, 0, 'dBm'));
        setText('[data-device-live-soil-temp]', formatNumber(latestData && latestData.soil_temperature_c, 1, '\u00b0C'));
        setText('[data-device-live-soil-moisture]', formatNumber(latestData && latestData.soil_moisture_pct, 1, '%'));
        setText('[data-device-live-raw]', formatNumber(latestData && latestData.soil_moisture_raw, 0, 'ADC'));
        setText('[data-device-live-uptime]', formatUptime(latestData && latestData.uptime_ms));

        var summary = isOnline
            ? 'Perangkat aktif. Data terakhir diterima ' + relative + '.'
            : 'Perangkat belum mengirim data baru. Data terakhir diterima ' + relative + '.';
        setText('[data-device-live-summary]', summary);
    }

    function fetchLatest() {
        if (!databaseURL) {
            return;
        }

        fetch(latestUrl(), { cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Firebase HTTP ' + response.status);
                }

                return response.json();
            })
            .then(applyLatest)
            .catch(function () {
                setText('[data-device-live-summary]', 'Belum bisa membaca status perangkat. Periksa koneksi internet dan perangkat.');
            });
    }

    function startPolling() {
        if (pollingStarted) {
            return;
        }

        pollingStarted = true;
        fetchLatest();
        window.setInterval(fetchLatest, 15000);
    }

    function mergeFirebaseEvent(payload) {
        if (!payload || payload.data === undefined) {
            return;
        }

        if (payload.path === '/') {
            applyLatest(payload.data);
            return;
        }

        latestData = latestData || {};
        var key = String(payload.path || '').replace(/^\//, '');
        if (key) {
            latestData[key] = payload.data;
            applyLatest(latestData);
        }
    }

    function startRealtimeStream() {
        if (!databaseURL || typeof EventSource === 'undefined') {
            startPolling();
            return;
        }

        fetchLatest();

        var stream = new EventSource(latestUrl());
        stream.addEventListener('put', function (event) {
            mergeFirebaseEvent(JSON.parse(event.data));
        });
        stream.addEventListener('patch', function (event) {
            mergeFirebaseEvent(JSON.parse(event.data));
        });
        stream.onerror = function () {
            startPolling();
        };
    }

    if (document.body && document.body.classList.contains('iot-page')) {
        startRealtimeStream();
    }
})();
