<?php
session_start();

// Kalau belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/koneksi.php';

// Array kata motivasi pertanian
$motivasi = [
    "Setiap benih yang ditanam adalah harapan masa depan 🌱",
    "Pertanian adalah seni merawat bumi dan menuai berkah 🌾",
    "Dengan tangan yang rajin, ladang akan berbuah lebat 🍎",
    "Tanamlah hari ini, panenlah impian besok 🌻",
    "Bumi memberi apa yang kita taburkan 💚"
];

// Pilih motivasi random
$motivasi_random = $motivasi[array_rand($motivasi)];

// Ambil jumlah jurnal milik user yang login
$user_id = $_SESSION['user_id'];
$query   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jurnal_tanam WHERE user_id = $user_id");
$data    = mysqli_fetch_assoc($query);
$total   = $data['total'];

// Ambil cuplikan tanaman terakhir (maksimal 3)
$query_recent = mysqli_query($koneksi, "SELECT nama_tanaman, tanggal_tanam, jumlah, status FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT 3");
$recent_plants = mysqli_fetch_all($query_recent, MYSQLI_ASSOC);

// Ambil data untuk chart (maksimal 5)
$query_chart = mysqli_query($koneksi, "SELECT nama_tanaman, jumlah FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT 5");
$chart_data = mysqli_fetch_all($query_chart, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="overview-grid">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-header">
                <h1>Halo, <?= $_SESSION['username'] ?>!</h1>
                <p>Selamat datang di dashboard Fresh Smart Farm</p>
            </div>
            <blockquote>"<?= $motivasi_random ?>"</blockquote>
            <div class="hero-pill-grid">
                <div class="hero-pill">
                    <span>Total Jurnal</span>
                    <strong><?= $total ?></strong>
                </div>
                <div class="hero-pill">
                    <span>Tanaman Terakhir</span>
                    <strong><?= count($recent_plants) ?></strong>
                </div>
                <div class="hero-pill">
                    <span>Data Chart</span>
                    <strong><?= count($chart_data) ?></strong>
                </div>
            </div>
        </section>

        <!-- Statistik Utama -->
        <section class="stat-card">
            <div class="stat-card-title">Kinerja Cepat</div>
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Jurnal Tanam</div>
            <p class="stat-note">Pantau hasil tanam terbaru dan buat keputusan lebih cepat setiap hari.</p>
        </section>
    </div>

    <div class="content-grid">
        <!-- Cuplikan Tanaman Terakhir -->
        <section class="recent-list">
            <div class="section-header">
                <h3>Tanaman Terakhir</h3>
                <span class="section-badge"><?= count($recent_plants) ?> entri terbaru</span>
            </div>
            <?php if (count($recent_plants) > 0): ?>
                <ul>
                    <?php foreach ($recent_plants as $plant): ?>
                        <li>
                            <div class="plant-name"><?= htmlspecialchars($plant['nama_tanaman']) ?></div>
                            <div class="plant-meta">Tanggal: <strong><?= date('d M Y', strtotime($plant['tanggal_tanam'])) ?></strong></div>
                            <div class="plant-meta">Jumlah: <strong><?= $plant['jumlah'] ?></strong></div>
                            <div class="plant-status">Status: <strong><?= htmlspecialchars($plant['status']) ?></strong></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada data tanaman</p>
            <?php endif; ?>
        </section>

        <!-- Mini Chart -->
        <section class="chart-section">
            <div class="section-header">
                <h3>Preview Jurnal Tanam</h3>
                <span class="section-badge"><?= count($chart_data) ?> data</span>
            </div>
            <?php if (count($chart_data) > 0): ?>
                <div class="chart-wrapper">
                    <canvas id="chartCanvas"></canvas>
                </div>
            <?php else: ?>
                <p class="chart-empty">Belum ada data untuk chart</p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Menu Navigasi -->
    <section class="menu-grid">
        <a href="jurnal/index.php" class="menu-card">
            <span class="menu-icon">
                <img src="../assets/icons/jurnal_putih.svg" alt="Jurnal Tanam">
            </span>
            <span class="menu-title">Jurnal Tanam</span>
            <small>Catat setiap langkah tanammu</small>
        </a>
        <a href="artikel.php" class="menu-card">
            <span class="menu-icon">
                <img src="../assets/icons/artikel_putih.svg" alt="Artikel">
            </span>
            <span class="menu-title">Artikel</span>
            <small>Baca tips dan berita pertanian</small>
        </a>
        <a href="harga_pasar.php" class="menu-card">
            <span class="menu-icon">
                <img src="../assets/icons/harga_putih.svg" alt="Harga Pasar">
            </span>
            <span class="menu-title">Harga Pasar</span>
            <small>Cek tren harga komoditas</small>
        </a>
        <a href="grafik.php" class="menu-card">
            <span class="menu-icon">
                <img src="../assets/icons/grafik_putih.svg" alt="Grafik & Statistik">
            </span>
            <span class="menu-title">Grafik & Statistik</span>
            <small>Analisis data jurnal secara visual</small>
        </a>
    </section>

    <!-- Logout -->
    <div class="logout-section">
        <a href="../logout.php" class="logout-btn">
            <img src="../assets/icons/logout.svg" alt="Logout">
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
    // Data untuk chart
    const chartLabels = <?= json_encode(array_column($chart_data, 'nama_tanaman')) ?>;
    const chartValues = <?= json_encode(array_column($chart_data, 'jumlah')) ?>;

    const chartCanvas = document.getElementById('chartCanvas');
    if (chartCanvas && chartLabels.length > 0) {
        const ctx = chartCanvas.getContext('2d');
        let chartType = 'bar';
        if (chartLabels.length <= 2) {
            chartType = 'doughnut';
        }

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: chartValues,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: chartType === 'bar' ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }
</script>

</body>
</html>