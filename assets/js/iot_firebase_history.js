(function () {
    'use strict';

    var config = window.FRESHFARM_IOT_FIREBASE || {};
    var databaseURL = (config.databaseURL || '').replace(/\/$/, '');
    var deviceUid = config.deviceUid || 'farm-esp32-01';
    var historyLimit = Number(config.historyLimit || 500);
    var historyRefreshMs = Number(config.historyRefreshMs || 300000);

    var body = document.querySelector('[data-history-body]');
    var countOutput = document.querySelector('[data-history-count]');
    var periodInput = document.querySelector('[data-history-period]');
    var clearButton = document.querySelector('[data-clear-history]');
    var statusBox = document.querySelector('[data-history-status]');

    function readingsBaseUrl() {
        return databaseURL + '/iot/devices/' + encodeURIComponent(deviceUid) + '/readings.json';
    }

    function readingsUrl() {
        var params = new URLSearchParams();
        var period = periodInput ? periodInput.value : '86400';

        params.set('orderBy', '"$key"');
        params.set('limitToLast', String(historyLimit));

        if (period !== 'all') {
            var startAt = Math.floor(Date.now() / 1000) - Number(period || 86400);
            params.set('startAt', '"' + startAt + '"');
        }

        return readingsBaseUrl() + '?' + params.toString();
    }

    function setStatus(message, type) {
        if (!statusBox) {
            return;
        }

        statusBox.textContent = message;
        statusBox.hidden = false;
        statusBox.classList.toggle('iot-message--danger', type === 'danger');
        statusBox.classList.toggle('iot-message--success', type === 'success');
    }

    function hideStatus() {
        if (statusBox) {
            statusBox.hidden = true;
        }
    }

    function readTimestamp(reading, key) {
        if (reading && reading.recorded_at_unix && !String(reading.recorded_at_unix).startsWith('millis-')) {
            return Number(reading.recorded_at_unix) * 1000;
        }

        if (reading && reading.recorded_at) {
            var parsed = Date.parse(String(reading.recorded_at).replace(' ', 'T') + '+07:00');
            if (!Number.isNaN(parsed)) {
                return parsed;
            }
        }

        if (/^\d+$/.test(String(key))) {
            return Number(key) * 1000;
        }

        return 0;
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
                    timestamp: readTimestamp(reading, key),
                    data: reading
                };
            })
            .sort(function (left, right) {
                return right.timestamp - left.timestamp;
            });
    }

    function formatDate(timestampMs) {
        if (!timestampMs) {
            return '-';
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(new Date(timestampMs));
    }

    function formatValue(value, decimals, unit) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '-';
        }

        var formatted = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(Number(value));

        return unit ? formatted + ' ' + unit : formatted;
    }

    function appendCell(row, text) {
        var cell = document.createElement('td');
        cell.textContent = text;
        row.appendChild(cell);
    }

    function renderEmpty(message) {
        if (!body) {
            return;
        }

        body.innerHTML = '';
        var row = document.createElement('tr');
        var cell = document.createElement('td');
        cell.colSpan = 7;
        cell.className = 'iot-empty-row';
        cell.textContent = message;
        row.appendChild(cell);
        body.appendChild(row);
    }

    function renderRows(readings) {
        var normalized = normalizeReadings(readings);

        if (countOutput) {
            countOutput.textContent = normalized.length + ' pembacaan ditampilkan';
        }

        if (!normalized.length) {
            renderEmpty('Belum ada riwayat pembacaan pada periode ini.');
            return;
        }

        body.innerHTML = '';
        var fragment = document.createDocumentFragment();

        normalized.forEach(function (reading) {
            var data = reading.data;
            var row = document.createElement('tr');

            appendCell(row, formatDate(reading.timestamp));
            appendCell(row, formatValue(data.air_temperature_c, 1, '\u00b0C'));
            appendCell(row, formatValue(data.air_humidity_pct, 1, '%'));
            appendCell(row, formatValue(data.light_lux, 0, 'lux'));
            appendCell(row, formatValue(data.soil_moisture_pct, 1, '%'));
            appendCell(row, formatValue(data.soil_temperature_c, 1, '\u00b0C'));
            appendCell(row, formatValue(data.wifi_rssi, 0, 'dBm'));

            fragment.appendChild(row);
        });

        body.appendChild(fragment);
    }

    function fetchHistory() {
        if (!databaseURL) {
            renderEmpty('Konfigurasi data perangkat belum tersedia.');
            return;
        }

        if (countOutput) {
            countOutput.textContent = 'Memuat riwayat...';
        }

        fetch(readingsUrl(), { cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Firebase HTTP ' + response.status);
                }

                return response.json();
            })
            .then(function (readings) {
                hideStatus();
                renderRows(readings);
            })
            .catch(function () {
                renderEmpty('Riwayat pembacaan belum bisa dimuat.');
                setStatus('Gagal membaca riwayat. Periksa koneksi internet dan perangkat.', 'danger');
            });
    }

    function clearHistory() {
        if (!databaseURL) {
            setStatus('Konfigurasi data perangkat belum tersedia.', 'danger');
            return;
        }

        var confirmed = window.confirm(
            'Bersihkan semua riwayat pembacaan untuk perangkat ini? Data terbaru di ringkasan tetap aman.'
        );

        if (!confirmed) {
            return;
        }

        if (clearButton) {
            clearButton.disabled = true;
            clearButton.textContent = 'Membersihkan...';
        }

        fetch(readingsBaseUrl(), {
            method: 'DELETE',
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Firebase HTTP ' + response.status);
                }

                renderRows(null);
                setStatus('Riwayat pembacaan berhasil dibersihkan. Data terbaru di ringkasan tetap aman.', 'success');
            })
            .catch(function () {
                setStatus('Gagal membersihkan riwayat. Periksa koneksi dan izin database.', 'danger');
            })
            .finally(function () {
                if (clearButton) {
                    clearButton.disabled = false;
                    clearButton.textContent = 'Bersihkan Riwayat';
                }
            });
    }

    if (document.body && document.body.getAttribute('data-iot-page') === 'history') {
        fetchHistory();
        window.setInterval(fetchHistory, historyRefreshMs);

        if (periodInput) {
            periodInput.addEventListener('change', fetchHistory);
        }

        if (clearButton) {
            clearButton.addEventListener('click', clearHistory);
        }
    }
})();
