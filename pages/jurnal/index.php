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

$status_options = [
    'all' => 'Semua Status',
    'Sedang Tanam' => 'Sedang Tanam',
    'Sudah Panen' => 'Sudah Panen',
    'Gagal' => 'Gagal',
];

$sort_options = [
    'tanggal_desc' => 'Tanggal terbaru',
    'tanggal_asc' => 'Tanggal terlama',
    'jumlah_desc' => 'Jumlah tertinggi',
    'panen_desc' => 'Panen tertinggi',
];

$q = trim($_GET['q'] ?? '');
$q = mb_substr($q, 0, 100);

$status_filter = $_GET['status'] ?? 'all';
if (!array_key_exists($status_filter, $status_options)) {
    $status_filter = 'all';
}

$sort = $_GET['sort'] ?? 'tanggal_desc';
if (!array_key_exists($sort, $sort_options)) {
    $sort = 'tanggal_desc';
}

$order_map = [
    'tanggal_desc' => 'tanggal_tanam DESC, id DESC',
    'tanggal_asc' => 'tanggal_tanam ASC, id ASC',
    'jumlah_desc' => 'jumlah DESC, id DESC',
    'panen_desc' => 'hasil_panen DESC, id DESC',
];

$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 10;

$summary = [
    'total_entri' => 0,
    'total_jumlah' => 0.0,
    'total_panen' => 0.0,
];

$summary_stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total_entri,
            COALESCE(SUM(jumlah), 0) AS total_jumlah,
            COALESCE(SUM(hasil_panen), 0) AS total_panen
     FROM jurnal_tanam
     WHERE user_id = ?"
);
if ($summary_stmt) {
    mysqli_stmt_bind_param($summary_stmt, "i", $user_id);
    mysqli_stmt_execute($summary_stmt);
    $summary_result = mysqli_stmt_get_result($summary_stmt);
    $summary = mysqli_fetch_assoc($summary_result) ?: $summary;
    mysqli_stmt_close($summary_stmt);
}

$status_count = [
    'Sedang Tanam' => 0,
    'Sudah Panen' => 0,
    'Gagal' => 0,
];
$status_stmt = mysqli_prepare(
    $koneksi,
    "SELECT status, COUNT(*) AS total
     FROM jurnal_tanam
     WHERE user_id = ?
     GROUP BY status"
);
if ($status_stmt) {
    mysqli_stmt_bind_param($status_stmt, "i", $user_id);
    mysqli_stmt_execute($status_stmt);
    $status_result = mysqli_stmt_get_result($status_stmt);
    while ($row = mysqli_fetch_assoc($status_result)) {
        if (array_key_exists($row['status'], $status_count)) {
            $status_count[$row['status']] = (int) $row['total'];
        }
    }
    mysqli_stmt_close($status_stmt);
}

$total_rows = 0;
$search_like = '%' . $q . '%';

if ($q !== '' && $status_filter !== 'all') {
    $count_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ? AND status = ? AND nama_tanaman LIKE ?"
    );
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "iss", $user_id, $status_filter, $search_like);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
} elseif ($q !== '') {
    $count_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ? AND nama_tanaman LIKE ?"
    );
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "is", $user_id, $search_like);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
} elseif ($status_filter !== 'all') {
    $count_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ? AND status = ?"
    );
    if ($count_stmt) {
        mysqli_stmt_bind_param($count_stmt, "is", $user_id, $status_filter);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_rows = (int) (mysqli_fetch_assoc($count_result)['total'] ?? 0);
        mysqli_stmt_close($count_stmt);
    }
} else {
    $count_stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM jurnal_tanam
         WHERE user_id = ?"
    );
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

$order_by = $order_map[$sort];
$items = [];

if ($q !== '' && $status_filter !== 'all') {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND status = ? AND nama_tanaman LIKE ?
         ORDER BY $order_by
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "issii", $user_id, $status_filter, $search_like, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $items_result = mysqli_stmt_get_result($list_stmt);
        $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
} elseif ($q !== '') {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND nama_tanaman LIKE ?
         ORDER BY $order_by
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "isii", $user_id, $search_like, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $items_result = mysqli_stmt_get_result($list_stmt);
        $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
} elseif ($status_filter !== 'all') {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen
         FROM jurnal_tanam
         WHERE user_id = ? AND status = ?
         ORDER BY $order_by
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "isii", $user_id, $status_filter, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $items_result = mysqli_stmt_get_result($list_stmt);
        $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
} else {
    $list_stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen
         FROM jurnal_tanam
         WHERE user_id = ?
         ORDER BY $order_by
         LIMIT ? OFFSET ?"
    );
    if ($list_stmt) {
        mysqli_stmt_bind_param($list_stmt, "iii", $user_id, $per_page, $offset);
        mysqli_stmt_execute($list_stmt);
        $items_result = mysqli_stmt_get_result($list_stmt);
        $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
        mysqli_stmt_close($list_stmt);
    }
}

