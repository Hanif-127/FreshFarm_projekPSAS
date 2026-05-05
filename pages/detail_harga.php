<?php
session_start();
include '../includes/koneksi.php';

$id    = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM harga_pasar WHERE id=$id");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak tersedia.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Harga - Fresh Smart Farm</title>
    <link rel="stylesheet" href="../assets/css/harga_pasar.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="harga-pasar-page">
    <section class="harga-pasar-hero">
        <div class="harga-pasar-hero__overlay"></div>
        <div class="harga-pasar-hero__content">
            <span class="harga-pasar-label">Detail Harga</span>
            <h1><?= htmlspecialchars($data['nama_komoditas']) ?></h1>
            <p>Informasi lengkap harga pasar komoditas ini.</p>
        </div>
    </section>

    <section class="harga-pasar-detail">
        <div class="harga-pasar-detail__content">
            <div class="harga-pasar-detail__info">
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Harga</span>
                    <span class="harga-pasar-detail__value">Rp <?= number_format($data['harga'], 0, ',', '.') ?></span>
                </div>
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Satuan</span>
                    <span class="harga-pasar-detail__value"><?= htmlspecialchars($data['satuan']) ?></span>
                </div>
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Tanggal Update</span>
                    <span class="harga-pasar-detail__value"><?= date('d F Y', strtotime($data['tanggal'])) ?></span>
                </div>
            </div>
        </div>

        <div class="harga-pasar-footer-link">
            <a href="harga_pasar.php" class="btn btn-secondary">← Kembali ke Daftar Harga</a>
        </div>
    </section>
</main>

</body>
</html>