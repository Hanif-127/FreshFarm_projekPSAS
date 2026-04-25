<?php
session_start();
include '../includes/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Harga Pasar - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/harga_pasar.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<h2>💰 Harga Pasar Komoditas</h2>

<?php if (mysqli_num_rows($query) == 0): ?>
    <p>Data tidak tersedia.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Komoditas</th>
            <th>Harga</th>
            <th>Satuan</th>
            <th>Tanggal</th>
            <th>Detail</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_komoditas'] ?></td>
            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td><?= $row['satuan'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><a href="detail_harga.php?id=<?= $row['id'] ?>">Lihat Detail</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<br>
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="dashboard.php">← Kembali ke Dashboard</a>
<?php else: ?>
    <a href="../index.php">← Kembali ke Beranda</a>
<?php endif; ?>

</body>
</html>