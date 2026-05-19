<?php
session_start();
include '../includes/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC, nama_komoditas ASC");
$harga_pasar = [];

while ($row = mysqli_fetch_assoc($query)) {
    $harga_pasar[] = $row;
}

$total_harga = count($harga_pasar);
$update_terakhir = $total_harga > 0 ? format_tanggal_harga($harga_pasar[0]['tanggal'], 'd M Y') : '-';

function format_tanggal_harga($tanggal, $format = 'd M Y') {
    $waktu = strtotime((string) $tanggal);

    if (!$waktu) {
        return '-';
    }

    return date($format, $waktu);
}

function format_rupiah($nilai) {
    return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Pasar - Fresh Smart Farm</title>
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
            <span class="harga-pasar-label">Harga Pasar</span>
            <h1>Pantau Harga Komoditas</h1>
            <p>Semua data harga pasar dikumpulkan dalam tabel yang lebih bersih agar komoditas, satuan, dan tanggal update mudah dibandingkan.</p>
        </div>
        <div class="harga-pasar-hero__stats" aria-label="Ringkasan harga pasar">
            <div class="harga-pasar-stat">
                <strong><?= $total_harga ?></strong>
                <span>Data harga</span>
            </div>
            <div class="harga-pasar-stat">
                <strong><?= $update_terakhir ?></strong>
                <span>Update terbaru</span>
            </div>
        </div>
    </section>

    <section class="harga-pasar-listing">
        <div class="harga-pasar-listing__header">
            <div>
                <span class="harga-pasar-kicker">Katalog Harga</span>
                <h2>Semua Harga Pasar</h2>
                <p>Preview landing dibuat ringkas. Halaman ini menyimpan daftar lengkap untuk pengecekan sebelum jual, panen, atau belanja stok.</p>
            </div>
            <a href="../index.php#harga" class="btn btn-secondary">Kembali ke Landing</a>
        </div>

        <?php if ($total_harga == 0): ?>
            <div class="harga-pasar-empty">
                <strong>Data harga pasar belum tersedia.</strong>
                <p>Data baru akan muncul di sini setelah harga komoditas ditambahkan ke sistem.</p>
            </div>
        <?php else: ?>
            <div class="harga-pasar-table-wrap">
                <table class="harga-pasar-table">
                    <thead>
                        <tr>
                            <th>Komoditas</th>
                            <th>Harga</th>
                            <th>Satuan</th>
                            <th>Tanggal Update</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($harga_pasar as $row): ?>
                            <tr>
                                <td class="harga-pasar-name"><?= htmlspecialchars($row['nama_komoditas']) ?></td>
                                <td class="harga-pasar-price"><?= format_rupiah($row['harga']) ?></td>
                                <td><?= htmlspecialchars($row['satuan']) ?></td>
                                <td><?= format_tanggal_harga($row['tanggal']) ?></td>
                                <td>
                                    <a href="detail_harga.php?id=<?= (int) $row['id'] ?>" class="table-link">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="harga-pasar-footer-link">
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
