<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/koneksi.php';
include '../includes/user_settings.php';

$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$debug_mode = isset($_GET['debug']) && $_GET['debug'] === '1';
$user_id = (int) $_SESSION['user_id'];
$settings = user_settings_get($koneksi, $user_id);

$allowed_date_formats = ['d M Y', 'd/m/Y', 'Y-m-d'];
$date_format = in_array((string) ($settings['format_tanggal'] ?? ''), $allowed_date_formats, true) ? (string) $settings['format_tanggal'] : 'd M Y';
$dashboard_mode = ($settings['dashboard_mode'] ?? 'compact') === 'normal' ? 'normal' : 'compact';
$is_normal_mode = $dashboard_mode === 'normal';
$show_focus = (int) ($settings['show_focus'] ?? 1) === 1;
$show_quick_actions = (int) ($settings['show_quick_actions'] ?? 1) === 1;
$show_schedule = (int) ($settings['show_schedule'] ?? 1) === 1;
$show_market = (int) ($settings['show_market'] ?? 1) === 1;
$show_complaint = (int) ($settings['show_complaint'] ?? 1) === 1;
$show_critical_stock = (int) ($settings['show_critical_stock'] ?? 1) === 1;
$show_plant_status = (int) ($settings['show_plant_status'] ?? 1) === 1;
$limit_recent_activities = max(2, min(10, (int) ($settings['limit_recent_activities'] ?? 4)));
$limit_market_prices = max(2, min(10, (int) ($settings['limit_market_prices'] ?? 4)));
$limit_plant_status = max(2, min(10, (int) ($settings['limit_plant_status'] ?? 5)));

if ($is_normal_mode) {
    $show_focus = true;
    $show_quick_actions = true;
    $show_schedule = true;
    $show_market = true;
    $show_complaint = true;
    $show_critical_stock = true;
    $show_plant_status = true;

    $limit_recent_activities = max(6, $limit_recent_activities);
    $limit_market_prices = max(6, $limit_market_prices);
    $limit_plant_status = max(6, $limit_plant_status);
} else {
    $limit_recent_activities = min(4, $limit_recent_activities);
    $limit_market_prices = min(4, $limit_market_prices);
    $limit_plant_status = min(4, $limit_plant_status);
}

$chart_limit = $is_normal_mode ? 8 : 5;
$critical_stock_limit = $is_normal_mode ? 6 : 4;

$timezone = (string) ($settings['timezone'] ?? 'Asia/Jakarta');
if (in_array($timezone, timezone_identifiers_list(), true)) {
    date_default_timezone_set($timezone);
}

$motivasi = [
    "Setiap benih yang ditanam adalah harapan masa depan.",
    "Pertanian adalah seni merawat bumi dan menuai berkah.",
    "Dengan tangan yang rajin, ladang akan berbuah lebat.",
    "Tanamlah hari ini, panenlah impian besok.",
    "Bumi memberi apa yang kita taburkan."
];
$motivasi_random = $motivasi[array_rand($motivasi)];

$total = 0;
$total_jumlah_tanaman = 0;
$jadwal_hari_ini = 0;
$stok_tipis = 0;
$pengaduan_aktif = 0;
$aktivitas_minggu_ini = 0;
$recent_plants = [];
$chart_data = [];
$latest_prices = [];
$next_jadwal = null;
$latest_pengaduan = null;
$jadwal_minggu_ini = 0;
$stok_kritis_items = [];
$status_tanaman = [];

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total, COALESCE(SUM(jumlah),0) AS total_jumlah FROM jurnal_tanam WHERE user_id = $user_id");
if ($q_total) {
    $row_total = mysqli_fetch_assoc($q_total);
    $total = (int) ($row_total['total'] ?? 0);
    $total_jumlah_tanaman = (float) ($row_total['total_jumlah'] ?? 0);
}

$q_recent = mysqli_query($koneksi, "SELECT nama_tanaman, tanggal_tanam, jumlah, status FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT $limit_recent_activities");
if ($q_recent) {
    $recent_plants = mysqli_fetch_all($q_recent, MYSQLI_ASSOC);
}

$q_chart = mysqli_query($koneksi, "SELECT nama_tanaman, jumlah FROM jurnal_tanam WHERE user_id = $user_id ORDER BY tanggal_tanam DESC LIMIT $chart_limit");
if ($q_chart) {
    $chart_data = mysqli_fetch_all($q_chart, MYSQLI_ASSOC);
}

