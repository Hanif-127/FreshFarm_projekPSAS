<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../includes/koneksi.php';
include '../../includes/module_icons.php';

$dashboard_base_css_version = filemtime(__DIR__ . '/../../assets/css/dashboard_base.css');
$jurnal_tanam_css_version = filemtime(__DIR__ . '/../../assets/css/jurnal_tanam.css');
$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$app_base_path = '';
if (preg_match('~^(.+?)/pages/~', $current_script, $matches)) {
    $app_base_path = rtrim($matches[1], '/');
}

$user_id = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);
$allowed_status = ['Sedang Tanam', 'Sudah Panen', 'Gagal'];

if ($id <= 0) {
    header('Location: index.php?pesan=ID data tidak valid.');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen FROM jurnal_tanam WHERE id = ? AND user_id = ? LIMIT 1'
);

if (!$stmt) {
    header('Location: index.php?pesan=Terjadi kesalahan saat membuka data.');
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result) ?: null;
mysqli_stmt_close($stmt);

if (!$data) {
    header('Location: index.php?pesan=Data tidak ditemukan atau bukan milik Anda.');
    exit;
}

$error = '';
$nama_tanaman = (string) $data['nama_tanaman'];
$tanggal_tanam = (string) $data['tanggal_tanam'];
$jumlah = rtrim(rtrim(number_format((float) $data['jumlah'], 2, '.', ''), '0'), '.');
$status = (string) $data['status'];
$hasil_panen = rtrim(rtrim(number_format((float) $data['hasil_panen'], 2, '.', ''), '0'), '.');
if ($hasil_panen === '') {
    $hasil_panen = '0';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_tanaman = trim($_POST['nama_tanaman'] ?? '');
    $tanggal_tanam = trim($_POST['tanggal_tanam'] ?? '');
    $jumlah = trim((string) ($_POST['jumlah'] ?? ''));
    $status = trim($_POST['status'] ?? '');
    $hasil_panen = trim((string) ($_POST['hasil_panen'] ?? '0'));

    if ($nama_tanaman === '' || $tanggal_tanam === '' || $jumlah === '' || $status === '') {
        $error = 'Mohon lengkapi data tanam dengan benar.';
    } elseif (mb_strlen($nama_tanaman) > 120) {
        $error = 'Nama tanaman maksimal 120 karakter.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Status tanaman tidak valid.';
    } elseif (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $tanggal_tanam) || strtotime($tanggal_tanam) === false) {
        $error = 'Format tanggal tanam tidak valid.';
    } elseif (!is_numeric($jumlah) || (float) $jumlah <= 0) {
        $error = 'Jumlah tanaman harus berupa angka lebih dari 0.';
    } elseif (!is_numeric($hasil_panen) || (float) $hasil_panen < 0) {
        $error = 'Hasil panen harus berupa angka 0 atau lebih.';
    } else {
        $jumlah_float = (float) $jumlah;
        $hasil_panen_float = (float) $hasil_panen;

        if ($status !== 'Sudah Panen') {
            $hasil_panen_float = 0.0;
        }

        $update_stmt = mysqli_prepare(
            $koneksi,
            'UPDATE jurnal_tanam SET nama_tanaman = ?, tanggal_tanam = ?, jumlah = ?, status = ?, hasil_panen = ? WHERE id = ? AND user_id = ?'
        );

        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, 'ssdsdii', $nama_tanaman, $tanggal_tanam, $jumlah_float, $status, $hasil_panen_float, $id, $user_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            header('Location: index.php?pesan=Data tanam berhasil diperbarui.');
            exit;
        }

        $error = 'Gagal memperbarui data. Coba lagi sebentar.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jurnal Tanam</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= (int) $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/jurnal_tanam.css?v=<?= (int) $jurnal_tanam_css_version ?>">
