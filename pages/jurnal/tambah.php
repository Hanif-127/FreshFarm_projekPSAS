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
    <link rel="stylesheet" href="../../assets/css/form_jurnal.css">
</head>
<body>

<?php include '../../includes/header.php'; ?>

<main class="form-jurnal-page">
    <section class="form-jurnal-hero">
        <div class="form-jurnal-hero__overlay"></div>
        <div class="form-jurnal-hero__content">
            <span class="form-jurnal-label">Tambah Jurnal</span>
            <h1>Tambah Data Tanam Baru</h1>
            <p>Catat aktivitas tanam Anda untuk memantau perkembangan tanaman.</p>
        </div>
    </section>

    <section class="form-jurnal-content">
        <div class="form-jurnal-card">
            <h2>+ Tambah Data Jurnal Tanam</h2>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nama_tanaman">Nama Tanaman</label>
                    <input type="text" id="nama_tanaman" name="nama_tanaman" placeholder="Contoh: Tomat, Cabai, dll" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_tanam">Tanggal Tanam</label>
                    <input type="date" id="tanggal_tanam" name="tanggal_tanam" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Tanaman</label>
                    <input type="number" id="jumlah" name="jumlah" placeholder="Jumlah bibit yang ditanam" min="1" required>
                </div>

                <div class="form-group">
                    <label for="status">Status Tanaman</label>
                    <select id="status" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="Sedang Tanam">Sedang Tanam</option>
                        <option value="Sudah Panen">Sudah Panen</option>
                        <option value="Gagal">Gagal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hasil_panen">Hasil Panen (kg)</label>
                    <input type="number" id="hasil_panen" name="hasil_panen" placeholder="0 jika belum panen" min="0" step="0.1" value="0">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
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