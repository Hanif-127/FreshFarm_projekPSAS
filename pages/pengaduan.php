<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';
include '../includes/module_icons.php';
$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$pengaduan_css_version = filemtime(__DIR__ . '/../assets/css/pengaduan.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

$user_id = (int) $_SESSION['user_id'];
$error = '';
$allowed_prioritas = ['rendah', 'sedang', 'tinggi'];
$allowed_status = ['dikirim', 'diproses', 'selesai', 'ditolak'];

if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    if ($hapus_id > 0) {
        $delete_stmt = mysqli_prepare($koneksi, "DELETE FROM pengaduan WHERE id = ? AND user_id = ? AND status = 'dikirim'");
        if ($delete_stmt) {
            mysqli_stmt_bind_param($delete_stmt, "ii", $hapus_id, $user_id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }
    }
    header("Location: pengaduan.php?pesan=Pengaduan berhasil dihapus");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    $prioritas = trim($_POST['prioritas'] ?? 'sedang');

    if ($judul === '' || $pesan === '') {
        $error = 'Judul dan isi pengaduan wajib diisi.';
    } elseif (!in_array($prioritas, $allowed_prioritas, true)) {
        $error = 'Prioritas pengaduan tidak valid.';
    } elseif (mb_strlen($judul) > 180) {
        $error = 'Judul maksimal 180 karakter.';
    } elseif (mb_strlen($pesan) > 4000) {
        $error = 'Isi pengaduan maksimal 4000 karakter.';
    } else {
        $insert_stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO pengaduan (user_id, judul, pesan, prioritas, status)
             VALUES (?, ?, ?, ?, 'dikirim')"
        );
        if ($insert_stmt) {
            mysqli_stmt_bind_param($insert_stmt, "isss", $user_id, $judul, $pesan, $prioritas);
            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            header("Location: pengaduan.php?pesan=Pengaduan berhasil dikirim");
            exit;
        }
        $error = 'Gagal mengirim pengaduan.';
    }
}

$pengaduan_stmt = mysqli_prepare($koneksi, "SELECT * FROM pengaduan WHERE user_id = ? ORDER BY created_at DESC");
$pengaduan_list = [];
if ($pengaduan_stmt) {
    mysqli_stmt_bind_param($pengaduan_stmt, "i", $user_id);
    mysqli_stmt_execute($pengaduan_stmt);
    $pengaduan_result = mysqli_stmt_get_result($pengaduan_stmt);
    $pengaduan_list = mysqli_fetch_all($pengaduan_result, MYSQLI_ASSOC);
    mysqli_stmt_close($pengaduan_stmt);
}

function status_badge_class(string $status): string {
    return match ($status) {
        'dikirim' => 'module-status module-status-info',
        'diproses' => 'module-status module-status-warning',
        'selesai' => 'module-status module-status-success',
        'ditolak' => 'module-status module-status-danger',
        default => 'module-status module-status-neutral',
    };
}

function prioritas_badge_class(string $prioritas): string {
    return match ($prioritas) {
        'tinggi' => 'module-status module-status-danger',
        'sedang' => 'module-status module-status-warning',
        default => 'module-status module-status-success',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaduan - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/pengaduan.css?v=<?= $pengaduan_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap">
    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('complaint') ?></span><span>Kirim Pengaduan</span></h2>
        <?php if (isset($_GET['pesan'])): ?><div class="module-msg"><?= htmlspecialchars($_GET['pesan']) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="module-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="module-grid">
                <div class="module-field">
                    <label>Judul Pengaduan</label>
                    <input type="text" name="judul" required maxlength="180" placeholder="Contoh: Halaman lambat saat simpan data">
                </div>
                <div class="module-field">
                    <label>Prioritas</label>
                    <select name="prioritas">
                        <option value="rendah">Rendah</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="tinggi">Tinggi</option>
                    </select>
                </div>
            </div>
            <div class="module-field module-field-gap">
                <label>Isi Pengaduan</label>
                <textarea name="pesan" required maxlength="4000" placeholder="Jelaskan masalah yang terjadi, langkah reproduksi, dan dampaknya."></textarea>
            </div>
            <div class="module-actions">
                <button class="module-btn module-btn-primary" type="submit"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('plus') ?></span><span>Kirim Pengaduan</span></button>
            </div>
        </form>
    </section>

    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('list') ?></span><span>Riwayat Pengaduan Anda</span></h2>
        <?php if (count($pengaduan_list) === 0): ?>
            <p class="module-empty">Belum ada pengaduan yang dikirim.</p>
        <?php else: ?>
            <div class="module-list">
                <?php foreach ($pengaduan_list as $row): ?>
                    <?php
                    $status = in_array($row['status'], $allowed_status, true) ? $row['status'] : 'dikirim';
                    ?>
                    <article class="module-item">
                        <div class="module-item-title">
                            <?= htmlspecialchars($row['judul']) ?>
                            <span class="<?= htmlspecialchars(status_badge_class($status)) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                        </div>
                        <div class="module-meta">
                            Prioritas: <span class="complaint-priority <?= htmlspecialchars(prioritas_badge_class((string) $row['prioritas'])) ?>"><?= htmlspecialchars(ucfirst($row['prioritas'])) ?></span> |
                            Dikirim: <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($row['pesan'])) ?></p>
                        <?php if (!empty($row['respon_admin'])): ?>
                            <div class="module-meta"><strong>Respon Admin:</strong> <?= nl2br(htmlspecialchars($row['respon_admin'])) ?></div>
                        <?php endif; ?>
                        <?php if ($status === 'dikirim'): ?>
                            <a class="module-danger-link" href="pengaduan.php?hapus=<?= (int) $row['id'] ?>" onclick="return confirm('Hapus pengaduan ini?')">Hapus Pengaduan</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
        </div>
    </main>
</div>
</body>
</html>
