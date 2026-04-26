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

<main class="harga-pasar-page">
    <section class="harga-pasar-hero">
        <div class="harga-pasar-hero__overlay"></div>
        <div class="harga-pasar-hero__content">
            <span class="harga-pasar-label">Harga Pasar</span>
            <h1>Harga Komoditas Terkini</h1>
            <p>Pantau harga pasar komoditas pertanian terkini untuk membantu perencanaan panen dan penjualan Anda.</p>
        </div>
    </section>

    <section class="harga-pasar-listing">
        <div class="harga-pasar-listing__header">
            <h2>Daftar Harga Pasar</h2>
            <p>Data harga terbaru dari pasar lokal untuk berbagai komoditas pertanian.</p>
        </div>

        <?php if (mysqli_num_rows($query) == 0): ?>
            <div class="harga-pasar-empty">
                <p>Data harga pasar belum tersedia saat ini.</p>
            </div>
        <?php else: ?>
            <div class="harga-pasar-grid">
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <article class="harga-pasar-card">
                        <div class="harga-pasar-card__top">
                            <span class="harga-pasar-card__tag">Komoditas</span>
                            <span class="harga-pasar-card__date">📅 <?= date('d M Y', strtotime($row['tanggal'])) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($row['nama_komoditas']) ?></h3>
                        <div class="harga-pasar-card__price">
                            <span class="harga-pasar-card__amount">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                            <span class="harga-pasar-card__unit">per <?= htmlspecialchars($row['satuan']) ?></span>
                        </div>
                        <a href="detail_harga.php?id=<?= $row['id'] ?>" class="btn btn-primary">Lihat Detail</a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="harga-pasar-footer-link">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
            <?php else: ?>
                <a href="../index.php" class="btn btn-secondary">← Kembali ke Beranda</a>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>