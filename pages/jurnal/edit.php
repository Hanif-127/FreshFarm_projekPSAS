<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
include '../../includes/koneksi.php';

$id    = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM jurnal_tanam WHERE id = $id AND user_id = {$_SESSION['user_id']}");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_tanaman  = trim($_POST['nama_tanaman']);
    $tanggal_tanam = $_POST['tanggal_tanam'];
    $jumlah        = $_POST['jumlah'];
    $status        = $_POST['status'];
    $hasil_panen   = $_POST['hasil_panen'];

    if (empty($nama_tanaman) || empty($tanggal_tanam) || empty($jumlah) || empty($status)) {
        $error = "Mohon lengkapi data tanam dengan benar!";
    } else {
        $update = "UPDATE jurnal_tanam SET 
                    nama_tanaman='$nama_tanaman',
                    tanggal_tanam='$tanggal_tanam',
                    jumlah='$jumlah',
                    status='$status',
                    hasil_panen='$hasil_panen'
                   WHERE id=$id AND user_id={$_SESSION['user_id']}";
        mysqli_query($koneksi, $update);
        header("Location: index.php?pesan=Data tanam berhasil diperbarui!");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jurnal</title>
</head>
<body>

<h2>✏️ Edit Data Jurnal Tanam</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nama Tanaman:</label><br>
    <input type="text" name="nama_tanaman" value="<?= $data['nama_tanaman'] ?>"><br><br>

    <label>Tanggal Tanam:</label><br>
    <input type="date" name="tanggal_tanam" value="<?= $data['tanggal_tanam'] ?>"><br><br>

    <label>Jumlah:</label><br>
    <input type="number" name="jumlah" value="<?= $data['jumlah'] ?>"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option <?= $data['status'] == 'Sedang Tanam' ? 'selected' : '' ?>>Sedang Tanam</option>
        <option <?= $data['status'] == 'Sudah Panen' ? 'selected' : '' ?>>Sudah Panen</option>
        <option <?= $data['status'] == 'Gagal' ? 'selected' : '' ?>>Gagal</option>
    </select><br><br>

    <label>Hasil Panen (kg):</label><br>
    <input type="number" name="hasil_panen" value="<?= $data['hasil_panen'] ?>"><br><br>

    <button type="submit">Update</button>
    <a href="index.php">Batal</a>
</form>

</body>
</html>