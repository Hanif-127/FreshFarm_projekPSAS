<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/koneksi.php';
include '../includes/user_settings.php';
include '../includes/module_icons.php';

$dashboard_base_css_version = filemtime(__DIR__ . '/../assets/css/dashboard_base.css');
$pengaturan_css_version = filemtime(__DIR__ . '/../assets/css/pengaturan.css');
$app_base_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$user_id = (int) $_SESSION['user_id'];

$allowed_tabs = ['general', 'account', 'notifications', 'dashboard'];
$active_tab = $_GET['tab'] ?? 'general';
if (!in_array($active_tab, $allowed_tabs, true)) {
    $active_tab = 'general';
}

$messages = [];
$error = '';

$user_stmt = mysqli_prepare($koneksi, "SELECT username, password FROM users WHERE id = ? LIMIT 1");
$user = ['username' => $_SESSION['username'] ?? '', 'password' => ''];
if ($user_stmt) {
    mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
    mysqli_stmt_execute($user_stmt);
    $result = mysqli_stmt_get_result($user_stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $user = $row;
    }
    mysqli_stmt_close($user_stmt);
}

$settings = user_settings_get($koneksi, $user_id);

$format_tanggal_options = [
    'd M Y' => '10 Mei 2026',
    'd/m/Y' => '10/05/2026',
    'Y-m-d' => '2026-05-10',
];
$satuan_options = ['kg', 'gram', 'liter', 'unit'];
$timezone_options = ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'general') {
        $active_tab = 'general';
        $kebun_nama = trim($_POST['kebun_nama'] ?? '');
        $kebun_lokasi = trim($_POST['kebun_lokasi'] ?? '');
        $satuan_utama = trim($_POST['satuan_utama'] ?? 'kg');
        $format_tanggal = trim($_POST['format_tanggal'] ?? 'd M Y');
        $timezone = trim($_POST['timezone'] ?? 'Asia/Jakarta');

        if ($kebun_nama === '') {
            $error = 'Nama kebun wajib diisi.';
        } elseif (mb_strlen($kebun_nama) > 120) {
            $error = 'Nama kebun maksimal 120 karakter.';
        } elseif (!in_array($satuan_utama, $satuan_options, true)) {
            $error = 'Satuan utama tidak valid.';
        } elseif (!isset($format_tanggal_options[$format_tanggal])) {
            $error = 'Format tanggal tidak valid.';
        } elseif (!in_array($timezone, $timezone_options, true)) {
            $error = 'Zona waktu tidak valid.';
        } else {
            $settings['kebun_nama'] = $kebun_nama;
            $settings['kebun_lokasi'] = mb_substr($kebun_lokasi, 0, 180);
            $settings['satuan_utama'] = $satuan_utama;
            $settings['format_tanggal'] = $format_tanggal;
            $settings['timezone'] = $timezone;

            if (user_settings_upsert($koneksi, $user_id, $settings)) {
                $messages[] = 'Pengaturan umum berhasil disimpan.';
            } else {
                $error = 'Gagal menyimpan pengaturan umum.';
            }
        }
    } elseif ($action === 'account') {
        $active_tab = 'account';
        $username = trim($_POST['username'] ?? '');
        $full_name = trim($_POST['account_full_name'] ?? '');
        $email = trim($_POST['account_email'] ?? '');
        $phone = trim($_POST['account_phone'] ?? '');

        if ($username === '') {
            $error = 'Username wajib diisi.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
            $error = 'Username hanya boleh huruf, angka, titik, underscore, atau strip (3-40 karakter).';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,30}$/', $phone)) {
            $error = 'Nomor HP tidak valid.';
        } else {
            $exists_stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
            $username_used = false;
            if ($exists_stmt) {
                mysqli_stmt_bind_param($exists_stmt, 'si', $username, $user_id);
                mysqli_stmt_execute($exists_stmt);
                $exists_result = mysqli_stmt_get_result($exists_stmt);
                $username_used = mysqli_num_rows($exists_result) > 0;
                mysqli_stmt_close($exists_stmt);
            }

            if ($username_used) {
                $error = 'Username sudah digunakan akun lain.';
            } else {
                $update_user_stmt = mysqli_prepare($koneksi, 'UPDATE users SET username = ? WHERE id = ?');
                $updated_user = false;
                if ($update_user_stmt) {
                    mysqli_stmt_bind_param($update_user_stmt, 'si', $username, $user_id);
                    $updated_user = mysqli_stmt_execute($update_user_stmt);
                    mysqli_stmt_close($update_user_stmt);
                }

                if (!$updated_user) {
                    $error = 'Gagal memperbarui data akun.';
                } else {
                    $_SESSION['username'] = $username;
                    $user['username'] = $username;

                    $settings['account_full_name'] = mb_substr($full_name, 0, 120);
                    $settings['account_email'] = mb_substr($email, 0, 160);
                    $settings['account_phone'] = mb_substr($phone, 0, 30);

                    if (user_settings_upsert($koneksi, $user_id, $settings)) {
                        $messages[] = 'Data akun berhasil diperbarui.';
                    } else {
                        $error = 'Data akun tersimpan sebagian. Profil tambahan gagal diperbarui.';
                    }
                }
            }
        }
    } elseif ($action === 'password') {
        $active_tab = 'account';
        $current_password = (string) ($_POST['current_password'] ?? '');
        $new_password = (string) ($_POST['new_password'] ?? '');
        $confirm_password = (string) ($_POST['confirm_password'] ?? '');

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error = 'Semua field password wajib diisi.';
        } elseif (md5($current_password) !== (string) $user['password']) {
            $error = 'Password saat ini tidak cocok.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Konfirmasi password baru tidak sama.';
        } else {
            $new_hash = md5($new_password);
            $pass_stmt = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id = ?');
            if ($pass_stmt) {
                mysqli_stmt_bind_param($pass_stmt, 'si', $new_hash, $user_id);
                $ok = mysqli_stmt_execute($pass_stmt);
                mysqli_stmt_close($pass_stmt);

                if ($ok) {
                    $user['password'] = $new_hash;
                    $messages[] = 'Password berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui password.';
                }
            } else {
                $error = 'Gagal memperbarui password.';
            }
        }
    } elseif ($action === 'notifications') {
        $active_tab = 'notifications';
        $settings['notif_jadwal'] = isset($_POST['notif_jadwal']) ? 1 : 0;
        $settings['notif_stok'] = isset($_POST['notif_stok']) ? 1 : 0;
        $settings['notif_pengaduan'] = isset($_POST['notif_pengaduan']) ? 1 : 0;
        $settings['notif_ringkasan'] = isset($_POST['notif_ringkasan']) ? 1 : 0;
        $settings['notif_email'] = isset($_POST['notif_email']) ? 1 : 0;

        if (user_settings_upsert($koneksi, $user_id, $settings)) {
            $messages[] = 'Preferensi notifikasi berhasil disimpan.';
        } else {
            $error = 'Gagal menyimpan notifikasi.';
        }
    } elseif ($action === 'dashboard') {
        $active_tab = 'dashboard';
        $dashboard_mode = trim($_POST['dashboard_mode'] ?? 'compact');

        $limit_recent = (int) ($_POST['limit_recent_activities'] ?? 4);
        $limit_prices = (int) ($_POST['limit_market_prices'] ?? 4);
        $limit_status = (int) ($_POST['limit_plant_status'] ?? 5);

        if (!in_array($dashboard_mode, ['compact', 'normal'], true)) {
            $error = 'Mode dashboard tidak valid.';
        } else {
            $settings['dashboard_mode'] = $dashboard_mode;
            if ($dashboard_mode === 'normal') {
                // Mode normal dirancang lebih penuh, jadi semua widget utama aktif.
                $settings['show_focus'] = 1;
                $settings['show_quick_actions'] = 1;
                $settings['show_schedule'] = 1;
                $settings['show_market'] = 1;
                $settings['show_complaint'] = 1;
                $settings['show_critical_stock'] = 1;
                $settings['show_plant_status'] = 1;

                // Mode normal menampilkan data lebih banyak dari compact.
                $settings['limit_recent_activities'] = max(6, min(10, $limit_recent));
                $settings['limit_market_prices'] = max(6, min(10, $limit_prices));
                $settings['limit_plant_status'] = max(6, min(10, $limit_status));
            } else {
                $settings['show_focus'] = isset($_POST['show_focus']) ? 1 : 0;
                $settings['show_quick_actions'] = isset($_POST['show_quick_actions']) ? 1 : 0;
                $settings['show_schedule'] = isset($_POST['show_schedule']) ? 1 : 0;
                $settings['show_market'] = isset($_POST['show_market']) ? 1 : 0;
                $settings['show_complaint'] = isset($_POST['show_complaint']) ? 1 : 0;
                $settings['show_critical_stock'] = isset($_POST['show_critical_stock']) ? 1 : 0;
                $settings['show_plant_status'] = isset($_POST['show_plant_status']) ? 1 : 0;

                $settings['limit_recent_activities'] = max(2, min(10, $limit_recent));
                $settings['limit_market_prices'] = max(2, min(10, $limit_prices));
                $settings['limit_plant_status'] = max(2, min(10, $limit_status));
            }

            if (user_settings_upsert($koneksi, $user_id, $settings)) {
                $messages[] = 'Preferensi dashboard berhasil disimpan.';
            } else {
                $error = 'Gagal menyimpan preferensi dashboard.';
            }
        }
    }

    $settings = user_settings_get($koneksi, $user_id);
}

