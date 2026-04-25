<?php
session_start();
include 'includes/koneksi.php';

// Ambil artikel terbaru (3 artikel)
$artikel = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC LIMIT 3");

// Ambil harga pasar terbaru
$harga = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Smart Farm</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f4f9f4;
            color: #333;
        }

        /* ===== NAVBAR ===== */
        nav {
            background: #2e7d32;
            padding: 14px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav .logo {
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
        }

        nav .logo span {
            color: #a5d6a7;
        }

        nav a.btn-login {
            background: white;
            color: #2e7d32;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s;
        }

        nav a.btn-login:hover {
            background: #c8e6c9;
        }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
            color: white;
            text-align: center;
            padding: 80px 20px;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 16px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero a {
            background: white;
            color: #2e7d32;
            padding: 12px 32px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: transform 0.2s;
            display: inline-block;
        }

        .hero a:hover {
            transform: scale(1.05);
        }

        /* ===== SECTION ===== */
        section {
            padding: 50px 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

        section h2 {
            font-size: 26px;
            color: #2e7d32;
            margin-bottom: 24px;
            border-left: 5px solid #66bb6a;
            padding-left: 12px;
        }

        /* ===== ARTIKEL CARDS ===== */
        .artikel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .artikel-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }

        .artikel-card:hover {
            transform: translateY(-4px);
        }

        .artikel-card h3 {
            font-size: 17px;
            margin-bottom: 8px;
            color: #1b5e20;
        }

        .artikel-card .tanggal {
            font-size: 13px;
            color: #888;
            margin-bottom: 10px;
        }

        .artikel-card p {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .artikel-card a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        .artikel-card a:hover {
            text-decoration: underline;
        }

        /* ===== HARGA PASAR ===== */
        .harga-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .harga-table th {
            background: #2e7d32;
            color: white;
            padding: 12px 16px;
            text-align: left;
            font-size: 14px;
        }

        .harga-table td {
            padding: 12px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .harga-table tr:last-child td {
            border-bottom: none;
        }

        .harga-table tr:hover td {
            background: #f9fff9;
        }

        .harga-table a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }

        /* ===== FITUR ===== */
        .fitur-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .fitur-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .fitur-card .icon {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .fitur-card h3 {
            font-size: 16px;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .fitur-card p {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        /* ===== KOSONG ===== */
        .kosong {
            text-align: center;
            color: #999;
            padding: 30px;
            background: white;
            border-radius: 12px;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #1b5e20;
            color: #a5d6a7;
            text-align: center;
            padding: 24px;
            font-size: 14px;
            margin-top: 40px;
        }

        footer span {
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a class="logo" href="index.php">🌱 Fresh <span>Smart Farm</span></a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn-login" href="pages/dashboard.php">Dashboard →</a>
    <?php else: ?>
        <a class="btn-login" href="login.php">Login</a>
    <?php endif; ?>
</nav>

<!-- HERO -->
<div class="hero">
    <h1>🌾 Fresh Smart Farm</h1>
    <p>Sistem pencatatan pertanian modern untuk petani Indonesia.</p>
    <a href="login.php">Mulai Sekarang →</a>
</div>

<!-- FITUR UNGGULAN -->
<section>
    <h2>✨ Fitur Unggulan</h2>
    <div class="fitur-grid">
        <div class="fitur-card">
            <div class="icon">📋</div>
            <h3>Jurnal Tanam</h3>
            <p>Catat semua aktivitas tanam kamu secara terstruktur dan rapi.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">📊</div>
            <h3>Grafik & Statistik</h3>
            <p>Lihat perkembangan hasil panen dalam bentuk grafik yang mudah dipahami.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">💰</div>
            <h3>Harga Pasar</h3>
            <p>Pantau harga komoditas pertanian terkini langsung dari dashboard.</p>
        </div>
        <div class="fitur-card">
            <div class="icon">📰</div>
            <h3>Artikel Pertanian</h3>
            <p>Baca tips dan informasi seputar dunia pertanian terpercaya.</p>
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
    &copy; 2025 <span>Fresh Smart Farm</span> — Dibuat dengan ❤️ oleh siswa SMK Telkom Purwokerto
</footer>

</body>
</html>