function jurnal_index_url(string $q, string $status_filter, string $sort, int $page): string {
    $params = [];
    if ($q !== '') {
        $params['q'] = $q;
    }
    if ($status_filter !== 'all') {
        $params['status'] = $status_filter;
    }
    if ($sort !== 'tanggal_desc') {
        $params['sort'] = $sort;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return 'index.php' . (!empty($params) ? '?' . http_build_query($params) : '');
}

$start_row = $total_rows > 0 ? $offset + 1 : 0;
$end_row = min($offset + $per_page, $total_rows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Tanam</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/jurnal_tanam.css?v=<?= $jurnal_tanam_css_version ?>">
</head>
<body class="module-page">
<?php include '../../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <section class="jurnal-page-v2">
            <section class="module-card jurnal-head-card">
                <div class="jurnal-head-row">
                    <div class="jurnal-head-copy">
                        <h1 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('summary') ?></span><span>Jurnal Tanam</span></h1>
                        <p>Catat aktivitas tanam, pantau progres, dan kelola riwayat dengan lebih rapi.</p>
                    </div>
                    <a href="tambah.php" class="module-btn module-btn-primary"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('plus') ?></span><span>Tambah Data</span></a>
                </div>
            </section>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="module-msg"><?= htmlspecialchars($_GET['pesan']) ?></div>
            <?php endif; ?>

            <section class="jurnal-kpi-grid">
                <article class="module-card jurnal-kpi-card">
                    <span>Total Entri</span>
                    <strong><?= (int) $summary['total_entri'] ?></strong>
                </article>
                <article class="module-card jurnal-kpi-card">
                    <span>Total Tanaman</span>
                    <strong><?= rtrim(rtrim(number_format((float) $summary['total_jumlah'], 2, '.', ''), '0'), '.') ?></strong>
                </article>
                <article class="module-card jurnal-kpi-card">
                    <span>Total Panen (kg)</span>
                    <strong><?= rtrim(rtrim(number_format((float) $summary['total_panen'], 2, '.', ''), '0'), '.') ?></strong>
                </article>
                <article class="module-card jurnal-kpi-card state-warning">
                    <span>Sedang Tanam</span>
                    <strong><?= $status_count['Sedang Tanam'] ?></strong>
                </article>
            </section>

            <section class="module-card">
                <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('filter') ?></span><span>Filter Data</span></h2>
                <form method="GET" class="jurnal-filter-form">
                    <div class="module-field">
                        <label>Cari Tanaman</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Contoh: cabai, tomat">
                    </div>
                    <div class="module-field">
                        <label>Status</label>
                        <select name="status">
                            <?php foreach ($status_options as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $status_filter === $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="module-field">
                        <label>Urutkan</label>
                        <select name="sort">
                            <?php foreach ($sort_options as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $sort === $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="jurnal-filter-actions">
                        <button type="submit" class="module-btn module-btn-primary"><span class="module-btn__icon" aria-hidden="true"><?= module_ui_icon('filter') ?></span><span>Terapkan</span></button>
                        <a href="index.php" class="module-btn module-btn-ghost">Reset</a>
                    </div>
                </form>
            </section>

            <section class="module-card">
                <div class="jurnal-listing-head">
                    <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('list') ?></span><span>Data Jurnal</span></h2>
                    <span class="module-meta">Menampilkan <?= $start_row ?>-<?= $end_row ?> dari <?= $total_rows ?> data</span>
                </div>

                <?php if (count($items) === 0): ?>
                    <p class="module-empty">Belum ada data jurnal sesuai filter.</p>
                <?php else: ?>
                    <div class="module-table-wrap">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Tanaman</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Panen (kg)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $row): ?>
                                    <?php
                                    $status_class = match ($row['status']) {
                                        'Sedang Tanam' => 'module-status module-status-warning',
                                        'Sudah Panen' => 'module-status module-status-success',
                                        'Gagal' => 'module-status module-status-danger',
                                        default => 'module-status module-status-info',
                                    };
                                    $edit_url = 'edit.php?id=' . (int) $row['id'];
                                    $hapus_url = 'hapus.php?id=' . (int) $row['id'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama_tanaman']) ?></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_tanam'])) ?></td>
                                        <td><?= rtrim(rtrim(number_format((float) $row['jumlah'], 2, '.', ''), '0'), '.') ?></td>
                                        <td><span class="<?= $status_class ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                        <td><?= rtrim(rtrim(number_format((float) $row['hasil_panen'], 2, '.', ''), '0'), '.') ?></td>
                                        <td>
                                            <div class="jurnal-row-actions">
                                                <a class="jurnal-action-link" href="<?= htmlspecialchars($edit_url) ?>">Edit</a>
                                                <a class="jurnal-action-link danger" href="<?= htmlspecialchars($hapus_url) ?>" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="module-pagination">
                            <span class="module-meta">Halaman <?= $page ?> dari <?= $total_pages ?></span>
                            <div class="module-pagination-pages">
                                <?php if ($page > 1): ?>
                                    <a class="module-page-link" href="<?= htmlspecialchars(jurnal_index_url($q, $status_filter, $sort, $page - 1)) ?>">Prev</a>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a class="module-page-link <?= $i === $page ? 'is-active' : '' ?>" href="<?= htmlspecialchars(jurnal_index_url($q, $status_filter, $sort, $i)) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a class="module-page-link" href="<?= htmlspecialchars(jurnal_index_url($q, $status_filter, $sort, $page + 1)) ?>">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </section>
    </main>
</div>
</body>
</html>
