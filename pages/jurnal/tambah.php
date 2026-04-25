<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
include '../../includes/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id       = $_SESSION['user_id'];
    $nama_tanaman  = trim($_POST['nama_tanaman']);
    $tanggal_tanam = $_POST['tanggal_tanam'];
    $jumlah        = $_POST['jumlah'];
    $status        = $_POST['status'];
    $hasil_panen   = $_POST['hasil_panen'];

    // Cek data lengkap
    if (empty($nama_tanaman) || empty($tanggal_tanam) || empty($jumlah) || empty($status)) {
        $error = "Mohon lengkapi data tanam dengan benar!";
    } else {
        $query = "INSERT INTO jurnal_tanam (user_id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen) 
                  VALUES ('$user_id', '$nama_tanaman', '$tanggal_tanam', '$jumlah', '$status', '$hasil_panen')";
        mysqli_query($koneksi, $query);
        header("Location: index.php?pesan=Data tanam baru berhasil ditambahkan!");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jurnal</title>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<h2>+ Tambah Data Jurnal Tanam</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nama Tanaman:</label><br>
    <input type="text" name="nama_tanaman"><br><br>

    <label>Tanggal Tanam:</label><br>
    <input type="date" name="tanggal_tanam"><br><br>

    <label>Jumlah:</label><br>
    <input type="number" name="jumlah"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="Sedang Tanam">Sedang Tanam</option>
        <option value="Sudah Panen">Sudah Panen</option>
        <option value="Gagal">Gagal</option>
    </select><br><br>

    <label>Hasil Panen (kg):</label><br>
    <input type="number" name="hasil_panen" value="0"><br><br>

    <button type="submit">Simpan</button>
    <a href="index.php">Batal</a>
</form>

</body>
</html>