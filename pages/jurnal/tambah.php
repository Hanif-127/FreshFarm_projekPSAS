<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../includes/koneksi.php';

$dashboard_css_version = filemtime(__DIR__ . '/../../assets/css/dashboard.css');
$form_jurnal_css_version = filemtime(__DIR__ . '/../../assets/css/form_jurnal.css');
$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$app_base_path = '';
if (preg_match('~^(.+?)/pages/~', $current_script, $matches)) {
    $app_base_path = rtrim($matches[1], '/');
}

$error = '';
$nama_tanaman = '';
$tanggal_tanam = date('Y-m-d');
$jumlah = '';
$status = 'Sedang Tanam';
$hasil_panen = '0';
$allowed_status = ['Sedang Tanam', 'Sudah Panen', 'Gagal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_SESSION['user_id'];
    $nama_tanaman = trim($_POST['nama_tanaman'] ?? '');
    $tanggal_tanam = trim($_POST['tanggal_tanam'] ?? '');
    $jumlah = trim((string) ($_POST['jumlah'] ?? ''));
    $status = trim($_POST['status'] ?? '');
    $hasil_panen = trim((string) ($_POST['hasil_panen'] ?? '0'));

    if ($nama_tanaman === '' || $tanggal_tanam === '' || $jumlah === '' || $status === '') {
        $error = 'Mohon lengkapi data tanam dengan benar!';
    } elseif (mb_strlen($nama_tanaman) > 120) {
        $error = 'Nama tanaman maksimal 120 karakter.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Status tanaman tidak valid.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_tanam) || strtotime($tanggal_tanam) === false) {
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

        $stmt = mysqli_prepare(
            $koneksi,
            'INSERT INTO jurnal_tanam (user_id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen) VALUES (?, ?, ?, ?, ?, ?)'
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'issdsd', $user_id, $nama_tanaman, $tanggal_tanam, $jumlah_float, $status, $hasil_panen_float);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header('Location: index.php?pesan=Data tanam baru berhasil ditambahkan!');
            exit;
        }

        $error = 'Gagal menyimpan data. Coba lagi sebentar.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jurnal Tanam</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard.css?v=<?= (int) $dashboard_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/form_jurnal.css?v=<?= (int) $form_jurnal_css_version ?>">
</head>
<body class="module-page">
<?php include '../../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap jurnal-create-wrap">
            <section class="module-card jurnal-create-hero">
                <span class="jurnal-create-badge">Tambah Jurnal</span>
                <h1>Tambah Data Tanam Baru</h1>
                <p>Isi catatan tanam harian dengan ringkas agar perkembangan tanaman dan hasil panen mudah dipantau.</p>
                <div class="jurnal-create-meta">
                    <span class="jurnal-meta-pill">Form Cepat</span>
                    <span class="jurnal-meta-pill">Terhubung ke Dashboard</span>
                </div>
            </section>

            <section class="jurnal-create-layout">
                <article class="module-card jurnal-create-form-card">
                    <h2>Form Jurnal Tanam</h2>

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
                                <small class="jurnal-field-note">Gunakan nama yang konsisten agar laporan mudah dibaca.</small>
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
                            <button type="submit" class="module-btn module-btn-primary">Simpan Data</button>
                            <a href="index.php" class="module-btn module-btn-ghost">Batal</a>
                        </div>
                    </form>
                </article>

                <aside class="module-card jurnal-create-side-card">
                    <h2>Panduan Singkat</h2>
                    <ul class="jurnal-guide-list">
                        <li>Isi jumlah dalam angka nyata, boleh desimal.</li>
                        <li>Jika status belum panen, hasil panen akan dianggap 0 kg.</li>
                        <li>Gunakan nama tanaman yang sama untuk data berulang.</li>
                    </ul>

                    <div class="jurnal-preview-box">
                        <div class="jurnal-preview-title">Ringkasan Input</div>
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