$q_today = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kalender_tanam WHERE user_id = $user_id AND tanggal_jadwal = CURDATE() AND status = 'terjadwal'");
if ($q_today) {
    $jadwal_hari_ini = (int) (mysqli_fetch_assoc($q_today)['total'] ?? 0);
}

$q_low_stock = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM inventaris WHERE user_id = $user_id AND jumlah_stok <= stok_minimum");
if ($q_low_stock) {
    $stok_tipis = (int) (mysqli_fetch_assoc($q_low_stock)['total'] ?? 0);
}

$q_pengaduan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE user_id = $user_id AND status IN ('dikirim','diproses')");
if ($q_pengaduan) {
    $pengaduan_aktif = (int) (mysqli_fetch_assoc($q_pengaduan)['total'] ?? 0);
}

$q_aktivitas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM jurnal_tanam WHERE user_id = $user_id AND tanggal_tanam >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
if ($q_aktivitas) {
    $aktivitas_minggu_ini = (int) (mysqli_fetch_assoc($q_aktivitas)['total'] ?? 0);
}

$q_next = mysqli_query($koneksi, "SELECT nama_kegiatan, tipe_kegiatan, tanggal_jadwal, jam_jadwal FROM kalender_tanam WHERE user_id = $user_id AND status = 'terjadwal' AND tanggal_jadwal >= CURDATE() ORDER BY tanggal_jadwal ASC, jam_jadwal ASC LIMIT 1");
if ($q_next) {
    $next_jadwal = mysqli_fetch_assoc($q_next) ?: null;
}

$q_week = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kalender_tanam WHERE user_id = $user_id AND status = 'terjadwal' AND tanggal_jadwal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
if ($q_week) {
    $jadwal_minggu_ini = (int) (mysqli_fetch_assoc($q_week)['total'] ?? 0);
}

$q_stock_items = mysqli_query($koneksi, "SELECT nama_item, jumlah_stok, stok_minimum, satuan FROM inventaris WHERE user_id = $user_id AND jumlah_stok <= stok_minimum ORDER BY (stok_minimum - jumlah_stok) DESC, updated_at DESC LIMIT $critical_stock_limit");
if ($q_stock_items) {
    $stok_kritis_items = mysqli_fetch_all($q_stock_items, MYSQLI_ASSOC);
}

$q_status_tanaman = mysqli_query($koneksi, "SELECT COALESCE(NULLIF(status, ''), 'tanpa status') AS status, COUNT(*) AS total FROM jurnal_tanam WHERE user_id = $user_id GROUP BY status ORDER BY total DESC LIMIT $limit_plant_status");
if ($q_status_tanaman) {
    $status_tanaman = mysqli_fetch_all($q_status_tanaman, MYSQLI_ASSOC);
}

$q_prices = mysqli_query($koneksi, "SELECT nama_komoditas, harga, satuan, tanggal FROM harga_pasar ORDER BY tanggal DESC LIMIT $limit_market_prices");
if ($q_prices) {
    $latest_prices = mysqli_fetch_all($q_prices, MYSQLI_ASSOC);
}

$q_last_pengaduan = mysqli_query($koneksi, "SELECT judul, status, created_at FROM pengaduan WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1");
if ($q_last_pengaduan) {
    $latest_pengaduan = mysqli_fetch_assoc($q_last_pengaduan) ?: null;
}

function dashboard_state_by_value(int $value, int $warn_at, int $critical_at): string {
    if ($value >= $critical_at) {
        return 'state-critical';
    }
    if ($value >= $warn_at) {
        return 'state-warning';
    }
    return 'state-safe';
}

function dashboard_state_label(string $state_class): string {
    return match ($state_class) {
        'state-critical' => 'Kritis',
        'state-warning' => 'Peringatan',
        default => 'Aman',
    };
}

function dashboard_status_state_class(?string $status): string {
    $normalized = strtolower(trim((string) $status));

    return match ($normalized) {
        'gagal', 'ditolak', 'terlewat', 'dibatalkan', 'batal', 'habis', 'kritis' => 'state-critical',
        'sedang tanam', 'dikirim', 'diproses', 'menipis', 'rendah', 'tanpa status', '' => 'state-warning',
        default => 'state-safe',
    };
}

