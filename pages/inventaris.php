<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';
include '../includes/module_icons.php';
$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$inventaris_css_version = filemtime(__DIR__ . '/../assets/css/inventaris.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

$user_id = (int) $_SESSION['user_id'];
$error = '';
$edit_data = null;

$allowed_kategori = ['benih', 'pupuk', 'pestisida', 'alat', 'lainnya'];
$max_name_len = 120;
$max_unit_len = 30;
$max_location_len = 120;
$max_note_len = 2000;
$max_stock = 9999999999.99;

$q = trim($_GET['q'] ?? '');
$q = mb_substr($q, 0, 100);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;

function inventaris_page_link(int $target_page, string $q): string {
    $params = ['page' => $target_page];
    if ($q !== '') {
        $params['q'] = $q;
    }
    return 'inventaris.php?' . http_build_query($params);
}

function inventaris_link(array $params, string $q, int $page): string {
    if ($q !== '') {
        $params['q'] = $q;
    }
    $params['page'] = $page;
    return 'inventaris.php?' . http_build_query($params);
}

if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    if ($hapus_id > 0) {
        $delete_stmt = mysqli_prepare($koneksi, "DELETE FROM inventaris WHERE id = ? AND user_id = ?");
        if ($delete_stmt) {
            mysqli_stmt_bind_param($delete_stmt, "ii", $hapus_id, $user_id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }
    }
    $redirect_params = ['pesan' => 'Item berhasil dihapus'];
    if ($q !== '') {
        $redirect_params['q'] = $q;
    }
    header("Location: inventaris.php?" . http_build_query($redirect_params));
    exit;
}

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    if ($edit_id > 0) {
        $edit_stmt = mysqli_prepare($koneksi, "SELECT * FROM inventaris WHERE id = ? AND user_id = ? LIMIT 1");
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
    $nama_item = trim($_POST['nama_item'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jumlah_stok_raw = $_POST['jumlah_stok'] ?? '0';
    $satuan = trim($_POST['satuan'] ?? '');
    $stok_minimum_raw = $_POST['stok_minimum'] ?? '0';
    $lokasi_simpan = trim($_POST['lokasi_simpan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($nama_item === '' || $satuan === '') {
        $error = 'Nama item dan satuan wajib diisi.';
    } elseif (!in_array($kategori, $allowed_kategori, true)) {
        $error = 'Kategori inventaris tidak valid.';
    } elseif (!is_numeric($jumlah_stok_raw) || !is_numeric($stok_minimum_raw)) {
        $error = 'Jumlah stok dan stok minimum harus berupa angka.';
    } else {
        $jumlah_stok = (float) $jumlah_stok_raw;
        $stok_minimum = (float) $stok_minimum_raw;

        if ($jumlah_stok < 0 || $jumlah_stok > $max_stock) {
            $error = 'Jumlah stok harus antara 0 sampai 9.999.999.999,99.';
        } elseif ($stok_minimum < 0 || $stok_minimum > $max_stock) {
            $error = 'Stok minimum harus antara 0 sampai 9.999.999.999,99.';
        } elseif (mb_strlen($nama_item) > $max_name_len) {
            $error = 'Nama item maksimal 120 karakter.';
        } elseif (mb_strlen($satuan) > $max_unit_len) {
            $error = 'Satuan maksimal 30 karakter.';
        } elseif ($lokasi_simpan !== '' && mb_strlen($lokasi_simpan) > $max_location_len) {
            $error = 'Lokasi simpan maksimal 120 karakter.';
        } elseif ($catatan !== '' && mb_strlen($catatan) > $max_note_len) {
            $error = 'Catatan maksimal 2000 karakter.';
        } else {
            $lokasi_sql = $lokasi_simpan === '' ? null : $lokasi_simpan;
            $catatan_sql = $catatan === '' ? null : $catatan;

            if ($id > 0) {
                $update_stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE inventaris SET
                        nama_item = ?,
                        kategori = ?,
                        jumlah_stok = ?,
                        satuan = ?,
                        stok_minimum = ?,
                        lokasi_simpan = ?,
                        catatan = ?
                     WHERE id = ? AND user_id = ?"
                );
                if ($update_stmt) {
                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "ssdsdssii",
                        $nama_item,
                        $kategori,
                        $jumlah_stok,
                        $satuan,
                        $stok_minimum,
                        $lokasi_sql,
                        $catatan_sql,
                        $id,
                        $user_id
                    );
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    header("Location: inventaris.php?pesan=Item berhasil diperbarui");
                    exit;
                }
                $error = 'Gagal memperbarui item inventaris.';
            } else {
                $insert_stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO inventaris
                    (user_id, nama_item, kategori, jumlah_stok, satuan, stok_minimum, lokasi_simpan, catatan)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                if ($insert_stmt) {
                    mysqli_stmt_bind_param(
                        $insert_stmt,
                        "issdsdss",
                        $user_id,
                        $nama_item,
                        $kategori,
                        $jumlah_stok,
                        $satuan,
                        $stok_minimum,
                        $lokasi_sql,
                        $catatan_sql
                    );
                    mysqli_stmt_execute($insert_stmt);
                    mysqli_stmt_close($insert_stmt);
                    header("Location: inventaris.php?pesan=Item berhasil ditambahkan");
                    exit;
                }
                $error = 'Gagal menyimpan inventaris.';
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
         FROM inventaris
         WHERE user_id = ?
         AND (nama_item LIKE ? OR kategori LIKE ? OR satuan LIKE ? OR COALESCE(lokasi_simpan, '') LIKE ?)"
    );
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "issss", $user_id, $search, $search, $search, $search);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
} else {
    $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM inventaris WHERE user_id = ?");
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

$inventaris = [];
if ($q !== '') {
    $search = '%' . $q . '%';
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM inventaris
         WHERE user_id = ?
         AND (nama_item LIKE ? OR kategori LIKE ? OR satuan LIKE ? OR COALESCE(lokasi_simpan, '') LIKE ?)
         ORDER BY updated_at DESC
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "issssii", $user_id, $search, $search, $search, $search, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $list_result = mysqli_stmt_get_result($list_stmt);
        $inventaris = mysqli_fetch_all($list_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
} else {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM inventaris
         WHERE user_id = ?
         ORDER BY updated_at DESC
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "iii", $user_id, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $list_result = mysqli_stmt_get_result($list_stmt);
        $inventaris = mysqli_fetch_all($list_result, MYSQLI_ASSOC);
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
    <title>Inventaris - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/inventaris.css?v=<?= $inventaris_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>
<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap">
    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('inventory') ?></span><span><?= $edit_data ? 'Edit Inventaris' : 'Tambah Inventaris' ?></span></h2>
        <?php if (isset($_GET['pesan'])): ?><div class="module-msg"><?= htmlspecialchars($_GET['pesan']) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="module-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data ? (int) $edit_data['id'] : 0 ?>">
            <div class="module-grid">
                <div class="module-field">
                    <label>Nama Item</label>
                    <input type="text" name="nama_item" maxlength="120" required value="<?= htmlspecialchars($edit_data['nama_item'] ?? '') ?>">
                </div>
                <div class="module-field">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php foreach ($allowed_kategori as $kategori): ?>
                            <option value="<?= $kategori ?>" <?= (($edit_data['kategori'] ?? 'lainnya') === $kategori) ? 'selected' : '' ?>><?= ucfirst($kategori) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="module-field">
                    <label>Jumlah Stok</label>
                    <input type="number" step="0.01" min="0" max="9999999999.99" name="jumlah_stok" value="<?= htmlspecialchars((string) ($edit_data['jumlah_stok'] ?? '0')) ?>">
                </div>
                <div class="module-field">
                    <label>Satuan</label>
                    <input type="text" name="satuan" maxlength="30" required value="<?= htmlspecialchars($edit_data['satuan'] ?? 'unit') ?>">
                </div>
                <div class="module-field">
                    <label>Stok Minimum</label>
                    <input type="number" step="0.01" min="0" max="9999999999.99" name="stok_minimum" value="<?= htmlspecialchars((string) ($edit_data['stok_minimum'] ?? '0')) ?>">
                </div>
                <div class="module-field">
                    <label>Lokasi Simpan</label>
                    <input type="text" name="lokasi_simpan" maxlength="120" value="<?= htmlspecialchars($edit_data['lokasi_simpan'] ?? '') ?>">
                </div>
            </div>
            <div class="module-field module-field-gap">
                <label>Catatan</label>
                <textarea name="catatan" maxlength="2000"><?= htmlspecialchars($edit_data['catatan'] ?? '') ?></textarea>
            </div>
            <div class="module-actions">
                <button class="module-btn module-btn-primary" type="submit"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon($edit_data ? 'settings' : 'plus') ?></span><span><?= $edit_data ? 'Update Item' : 'Simpan Item' ?></span></button>
                <?php if ($edit_data): ?><a class="module-btn module-btn-muted" href="inventaris.php">Batal Edit</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="module-card">
        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('list') ?></span><span>Daftar Inventaris</span></h2>
        <div class="module-toolbar">
            <form class="module-search" method="GET">
                <div class="module-field">
                    <label>Cari inventaris</label>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nama item, kategori, satuan, lokasi">
                </div>
                <button class="module-btn module-btn-primary" type="submit"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('filter') ?></span><span>Cari</span></button>
                <a class="module-btn module-btn-ghost" href="inventaris.php">Reset</a>
            </form>
        </div>
        <?php if (count($inventaris) === 0): ?>
            <p class="module-empty">Belum ada item inventaris.</p>
        <?php else: ?>
            <div class="module-table-wrap">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Min</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inventaris as $row): ?>
                        <?php $is_low = (float) $row['jumlah_stok'] <= (float) $row['stok_minimum']; ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_item']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td class="<?= $is_low ? 'module-stock-low' : '' ?>">
                                <?= rtrim(rtrim(number_format((float) $row['jumlah_stok'], 2, '.', ''), '0'), '.') ?> <?= htmlspecialchars($row['satuan']) ?>
                                <?php if ($is_low): ?><span class="stock-state-label">Kritis</span><?php endif; ?>
                            </td>
                            <td><?= rtrim(rtrim(number_format((float) $row['stok_minimum'], 2, '.', ''), '0'), '.') ?></td>
                            <td><?= htmlspecialchars((string) $row['lokasi_simpan']) ?></td>
                            <td>
                                <div class="module-item-actions">
                                    <a href="<?= htmlspecialchars(inventaris_link(['edit' => (int) $row['id']], $q, $page)) ?>">Edit</a>
                                    <a class="module-danger-link" href="<?= htmlspecialchars(inventaris_link(['hapus' => (int) $row['id']], $q, $page)) ?>" onclick="return confirm('Hapus item ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="module-pagination">
                <div class="module-meta">Menampilkan <?= $start_row ?>-<?= $end_row ?> dari <?= $total_rows ?> data</div>
                <?php if ($total_pages > 1): ?>
                    <div class="module-pagination-pages">
                        <?php if ($page > 1): ?>
                            <a class="module-page-link" href="<?= htmlspecialchars(inventaris_page_link($page - 1, $q)) ?>">Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a class="module-page-link <?= $i === $page ? 'is-active' : '' ?>" href="<?= htmlspecialchars(inventaris_page_link($i, $q)) ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a class="module-page-link" href="<?= htmlspecialchars(inventaris_page_link($page + 1, $q)) ?>">Next</a>
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
