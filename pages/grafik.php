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
</head>
<body>

<?php include '../includes/header.php'; ?>

<h2>📊 Grafik & Statistik Jurnal Tanam</h2>

<?php if ($total_data == 0): ?>
    <p>Belum ada data jurnal tanam.</p>
<?php else: ?>

    <!-- Ringkasan -->
    <div style="display:flex; gap:20px; margin-bottom:20px;">
        <div style="border:1px solid #ccc; padding:16px; border-radius:8px; text-align:center;">
            <h3><?= $total_tanaman ?></h3>
            <p>Total Tanaman</p>
        </div>
        <div style="border:1px solid #ccc; padding:16px; border-radius:8px; text-align:center;">
            <h3><?= $total_panen ?> kg</h3>
            <p>Total Hasil Panen</p>
        </div>
        <div style="border:1px solid #ccc; padding:16px; border-radius:8px; text-align:center;">
            <h3><?= $total_data ?></h3>
            <p>Total Data Jurnal</p>
        </div>
    </div>

    <!-- Filter jenis grafik -->
    <label>Pilih Jenis Grafik:</label>
    <select id="filterGrafik" onchange="gantiGrafik()">
        <option value="bar">Batang (Bar)</option>
        <option value="pie">Lingkaran (Pie)</option>
        <option value="line">Garis (Line)</option>
    </select>
    <br><br>

    <!-- Canvas grafik -->
    <canvas id="myChart" style="max-width:600px; max-height:400px;"></canvas>

    <script>
        const labels = <?= $label_json ?>;
        const data   = <?= $data_json ?>;

        let myChart = null;

        function gantiGrafik() {
            const jenis = document.getElementById('filterGrafik').value;

            // Hapus grafik lama
            if (myChart) myChart.destroy();

            const ctx = document.getElementById('myChart').getContext('2d');

            myChart = new Chart(ctx, {
                type: jenis,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hasil Panen (kg)',
                        data: data,
                        backgroundColor: [
                            '#4CAF50', '#2196F3', '#FF9800',
                            '#E91E63', '#9C27B0', '#00BCD4'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Tampilkan grafik default saat halaman dibuka
        gantiGrafik();
    </script>

<?php endif; ?>

<br>
<a href="dashboard.php">← Kembali ke Dashboard</a>

</body>
</html>