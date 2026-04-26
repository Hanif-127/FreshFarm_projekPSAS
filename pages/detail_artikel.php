<?php
session_start();
include '../includes/koneksi.php';

$id    = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM artikel WHERE id=$id");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Artikel tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['judul']) ?> - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/artikel.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="artikel-page">
    <section class="artikel-hero">
        <div class="artikel-hero__overlay"></div>
        <div class="artikel-hero__content">
            <span class="artikel-label">Artikel Detail</span>
            <h1><?= htmlspecialchars($data['judul']) ?></h1>
            <p>📅 <?= date('d F Y', strtotime($data['tanggal_publish'])) ?></p>
        </div>
    </section>

    <section class="artikel-detail">
        <div class="artikel-detail__content">
            <?php if ($data['gambar']): ?>
                <div class="artikel-detail__image">
                    <img src="../assets/images/<?= htmlspecialchars($data['gambar']) ?>" alt="<?= htmlspecialchars($data['judul']) ?>">
                </div>
            <?php endif; ?>

            <div class="artikel-detail__text">
                <?= nl2br(htmlspecialchars($data['isi'])) ?>
            </div>
        </div>

        <div class="artikel-footer-link">
            <a href="artikel.php" class="btn btn-secondary">← Kembali ke Daftar Artikel</a>
        </div>
    </section>
</main>

</body>
</html>