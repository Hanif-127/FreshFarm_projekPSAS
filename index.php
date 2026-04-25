<?php
session_start();
include 'includes/koneksi.php';

// Ambil artikel terbaru
$artikel = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC LIMIT 3");

// Ambil harga pasar
$harga = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC LIMIT 6");

// Hitung statistik
$total_artikel = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM artikel"));
$total_harga   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM harga_pasar"));
$total_user    = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM users"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Smart Farm</title>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a class="logo" href="index.php">
        <img src="assets/images/logo.png" alt="Logo Fresh Smart Farm"
             onerror="this.style.display='none'">
        <span class="logo-text">Fresh <span>Smart Farm</span></span>
    </a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn-login" href="pages/dashboard.php">Dashboard →</a>
    <?php else: ?>
        <a class="btn-login" href="login.php">Login</a>
    <?php endif; ?>
</nav>

<!-- HERO dengan background -->
<div class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <img class="logo-hero"
             src="assets/images/logo.png"
             alt="Logo Fresh Smart Farm"
             onerror="this.style.display='none'">
        <h1>🌾 Fresh Smart Farm</h1>
        <p>Sistem pencatatan pertanian modern yang membantu petani Indonesia mencatat, memantau, dan menganalisa hasil pertanian dengan mudah.</p>
        <a href="login.php">Mulai Sekarang →</a>
    </div>
</div>

<!-- TENTANG FRESH SMART FARM -->
<div class="about">
    <div class="about-inner">
        <div class="about-logo">
            <img src="assets/images/logo.png"
                 alt="Logo Fresh Smart Farm"
                 onerror="this.src=''; this.alt='🌱'">
            <p>Fresh Smart Farm</p>
        </div>
        <div class="about-text">
            <h2>Apa itu Fresh Smart Farm?</h2>
            <p>
                <strong>Fresh Smart Farm</strong> adalah platform digital yang dirancang khusus untuk membantu para petani Indonesia dalam mengelola kegiatan pertanian mereka secara lebih terstruktur dan efisien.
            </p>
            <p>
                Dengan sistem ini, petani dapat mencatat jurnal tanam harian, memantau harga pasar komoditas terkini, membaca artikel pertanian terpercaya, serta melihat statistik dan grafik perkembangan hasil panen mereka.
            </p>
            <p>Fitur yang tersedia:</p>
            <ul>
                <li>Jurnal tanam digital dengan sistem CRUD lengkap</li>
                <li>Pantau harga komoditas pertanian terkini</li>
                <li>Artikel dan tips pertanian terpercaya</li>
                <li>Grafik & statistik hasil panen interaktif</li>
                <li>Sistem login aman untuk setiap petani</li>
            </ul>
        </div>
    </div>
</div>

<!-- STATISTIK -->
<section>
    <h2>📈 Statistik Platform</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_user ?>+</h3>
            <p>Petani Terdaftar</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_artikel ?>+</h3>
            <p>Artikel Tersedia</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_harga ?>+</h3>
            <p>Data Harga Komoditas</p>
        </div>
    </div>
</section>

<!-- FITUR UNGGULAN -->
<section>
    <h2>✨ Fitur Unggulan</h2>
    <div class="fitur-grid">
        <div class="fitur-card">
            <div class="icon">📋</div>
            <h3>Jurnal Tanam</h3>
            <p>Catat semua aktivitas tanam kamu secara terstruktur dan rapi setiap harinya.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">📊</div>
            <h3>Grafik & Statistik</h3>
            <p>Lihat perkembangan hasil panen dalam bentuk grafik yang mudah dipahami.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">💰</div>
            <h3>Harga Pasar</h3>
            <p>Pantau harga komoditas pertanian terkini langsung dari dashboard kamu.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">📰</div>
            <h3>Artikel Pertanian</h3>
            <p>Baca tips dan informasi seputar dunia pertanian yang terpercaya.</p>
        </div>
    </div>
</section>

<!-- ARTIKEL TERBARU -->
<section>
    <h2>📰 Artikel Terbaru</h2>
    <?php if (mysqli_num_rows($artikel) == 0): ?>
        <div class="kosong">Belum ada artikel tersedia.</div>
    <?php else: ?>
        <div class="artikel-grid">
            <?php while ($row = mysqli_fetch_assoc($artikel)): ?>
                <div class="artikel-card">
                    <h3><?= $row['judul'] ?></h3>
                    <p class="tanggal">📅 <?= $row['tanggal_publish'] ?></p>
                    <p><?= substr($row['isi'], 0, 120) ?>...</p>
                    <a href="pages/detail_artikel.php?id=<?= $row['id'] ?>">Baca Selengkapnya →</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<!-- HARGA PASAR -->
<section>
    <h2>💰 Harga Pasar Hari Ini</h2>
    <?php if (mysqli_num_rows($harga) == 0): ?>
        <div class="kosong">Data harga pasar belum tersedia.</div>
    <?php else: ?>
        <table class="harga-table">
            <tr>
                <th>Komoditas</th>
                <th>Harga</th>
                <th>Satuan</th>
                <th>Tanggal</th>
                <th>Detail</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($harga)): ?>
                <tr>
                    <td><?= $row['nama_komoditas'] ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= $row['satuan'] ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><a href="pages/detail_harga.php?id=<?= $row['id'] ?>">Lihat →</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-logo">
        <img src="assets/images/logo.png"
             alt="Logo"
             onerror="this.style.display='none'">
        <span>Fresh Smart Farm</span>
    </div>
    <p>&copy; 2025 <span class="highlight">Fresh Smart Farm</span> — Dibuat dengan ❤️ oleh siswa SMK Telkom Purwokerto</p>
</footer>

</body>
</html>