<?php
session_start();
include '../includes/koneksi.php';

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$query = mysqli_query($koneksi, "SELECT * FROM harga_pasar WHERE id=$id");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak tersedia.";
    exit;
}

function detail_format_tanggal_harga($tanggal, $format = 'd F Y') {
    $waktu = strtotime((string) $tanggal);

    if (!$waktu) {
        return '-';
    }

    return date($format, $waktu);
}

function detail_format_rupiah($nilai) {
    return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Harga - Fresh Smart Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/harga_pasar.css">
</head>
<body class="harga-pasar-page-body">

<?php include '../includes/header.php'; ?>

<main class="harga-pasar-page">
    <section class="harga-pasar-hero">
        <div class="harga-pasar-hero__content">
            <span class="harga-pasar-label">Detail Harga</span>
            <h1><?= htmlspecialchars($data['nama_komoditas']) ?></h1>
            <p>Informasi harga pasar untuk satu komoditas, termasuk satuan dan tanggal update terakhir.</p>
        </div>
        <div class="harga-pasar-hero__stats" aria-label="Ringkasan detail harga">
            <div class="harga-pasar-stat">
                <strong><?= detail_format_rupiah($data['harga']) ?></strong>
                <span>Harga terbaru</span>
            </div>
            <div class="harga-pasar-stat">
                <strong><?= htmlspecialchars($data['satuan']) ?></strong>
                <span>Satuan</span>
            </div>
        </div>
    </section>

    <section class="harga-pasar-detail">
        <div class="harga-pasar-detail__content">
            <div class="harga-pasar-detail__info">
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Harga</span>
                    <span class="harga-pasar-detail__value"><?= detail_format_rupiah($data['harga']) ?></span>
                </div>
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Satuan</span>
                    <span class="harga-pasar-detail__value"><?= htmlspecialchars($data['satuan']) ?></span>
                </div>
                <div class="harga-pasar-detail__item">
                    <span class="harga-pasar-detail__label">Tanggal Update</span>
                    <span class="harga-pasar-detail__value"><?= detail_format_tanggal_harga($data['tanggal']) ?></span>
                </div>
            </div>
        </div>

        <div class="harga-pasar-footer-link">
            <a href="harga_pasar.php" class="btn btn-secondary">Kembali ke Daftar Harga</a>
        </div>
    </section>
</main>

</body>
</html>
