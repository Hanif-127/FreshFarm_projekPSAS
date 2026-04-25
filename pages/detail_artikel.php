<?php
session_start();
include '../includes/koneksi.php';

$id    = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM artikel WHERE id=$id");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Artikel tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $data['judul'] ?> - Fresh Smart Farm</title>
</head>
<body>

<h2><?= $data['judul'] ?></h2>
<p style="color:gray;">📅 <?= $data['tanggal_publish'] ?></p>

<?php if ($data['gambar']): ?>
    <img src="../assets/images/<?= $data['gambar'] ?>" style="max-width:500px;"><br><br>
<?php endif; ?>

<p><?= nl2br($data['isi']) ?></p>

<br>
<a href="artikel.php">← Kembali ke Daftar Artikel</a>

</body>
</html>