function dashboard_format_date(?string $date_value, string $format): string {
    if (!$date_value) {
        return '-';
    }

    $timestamp = strtotime($date_value);
    if ($timestamp === false) {
        return '-';
    }

    return date($format, $timestamp);
}

function dashboard_ui_icon(string $name): string {
    $icons = [
        'plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 10h18"/></svg>',
        'inventory' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 7.5 12 3l9 4.5-9 4.5z"/><path d="M3 7.5V16.5L12 21l9-4.5V7.5"/><path d="M12 12v9"/></svg>',
        'summary' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 3h9l4 4v14H6z"/><path d="M15 3v4h4"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>',
        'activity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 12h4l2.5-5 4 10 2.5-5H21"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20v-4"/><path d="M3 20h20"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'market' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 8h16"/><path d="M6 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/><path d="M6 8v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8"/><path d="M12 11v6"/><path d="M9.5 13.5h5"/></svg>',
        'complaint' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 5h16v11H8l-4 3z"/><path d="M8 9h8"/><path d="M8 12h5"/></svg>',
        'stock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 7.5 12 3l9 4.5-9 4.5z"/><path d="M3 7.5V16.5L12 21l9-4.5V7.5"/><path d="M8 12h8"/><path d="M8 15h5"/></svg>',
        'plant' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 20v-7"/><path d="M12 13c0-4.5 3.2-7 7-7 0 3.8-2.5 7-7 7z"/><path d="M12 15c0-3.4-2.4-5.5-6-5.5 0 3.3 2 5.5 6 5.5z"/><path d="M7 20h10"/></svg>',
        'spark' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3v5"/><path d="M12 16v5"/><path d="M4.9 4.9l3.5 3.5"/><path d="M15.6 15.6l3.5 3.5"/><path d="M3 12h5"/><path d="M16 12h5"/><path d="M4.9 19.1l3.5-3.5"/><path d="M15.6 8.4l3.5-3.5"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>',
    ];

    return $icons[$name] ?? $icons['arrow'];
}

function dashboard_empty_state(string $title, string $description, string $cta_label, string $cta_href): string {
    $title_html = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description_html = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $cta_label_html = htmlspecialchars($cta_label, ENT_QUOTES, 'UTF-8');
    $cta_href_html = htmlspecialchars($cta_href, ENT_QUOTES, 'UTF-8');

    $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 20v-6"/><path d="M12 14c0-4.3 3-6.7 6.5-6.7 0 3.6-2.3 6.7-6.5 6.7z"/><path d="M12 16.2c0-3.1-2.2-5.1-5.7-5.1 0 3 1.9 5.1 5.7 5.1z"/><path d="M6.5 20h11"/></svg>';

    return '
        <div class="dashboard-empty-state">
            <span class="empty-state-icon" aria-hidden="true">' . $icon . '</span>
            <h4>' . $title_html . '</h4>
            <p>' . $description_html . '</p>
            <a class="empty-state-action" href="' . $cta_href_html . '">' . $cta_label_html . '</a>
        </div>
    ';
}

function dashboard_card_caption(string $text): string {
    return '<p class="dashboard-card-caption">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
}

$jadwal_state = dashboard_state_by_value($jadwal_hari_ini, 2, 5);
$stok_state = dashboard_state_by_value($stok_tipis, 1, 3);
$pengaduan_state = dashboard_state_by_value($pengaduan_aktif, 1, 2);
$jadwal_minggu_state = dashboard_state_by_value($jadwal_minggu_ini, 4, 9);

$focus_hari_ini = [];
if ($jadwal_hari_ini > 0) {
    $focus_hari_ini[] = [
        'text' => "Ada $jadwal_hari_ini jadwal yang perlu diselesaikan hari ini.",
        'state' => $jadwal_state
    ];
}
if ($stok_tipis > 0) {
    $focus_hari_ini[] = [
        'text' => "Ada $stok_tipis item inventaris dengan stok menipis.",
        'state' => $stok_state
    ];
}
if ($pengaduan_aktif > 0) {
    $focus_hari_ini[] = [
        'text' => "Ada $pengaduan_aktif pengaduan aktif yang perlu dipantau.",
        'state' => $pengaduan_state
    ];
}
if ($jadwal_minggu_ini > 0) {
    $focus_hari_ini[] = [
        'text' => "Dalam 7 hari ke depan ada $jadwal_minggu_ini agenda terjadwal.",
        'state' => $jadwal_minggu_state
    ];
}
if (count($focus_hari_ini) === 0) {
    $focus_hari_ini[] = [
        'text' => "Semua indikator utama aman. Pertahankan ritme pencatatan harian.",
        'state' => 'state-safe'
    ];
}

