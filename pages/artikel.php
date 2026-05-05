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
    <link rel="stylesheet" href="../assets/css/artikel.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="artikel-page">
    <section class="artikel-hero">
        <div class="artikel-hero__overlay"></div>
        <div class="artikel-hero__content">
            <span class="artikel-label">Blog Pertanian</span>
            <h1>Inspirasi &amp; Tips Bertani</h1>
            <p>Pelajari cara merawat tanaman, tren pasar, dan strategi panen lewat artikel yang mudah dibaca.</p>
        </div>
    </section>

    <section class="artikel-listing">
        <div class="artikel-listing__header">
            <h2>Semua Artikel</h2>
            <p>Temukan wawasan terbaru untuk mendukung produktivitas pertanian Anda.</p>
        </div>

        <?php if (mysqli_num_rows($query) == 0): ?>
            <div class="artikel-empty">
                <p>Belum ada artikel tersedia saat ini.</p>
            </div>
        <?php else: ?>
            <div class="artikel-grid">
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <article class="artikel-card">
                        <div class="artikel-card__top">
                            <span class="artikel-card__tag">Artikel</span>
                            <span class="artikel-card__date">📅 <?= date('d M Y', strtotime($row['tanggal_publish'])) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($row['judul']) ?></h3>
                        <p><?= htmlspecialchars(substr($row['isi'], 0, 160)) ?>...</p>
                        <a href="detail_artikel.php?id=<?= $row['id'] ?>" class="btn btn-primary">Baca Selengkapnya</a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="artikel-footer-link">
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