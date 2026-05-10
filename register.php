<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error_code = (string) ($_GET['error'] ?? '');
$full_name = trim((string) ($_GET['full_name'] ?? ''));
$email = trim((string) ($_GET['email'] ?? ''));
$username = trim((string) ($_GET['username'] ?? ''));

if (mb_strlen($full_name) > 120) {
    $full_name = mb_substr($full_name, 0, 120);
}
if (mb_strlen($email) > 160) {
    $email = mb_substr($email, 0, 160);
}
if (mb_strlen($username) > 50) {
    $username = mb_substr($username, 0, 50);
}

$error_message = '';
if ($error_code !== '') {
    $error_message = match ($error_code) {
        'empty' => 'Username, password, dan konfirmasi password wajib diisi.',
        'username_format' => 'Username minimal 3 karakter, hanya huruf, angka, titik, garis bawah, atau minus.',
        'password_short' => 'Password minimal 6 karakter.',
        'password_mismatch' => 'Konfirmasi password tidak cocok.',
        'email_invalid' => 'Format email tidak valid.',
        'username_taken' => 'Username sudah dipakai. Gunakan username lain.',
        default => 'Gagal membuat akun. Silakan coba lagi.',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun - Fresh Smart Farm</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a href="index.php" class="auth-brand">Fresh Smart Farm</a>

        <section class="auth-card" aria-labelledby="registerTitle">
            <header class="auth-head">
                <h1 id="registerTitle">Buat Akun Baru</h1>
                <p>Daftarkan akun untuk mulai menggunakan fitur pertanian digital.</p>
            </header>

            <?php if ($error_message !== ''): ?>
                <div class="auth-alert auth-alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="proses_register.php" method="POST" class="auth-form" novalidate>
                <div class="auth-field">
                    <label for="full_name">Nama Lengkap (opsional)</label>
                    <input type="text" id="full_name" name="full_name" maxlength="120" value="<?= htmlspecialchars($full_name) ?>" placeholder="Contoh: Hanif Pratama">
                </div>

                <div class="auth-field">
                    <label for="email">Email (opsional)</label>
                    <input type="email" id="email" name="email" maxlength="160" value="<?= htmlspecialchars($email) ?>" placeholder="Contoh: hanif@email.com">
                </div>

                <div class="auth-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" maxlength="50" value="<?= htmlspecialchars($username) ?>" placeholder="Minimal 3 karakter" required>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="6" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="auth-field">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="auth-btn">Buat Akun</button>
            </form>

            <p class="auth-switch">Sudah punya akun? <a href="login.php">Login sekarang</a></p>
        </section>
    </main>
</body>
</html>
