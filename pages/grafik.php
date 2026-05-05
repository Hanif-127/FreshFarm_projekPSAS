<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';

$user_id = $_SESSION['user_id'];

// Ambil data jurnal milik user
$query = mysqli_query($koneksi, "SELECT * FROM jurnal_tanam WHERE user_id = $user_id");
$total_data = mysqli_num_rows($query);

// Hitung total panen & jumlah tanaman
$total_panen   = 0;
$total_tanaman = 0;
$label_tanaman = [];
$data_panen    = [];

while ($row = mysqli_fetch_assoc($query)) {
    $total_panen   += $row['hasil_panen'];
    $total_tanaman += $row['jumlah'];
    $label_tanaman[] = $row['nama_tanaman'];
    $data_panen[]    = $row['hasil_panen'];
}

// Ubah ke format JSON untuk Chart.js
$label_json = json_encode($label_tanaman);
$data_json  = json_encode($data_panen);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Grafik & Statistik - Fresh Smart Farm</title>
    <!-- Import Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../assets/css/grafik.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="grafik-page">
    <section class="grafik-hero">
        <div class="grafik-hero__overlay"></div>
        <div class="grafik-hero__content">
            <span class="grafik-label">Analisis Data</span>
            <h1>Grafik & Statistik Pertanian</h1>
            <p>Visualisasikan data jurnal tanam Anda dalam bentuk grafik interaktif untuk memantau produktivitas pertanian.</p>
        </div>
    </section>

    <section class="grafik-content">
        <?php if ($total_data == 0): ?>
            <div class="grafik-empty">
                <p>Belum ada data jurnal tanam untuk ditampilkan dalam grafik.</p>
                <a href="jurnal/index.php" class="btn btn-primary">Tambah Data Jurnal</a>
            </div>
        <?php else: ?>

            <!-- Ringkasan Statistik -->
            <div class="stat-grid">
                <div class="stat-card">
                    <h3><?= number_format($total_tanaman) ?></h3>
                    <p>Total Tanaman</p>
                </div>
                <div class="stat-card">
                    <h3><?= number_format($total_panen) ?> <small>kg</small></h3>
                    <p>Total Hasil Panen</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total_data ?></h3>
                    <p>Data Jurnal</p>
                </div>
            </div>

            <!-- Grafik -->
            <div class="chart-box">
                <div class="chart-header">
                    <h3>Visualisasi Hasil Panen</h3>
                    <div class="chart-controls">
                        <label for="filterGrafik">Jenis Grafik:</label>
                        <select id="filterGrafik" onchange="gantiGrafik()">
                            <option value="bar">Batang (Bar)</option>
                            <option value="pie">Lingkaran (Pie)</option>
                            <option value="line">Garis (Line)</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <script>
                const labels = <?= $label_json ?>;
                const data   = <?= $data_json ?>;

                let myChart = null;

                function gantiGrafik() {
                    const jenis = document.getElementById('filterGrafik').value;

                    if (myChart) {
                        myChart.destroy();
                    }

                    const ctx = document.getElementById('myChart').getContext('2d');
                    const chartConfig = {
                        type: jenis,
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Hasil Panen (kg)',
                                data: data,
                                backgroundColor: jenis === 'pie' ? [
                                    '#4CAF50', '#2196F3', '#FF9800',
                                    '#E91E63', '#9C27B0', '#00BCD4',
                                    '#FF5722', '#795548', '#607D8B'
                                ] : 'rgba(36, 185, 86, 0.8)',
                                borderColor: jenis === 'pie' ? undefined : '#24b956',
                                borderWidth: 2,
                                fill: jenis === 'line' ? false : true,
                                tension: jenis === 'line' ? 0.35 : 0,
                                pointRadius: jenis === 'line' ? 6 : 4,
                                pointBackgroundColor: '#24b956'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: 12
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: '#f5f7f3',
                                        font: {
                                            size: 12
                                        },
                                        padding: 16
                                    }
                                },
                                tooltip: {
                                    bodyColor: '#f5f7f3',
                                    titleColor: '#ffffff',
                                    backgroundColor: 'rgba(16, 62, 35, 0.95)',
                                    borderColor: 'rgba(255, 255, 255, 0.12)',
                                    borderWidth: 1
                                }
                            },
                            scales: jenis !== 'pie' ? {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#f5f7f3',
                                        padding: 8
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#f5f7f3',
                                        padding: 8
                                    }
                                }
                            } : {}
                        }
                    };

                    myChart = new Chart(ctx, chartConfig);
                }

                window.addEventListener('DOMContentLoaded', gantiGrafik);
            </script>

        <?php endif; ?>

        <div class="grafik-footer-link">
            <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>
    </section>
</main>

</body>
</html>