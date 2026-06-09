(function () {
    'use strict';

    var config = window.FRESHFARM_IOT_FIREBASE || {};
    var databaseURL = (config.databaseURL || '').replace(/\/$/, '');
    var deviceUid = config.deviceUid || '';
    var maxDataAgeMs = Number(config.maxDataAgeMs || 900000);
    var historyLimit = Number(config.historyLimit || 12);
    var chartRefreshMs = Number(config.chartRefreshMs || 300000);
    var latestData = null;
    var pollingStarted = false;

    var sensorMap = {
        air_temperature: {
            field: 'air_temperature_c',
            decimals: 1,
            min: 18,
            max: 34,
            label: 'Suhu udara'
        },
        air_humidity: {
            field: 'air_humidity_pct',
            decimals: 1,
            min: 40,
            max: 90,
            label: 'Kelembapan udara'
        },
        light: {
            field: 'light_lux',
            decimals: 0,
            min: 1000,
            max: 80000,
            label: 'Intensitas cahaya'
        },
        soil_moisture: {
            field: 'soil_moisture_pct',
            decimals: 1,
            min: 55,
            max: 85,
            label: 'Kelembapan tanah'
        },
        soil_temperature: {
            field: 'soil_temperature_c',
            decimals: 1,
            min: 18,
            max: 32,
            label: 'Suhu tanah'
        },
        wifi: {
            field: 'wifi_rssi',
            decimals: 0,
            min: -70,
            label: 'Sinyal perangkat'
        }
    };

    function latestUrl() {
        return databaseURL + '/iot/devices/' + encodeURIComponent(deviceUid) + '/latest.json';
    }

    function readingsUrl() {
        var query = '?orderBy=' + encodeURIComponent('"$key"') + '&limitToLast=' + encodeURIComponent(String(historyLimit));
        return databaseURL + '/iot/devices/' + encodeURIComponent(deviceUid) + '/readings.json' + query;
    }

    function formatNumber(value, decimals) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '-';
        }

        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(Number(value));
    }

    function stateLabel(state) {
        if (state === 'critical') {
            return 'Belum Ada Data';
        }

        return state === 'warning' ? 'Perhatian' : 'Aman';
    }

    function noteForState(state) {
        if (state === 'critical') {
            return 'Belum ada pembacaan sensor';
        }

        return state === 'warning' ? 'Di luar batas aman' : 'Dalam batas aman';
    }

    function sensorState(sensor, value) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return 'critical';
        }

        if (sensor.field === 'wifi_rssi') {
            return Number(value) >= sensor.min ? 'safe' : 'warning';
        }

        if (
            (sensor.min !== undefined && Number(value) < sensor.min) ||
            (sensor.max !== undefined && Number(value) > sensor.max)
        ) {
            return 'warning';
        }

        return 'safe';
    }

    function setCardState(card, state) {
        card.classList.remove('state-safe', 'state-warning', 'state-critical');
        card.classList.add('state-' + state);
    }

    function setText(selector, text) {
        document.querySelectorAll(selector).forEach(function (element) {
            element.textContent = text;
        });
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

    function timestampFromReading(key, reading) {
        var timestamp = readTimestamp(reading);

        if (timestamp) {
            return timestamp;
        }

        if (/^\d+$/.test(String(key))) {
            return Number(key) * 1000;
        }

        return 0;
    }

    function chartLabel(timestampMs) {
        if (!timestampMs) {
            return '-';
        }

        return new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(new Date(timestampMs));
    }

    function numericOrNull(value) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return null;
        }

        return Number(value);
    }

    function normalizeReadings(readings) {
        if (!readings || typeof readings !== 'object') {
            return [];
        }

        return Object.keys(readings)
            .map(function (key) {
                var reading = readings[key] || {};
                return {
                    key: key,
                    timestamp: timestampFromReading(key, reading),
                    data: reading
                };
            })
            .sort(function (left, right) {
                return left.timestamp - right.timestamp;
            });
    }

    function updateChart(canvasId, labels, values) {
        var canvas = document.getElementById(canvasId);

        if (!canvas || typeof Chart === 'undefined' || typeof Chart.getChart !== 'function') {
            return;
        }

        var chart = Chart.getChart(canvas);
        if (!chart) {
            return;
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.update();
    }

    function updateChartsFromHistory(readings) {
        var normalized = normalizeReadings(readings);
        var labels = normalized.map(function (reading) {
            return chartLabel(reading.timestamp);
        });

        [
            { canvasId: 'iotTemperatureChart', field: 'air_temperature_c' },
            { canvasId: 'iotSoilTemperatureChart', field: 'soil_temperature_c' },
            { canvasId: 'iotAirHumidityChart', field: 'air_humidity_pct' },
            { canvasId: 'iotMoistureChart', field: 'soil_moisture_pct' }
        ].forEach(function (series) {
            var values = normalized.map(function (reading) {
                return numericOrNull(reading.data[series.field]);
            });

            updateChart(series.canvasId, labels, values);
        });
    }

    function fetchHistory() {
        if (!databaseURL || !deviceUid) {
            return;
        }

        fetch(readingsUrl(), { cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Firebase HTTP ' + response.status);
                }

                return response.json();
            })
            .then(updateChartsFromHistory)
            .catch(function () {
                setText('[data-iot-firebase-summary]', 'Pembacaan terbaru tersedia, tetapi tren berkala belum bisa dimuat.');
            });
    }

    function updateSensorCards(data) {
        Object.keys(sensorMap).forEach(function (key) {
            var sensor = sensorMap[key];
            var card = document.querySelector('[data-sensor="' + key + '"]');

            if (!card) {
                return;
            }

            var value = data ? data[sensor.field] : null;
            var state = sensorState(sensor, value);
            var valueOutput = card.querySelector('[data-sensor-value]');
            var stateOutput = card.querySelector('[data-sensor-state]');
            var noteOutput = card.querySelector('[data-sensor-note]');

            setCardState(card, state);

            if (valueOutput) {
                valueOutput.textContent = formatNumber(value, sensor.decimals);
            }

            if (stateOutput) {
                stateOutput.textContent = stateLabel(state);
            }

            if (noteOutput) {
                noteOutput.textContent = noteForState(state);
            }
        });
    }

    function updateStatus(data) {
        var timestamp = readTimestamp(data);
        var isOnline = Boolean(timestamp && Date.now() - timestamp <= maxDataAgeMs);
        var statusText = isOnline ? 'Online' : 'Offline';
        var relative = relativeTime(timestamp);

        document.querySelectorAll('[data-iot-status-dot]').forEach(function (dot) {
            dot.classList.toggle('is-offline', !isOnline);
        });

        setText('[data-iot-device-status-text]', statusText + ', data terakhir ' + relative);
        setText('[data-iot-workspace-status-text]', statusText + ', ' + relative);
        setText('[data-iot-device-alert-title]', isOnline ? 'Perangkat terhubung stabil' : 'Perangkat sedang offline');
        setText('[data-iot-device-alert-text]', 'Data terakhir diterima ' + relative + '.');
        setText('[data-iot-device-wifi]', formatNumber(data && data.wifi_rssi, 0) + ' dBm');

        var summary = timestamp
            ? 'Pembacaan sensor diperbarui otomatis, data terakhir ' + relative + '.'
            : 'Menunggu data terbaru dari perangkat.';
        setText('[data-iot-firebase-summary]', summary);
    }

    function updateSoilAlert(data) {
        var value = data ? data.soil_moisture_pct : null;
        var sensor = sensorMap.soil_moisture;
        var state = sensorState(sensor, value);
        var title = state === 'safe'
            ? 'Kelembapan tanah dalam batas aman'
            : 'Kelembapan tanah perlu diperiksa';
        var text = 'Nilai saat ini ' + formatNumber(value, 1) + '%. ' + noteForState(state);
        var alert = document.querySelector('[data-iot-soil-alert-title]');

        if (alert && alert.parentElement) {
            alert.parentElement.classList.remove('state-safe', 'state-warning', 'state-critical');
            alert.parentElement.classList.add('state-' + state);
        }

        setText('[data-iot-soil-alert-title]', title);
        setText('[data-iot-soil-alert-text]', text);
    }

    function applyLatest(data) {
        latestData = data || null;
        updateSensorCards(latestData);
        updateStatus(latestData);
        updateSoilAlert(latestData);
    }

    function fetchLatest() {
        if (!databaseURL || !deviceUid) {
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
                setText('[data-iot-firebase-summary]', 'Belum bisa membaca data perangkat. Periksa koneksi internet dan perangkat.');
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
        if (!databaseURL || !deviceUid) {
            return;
        }

        if (typeof EventSource === 'undefined') {
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

    if (document.body && document.body.getAttribute('data-iot-page') === 'dashboard') {
        fetchHistory();
        window.setInterval(fetchHistory, chartRefreshMs);
        startRealtimeStream();
    }
})();