</head>
<body class="module-page">
<?php include '../../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap jurnal-create-wrap">
            <section class="module-card jurnal-create-hero">
                <span class="jurnal-create-badge">Edit Jurnal</span>
                <h1>Perbarui Data Tanam</h1>
                <p>Ubah data jurnal agar laporan, grafik, dan ringkasan dashboard tetap akurat untuk pengujian Anda.</p>
                <div class="jurnal-create-meta">
                    <span class="jurnal-meta-pill">Edit Aman</span>
                    <span class="jurnal-meta-pill">Sinkron ke Dashboard</span>
                </div>
            </section>

            <section class="jurnal-create-layout">
                <article class="module-card jurnal-create-form-card">
                    <h2>Form Edit Jurnal Tanam</h2>

                    <?php if ($error): ?>
                        <div class="module-err"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="jurnal-create-form">
                        <div class="module-grid">
                            <div class="module-field jurnal-field-span-2">
                                <label for="nama_tanaman">Nama Tanaman</label>
                                <input
                                    type="text"
                                    id="nama_tanaman"
                                    name="nama_tanaman"
                                    placeholder="Contoh: Tomat, Cabai Merah, Jagung Manis"
                                    maxlength="120"
                                    value="<?= htmlspecialchars($nama_tanaman) ?>"
                                    required
                                >
                                <small class="jurnal-field-note">Gunakan nama tanaman yang konsisten agar data ringkasan tetap rapi.</small>
                            </div>

                            <div class="module-field">
                                <label for="tanggal_tanam">Tanggal Tanam</label>
                                <input type="date" id="tanggal_tanam" name="tanggal_tanam" value="<?= htmlspecialchars($tanggal_tanam) ?>" required>
                            </div>

                            <div class="module-field">
                                <label for="jumlah">Jumlah Tanaman</label>
                                <input type="number" id="jumlah" name="jumlah" placeholder="Mis. 120" min="0.1" step="0.1" value="<?= htmlspecialchars($jumlah) ?>" required>
                            </div>

                            <div class="module-field">
                                <label for="status">Status Tanaman</label>
                                <select id="status" name="status" required>
                                    <?php foreach ($allowed_status as $status_option): ?>
                                        <option value="<?= htmlspecialchars($status_option) ?>" <?= $status === $status_option ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status_option) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="module-field">
                                <label for="hasil_panen">Hasil Panen (kg)</label>
                                <input type="number" id="hasil_panen" name="hasil_panen" placeholder="0 jika belum panen" min="0" step="0.1" value="<?= htmlspecialchars($hasil_panen) ?>">
                            </div>
                        </div>

                        <div class="module-actions">
                            <button type="submit" class="module-btn module-btn-primary">Simpan Perubahan</button>
                            <a href="index.php" class="module-btn module-btn-ghost">Batal</a>
                        </div>
                    </form>
                </article>

                <aside class="module-card jurnal-create-side-card">
                    <h2>Ringkasan Data</h2>
                    <ul class="jurnal-guide-list">
                        <li>ID data jurnal: <strong>#<?= (int) $id ?></strong></li>
                        <li>Pastikan status sesuai kondisi terbaru di lapangan.</li>
                        <li>Jika belum panen, hasil panen akan otomatis dianggap 0 kg.</li>
                    </ul>

                    <div class="jurnal-preview-box">
                        <div class="jurnal-preview-title">Preview Saat Ini</div>
                        <p><strong>Tanaman:</strong> <?= $nama_tanaman !== '' ? htmlspecialchars($nama_tanaman) : '-' ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
                        <p><strong>Jumlah:</strong> <?= $jumlah !== '' ? htmlspecialchars($jumlah) : '-' ?></p>
                    </div>

                    <a href="index.php" class="module-btn module-btn-muted jurnal-back-link">Kembali ke Jurnal</a>
                </aside>
            </section>
        </div>
    </main>
</div>

<script>
    const statusSelect = document.getElementById('status');
    const hasilPanenInput = document.getElementById('hasil_panen');

    if (statusSelect && hasilPanenInput) {
        const syncHasilPanenState = () => {
            const isPanen = statusSelect.value === 'Sudah Panen';
            hasilPanenInput.disabled = !isPanen;
            if (!isPanen) {
                hasilPanenInput.value = '0';
            } else if (hasilPanenInput.value === '') {
                hasilPanenInput.value = '0';
            }
        };

        syncHasilPanenState();
        statusSelect.addEventListener('change', syncHasilPanenState);
    }
</script>
</body>
</html>