$health_score = 100;
$health_score -= min(36, $stok_tipis * 9);
$health_score -= min(36, $pengaduan_aktif * 12);
$health_score -= min(18, max(0, $jadwal_hari_ini - 2) * 6);
$health_score = max(20, min(100, $health_score));

$health_label = 'Perlu Aksi Cepat';
$health_class = 'health-alert';
if ($health_score >= 85) {
    $health_label = 'Prima';
    $health_class = 'health-good';
} elseif ($health_score >= 70) {
    $health_label = 'Stabil';
    $health_class = 'health-stable';
} elseif ($health_score >= 55) {
    $health_label = 'Perlu Perhatian';
    $health_class = 'health-watch';
}

$latest_pengaduan_state = 'state-safe';
if ($latest_pengaduan && isset($latest_pengaduan['status'])) {
    $latest_pengaduan_state = match ($latest_pengaduan['status']) {
        'dikirim', 'diproses' => 'state-warning',
        'ditolak' => 'state-critical',
        default => 'state-safe',
    };
}

$has_extra_cards = $show_schedule || $show_market || $show_complaint || $show_critical_stock || $show_plant_status;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= (int) $dashboard_base_css_version ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-home dashboard-<?= htmlspecialchars($dashboard_mode) ?>">
<?php include '../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="dashboard-main-content">
        <div class="dashboard-container dashboard-v2">
            <?php if ($debug_mode): ?>
                <div class="debug-panel">
                    <strong>DEBUG MODE ACTIVE</strong><br>
                    FILE: <?= htmlspecialchars(__FILE__) ?><br>
                    CSS: <?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= (int) $dashboard_base_css_version ?><br>
                    HASH: <?= hash_file('sha256', __FILE__) ?>
                </div>
            <?php endif; ?>

            <section class="dashboard-command-center" data-reveal>
                <div class="command-copy">
                    <span class="dashboard-eyebrow">Dashboard Operasional</span>
                    <h1>Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                    <p>Semua hal penting untuk <?= htmlspecialchars((string) ($settings['kebun_nama'] ?? 'kebunmu')) ?> diringkas dari yang paling perlu ditindak sampai data pendukung.</p>
                    <div class="command-meta">
                        <span><?= dashboard_format_date(date('Y-m-d'), $date_format) ?></span>
                        <span><?= $is_normal_mode ? 'Mode Normal' : 'Mode Compact' ?></span>
                    </div>
                    <?php if ($is_normal_mode): ?>
                        <p class="command-quote">"<?= htmlspecialchars($motivasi_random) ?>"</p>
                    <?php endif; ?>
                </div>

                <?php if ($show_quick_actions): ?>
                    <div class="command-actions" aria-label="Aksi cepat dashboard">
                        <span class="command-actions-title">Aksi cepat</span>
                        <a class="command-action command-action--primary" href="jurnal/tambah.php">
                            <span class="quick-link-pill__icon" aria-hidden="true"><?= dashboard_ui_icon('plus') ?></span>
                            <span>Tambah Jurnal</span>
                        </a>
                        <a class="command-action" href="kalender.php">
                            <span class="quick-link-pill__icon" aria-hidden="true"><?= dashboard_ui_icon('calendar') ?></span>
                            <span>Kalender</span>
                        </a>
                        <a class="command-action" href="inventaris.php">
                            <span class="quick-link-pill__icon" aria-hidden="true"><?= dashboard_ui_icon('inventory') ?></span>
                            <span>Inventaris</span>
                        </a>
                        <a class="command-action" href="ringkasan.php">
                            <span class="quick-link-pill__icon" aria-hidden="true"><?= dashboard_ui_icon('summary') ?></span>
                            <span>Ringkasan</span>
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-kpi-grid" aria-label="Indikator utama dashboard">
                <article class="metric-card metric-card--primary" data-reveal>
                    <span class="metric-label">Total Jurnal</span>
                    <strong><?= $total ?></strong>
                    <small>Catatan tanam tersimpan</small>
                </article>

                <article class="metric-card" data-reveal>
                    <span class="metric-label">Tanaman Tercatat</span>
                    <strong><?= rtrim(rtrim(number_format($total_jumlah_tanaman, 2, '.', ''), '0'), '.') ?></strong>
                    <small>Total jumlah dari jurnal</small>
                </article>

                <article class="metric-card <?= htmlspecialchars($jadwal_state) ?>" data-reveal>
                    <span class="metric-label">Jadwal Hari Ini</span>
                    <strong><?= $jadwal_hari_ini ?></strong>
                    <small><?= htmlspecialchars(dashboard_state_label($jadwal_state)) ?></small>
                </article>

                <article class="metric-card <?= htmlspecialchars($stok_state) ?>" data-reveal>
                    <span class="metric-label">Stok Menipis</span>
                    <strong><?= $stok_tipis ?></strong>
                    <small><?= htmlspecialchars(dashboard_state_label($stok_state)) ?></small>
                </article>

                <article class="metric-card <?= htmlspecialchars($pengaduan_state) ?>" data-reveal>
                    <span class="metric-label">Pengaduan Aktif</span>
                    <strong><?= $pengaduan_aktif ?></strong>
                    <small><?= htmlspecialchars(dashboard_state_label($pengaduan_state)) ?></small>
                </article>
            </section>

            <section class="dashboard-workspace-grid">
                <article class="dashboard-panel dashboard-panel--priority" data-reveal>
                    <div class="section-header">
                        <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('spark') ?></span><span>Prioritas Hari Ini</span></h3>
                        <span class="section-badge"><?= count($focus_hari_ini) ?> poin</span>
                    </div>

                    <?php if ($show_focus): ?>
                        <ul class="focus-list">
                            <?php foreach ($focus_hari_ini as $point): ?>
                                <li class="<?= htmlspecialchars($point['state']) ?>">
                                    <span><?= htmlspecialchars($point['text']) ?></span>
                                    <span class="focus-state-badge <?= htmlspecialchars($point['state']) ?>"><?= htmlspecialchars(dashboard_state_label((string) $point['state'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="focus-note">Daftar fokus disembunyikan dari preferensi dashboard.</p>
                    <?php endif; ?>

                    <div class="dashboard-health-card">
                        <div>
                            <span class="metric-label">Kesehatan Operasional</span>
                            <p class="health-score-line">
                                <strong><?= $health_score ?>%</strong>
                                <span class="health-tag <?= htmlspecialchars($health_class) ?>"><?= htmlspecialchars($health_label) ?></span>
                            </p>
                        </div>
                        <div class="health-meter" role="img" aria-label="Skor kesehatan operasional <?= $health_score ?> persen">
                            <span style="width: <?= $health_score ?>%"></span>
                        </div>
                    </div>
                </article>

                <?php if ($show_schedule): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('clock') ?></span><span>Jadwal Terdekat</span></h3>
                            <span class="section-badge <?= htmlspecialchars($jadwal_minggu_state) ?>"><?= $jadwal_minggu_ini ?> agenda</span>
                        </div>
                        <?php if ($next_jadwal): ?>
                            <div class="extra-highlight"><?= htmlspecialchars($next_jadwal['nama_kegiatan']) ?></div>
                            <p><?= htmlspecialchars(ucfirst($next_jadwal['tipe_kegiatan'])) ?> | <?= dashboard_format_date((string) $next_jadwal['tanggal_jadwal'], $date_format) ?><?= !empty($next_jadwal['jam_jadwal']) ? ' | ' . substr((string) $next_jadwal['jam_jadwal'], 0, 5) : '' ?></p>
                            <span class="state-chip <?= htmlspecialchars($jadwal_minggu_state) ?>"><?= htmlspecialchars(dashboard_state_label($jadwal_minggu_state)) ?></span>
                        <?php else: ?>
                            <?= dashboard_empty_state('Belum Ada Jadwal', 'Jadwalkan kegiatan tanam supaya ritme kerja tetap teratur.', 'Tambah Data Pertama', 'kalender.php') ?>
                        <?php endif; ?>
                        <a class="extra-link" href="kalender.php"><span>Kelola Kalender</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>

                <?php if ($show_critical_stock): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('stock') ?></span><span>Stok Kritis</span></h3>
                            <span class="section-badge <?= count($stok_kritis_items) > 0 ? 'state-critical' : 'state-safe' ?>"><?= count($stok_kritis_items) ?> item</span>
                        </div>
                        <?php if (count($stok_kritis_items) > 0): ?>
                            <ul class="mini-list">
                                <?php foreach ($stok_kritis_items as $stok): ?>
                                    <li class="state-critical">
                                        <span><?= htmlspecialchars($stok['nama_item']) ?></span>
                                        <strong><?= rtrim(rtrim(number_format((float) $stok['jumlah_stok'], 2, '.', ''), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float) $stok['stok_minimum'], 2, '.', ''), '0'), '.') ?> <?= htmlspecialchars($stok['satuan']) ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Tidak ada stok kritis. Kondisi inventaris aman.</p>
                        <?php endif; ?>
                        <a class="extra-link" href="inventaris.php"><span>Cek Inventaris</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>
            </section>

            <section class="dashboard-analytics-grid">
                <article class="dashboard-panel dashboard-panel--chart" data-reveal>
                    <div class="section-header">
                        <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('chart') ?></span><span>Grafik Jumlah Tanam</span></h3>
                        <span class="section-badge"><?= count($chart_data) ?> titik</span>
                    </div>
                    <?php if (count($chart_data) > 0): ?>
                        <div class="chart-wrapper">
                            <canvas id="chartCanvas"></canvas>
                        </div>
                    <?php else: ?>
                        <?= dashboard_empty_state('Grafik Belum Tersedia', 'Tambahkan data jurnal tanam agar grafik bisa ditampilkan.', 'Tambah Data Pertama', 'jurnal/tambah.php') ?>
                    <?php endif; ?>
                </article>

                <article class="dashboard-panel dashboard-panel--recent" data-reveal>
                    <div class="section-header">
                        <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('activity') ?></span><span>Aktivitas Terbaru</span></h3>
                        <span class="section-badge"><?= count($recent_plants) ?> data</span>
                    </div>
                    <?php if (count($recent_plants) > 0): ?>
                        <ul class="dashboard-timeline">
                            <?php foreach ($recent_plants as $plant): ?>
                                <?php $plant_state = dashboard_status_state_class($plant['status'] ?? ''); ?>
                                <li class="<?= htmlspecialchars($plant_state) ?>">
                                    <div class="plant-name"><?= htmlspecialchars($plant['nama_tanaman']) ?></div>
                                    <div class="plant-meta"><?= dashboard_format_date((string) $plant['tanggal_tanam'], $date_format) ?> | <?= rtrim(rtrim(number_format((float) $plant['jumlah'], 2, '.', ''), '0'), '.') ?> tanaman</div>
                                    <div class="plant-status <?= htmlspecialchars($plant_state) ?>"><?= htmlspecialchars($plant['status']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?= dashboard_empty_state('Aktivitas Masih Kosong', 'Mulai catat aktivitas tanam untuk melihat ringkasan harian.', 'Tambah Data Pertama', 'jurnal/tambah.php') ?>
                    <?php endif; ?>
                </article>
            </section>

            <section class="dashboard-support-grid">
                <?php if ($show_market): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('market') ?></span><span>Harga Pasar</span></h3>
                            <span class="section-badge"><?= count($latest_prices) ?> komoditas</span>
                        </div>
                        <?php if (count($latest_prices) > 0): ?>
                            <ul class="price-list">
                                <?php foreach ($latest_prices as $item): ?>
                                    <li>
                                        <span><?= htmlspecialchars($item['nama_komoditas']) ?></span>
                                        <strong>Rp <?= number_format((int) $item['harga'], 0, ',', '.') ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <?= dashboard_empty_state('Harga Pasar Masih Kosong', 'Data harga belum tersedia. Anda bisa isi data harga terbaru.', 'Tambah Data Pertama', 'harga_pasar.php') ?>
                        <?php endif; ?>
                        <a class="extra-link" href="harga_pasar.php"><span>Lihat Harga Pasar</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>

                <?php if ($show_plant_status): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('plant') ?></span><span>Status Tanaman</span></h3>
                            <span class="section-badge"><?= count($status_tanaman) ?> status</span>
                        </div>
                        <?php if (count($status_tanaman) > 0): ?>
                            <div class="mini-chart-wrapper">
                                <canvas id="statusChartCanvas"></canvas>
                            </div>
                            <ul class="mini-list mini-list--compact">
                                <?php foreach ($status_tanaman as $status): ?>
                                    <?php $status_state = dashboard_status_state_class($status['status'] ?? ''); ?>
                                    <li class="<?= htmlspecialchars($status_state) ?>">
                                        <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $status['status']))) ?></span>
                                        <strong><?= (int) $status['total'] ?> data</strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <?= dashboard_empty_state('Status Belum Tercatat', 'Isi jurnal tanam untuk mulai membangun data status tanaman.', 'Tambah Data Pertama', 'jurnal/tambah.php') ?>
                        <?php endif; ?>
                        <a class="extra-link" href="jurnal/index.php"><span>Buka Jurnal Tanam</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>

                <?php if ($show_complaint): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('complaint') ?></span><span>Pengaduan</span></h3>
                            <span class="section-badge <?= htmlspecialchars($pengaduan_aktif > 0 ? $pengaduan_state : 'state-safe') ?>"><?= $pengaduan_aktif ?> aktif</span>
                        </div>
                        <?php if ($latest_pengaduan): ?>
                            <div class="extra-highlight"><?= htmlspecialchars($latest_pengaduan['judul']) ?></div>
                            <p>Status: <strong><?= htmlspecialchars(ucfirst($latest_pengaduan['status'])) ?></strong></p>
                            <p>Dikirim: <?= dashboard_format_date((string) $latest_pengaduan['created_at'], $date_format . ' H:i') ?></p>
                            <span class="state-chip <?= htmlspecialchars($latest_pengaduan_state) ?>"><?= htmlspecialchars(dashboard_state_label($latest_pengaduan_state)) ?></span>
                        <?php else: ?>
                            <?= dashboard_empty_state('Belum Ada Pengaduan', 'Jika ada kendala, kirim pengaduan agar tim dapat membantu.', 'Tambah Data Pertama', 'pengaduan.php') ?>
                        <?php endif; ?>
                        <a class="extra-link" href="pengaduan.php"><span>Buka Pengaduan</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>

                <?php if ($is_normal_mode): ?>
                    <article class="dashboard-panel" data-reveal>
                        <div class="section-header">
                            <h3 class="dashboard-title-with-icon"><span class="dashboard-title-icon" aria-hidden="true"><?= dashboard_ui_icon('summary') ?></span><span>Kelengkapan Data</span></h3>
                            <span class="section-badge">Mode Normal</span>
                        </div>
                        <ul class="mini-list">
                            <li><span>Aktivitas Terbaru</span><strong><?= count($recent_plants) ?> / <?= $limit_recent_activities ?></strong></li>
                            <li><span>Harga Pasar</span><strong><?= count($latest_prices) ?> / <?= $limit_market_prices ?></strong></li>
                            <li><span>Status Tanaman</span><strong><?= count($status_tanaman) ?> / <?= $limit_plant_status ?></strong></li>
                        </ul>
                    </article>
                <?php endif; ?>

                <?php if (!$has_extra_cards): ?>
                    <article class="dashboard-panel" data-reveal>
                        <h3>Kartu Tambahan Dinonaktifkan</h3>
                        <p>Semua kartu tambahan sedang disembunyikan dari preferensi dashboard.</p>
                        <a class="extra-link" href="pengaturan.php?tab=dashboard"><span>Atur Preferensi Dashboard</span><span class="extra-link__icon" aria-hidden="true"><?= dashboard_ui_icon('arrow') ?></span></a>
                    </article>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>

