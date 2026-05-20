<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';
include '../includes/module_icons.php';

$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$laporan_grafik_css_version = filemtime(__DIR__ . '/../assets/css/laporan_grafik.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

$user_id = (int) $_SESSION['user_id'];

$periode_options = [
    '30' => ['label' => '30 hari', 'days' => 30],
    '90' => ['label' => '90 hari', 'days' => 90],
    '365' => ['label' => '1 tahun', 'days' => 365],
    'all' => ['label' => 'Semua waktu', 'days' => null],
];

$periode = $_GET['periode'] ?? '90';
if (!array_key_exists($periode, $periode_options)) {
    $periode = '90';
}
$periode_days = $periode_options[$periode]['days'];

$summary = [
    'total_entri' => 0,
    'total_jumlah' => 0.0,
    'total_panen' => 0.0,
    'rata_panen' => 0.0,
];

if ($periode_days === null) {
    $summary_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen,
                COALESCE(AVG(hasil_panen), 0) AS rata_panen
         FROM jurnal_tanam
         WHERE user_id = ?"
    );
    if ($summary_stmt) {
        mysqli_stmt_bind_param($summary_stmt, "i", $user_id);
        mysqli_stmt_execute($summary_stmt);
        $summary_result = mysqli_stmt_get_result($summary_stmt);
        $summary = mysqli_fetch_assoc($summary_result) ?: $summary;
        mysqli_stmt_close($summary_stmt);
    }
} else {
    $summary_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen,
                COALESCE(AVG(hasil_panen), 0) AS rata_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL ? DAY)"
    );
    if ($summary_stmt) {
        mysqli_stmt_bind_param($summary_stmt, "ii", $user_id, $periode_days);
        mysqli_stmt_execute($summary_stmt);
        $summary_result = mysqli_stmt_get_result($summary_stmt);
        $summary = mysqli_fetch_assoc($summary_result) ?: $summary;
        mysqli_stmt_close($summary_stmt);
    }
}

$status_counts = [
    'Sedang Tanam' => 0,
    'Sudah Panen' => 0,
    'Gagal' => 0,
];

if ($periode_days === null) {
    $status_stmt = mysqli_prepare(
        $koneksi,
        "SELECT status, COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ?
         GROUP BY status"
    );
    if ($status_stmt) {
        mysqli_stmt_bind_param($status_stmt, "i", $user_id);
        mysqli_stmt_execute($status_stmt);
        $status_result = mysqli_stmt_get_result($status_stmt);
        while ($row = mysqli_fetch_assoc($status_result)) {
            if (array_key_exists($row['status'], $status_counts)) {
                $status_counts[$row['status']] = (int) $row['total'];
            }
        }
        mysqli_stmt_close($status_stmt);
    }
} else {
    $status_stmt = mysqli_prepare(
        $koneksi,
        "SELECT status, COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ? AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY status"
    );
    if ($status_stmt) {
        mysqli_stmt_bind_param($status_stmt, "ii", $user_id, $periode_days);
        mysqli_stmt_execute($status_stmt);
        $status_result = mysqli_stmt_get_result($status_stmt);
        while ($row = mysqli_fetch_assoc($status_result)) {
            if (array_key_exists($row['status'], $status_counts)) {
                $status_counts[$row['status']] = (int) $row['total'];
            }
        }
        mysqli_stmt_close($status_stmt);
    }
}

