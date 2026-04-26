<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
include '../../includes/koneksi.php';

$user_id = $_SESSION['user_id'];
$query   = mysqli_query($koneksi, "SELECT * FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Tanam</title>
    <link rel="stylesheet" href="../../assets/css/jurnal.css">
</head>
<body>

<?php include '../../includes/header.php'; ?>

<main class="jurnal-page">
    <section class="jurnal-hero">
        <div class="jurnal-hero__overlay"></div>
        <div class="jurnal-hero__content">
            <span class="jurnal-label">Jurnal Tanam</span>
            <h1>Catatan Pertanian Anda</h1>
            <p>Kelola dan pantau perkembangan tanaman Anda dengan mudah melalui jurnal tanam ini.</p>
        </div>
    </section>

    <section class="jurnal-listing">
        <div class="jurnal-listing__header">
            <h2>Data Jurnal Tanam</h2>
            <p>Rekam setiap aktivitas tanam untuk memantau hasil panen dan status tanaman.</p>
            <a href="tambah.php" class="btn btn-primary">+ Tambah Data Baru</a>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert-sukses">
                <?= htmlspecialchars($_GET['pesan']) ?>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($query) == 0): ?>
            <div class="jurnal-empty">
                <p>Belum ada data jurnal tanam. Mulai tambahkan data pertama Anda!</p>
            </div>
        <?php else: ?>
            <div class="jurnal-grid">
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <article class="jurnal-card">
                        <div class="jurnal-card__top">
                            <span class="jurnal-card__tag">Jurnal</span>
                            <span class="jurnal-card__date">📅 <?= date('d M Y', strtotime($row['tanggal_tanam'])) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($row['nama_tanaman']) ?></h3>
                        <div class="jurnal-card__details">
                            <div class="jurnal-card__detail">
                                <span class="jurnal-card__label">Jumlah:</span>
                                <span class="jurnal-card__value"><?= htmlspecialchars($row['jumlah']) ?> tanaman</span>
                            </div>
                            <div class="jurnal-card__detail">
                                <span class="jurnal-card__label">Status:</span>
                                <span class="jurnal-card__value status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                            </div>
                            <?php if ($row['hasil_panen'] > 0): ?>
                                <div class="jurnal-card__detail">
                                    <span class="jurnal-card__label">Hasil Panen:</span>
                                    <span class="jurnal-card__value"><?= htmlspecialchars($row['hasil_panen']) ?> kg</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="jurnal-card__actions">
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-secondary">Edit</a>
                            <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="jurnal-footer-link">
            <a href="../dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>
    </section>
</main>

</body>
</html>