<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/koneksi.php';

$dashboard_css_version = filemtime(__DIR__ . '/../assets/css/dashboard.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$debug_mode = isset($_GET['debug']) && $_GET['debug'] === '1';
$user_id = (int) $_SESSION['user_id'];

$motivasi = [
    "Setiap benih yang ditanam adalah harapan masa depan.",
    "Pertanian adalah seni merawat bumi dan menuai berkah.",
    "Dengan tangan yang rajin, ladang akan berbuah lebat.",
    "Tanamlah hari ini, panenlah impian besok.",
    "Bumi memberi apa yang kita taburkan."
];
$motivasi_random = $motivasi[array_rand($motivasi)];

$total = 0;
$total_jumlah_tanaman = 0;
$jadwal_hari_ini = 0;
$stok_tipis = 0;
$pengaduan_aktif = 0;
$aktivitas_minggu_ini = 0;
$recent_plants = [];
$chart_data = [];
$latest_prices = [];
$next_jadwal = null;
$latest_pengaduan = null;

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total, COALESCE(SUM(jumlah),0) AS total_jumlah FROM jurnal_tanam WHERE user_id = $user_id");
if ($q_total) {
    $row_total = mysqli_fetch_assoc($q_total);
    $total = (int) ($row_total['total'] ?? 0);
    $total_jumlah_tanaman = (float) ($row_total['total_jumlah'] ?? 0);
}

$q_recent = mysqli_query($koneksi, "SELECT nama_tanaman, tanggal_tanam, jumlah, status FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT 4");
if ($q_recent) {
    $recent_plants = mysqli_fetch_all($q_recent, MYSQLI_ASSOC);
}

$q_chart = mysqli_query($koneksi, "SELECT nama_tanaman, jumlah FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT 6");
if ($q_chart) {
    $chart_data = mysqli_fetch_all($q_chart, MYSQLI_ASSOC);
}

$q_today = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kalender_tanam WHERE user_id = $user_id AND tanggal_jadwal = CURDATE() AND status = 'terjadwal'");
if ($q_today) {
    $jadwal_hari_ini = (int) (mysqli_fetch_assoc($q_today)['total'] ?? 0);
}

$q_low_stock = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM inventaris WHERE user_id = $user_id AND jumlah_stok <= stok_minimum");
if ($q_low_stock) {
    $stok_tipis = (int) (mysqli_fetch_assoc($q_low_stock)['total'] ?? 0);
}

$q_pengaduan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE user_id = $user_id AND status IN ('dikirim','diproses')");
if ($q_pengaduan) {
    $pengaduan_aktif = (int) (mysqli_fetch_assoc($q_pengaduan)['total'] ?? 0);
}

$q_aktivitas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM jurnal_tanam WHERE user_id = $user_id AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
if ($q_aktivitas) {
    $aktivitas_minggu_ini = (int) (mysqli_fetch_assoc($q_aktivitas)['total'] ?? 0);
}

$q_next = mysqli_query($koneksi, "SELECT nama_kegiatan, tipe_kegiatan, tanggal_jadwal, jam_jadwal FROM kalender_tanam WHERE user_id = $user_id AND status = 'terjadwal' AND tanggal_jadwal >= CURDATE() ORDER BY tanggal_jadwal ASC, jam_jadwal ASC LIMIT 1");
if ($q_next) {
    $next_jadwal = mysqli_fetch_assoc($q_next) ?: null;
}

$q_prices = mysqli_query($koneksi, "SELECT nama_komoditas, harga, satuan, tanggal FROM harga_pasar ORDER BY tanggal DESC LIMIT 4");
if ($q_prices) {
    $latest_prices = mysqli_fetch_all($q_prices, MYSQLI_ASSOC);
}

$q_last_pengaduan = mysqli_query($koneksi, "SELECT judul, status, created_at FROM pengaduan WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1");
if ($q_last_pengaduan) {
    $latest_pengaduan = mysqli_fetch_assoc($q_last_pengaduan) ?: null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard.css?v=<?= (int) $dashboard_css_version ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-home">
<?php include '../includes/header.php'; ?>

<button class="sidebar-toggle" id="sidebarToggle" type="button" aria-controls="dashboardSidebar" aria-expanded="false">
    <img src="../assets/icons/titiktiga.svg" alt="Menu Dashboard" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
    <span class="sidebar-toggle-fallback" aria-hidden="true">•••</span>
</button>

<div class="sidebar-overlay" id="sidebarOverlay" hidden></div>

<aside class="dashboard-sidebar" id="dashboardSidebar" aria-hidden="true">
    <div class="dashboard-sidebar-head">
        <h2>Menu Dashboard</h2>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Tutup menu">&times;</button>
    </div>
    <nav class="dashboard-sidebar-nav">
        <a href="ringkasan.php">Ringkasan</a>
        <a href="jurnal/index.php">Jurnal Tanam</a>
        <a href="kalender.php">Kalender Tanam</a>
        <a href="inventaris.php">Inventaris</a>
        <a href="grafik.php">Laporan & Grafik</a>
        <a href="pengaduan.php">Pengaduan</a>
        <a href="pengaturan.php">Pengaturan</a>
    </nav>
</aside>

<main class="dashboard-container dashboard-v2">
    <?php if ($debug_mode): ?>
        <div class="debug-panel">
            <strong>DEBUG MODE ACTIVE</strong><br>
            FILE: <?= htmlspecialchars(__FILE__) ?><br>
            CSS: <?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard.css?v=<?= (int) $dashboard_css_version ?><br>
            HASH: <?= hash_file('sha256', __FILE__) ?>
        </div>
    <?php endif; ?>

    <section class="overview-grid">
        <article class="hero">
            <div class="hero-header">
                <h1>Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                <p>Monitoring cepat untuk kebunmu hari ini.</p>
            </div>
            <blockquote>"<?= htmlspecialchars($motivasi_random) ?>"</blockquote>
            <div class="hero-pill-grid">
                <div class="hero-pill">
                    <span>Total Jurnal</span>
                    <strong><?= $total ?></strong>
                </div>
                <div class="hero-pill">
                    <span>Jadwal Hari Ini</span>
                    <strong><?= $jadwal_hari_ini ?></strong>
                </div>
                <div class="hero-pill">
                    <span>Stok Menipis</span>
                    <strong><?= $stok_tipis ?></strong>
                </div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card-title">Ringkasan Minggu Ini</div>
            <div class="stat-number"><?= $aktivitas_minggu_ini ?></div>
            <div class="stat-label">Aktivitas Tanam 7 Hari</div>
            <p class="stat-note">Total tanaman tercatat: <strong><?= rtrim(rtrim(number_format($total_jumlah_tanaman, 2, '.', ''), '0'), '.') ?></strong>. Pengaduan aktif: <strong><?= $pengaduan_aktif ?></strong>.</p>
        </article>
    </section>

    <section class="content-grid">
        <article class="recent-list">
            <div class="section-header">
                <h3>Aktivitas Terbaru</h3>
                <span class="section-badge"><?= count($recent_plants) ?> data</span>
            </div>
            <?php if (count($recent_plants) > 0): ?>
                <ul>
                    <?php foreach ($recent_plants as $plant): ?>
                        <li>
                            <div class="plant-name"><?= htmlspecialchars($plant['nama_tanaman']) ?></div>
                            <div class="plant-meta">Tanggal: <strong><?= date('d M Y', strtotime($plant['tanggal_tanam'])) ?></strong></div>
                            <div class="plant-meta">Jumlah: <strong><?= rtrim(rtrim(number_format((float) $plant['jumlah'], 2, '.', ''), '0'), '.') ?></strong></div>
                            <div class="plant-status">Status: <strong><?= htmlspecialchars($plant['status']) ?></strong></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada data aktivitas tanam.</p>
            <?php endif; ?>
        </article>

        <article class="chart-section">
            <div class="section-header">
                <h3>Grafik Jumlah Tanam</h3>
                <span class="section-badge"><?= count($chart_data) ?> titik</span>
            </div>
            <?php if (count($chart_data) > 0): ?>
                <div class="chart-wrapper">
                    <canvas id="chartCanvas"></canvas>
                </div>
            <?php else: ?>
                <p class="chart-empty">Belum ada data untuk grafik.</p>
            <?php endif; ?>
        </article>
    </section>

    <section class="dashboard-extra-grid">
        <article class="dashboard-extra-card">
            <h3>Jadwal Terdekat</h3>
            <?php if ($next_jadwal): ?>
                <div class="extra-highlight"><?= htmlspecialchars($next_jadwal['nama_kegiatan']) ?></div>
                <p><?= htmlspecialchars(ucfirst($next_jadwal['tipe_kegiatan'])) ?> • <?= date('d M Y', strtotime($next_jadwal['tanggal_jadwal'])) ?><?= !empty($next_jadwal['jam_jadwal']) ? ' • ' . substr((string) $next_jadwal['jam_jadwal'], 0, 5) : '' ?></p>
            <?php else: ?>
                <p>Belum ada jadwal berikutnya.</p>
            <?php endif; ?>
            <a class="extra-link" href="kalender.php">Kelola Kalender</a>
        </article>

        <article class="dashboard-extra-card">
            <h3>Harga Pasar Terkini</h3>
            <?php if (count($latest_prices) > 0): ?>
                <ul class="price-list">
                    <?php foreach ($latest_prices as $item): ?>
                        <li>
                            <span><?= htmlspecialchars($item['nama_komoditas']) ?></span>
                            <strong>Rp <?= number_format((int) $item['harga'], 0, ',', '.') ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada data harga.</p>
            <?php endif; ?>
            <a class="extra-link" href="harga_pasar.php">Lihat Harga Pasar</a>
        </article>

        <article class="dashboard-extra-card">
            <h3>Status Pengaduan</h3>
            <?php if ($latest_pengaduan): ?>
                <div class="extra-highlight"><?= htmlspecialchars($latest_pengaduan['judul']) ?></div>
                <p>Status: <strong><?= htmlspecialchars(ucfirst($latest_pengaduan['status'])) ?></strong></p>
                <p>Dikirim: <?= date('d M Y H:i', strtotime($latest_pengaduan['created_at'])) ?></p>
            <?php else: ?>
                <p>Belum ada riwayat pengaduan.</p>
            <?php endif; ?>
            <a class="extra-link" href="pengaduan.php">Buka Pengaduan</a>
        </article>
    </section>
</main>

<script>
    const chartLabels = <?= json_encode(array_column($chart_data, 'nama_tanaman')) ?>;
    const chartValues = <?= json_encode(array_map(static fn($row) => (float) $row['jumlah'], $chart_data)) ?>;

    const chartCanvas = document.getElementById('chartCanvas');
    if (chartCanvas && chartLabels.length > 0) {
        const ctx = chartCanvas.getContext('2d');
        const chartType = chartLabels.length <= 2 ? 'doughnut' : 'bar';

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: chartValues,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(168, 85, 247, 0.85)',
                        'rgba(251, 191, 36, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(14, 165, 233, 0.85)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: chartType === 'bar' ? {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                } : {}
            }
        });
    }

    (function () {
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('dashboardSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (!toggleBtn || !closeBtn || !sidebar || !overlay) return;

        function openSidebar() {
            sidebar.classList.add('is-open');
            overlay.hidden = false;
            overlay.classList.add('is-open');
            sidebar.setAttribute('aria-hidden', 'false');
            toggleBtn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('sidebar-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-open');
            overlay.hidden = true;
            sidebar.setAttribute('aria-hidden', 'true');
            toggleBtn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('sidebar-open');
        }

        toggleBtn.addEventListener('click', function () {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeSidebar();
            }
        });
    })();
</script>
</body>
</html>
