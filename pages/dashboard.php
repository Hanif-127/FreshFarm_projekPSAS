<?php
session_start();

// Kalau belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/koneksi.php';

// Ambil jumlah jurnal milik user yang login
$user_id = $_SESSION['user_id'];
$query   = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jurnal_tanam WHERE user_id = $user_id");
$data    = mysqli_fetch_assoc($query);
$total   = $data['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Fresh Smart Farm</title>
</head>
<body>

<h2>Selamat datang, <?= $_SESSION['username'] ?>! 🌱</h2>

<p>Kamu punya <strong><?= $total ?></strong> data jurnal tanam.</p>

<hr>

<h3>Menu</h3>
<ul>
    <li><a href="jurnal/index.php">📋 Jurnal Tanam</a></li>
    <li><a href="artikel.php">📰 Artikel</a></li>
    <li><a href="harga_pasar.php">💰 Harga Pasar</a></li>
    <li><a href="grafik.php">📊 Grafik & Statistik</a></li>
</ul>

<hr>
<a href="../logout.php">🚪 Logout</a>

</body>
</html>