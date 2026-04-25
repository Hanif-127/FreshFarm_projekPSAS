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
</head>
<body>

<h2>📦 <?= $data['nama_komoditas'] ?></h2>

<table border="1" cellpadding="8">
    <tr>
        <td>Harga</td>
        <td>Rp <?= number_format($data['harga'], 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td>Satuan</td>
        <td><?= $data['satuan'] ?></td>
    </tr>
    <tr>
        <td>Tanggal Update</td>
        <td><?= $data['tanggal'] ?></td>
    </tr>
</table>

<br>
<a href="harga_pasar.php">← Kembali ke Daftar Harga</a>

</body>
</html>