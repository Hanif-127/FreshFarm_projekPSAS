(function () {
    'use strict';

    function createSingleSensorChart(config) {
        var canvas = document.getElementById(config.canvasId);

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        var labels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
        var values = JSON.parse(canvas.getAttribute(config.dataAttribute) || '[]');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: config.label,
                        data: values,
                        borderColor: config.borderColor,
                        backgroundColor: config.backgroundColor,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return config.label + ': ' + context.parsed.y + config.unit;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        suggestedMin: config.suggestedMin,
                        suggestedMax: config.suggestedMax,
                        grid: {
                            color: 'rgba(36, 90, 52, 0.08)'
                        },
                        ticks: {
                            callback: function (value) {
                                return value + config.unit;
                            }
                        }
                    }
                }
            }
        });
    }

    function showDemoMessage(message) {
        var output = document.querySelector('[data-demo-message]');

        if (!output) {
            return;
        }

        output.textContent = message;
        output.hidden = false;
        output.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.querySelectorAll('[data-demo-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            var action = button.getAttribute('data-demo-action');
            var message = action === 'edit-device'
                ? 'Form perubahan perangkat akan diaktifkan setelah tabel perangkat dan API tersedia.'
                : 'Pendaftaran perangkat akan diaktifkan setelah API key perangkat tersedia.';

            showDemoMessage(message);
        });
    });

    var demoForm = document.querySelector('[data-demo-form]');
    if (demoForm) {
        demoForm.addEventListener('submit', function (event) {
            event.preventDefault();
            showDemoMessage('Catatan pengaturan tersimpan di tampilan. Untuk mengubah firmware, salin nilainya ke config.h lalu upload ulang ESP32.');
        });
    }

    createSingleSensorChart({
        canvasId: 'iotTemperatureChart',
        dataAttribute: 'data-temperature',
        label: 'Suhu Udara',
        unit: '\u00b0C',
        borderColor: '#d18332',
        backgroundColor: 'rgba(209, 131, 50, 0.12)',
        suggestedMin: 24,
        suggestedMax: 34
    });

    createSingleSensorChart({
        canvasId: 'iotSoilTemperatureChart',
        dataAttribute: 'data-soil-temperature',
        label: 'Suhu Tanah',
        unit: '\u00b0C',
        borderColor: '#a66a3f',
        backgroundColor: 'rgba(166, 106, 63, 0.12)',
        suggestedMin: 22,
        suggestedMax: 32
    });

    createSingleSensorChart({
        canvasId: 'iotAirHumidityChart',
        dataAttribute: 'data-air-humidity',
        label: 'Kelembapan Udara',
        unit: '%',
        borderColor: '#398a9b',
        backgroundColor: 'rgba(57, 138, 155, 0.12)',
        suggestedMin: 50,
        suggestedMax: 90
    });

    createSingleSensorChart({
        canvasId: 'iotMoistureChart',
        dataAttribute: 'data-moisture',
        label: 'Kelembapan Tanah',
        unit: '%',
        borderColor: '#2873a8',
        backgroundColor: 'rgba(40, 115, 168, 0.12)',
        suggestedMin: 40,
        suggestedMax: 80
    });
})();
