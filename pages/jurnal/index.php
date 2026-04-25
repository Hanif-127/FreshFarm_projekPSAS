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
</head>
<body>

<?php include '../../includes/header.php'; ?>

<h2>📋 Jurnal Tanam Saya</h2>

<?php if (isset($_GET['pesan'])): ?>
    <p style="color:green;"><?= $_GET['pesan'] ?></p>
<?php endif; ?>

<a href="tambah.php">+ Tambah Data</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>No</th>
        <th>Tanaman</th>
        <th>Tanggal Tanam</th>
        <th>Jumlah</th>
        <th>Status</th>
        <th>Hasil Panen</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_tanaman'] ?></td>
        <td><?= $row['tanggal_tanam'] ?></td>
        <td><?= $row['jumlah'] ?></td>
        <td><?= $row['status'] ?></td>
        <td><?= $row['hasil_panen'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br><a href="../dashboard.php">← Kembali ke Dashboard</a>

</body>
</html>