$top_tanaman = [];
if ($periode_days === null) {
    $top_stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama_tanaman,
                COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen
         FROM jurnal_tanam
         WHERE user_id = ?
         GROUP BY nama_tanaman
         ORDER BY total_entri DESC, nama_tanaman ASC
         LIMIT 8"
    );
    if ($top_stmt) {
        mysqli_stmt_bind_param($top_stmt, "i", $user_id);
        mysqli_stmt_execute($top_stmt);
        $top_result = mysqli_stmt_get_result($top_stmt);
        $top_tanaman = mysqli_fetch_all($top_result, MYSQLI_ASSOC);
        mysqli_stmt_close($top_stmt);
    }
} else {
    $top_stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama_tanaman,
                COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY nama_tanaman
         ORDER BY total_entri DESC, nama_tanaman ASC
         LIMIT 8"
    );
    if ($top_stmt) {
        mysqli_stmt_bind_param($top_stmt, "ii", $user_id, $periode_days);
        mysqli_stmt_execute($top_stmt);
        $top_result = mysqli_stmt_get_result($top_stmt);
        $top_tanaman = mysqli_fetch_all($top_result, MYSQLI_ASSOC);
        mysqli_stmt_close($top_stmt);
    }
}

$trend_rows = [];
if ($periode_days === null) {
    $trend_stmt = mysqli_prepare(
        $koneksi,
        "SELECT DATE_FORMAT(tanggal_tanam, '%Y-%m') AS periode,
                COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen
         FROM jurnal_tanam
         WHERE user_id = ?
         GROUP BY DATE_FORMAT(tanggal_tanam, '%Y-%m')
         ORDER BY periode ASC"
    );
    if ($trend_stmt) {
        mysqli_stmt_bind_param($trend_stmt, "i", $user_id);
        mysqli_stmt_execute($trend_stmt);
        $trend_result = mysqli_stmt_get_result($trend_stmt);
        $trend_rows = mysqli_fetch_all($trend_result, MYSQLI_ASSOC);
        mysqli_stmt_close($trend_stmt);
    }
} else {
    $trend_stmt = mysqli_prepare(
        $koneksi,
        "SELECT DATE_FORMAT(tanggal_tanam, '%Y-%m') AS periode,
                COUNT(*) AS total_entri,
                COALESCE(SUM(jumlah), 0) AS total_jumlah,
                COALESCE(SUM(hasil_panen), 0) AS total_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY DATE_FORMAT(tanggal_tanam, '%Y-%m')
         ORDER BY periode ASC"
    );
    if ($trend_stmt) {
        mysqli_stmt_bind_param($trend_stmt, "ii", $user_id, $periode_days);
        mysqli_stmt_execute($trend_stmt);
        $trend_result = mysqli_stmt_get_result($trend_stmt);
        $trend_rows = mysqli_fetch_all($trend_result, MYSQLI_ASSOC);
        mysqli_stmt_close($trend_stmt);
    }
}

if (count($trend_rows) > 12) {
    $trend_rows = array_slice($trend_rows, -12);
}

$month_names = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
];

$trend_labels = [];
$trend_entri = [];
$trend_jumlah = [];
$trend_panen = [];
foreach ($trend_rows as $row) {
    $parts = explode('-', (string) $row['periode']);
    $label = $row['periode'];
    if (count($parts) === 2 && isset($month_names[$parts[1]])) {
        $label = $month_names[$parts[1]] . ' ' . $parts[0];
    }
    $trend_labels[] = $label;
    $trend_entri[] = (int) $row['total_entri'];
    $trend_jumlah[] = (float) $row['total_jumlah'];
    $trend_panen[] = (float) $row['total_panen'];
}

$recent_items = [];
if ($periode_days === null) {
    $recent_stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama_tanaman, tanggal_tanam, jumlah, hasil_panen, status
         FROM jurnal_tanam
         WHERE user_id = ?
         ORDER BY tanggal_tanam DESC, id DESC
         LIMIT 5"
    );
    if ($recent_stmt) {
        mysqli_stmt_bind_param($recent_stmt, "i", $user_id);
        mysqli_stmt_execute($recent_stmt);
        $recent_result = mysqli_stmt_get_result($recent_stmt);
        $recent_items = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
        mysqli_stmt_close($recent_stmt);
    }
} else {
    $recent_stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama_tanaman, tanggal_tanam, jumlah, hasil_panen, status
         FROM jurnal_tanam
         WHERE user_id = ? AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         ORDER BY tanggal_tanam DESC, id DESC
         LIMIT 5"
    );
    if ($recent_stmt) {
        mysqli_stmt_bind_param($recent_stmt, "ii", $user_id, $periode_days);
        mysqli_stmt_execute($recent_stmt);
        $recent_result = mysqli_stmt_get_result($recent_stmt);
        $recent_items = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
        mysqli_stmt_close($recent_stmt);
    }
}

