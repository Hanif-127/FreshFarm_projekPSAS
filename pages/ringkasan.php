<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../includes/koneksi.php';
$dashboard_css_version = filemtime(__DIR__ . '/../assets/css/dashboard.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

$user_id = (int) $_SESSION['user_id'];

$total_jurnal_q = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM jurnal_tanam WHERE user_id = ?");
$total_jurnal = 0;
if ($total_jurnal_q) {
    mysqli_stmt_bind_param($total_jurnal_q, "i", $user_id);
    mysqli_stmt_execute($total_jurnal_q);
    $result = mysqli_stmt_get_result($total_jurnal_q);
    $total_jurnal = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
    mysqli_stmt_close($total_jurnal_q);
}

$jadwal_hari_ini_q = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM kalender_tanam WHERE user_id = ? AND tanggal_jadwal = CURDATE() AND status = 'terjadwal'");
$jadwal_hari_ini = 0;
if ($jadwal_hari_ini_q) {
    mysqli_stmt_bind_param($jadwal_hari_ini_q, "i", $user_id);
    mysqli_stmt_execute($jadwal_hari_ini_q);
    $result = mysqli_stmt_get_result($jadwal_hari_ini_q);
    $jadwal_hari_ini = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
    mysqli_stmt_close($jadwal_hari_ini_q);
}

$stok_tipis_q = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM inventaris WHERE user_id = ? AND jumlah_stok <= stok_minimum");
$stok_tipis = 0;
if ($stok_tipis_q) {
    mysqli_stmt_bind_param($stok_tipis_q, "i", $user_id);
    mysqli_stmt_execute($stok_tipis_q);
    $result = mysqli_stmt_get_result($stok_tipis_q);
    $stok_tipis = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
    mysqli_stmt_close($stok_tipis_q);
}

$pengaduan_aktif_q = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE user_id = ? AND status IN ('dikirim', 'diproses')");
$pengaduan_aktif = 0;
if ($pengaduan_aktif_q) {
    mysqli_stmt_bind_param($pengaduan_aktif_q, "i", $user_id);
    mysqli_stmt_execute($pengaduan_aktif_q);
    $result = mysqli_stmt_get_result($pengaduan_aktif_q);
    $pengaduan_aktif = (int) (mysqli_fetch_assoc($result)['total'] ?? 0);
    mysqli_stmt_close($pengaduan_aktif_q);
}

$harga_terbaru_q = mysqli_query($koneksi, "SELECT nama_komoditas, harga, satuan, tanggal FROM harga_pasar ORDER BY tanggal DESC LIMIT 5");
$harga_terbaru = mysqli_fetch_all($harga_terbaru_q, MYSQLI_ASSOC);

$aktivitas_stmt = mysqli_prepare($koneksi, "SELECT nama_tanaman, status, tanggal_tanam FROM jurnal_tanam WHERE user_id = ? ORDER BY tanggal_tanam DESC LIMIT 5");
$aktivitas = [];
if ($aktivitas_stmt) {
    mysqli_stmt_bind_param($aktivitas_stmt, "i", $user_id);
    mysqli_stmt_execute($aktivitas_stmt);
    $result = mysqli_stmt_get_result($aktivitas_stmt);
    $aktivitas = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($aktivitas_stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard.css?v=<?= $dashboard_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>

<main class="module-wrap">
    <section class="ringkasan-kpi-grid">
        <article class="module-card ringkasan-kpi-card">
            <span>Total Jurnal</span>
            <strong><?= $total_jurnal ?></strong>
        </article>
        <article class="module-card ringkasan-kpi-card">
            <span>Jadwal Hari Ini</span>
            <strong><?= $jadwal_hari_ini ?></strong>
        </article>
        <article class="module-card ringkasan-kpi-card">
            <span>Stok Menipis</span>
            <strong><?= $stok_tipis ?></strong>
        </article>
        <article class="module-card ringkasan-kpi-card">
            <span>Pengaduan Aktif</span>
            <strong><?= $pengaduan_aktif ?></strong>
        </article>
    </section>

    <section class="module-card">
        <h2>Alert Penting</h2>
        <ul class="ringkasan-list">
            <li><?= $jadwal_hari_ini > 0 ? "Ada $jadwal_hari_ini jadwal yang perlu dikerjakan hari ini." : "Tidak ada jadwal mendesak hari ini." ?></li>
            <li><?= $stok_tipis > 0 ? "Ada $stok_tipis item inventaris dengan stok menipis." : "Stok inventaris dalam kondisi aman." ?></li>
            <li><?= $pengaduan_aktif > 0 ? "Ada $pengaduan_aktif pengaduan yang belum selesai." : "Tidak ada pengaduan aktif." ?></li>
        </ul>
    </section>

    <section class="module-card">
        <h2>Harga Terbaru</h2>
        <?php if (count($harga_terbaru) === 0): ?>
            <p class="module-empty">Belum ada data harga terbaru.</p>
        <?php else: ?>
            <div class="module-table-wrap">
                <table class="module-table">
                    <thead>
                    <tr>
                        <th>Komoditas</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Tanggal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($harga_terbaru as $harga): ?>
                        <tr>
                            <td><?= htmlspecialchars($harga['nama_komoditas']) ?></td>
                            <td>Rp <?= number_format((int) $harga['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars((string) $harga['satuan']) ?></td>
                            <td><?= date('d M Y', strtotime($harga['tanggal'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-card">
        <h2>Aktivitas Terakhir</h2>
        <?php if (count($aktivitas) === 0): ?>
            <p class="module-empty">Belum ada aktivitas jurnal.</p>
        <?php else: ?>
            <ul class="ringkasan-list">
                <?php foreach ($aktivitas as $item): ?>
                    <li><?= htmlspecialchars($item['nama_tanaman']) ?> - <?= htmlspecialchars($item['status']) ?> (<?= date('d M Y', strtotime($item['tanggal_tanam'])) ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
