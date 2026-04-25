<?php
session_start();
include '../includes/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Artikel - Fresh Smart Farm</title>
</head>
<body>

<?php include '../includes/header.php'; ?>

<h2>📰 Artikel Pertanian</h2>

<?php if (mysqli_num_rows($query) == 0): ?>
    <p>Belum ada artikel tersedia.</p>
<?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($query)): ?>
        <div style="border:1px solid #ccc; padding:12px; margin-bottom:12px;">
            <h3><?= $row['judul'] ?></h3>
            <p style="color:gray;">📅 <?= $row['tanggal_publish'] ?></p>
            <p><?= substr($row['isi'], 0, 150) ?>...</p>
            <a href="detail_artikel.php?id=<?= $row['id'] ?>">Baca Selengkapnya →</a>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<br>
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="dashboard.php">← Kembali ke Dashboard</a>
<?php else: ?>
    <a href="../index.php">← Kembali ke Beranda</a>
<?php endif; ?>

</body>
</html>