<?php
session_start();
include '../includes/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC");
$total_artikel = mysqli_num_rows($query);

function artikel_gambar_src($gambar) {
    $nama_file = trim((string) $gambar);

    if ($nama_file === '') {
        return '../assets/images/artikel_default.png';
    }

    $nama_file = basename($nama_file);
    $path_file = __DIR__ . '/../assets/images/' . $nama_file;

    if (!is_file($path_file)) {
        return '../assets/images/artikel_default.png';
    }

    return '../assets/images/' . htmlspecialchars($nama_file, ENT_QUOTES, 'UTF-8');
}

function artikel_excerpt($isi, $batas = 155) {
    $bersih = trim(preg_replace('/\s+/', ' ', strip_tags($isi)));

    if (function_exists('mb_strlen') && mb_strlen($bersih) > $batas) {
        return mb_substr($bersih, 0, $batas) . '...';
    }

    if (strlen($bersih) > $batas) {
        return substr($bersih, 0, $batas) . '...';
    }

    return $bersih;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/artikel.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="artikel-page">
    <section class="artikel-hero">
        <div class="artikel-hero__content">
            <span class="artikel-label">Artikel Pertanian</span>
            <h1>Kumpulan Artikel Fresh Smart Farm</h1>
            <p>Baca wawasan, tips perawatan, dan informasi pasar yang membantu petani mengambil keputusan lebih tepat.</p>
        </div>
        <div class="artikel-hero__stat" aria-label="Jumlah artikel tersedia">
            <strong><?= $total_artikel ?></strong>
            <span>Artikel tersedia</span>
        </div>
    </section>

    <section class="artikel-listing">
        <div class="artikel-listing__header">
            <div>
                <span class="artikel-kicker">Katalog Artikel</span>
                <h2>Semua Artikel</h2>
                <p>Preview dari landing hanya menampilkan beberapa artikel terbaru. Di halaman ini semua artikel dikumpulkan lengkap.</p>
            </div>
            <a href="../index.php#artikel" class="btn btn-secondary">Kembali ke Preview</a>
        </div>

        <?php if ($total_artikel == 0): ?>
            <div class="artikel-empty">
                <strong>Belum ada artikel tersedia.</strong>
                <p>Artikel baru akan muncul di sini setelah data ditambahkan ke sistem.</p>
            </div>
        <?php else: ?>
            <div class="artikel-grid">
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <article class="artikel-card">
                        <a class="artikel-card__image" href="detail_artikel.php?id=<?= (int) $row['id'] ?>">
                            <img src="<?= artikel_gambar_src($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
                        </a>
                        <div class="artikel-card__body">
                            <div class="artikel-card__meta">
                                <span>Artikel</span>
                                <time datetime="<?= htmlspecialchars($row['tanggal_publish']) ?>">
                                    <?= date('d M Y', strtotime($row['tanggal_publish'])) ?>
                                </time>
                            </div>
                            <h3><?= htmlspecialchars($row['judul']) ?></h3>
                            <p><?= htmlspecialchars(artikel_excerpt($row['isi'])) ?></p>
                            <a href="detail_artikel.php?id=<?= (int) $row['id'] ?>" class="btn btn-primary">Baca Selengkapnya</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="artikel-footer-link">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            <?php else: ?>
                <a href="../index.php" class="btn btn-secondary">Kembali ke Beranda</a>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>
