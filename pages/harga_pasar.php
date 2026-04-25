<?php
session_start();
include '../includes/koneksi.php';

$query = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Harga Pasar - Fresh Smart Farm</title>
</head>
<body>

<h2>💰 Harga Pasar Komoditas</h2>

<?php if (mysqli_num_rows($query) == 0): ?>
    <p>Data tidak tersedia.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Komoditas</th>
            <th>Harga</th>
            <th>Satuan</th>
            <th>Tanggal</th>
            <th>Detail</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
        <t