$has_data = ((int) $summary['total_entri']) > 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Grafik - Fresh Smart Farm</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/laporan_grafik.css?v=<?= $laporan_grafik_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <section class="grafik-page-v2">
            <section class="module-card grafik-head-card">
                <div class="grafik-head-row">
                    <div class="grafik-head-copy">
                        <h1 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('chart') ?></span><span>Laporan & Grafik</span></h1>
                        <p>Analisis performa tanam berdasarkan periode, status, dan hasil panen.</p>
                    </div>
                    <form class="grafik-period-form" method="GET">
                        <div class="module-field">
                            <label>Periode</label>
                            <select name="periode" onchange="this.form.submit()">
                                <?php foreach ($periode_options as $key => $item): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" <?= $periode === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </section>

            <section class="grafik-kpi-grid">
                <article class="module-card grafik-kpi-card">
                    <span>Total Entri</span>
                    <strong><?= (int) $summary['total_entri'] ?></strong>
                </article>
                <article class="module-card grafik-kpi-card">
                    <span>Total Tanaman</span>
                    <strong><?= rtrim(rtrim(number_format((float) $summary['total_jumlah'], 2, '.', ''), '0'), '.') ?></strong>
                </article>
                <article class="module-card grafik-kpi-card">
                    <span>Total Panen (kg)</span>
                    <strong><?= rtrim(rtrim(number_format((float) $summary['total_panen'], 2, '.', ''), '0'), '.') ?></strong>
                </article>
                <article class="module-card grafik-kpi-card">
                    <span>Rata Panen / Entri</span>
                    <strong><?= rtrim(rtrim(number_format((float) $summary['rata_panen'], 2, '.', ''), '0'), '.') ?></strong>
                </article>
            </section>

            <?php if (!$has_data): ?>
                <section class="module-card grafik-chart-card grafik-chart-card--trend">
                    <p class="module-empty">Belum ada data jurnal pada periode ini. Tambahkan data jurnal untuk mulai analisis.</p>
                    <div class="module-actions">
                        <a href="jurnal/tambah.php" class="module-btn module-btn-primary"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('plus') ?></span><span>Tambah Data Jurnal</span></a>
                    </div>
                </section>
            <?php else: ?>
                <section class="module-card grafik-chart-card">
                    <div class="grafik-chart-head">
                        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('activity') ?></span><span>Tren Aktivitas Tanam</span></h2>
                        <div class="grafik-controls">
                            <div class="module-field">
                                <label>Metric</label>
                                <select id="metricSelect">
                                    <option value="entri">Jumlah Entri</option>
                                    <option value="jumlah">Jumlah Tanaman</option>
                                    <option value="panen" selected>Hasil Panen</option>
                                </select>
                            </div>
                            <div class="module-field">
                                <label>Jenis Grafik</label>
                                <select id="typeSelect">
                                    <option value="bar" selected>Batang</option>
                                    <option value="line">Garis</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="grafik-trend-wrap">
                        <canvas id="trendChart"></canvas>
                    </div>
                </section>

                <section class="module-card">
                    <div class="grafik-split">
                        <div class="grafik-pane">
                            <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('summary') ?></span><span>Status Tanaman</span></h2>
                            <div class="grafik-status-wrap">
                                <canvas id="statusChart"></canvas>
                            </div>
                            <div class="grafik-status-list">
                                <span class="status-chip status-sedang">Sedang Tanam: <?= $status_counts['Sedang Tanam'] ?></span>
                                <span class="status-chip status-panen">Sudah Panen: <?= $status_counts['Sudah Panen'] ?></span>
                                <span class="status-chip status-gagal">Gagal: <?= $status_counts['Gagal'] ?></span>
                            </div>
                        </div>

                        <div class="grafik-pane">
                            <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('market') ?></span><span>Top Tanaman</span></h2>
                            <?php if (count($top_tanaman) === 0): ?>
                                <p class="module-empty">Belum ada data tanaman.</p>
                            <?php else: ?>
                                <div class="module-table-wrap">
                                    <table class="module-table">
                                        <thead>
                                            <tr>
                                                <th>Tanaman</th>
                                                <th>Entri</th>
                                                <th>Jumlah</th>
                                                <th>Panen (kg)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_tanaman as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['nama_tanaman']) ?></td>
                                                    <td><?= (int) $item['total_entri'] ?></td>
                                                    <td><?= rtrim(rtrim(number_format((float) $item['total_jumlah'], 2, '.', ''), '0'), '.') ?></td>
                                                    <td><?= rtrim(rtrim(number_format((float) $item['total_panen'], 2, '.', ''), '0'), '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="module-card">
                    <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('clock') ?></span><span>Aktivitas Terbaru</span></h2>
                    <?php if (count($recent_items) === 0): ?>
                        <p class="module-empty">Belum ada aktivitas terbaru.</p>
                    <?php else: ?>
                        <div class="grafik-activity-list">
                            <?php foreach ($recent_items as $item): ?>
                                <?php
                                $status_class = match ($item['status']) {
                                    'Sedang Tanam' => 'module-status module-status-warning',
                                    'Sudah Panen' => 'module-status module-status-success',
                                    'Gagal' => 'module-status module-status-danger',
                                    default => 'module-status module-status-info',
                                };
                                ?>
                                <article class="grafik-activity-item">
                                    <div>
                                        <div class="grafik-activity-title"><?= htmlspecialchars($item['nama_tanaman']) ?></div>
                                        <div class="module-meta">
                                            <?= date('d M Y', strtotime($item['tanggal_tanam'])) ?> &bull;
                                            Jumlah: <?= rtrim(rtrim(number_format((float) $item['jumlah'], 2, '.', ''), '0'), '.') ?> &bull;
                                            Panen: <?= rtrim(rtrim(number_format((float) $item['hasil_panen'], 2, '.', ''), '0'), '.') ?> kg
                                        </div>
                                    </div>
                                    <span class="<?= $status_class ?>"><?= htmlspecialchars($item['status']) ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php if ($has_data): ?>
<script>
    (function () {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const chartDuration = 0;

        Chart.defaults.color = '#5b7362';
        Chart.defaults.font.family = "'Segoe UI', system-ui, -apple-system, sans-serif";

        const labels = <?= json_encode($trend_labels) ?>;
        const trendData = {
            entri: <?= json_encode($trend_entri) ?>,
            jumlah: <?= json_encode($trend_jumlah) ?>,
            panen: <?= json_encode($trend_panen) ?>
        };

        const metricLabel = {
            entri: 'Jumlah Entri',
            jumlah: 'Jumlah Tanaman',
            panen: 'Hasil Panen (kg)'
        };

        const chartPalette = {
            entri: {
                start: 'rgba(63, 144, 228, 0.9)',
                end: 'rgba(52, 112, 191, 0.84)',
                line: 'rgba(43, 104, 181, 1)'
            },
            jumlah: {
                start: 'rgba(57, 166, 92, 0.92)',
                end: 'rgba(42, 131, 74, 0.85)',
                line: 'rgba(31, 110, 62, 1)'
            },
            panen: {
                start: 'rgba(236, 180, 74, 0.94)',
                end: 'rgba(212, 146, 28, 0.86)',
                line: 'rgba(165, 108, 13, 1)'
            }
        };

        const metricSelect = document.getElementById('metricSelect');
        const typeSelect = document.getElementById('typeSelect');
        const trendCanvas = document.getElementById('trendChart');
        if (!metricSelect || !typeSelect || !trendCanvas) return;

        const trendCtx = trendCanvas.getContext('2d');
        let trendChart = null;

        const tooltipTheme = {
            backgroundColor: 'rgba(21, 53, 33, 0.94)',
            titleColor: '#f6fff7',
            bodyColor: '#def0e2',
            borderColor: 'rgba(116, 180, 137, 0.45)',
            borderWidth: 1,
            cornerRadius: 12,
            padding: 12,
            displayColors: false,
            titleFont: { weight: '700', size: 12 },
            bodyFont: { weight: '600', size: 12 }
        };

        function createTrendGradient(metric) {
            const colors = chartPalette[metric];
            const gradient = trendCtx.createLinearGradient(0, 0, 0, 340);
            gradient.addColorStop(0, colors.start);
            gradient.addColorStop(1, colors.end);
            return gradient;
        }

        function renderTrendChart() {
            const metric = metricSelect.value;
            const type = typeSelect.value;
            const colors = chartPalette[metric];
            const gradient = createTrendGradient(metric);

            if (trendChart) {
                trendChart.destroy();
            }

            trendChart = new Chart(trendCtx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: metricLabel[metric],
                        data: trendData[metric],
                        backgroundColor: type === 'line'
                            ? 'rgba(57, 166, 92, 0.14)'
                            : gradient,
                        borderColor: colors.line,
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: type === 'line' ? 0.34 : 0,
                        pointRadius: type === 'line' ? 3.2 : 0,
                        pointHoverRadius: type === 'line' ? 5 : 0,
                        pointBackgroundColor: type === 'line' ? colors.line : undefined,
                        borderRadius: type === 'bar' ? 4 : 0,
                        borderSkipped: false,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: chartDuration,
                        easing: 'easeOutCubic'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipTheme
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4f6858',
                                font: { weight: '600', size: 11 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#607869',
                                font: { weight: '600', size: 11 }
                            },
                            grid: {
                                color: 'rgba(47, 108, 68, 0.11)',
                                drawBorder: false
                            }
                        }
                    }
                }
            });
        }

        metricSelect.addEventListener('change', renderTrendChart);
        typeSelect.addEventListener('change', renderTrendChart);
        renderTrendChart();

        const statusCanvas = document.getElementById('statusChart');
        if (!statusCanvas) return;
        const statusCtx = statusCanvas.getContext('2d');
        const statusTotal = <?= (int) ($status_counts['Sedang Tanam'] + $status_counts['Sudah Panen'] + $status_counts['Gagal']) ?>;
        const statusCenterText = {
            id: 'statusCenterText',
            afterDraw(chart) {
                const meta = chart.getDatasetMeta(0);
                if (!meta || !meta.data || !meta.data[0]) return;
                const { x, y } = meta.data[0];
                const ctx = chart.ctx;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = '#2a6f41';
                ctx.font = '700 1.2rem Segoe UI';
                ctx.fillText(String(statusTotal), x, y - 1);
                ctx.fillStyle = '#6a806f';
                ctx.font = '600 0.72rem Segoe UI';
                ctx.fillText('Total Status', x, y + 16);
                ctx.restore();
            }
        };

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sedang Tanam', 'Sudah Panen', 'Gagal'],
                datasets: [{
                    data: <?= json_encode([
                        $status_counts['Sedang Tanam'],
                        $status_counts['Sudah Panen'],
                        $status_counts['Gagal']
                    ]) ?>,
                    backgroundColor: [
                        'rgba(236, 177, 74, 0.92)',
                        'rgba(72, 140, 219, 0.9)',
                        'rgba(224, 95, 89, 0.9)'
                    ],
                    borderColor: 'rgba(249, 253, 249, 0.97)',
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            plugins: [statusCenterText],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '63%',
                animation: {
                    duration: chartDuration,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTheme
                }
            }
        });
    })();
</script>
<?php endif; ?>
</body>
</html>
