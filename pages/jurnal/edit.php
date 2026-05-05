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
    <link rel="stylesheet" href="../../assets/css/form_jurnal.css">
</head>
<body>

<?php include '../../includes/header.php'; ?>

<main class="form-jurnal-page">
    <section class="form-jurnal-hero">
        <div class="form-jurnal-hero__overlay"></div>
        <div class="form-jurnal-hero__content">
            <span class="form-jurnal-label">Edit Jurnal</span>
            <h1>Edit Data Jurnal Tanam</h1>
            <p>Perbarui informasi tanaman Anda untuk catatan yang akurat.</p>
        </div>
    </section>

    <section class="form-jurnal-content">
        <div class="form-jurnal-card">
            <h2>✏️ Edit Data Jurnal Tanam</h2>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nama_tanaman">Nama Tanaman</label>
                    <input type="text" id="nama_tanaman" name="nama_tanaman" value="<?= htmlspecialchars($data['nama_tanaman']) ?>" placeholder="Contoh: Tomat, Cabai, dll" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_tanam">Tanggal Tanam</label>
                    <input type="date" id="tanggal_tanam" name="tanggal_tanam" value="<?= htmlspecialchars($data['tanggal_tanam']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Tanaman</label>
                    <input type="number" id="jumlah" name="jumlah" value="<?= htmlspecialchars($data['jumlah']) ?>" placeholder="Jumlah bibit yang ditanam" min="1" required>
                </div>

                <div class="form-group">
                    <label for="status">Status Tanaman</label>
                    <select id="status" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="Sedang Tanam" <?= $data['status'] == 'Sedang Tanam' ? 'selected' : '' ?>>Sedang Tanam</option>
                        <option value="Sudah Panen" <?= $data['status'] == 'Sudah Panen' ? 'selected' : '' ?>>Sudah Panen</option>
                        <option value="Gagal" <?= $data['status'] == 'Gagal' ? 'selected' : '' ?>>Gagal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hasil_panen">Hasil Panen (kg)</label>
                    <input type="number" id="hasil_panen" name="hasil_panen" value="<?= htmlspecialchars($data['hasil_panen']) ?>" placeholder="0 jika belum panen" min="0" step="0.1">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Data</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>

        <div class="form-jurnal-footer-link">
            <a href="index.php" class="btn btn-secondary">← Kembali ke Jurnal</a>
        </div>
    </section>
</main>

</body>
</html>