function settings_tab_active(string $tab, string $active_tab): string
{
    return $tab === $active_tab ? 'is-active' : '';
}

function settings_section_active(string $tab, string $active_tab): string
{
    return $tab === $active_tab ? 'is-active' : 'is-hidden';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Fresh Smart Farm</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/dashboard_base.css?v=<?= (int) $dashboard_base_css_version ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($app_base_path) ?>/assets/css/pengaturan.css?v=<?= (int) $pengaturan_css_version ?>">
</head>
<body class="module-page">
<?php include '../includes/header.php'; ?>

<div class="dashboard-shell-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="dashboard-main-content">
        <div class="module-wrap settings-wrap">
            <section class="module-card settings-hero-card">
                <h1>Pengaturan & Pusat Akun</h1>
                <p>Kelola preferensi umum, akun, notifikasi, dan tata letak dashboard Anda.</p>
            </section>

            <?php if ($error !== ''): ?>
                <div class="module-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php foreach ($messages as $msg): ?>
                <div class="module-msg"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>

            <section class="settings-layout">
                <aside class="module-card settings-nav-card">
                    <a class="settings-nav-link <?= settings_tab_active('general', $active_tab) ?>" href="pengaturan.php?tab=general"><span class="settings-nav-icon" aria-hidden="true"><?= module_ui_icon('settings') ?></span><span>Pengaturan Umum</span></a>
                    <a class="settings-nav-link <?= settings_tab_active('account', $active_tab) ?>" href="pengaturan.php?tab=account"><span class="settings-nav-icon" aria-hidden="true"><?= module_ui_icon('user') ?></span><span>Pusat Akun</span></a>
                    <a class="settings-nav-link <?= settings_tab_active('notifications', $active_tab) ?>" href="pengaturan.php?tab=notifications"><span class="settings-nav-icon" aria-hidden="true"><?= module_ui_icon('bell') ?></span><span>Notifikasi</span></a>
                    <a class="settings-nav-link <?= settings_tab_active('dashboard', $active_tab) ?>" href="pengaturan.php?tab=dashboard"><span class="settings-nav-icon" aria-hidden="true"><?= module_ui_icon('chart') ?></span><span>Preferensi Dashboard</span></a>
                </aside>

                <div class="settings-content-stack">
                    <section class="module-card settings-section <?= settings_section_active('general', $active_tab) ?>">
                        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('settings') ?></span><span>Pengaturan Umum</span></h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="general">
                            <div class="module-grid">
                                <div class="module-field jurnal-field-span-2">
                                    <label>Nama Kebun</label>
                                    <input type="text" name="kebun_nama" maxlength="120" value="<?= htmlspecialchars((string) $settings['kebun_nama']) ?>" required>
                                </div>
                                <div class="module-field jurnal-field-span-2">
                                    <label>Lokasi Kebun</label>
                                    <input type="text" name="kebun_lokasi" maxlength="180" placeholder="Contoh: Sleman, Yogyakarta" value="<?= htmlspecialchars((string) $settings['kebun_lokasi']) ?>">
                                </div>
                                <div class="module-field">
                                    <label>Satuan Utama</label>
                                    <select name="satuan_utama" required>
                                        <?php foreach ($satuan_options as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>" <?= $settings['satuan_utama'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper($opt)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="module-field">
                                    <label>Zona Waktu</label>
                                    <select name="timezone" required>
                                        <?php foreach ($timezone_options as $tz): ?>
                                            <option value="<?= htmlspecialchars($tz) ?>" <?= $settings['timezone'] === $tz ? 'selected' : '' ?>><?= htmlspecialchars($tz) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="module-field">
                                    <label>Format Tanggal</label>
                                    <select name="format_tanggal" required>
                                        <?php foreach ($format_tanggal_options as $format => $example): ?>
                                            <option value="<?= htmlspecialchars($format) ?>" <?= $settings['format_tanggal'] === $format ? 'selected' : '' ?>><?= htmlspecialchars($example) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="module-actions">
                                <button class="module-btn module-btn-primary" type="submit">Simpan Umum</button>
                            </div>
                        </form>
                    </section>

                    <section class="module-card settings-section <?= settings_section_active('account', $active_tab) ?>">
                        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('user') ?></span><span>Pusat Akun</span></h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="account">
                            <div class="module-grid">
                                <div class="module-field">
                                    <label>Username</label>
                                    <input type="text" name="username" maxlength="40" value="<?= htmlspecialchars((string) $user['username']) ?>" required>
                                </div>
                                <div class="module-field">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="account_full_name" maxlength="120" value="<?= htmlspecialchars((string) $settings['account_full_name']) ?>">
                                </div>
                                <div class="module-field">
                                    <label>Email</label>
                                    <input type="email" name="account_email" maxlength="160" value="<?= htmlspecialchars((string) $settings['account_email']) ?>">
                                </div>
                                <div class="module-field">
                                    <label>Nomor HP</label>
                                    <input type="text" name="account_phone" maxlength="30" value="<?= htmlspecialchars((string) $settings['account_phone']) ?>">
                                </div>
                            </div>
                            <div class="module-actions">
                                <button class="module-btn module-btn-primary" type="submit">Simpan Akun</button>
                            </div>
                        </form>

                        <hr class="settings-divider">

                        <h3 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('lock') ?></span><span>Keamanan Akun</span></h3>
                        <form method="POST" class="settings-password-form">
                            <input type="hidden" name="action" value="password">
                            <div class="module-grid">
                                <div class="module-field">
                                    <label>Password Saat Ini</label>
                                    <input type="password" name="current_password" required>
                                </div>
                                <div class="module-field">
                                    <label>Password Baru</label>
                                    <input type="password" name="new_password" minlength="6" required>
                                </div>
                                <div class="module-field">
                                    <label>Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" minlength="6" required>
                                </div>
                            </div>
                            <div class="module-actions">
                                <button class="module-btn module-btn-primary" type="submit">Ubah Password</button>
                            </div>
                        </form>
                    </section>

                    <section class="module-card settings-section <?= settings_section_active('notifications', $active_tab) ?>">
                        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('bell') ?></span><span>Notifikasi</span></h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="notifications">
                            <div class="settings-check-grid">
                                <label class="settings-check-item"><input type="checkbox" name="notif_jadwal" <?= (int) $settings['notif_jadwal'] === 1 ? 'checked' : '' ?>>Pengingat jadwal tanam</label>
                                <label class="settings-check-item"><input type="checkbox" name="notif_stok" <?= (int) $settings['notif_stok'] === 1 ? 'checked' : '' ?>>Peringatan stok menipis</label>
                                <label class="settings-check-item"><input type="checkbox" name="notif_pengaduan" <?= (int) $settings['notif_pengaduan'] === 1 ? 'checked' : '' ?>>Update status pengaduan</label>
                                <label class="settings-check-item"><input type="checkbox" name="notif_ringkasan" <?= (int) $settings['notif_ringkasan'] === 1 ? 'checked' : '' ?>>Ringkasan mingguan</label>
                                <label class="settings-check-item"><input type="checkbox" name="notif_email" <?= (int) $settings['notif_email'] === 1 ? 'checked' : '' ?>>Kirim juga lewat email</label>
                            </div>
                            <div class="module-actions">
                                <button class="module-btn module-btn-primary" type="submit">Simpan Notifikasi</button>
                            </div>
                        </form>
                    </section>

                    <section class="module-card settings-section <?= settings_section_active('dashboard', $active_tab) ?>">
                        <h2 class="module-title-with-icon"><span class="module-title-icon" aria-hidden="true"><?= module_ui_icon('chart') ?></span><span>Preferensi Dashboard</span></h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="dashboard">

                            <div class="settings-subhead">Mode Tampilan</div>
                            <div class="settings-radio-grid">
                                <label class="settings-radio-item"><input type="radio" name="dashboard_mode" value="compact" <?= $settings['dashboard_mode'] === 'compact' ? 'checked' : '' ?>>Compact</label>
                                <label class="settings-radio-item"><input type="radio" name="dashboard_mode" value="normal" <?= $settings['dashboard_mode'] === 'normal' ? 'checked' : '' ?>>Normal</label>
                            </div>

                            <div class="settings-subhead">Kartu yang Ditampilkan</div>
                            <div class="settings-check-grid">
                                <label class="settings-check-item"><input type="checkbox" name="show_focus" <?= (int) $settings['show_focus'] === 1 ? 'checked' : '' ?>>Fokus Hari Ini</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_quick_actions" <?= (int) $settings['show_quick_actions'] === 1 ? 'checked' : '' ?>>Aksi Cepat</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_schedule" <?= (int) $settings['show_schedule'] === 1 ? 'checked' : '' ?>>Jadwal Terdekat</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_market" <?= (int) $settings['show_market'] === 1 ? 'checked' : '' ?>>Harga Pasar</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_complaint" <?= (int) $settings['show_complaint'] === 1 ? 'checked' : '' ?>>Status Pengaduan</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_critical_stock" <?= (int) $settings['show_critical_stock'] === 1 ? 'checked' : '' ?>>Stok Kritis</label>
                                <label class="settings-check-item"><input type="checkbox" name="show_plant_status" <?= (int) $settings['show_plant_status'] === 1 ? 'checked' : '' ?>>Status Tanaman</label>
                            </div>

                            <div class="settings-subhead">Jumlah Data Default</div>
                            <div class="module-grid">
                                <div class="module-field">
                                    <label>Aktivitas Terbaru</label>
                                    <input type="number" name="limit_recent_activities" min="2" max="10" value="<?= (int) $settings['limit_recent_activities'] ?>" required>
                                </div>
                                <div class="module-field">
                                    <label>Harga Pasar</label>
                                    <input type="number" name="limit_market_prices" min="2" max="10" value="<?= (int) $settings['limit_market_prices'] ?>" required>
                                </div>
                                <div class="module-field">
                                    <label>Status Tanaman</label>
                                    <input type="number" name="limit_plant_status" min="2" max="10" value="<?= (int) $settings['limit_plant_status'] ?>" required>
                                </div>
                            </div>

                            <div class="module-actions">
                                <button class="module-btn module-btn-primary" type="submit">Simpan Preferensi Dashboard</button>
                            </div>
                        </form>
                    </section>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