<script>
    const chartLabels = <?= json_encode(array_column($chart_data, 'nama_tanaman')) ?>;
    const chartValues = <?= json_encode(array_map(static fn($row) => (float) $row['jumlah'], $chart_data)) ?>;
    const statusLabels = <?= json_encode(array_map(static fn($row) => ucwords(str_replace('_', ' ', (string) $row['status'])), $status_tanaman)) ?>;
    const statusValues = <?= json_encode(array_map(static fn($row) => (int) $row['total'], $status_tanaman)) ?>;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const lowPowerMode = Boolean(
        (navigator.deviceMemory && navigator.deviceMemory <= 4) ||
        (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) ||
        (navigator.connection && navigator.connection.saveData)
    );
    const enableMotion = false;
    const chartAnimationDuration = 0;

    if (lowPowerMode) {
        document.documentElement.classList.add('dashboard-lowpower');
    }

    const revealCards = Array.from(document.querySelectorAll('.dashboard-v2 [data-reveal]'));
    if (enableMotion) {
        document.documentElement.classList.add('dashboard-js');
    }

    if (!enableMotion || !('IntersectionObserver' in window)) {
        revealCards.forEach((card) => card.classList.add('is-visible'));
    } else if (revealCards.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });

        revealCards.forEach((card, index) => {
            card.style.setProperty('--reveal-delay', `${Math.min(index * 45, 320)}ms`);
            revealObserver.observe(card);
        });
    }

    const defaultTooltip = {
        backgroundColor: 'rgba(18, 45, 28, 0.92)',
        titleColor: '#f4fff6',
        bodyColor: '#d9ecdb',
        borderColor: 'rgba(121, 183, 141, 0.45)',
        borderWidth: 1,
        cornerRadius: 12,
        padding: 12,
        displayColors: false,
        titleFont: { weight: '700', size: 12 },
        bodyFont: { weight: '600', size: 12 }
    };

    const chartCanvas = document.getElementById('chartCanvas');
    if (chartCanvas && chartLabels.length > 0) {
        const ctx = chartCanvas.getContext('2d');
        const chartType = chartLabels.length <= 2 ? 'doughnut' : 'bar';
        const greenGradient = ctx.createLinearGradient(0, 0, 0, 320);
        greenGradient.addColorStop(0, 'rgba(66, 167, 102, 0.95)');
        greenGradient.addColorStop(1, 'rgba(33, 110, 67, 0.78)');

        const doughnutColors = [
            'rgba(46, 142, 79, 0.92)',
            'rgba(94, 179, 123, 0.88)',
            'rgba(151, 214, 166, 0.85)',
            'rgba(210, 237, 211, 0.88)',
            'rgba(229, 245, 231, 0.92)'
        ];

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: chartValues,
                    backgroundColor: chartType === 'bar' ? greenGradient : doughnutColors,
                    borderColor: chartType === 'bar' ? 'rgba(28, 96, 58, 0.72)' : 'rgba(248, 252, 248, 0.96)',
                    borderWidth: chartType === 'bar' ? 1 : 2,
                    borderRadius: chartType === 'bar' ? 4 : 0,
                    borderSkipped: false,
                    hoverBackgroundColor: chartType === 'bar' ? 'rgba(39, 132, 79, 0.96)' : doughnutColors,
                    hoverOffset: chartType === 'bar' ? 0 : 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: chartAnimationDuration,
                    easing: 'easeOutCubic'
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: chartType !== 'bar',
                        position: 'bottom',
                        labels: {
                            color: '#45604f',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14
                        }
                    },
                    tooltip: defaultTooltip
                },
                scales: chartType === 'bar' ? {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: '#4d6958',
                            font: { weight: '600' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#5f7767',
                            font: { weight: '600' }
                        },
                        grid: {
                            color: 'rgba(34, 78, 50, 0.10)',
                            drawBorder: false
                        }
                    }
                } : {}
            }
        });
    }

    const statusChartCanvas = document.getElementById('statusChartCanvas');
    if (statusChartCanvas && statusLabels.length > 0) {
        const statusCtx = statusChartCanvas.getContext('2d');
        const statusPalette = [
            'rgba(55, 155, 89, 0.92)',
            'rgba(110, 188, 134, 0.9)',
            'rgba(235, 178, 79, 0.9)',
            'rgba(205, 94, 88, 0.9)',
            'rgba(146, 205, 161, 0.88)'
        ];

        const mappedStatusColors = statusLabels.map((label, index) => {
            const normalized = label.toLowerCase();
            if (normalized.includes('panen') || normalized.includes('aman') || normalized.includes('selesai')) {
                return 'rgba(52, 150, 87, 0.92)';
            }
            if (normalized.includes('tanam') || normalized.includes('proses') || normalized.includes('tumbuh')) {
                return 'rgba(236, 177, 74, 0.92)';
            }
            if (normalized.includes('gagal') || normalized.includes('rusak') || normalized.includes('kritis')) {
                return 'rgba(203, 88, 84, 0.92)';
            }
            return statusPalette[index % statusPalette.length];
        });

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: mappedStatusColors,
                    borderColor: 'rgba(247, 251, 247, 0.96)',
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                animation: {
                    duration: chartAnimationDuration,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#4a6654',
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 12
                        }
                    },
                    tooltip: defaultTooltip
                }
            }
        });
    }
</script>
</body>
</html>
