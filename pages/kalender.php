<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';
include '../includes/module_icons.php';
$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$kalender_css_version = filemtime(__DIR__ . '/../assets/css/kalender_tanam.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

$user_id = (int) $_SESSION['user_id'];
$error = '';
$edit_data = null;

$allowed_tipe = ['tanam', 'pupuk', 'siram', 'panen', 'lainnya'];
$allowed_status = ['terjadwal', 'selesai', 'terlewat', 'dibatalkan'];
$max_text_len = 120;
$max_note_len = 2000;
$max_reminder = 365;

$q = trim($_GET['q'] ?? '');
$q = mb_substr($q, 0, 100);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;

function is_valid_date(string $date): bool {
    if ($date === '') {
        return false;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}

function is_valid_time_or_empty(string $time): bool {
    if ($time === '') {
        return true;
    }
    return preg_match('/^\d{2}:\d{2}$/', $time) === 1;
}

function page_link(int $target_page, string $q): string {
    $params = ['page' => $target_page];
    if ($q !== '') {
        $params['q'] = $q;
    }
    return 'kalender.php?' . http_build_query($params);
}

function kalender_link(array $params, string $q, int $page): string {
    if ($q !== '') {
        $params['q'] = $q;
    }
    $params['page'] = $page;
    return 'kalender.php?' . http_build_query($params);
}

function kalender_status_badge_class(string $status): string {
    return match ($status) {
        'selesai' => 'module-status module-status-success',
        'terlewat', 'dibatalkan' => 'module-status module-status-danger',
        'terjadwal' => 'module-status module-status-info',
        default => 'module-status module-status-neutral',
    };
}

if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    if ($hapus_id > 0) {
        $delete_stmt = mysqli_prepare($koneksi, "DELETE FROM kalender_tanam WHERE id = ? AND user_id = ?");
        if ($delete_stmt) {
            mysqli_stmt_bind_param($delete_stmt, "ii", $hapus_id, $user_id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }
    }
    $redirect_params = ['pesan' => 'Jadwal berhasil dihapus'];
    if ($q !== '') {
        $redirect_params['q'] = $q;
    }
    header("Location: kalender.php?" . http_build_query($redirect_params));
    exit;
}

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    if ($edit_id > 0) {
        $edit_stmt = mysqli_prepare($koneksi, "SELECT * FROM kalender_tanam WHERE id = ? AND user_id = ? LIMIT 1");
        if ($edit_stmt) {
            mysqli_stmt_bind_param($edit_stmt, "ii", $edit_id, $user_id);
            mysqli_stmt_execute($edit_stmt);
            $edit_result = mysqli_stmt_get_result($edit_stmt);
            $edit_data = mysqli_fetch_assoc($edit_result) ?: null;
            mysqli_stmt_close($edit_stmt);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nama_kegiatan = trim($_POST['nama_kegiatan'] ?? '');
    $tipe_kegiatan = trim($_POST['tipe_kegiatan'] ?? '');
    $komoditas = trim($_POST['komoditas'] ?? '');
    $tanggal_jadwal = trim($_POST['tanggal_jadwal'] ?? '');
    $jam_jadwal = trim($_POST['jam_jadwal'] ?? '');
    $pengingat_hari_raw = $_POST['pengingat_hari'] ?? '0';
    $status = trim($_POST['status'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($nama_kegiatan === '' || $tanggal_jadwal === '') {
        $error = 'Nama kegiatan dan tanggal jadwal wajib diisi.';
    } elseif (!in_array($tipe_kegiatan, $allowed_tipe, true)) {
        $error = 'Tipe kegiatan tidak valid.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Status jadwal tidak valid.';
    } elseif (!is_valid_date($tanggal_jadwal)) {
        $error = 'Format tanggal jadwal tidak valid.';
    } elseif (!is_valid_time_or_empty($jam_jadwal)) {
        $error = 'Format jam jadwal tidak valid.';
    } elseif (!is_numeric($pengingat_hari_raw)) {
        $error = 'Pengingat harus berupa angka.';
    } else {
        $pengingat_hari = (int) $pengingat_hari_raw;
        if ($pengingat_hari < 0 || $pengingat_hari > $max_reminder) {
            $error = 'Pengingat harus antara 0 sampai 365 hari.';
        } elseif (mb_strlen($nama_kegiatan) > $max_text_len) {
            $error = 'Nama kegiatan maksimal 120 karakter.';
        } elseif ($komoditas !== '' && mb_strlen($komoditas) > $max_text_len) {
            $error = 'Komoditas maksimal 120 karakter.';
        } elseif ($catatan !== '' && mb_strlen($catatan) > $max_note_len) {
            $error = 'Catatan maksimal 2000 karakter.';
        } else {
            $jam_sql = $jam_jadwal === '' ? null : $jam_jadwal . ':00';
            $komoditas_sql = $komoditas === '' ? null : $komoditas;
            $catatan_sql = $catatan === '' ? null : $catatan;

            if ($id > 0) {
                $update_stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE kalender_tanam SET
                        nama_kegiatan = ?,
                        tipe_kegiatan = ?,
                        komoditas = ?,
                        tanggal_jadwal = ?,
                        jam_jadwal = ?,
                        pengingat_hari = ?,
                        catatan = ?,
                        status = ?
                     WHERE id = ? AND user_id = ?"
                );
                if ($update_stmt) {
                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "sssssissii",
                        $nama_kegiatan,
                        $tipe_kegiatan,
                        $komoditas_sql,
                        $tanggal_jadwal,
                        $jam_sql,
                        $pengingat_hari,
                        $catatan_sql,
                        $status,
                        $id,
                        $user_id
                    );
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    header("Location: kalender.php?pesan=Jadwal berhasil diperbarui");
                    exit;
                }
                $error = 'Gagal memperbarui jadwal.';
            } else {
                $insert_stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO kalender_tanam
                    (user_id, nama_kegiatan, tipe_kegiatan, komoditas, tanggal_jadwal, jam_jadwal, pengingat_hari, catatan, status)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                if ($insert_stmt) {
                    mysqli_stmt_bind_param(
                        $insert_stmt,
                        "isssssiss",
                        $user_id,
                        $nama_kegiatan,
                        $tipe_kegiatan,
                        $komoditas_sql,
                        $tanggal_jadwal,
                        $jam_sql,
                        $pengingat_hari,
                        $catatan_sql,
                        $status
                    );
                    mysqli_stmt_execute($insert_stmt);
                    mysqli_stmt_close($insert_stmt);
                    header("Location: kalender.php?pesan=Jadwal berhasil ditambahkan");
                    exit;
                }
                $error = 'Gagal menyimpan jadwal.';
            }
        }
    }
}

$total_rows = 0;
if ($q !== '') {
    $search = '%' . $q . '%';
    $count_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM kalender_tanam
         WHERE user_id = ?
         AND (nama_kegiatan LIKE ? OR tipe_kegiatan LIKE ? OR COALESCE(komoditas, '') LIKE ? OR status LIKE ?)"
    );
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "issss", $user_id, $search, $search, $search, $search);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
} else {
    $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM kalender_tanam WHERE user_id = ?");
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "i", $user_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
}

$total_pages = max(1, (int) ceil($total_rows / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

$jadwal = [];
if ($q !== '') {
    $search = '%' . $q . '%';
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM kalender_tanam
         WHERE user_id = ?
         AND (nama_kegiatan LIKE ? OR tipe_kegiatan LIKE ? OR COALESCE(komoditas, '') LIKE ? OR status LIKE ?)
         ORDER BY tanggal_jadwal ASC, jam_jadwal ASC
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "issssii", $user_id, $search, $search, $search, $search, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $list_result = mysqli_stmt_get_result($list_stmt);
        $jadwal = mysqli_fetch_all($list_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
} else {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM kalender_tanam
         WHERE user_id = ?
         ORDER BY tanggal_jadwal ASC, jam_jadwal ASC
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "iii", $user_id, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $list_result = mysqli_stmt_get_result($list_stmt);
        $jadwal = mysqli_fetch_all($list_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
}

$start_row = $total_rows > 0 ? $offset + 1 : 0;
$end_row = min($offset + $per_page, $total_rows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Tanam - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/kalender_tanam.css?v=<?= $kalender_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>
<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap">
    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('calendar') ?></span><span><?= $edit_data ? 'Edit Jadwal Tanam' : 'Tambah Jadwal Tanam' ?></span></h2>
        <?php if (isset($_GET['pesan'])): ?><div class="module-msg"><?= htmlspecialchars($_GET['pesan']) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="module-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data ? (int) $edit_data['id'] : 0 ?>">
            <div class="module-grid">
                <div class="module-field">
                    <label>Nama Kegiatan</label>
                    <input type="text" name="nama_kegiatan" maxlength="120" required value="<?= htmlspecialchars($edit_data['nama_kegiatan'] ?? '') ?>">
                </div>
                <div class="module-field">
                    <label>Tipe</label>
                    <select name="tipe_kegiatan">
                        <?php foreach ($allowed_tipe as $tipe): ?>
                            <option value="<?= $tipe ?>" <?= (($edit_data['tipe_kegiatan'] ?? 'tanam') === $tipe) ? 'selected' : '' ?>><?= ucfirst($tipe) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="module-field">
                    <label>Komoditas</label>
                    <input type="text" name="komoditas" maxlength="120" value="<?= htmlspecialchars($edit_data['komoditas'] ?? '') ?>">
                </div>
                <div class="module-field">
                    <label>Tanggal Jadwal</label>
                    <input type="date" name="tanggal_jadwal" required value="<?= htmlspecialchars($edit_data['tanggal_jadwal'] ?? '') ?>">
                </div>
                <div class="module-field">
                    <label>Jam Jadwal</label>
                    <input type="time" name="jam_jadwal" value="<?= htmlspecialchars(($edit_data['jam_jadwal'] ?? '') !== '' ? substr((string) $edit_data['jam_jadwal'], 0, 5) : '') ?>">
                </div>
                <div class="module-field">
                    <label>Pengingat (hari sebelumnya)</label>
                    <input type="number" name="pengingat_hari" min="0" max="365" value="<?= htmlspecialchars((string) ($edit_data['pengingat_hari'] ?? 1)) ?>">
                </div>
                <div class="module-field">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach ($allowed_status as $status): ?>
                            <option value="<?= $status ?>" <?= (($edit_data['status'] ?? 'terjadwal') === $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="module-field module-field-gap">
                <label>Catatan</label>
                <textarea name="catatan" maxlength="2000"><?= htmlspecialchars($edit_data['catatan'] ?? '') ?></textarea>
            </div>
            <div class="module-actions">
                <button class="module-btn module-btn-primary" type="submit"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon($edit_data ? 'settings' : 'plus') ?></span><span><?= $edit_data ? 'Update Jadwal' : 'Simpan Jadwal' ?></span></button>
                <?php if ($edit_data): ?><a class="module-btn module-btn-muted" href="kalender.php">Batal Edit</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('list') ?></span><span>Daftar Jadwal</span></h2>
        <div class="module-toolbar">
            <form class="module-search" method="GET">
                <div class="module-field">
                    <label>Cari jadwal</label>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nama kegiatan, tipe, komoditas, status">
                </div>
                <button class="module-btn module-btn-primary" type="submit"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('filter') ?></span><span>Cari</span></button>
                <a class="module-btn module-btn-ghost" href="kalender.php">Reset</a>
            </form>
        </div>
        <?php if (count($jadwal) === 0): ?>
            <p class="module-empty">Belum ada jadwal tanam.</p>
        <?php else: ?>
            <div class="module-list">
                <?php foreach ($jadwal as $row): ?>
                    <article class="module-item">
                        <div class="module-item-title">
                            <?= htmlspecialchars($row['nama_kegiatan']) ?>
                            <span class="<?= htmlspecialchars(kalender_status_badge_class((string) $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                        </div>
                        <div class="module-meta">
                            Tipe: <?= htmlspecialchars($row['tipe_kegiatan']) ?> |
                            Komoditas: <?= htmlspecialchars((string) $row['komoditas']) ?> |
                            Tanggal: <?= date('d M Y', strtotime($row['tanggal_jadwal'])) ?><?= $row['jam_jadwal'] ? ' ' . substr((string) $row['jam_jadwal'], 0, 5) : '' ?>
                        </div>
                        <?php if (!empty($row['catatan'])): ?><div class="module-meta">Catatan: <?= htmlspecialchars($row['catatan']) ?></div><?php endif; ?>
                        <div class="module-item-actions">
                            <a href="<?= htmlspecialchars(kalender_link(['edit' => (int) $row['id']], $q, $page)) ?>">Edit</a>
                            <a class="module-danger-link" href="<?= htmlspecialchars(kalender_link(['hapus' => (int) $row['id']], $q, $page)) ?>" onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="module-pagination">
                <div class="module-meta">Menampilkan <?= $start_row ?>-<?= $end_row ?> dari <?= $total_rows ?> data</div>
                <?php if ($total_pages > 1): ?>
                    <div class="module-pagination-pages">
                        <?php if ($page > 1): ?>
                            <a class="module-page-link" href="<?= htmlspecialchars(page_link($page - 1, $q)) ?>">Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a class="module-page-link <?= $i === $page ? 'is-active' : '' ?>" href="<?= htmlspecialchars(page_link($i, $q)) ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a class="module-page-link" href="<?= htmlspecialchars(page_link($page + 1, $q)) ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
        </div>
    </main>
</div>
</body>
</html>
