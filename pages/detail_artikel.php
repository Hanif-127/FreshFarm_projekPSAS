<?php
session_start();
include '../includes/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$query = mysqli_query($koneksi, "SELECT * FROM artikel WHERE id=$id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Artikel tidak ditemukan.";
    exit;
}

function detail_artikel_gambar_src($gambar) {
    $nama_file = trim((string) $gambar);

    if ($nama_file === '') {
        return '';
    }

    $nama_file = basename($nama_file);
    $path_file = __DIR__ . '/../assets/images_artikel/' . $nama_file;

    if (!is_file($path_file)) {
        return '';
    }

    return '../assets/images_artikel/' . htmlspecialchars($nama_file, ENT_QUOTES, 'UTF-8');
}

$gambar_artikel = detail_artikel_gambar_src($data['gambar'] ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']) ?> - Fresh Smart Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/artikel.css">
</head>
<body class="artikel-page-body">

<?php include '../includes/header.php'; ?>

<main class="artikel-page">
    <section class="artikel-hero">
        <div class="artikel-hero__overlay"></div>
        <div class="artikel-hero__content">
            <span class="artikel-label">Artikel Detail</span>
            <h1><?= htmlspecialchars($data['judul']) ?></h1>
            <p>Tanggal: <?= date('d F Y', strtotime($data['tanggal_publish'])) ?></p>
        </div>
    </section>

    <section class="artikel-detail">
        <div class="artikel-detail__content">
            <?php if ($gambar_artikel !== ''): ?>
                <div class="artikel-detail__image">
                    <img src="<?= $gambar_artikel ?>" alt="<?= htmlspecialchars($data['judul']) ?>">
                </div>
            <?php endif; ?>

            <div class="artikel-detail__text">
                <?= nl2br(htmlspecialchars($data['isi'])) ?>
            </div>
        </div>

        <div class="artikel-footer-link">
            <a href="artikel.php" class="btn btn-secondary">Kembali ke Daftar Artikel</a>
        </div>
    </section>
</main>

</body>
</html>
