<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error_code = (string) ($_GET['error'] ?? '');
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
$username_prefill = trim((string) ($_GET['u'] ?? ''));
if (mb_strlen($username_prefill) > 50) {
    $username_prefill = mb_substr($username_prefill, 0, 50);
}

$error_message = '';
if ($error_code !== '') {
    $error_message = match ($error_code) {
        'empty' => 'Username dan password wajib diisi.',
        'invalid' => 'Username atau password tidak sesuai.',
        default => 'Gagal login. Silakan coba lagi.',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fresh Smart Farm</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a href="index.php" class="auth-brand">Fresh Smart Farm</a>

        <section class="auth-card" aria-labelledby="authTitle">
            <header class="auth-head">
                <h1 id="authTitle">LOGIN</h1>
                <p>Gunakan akun Anda untuk mengakses dashboard pertanian.</p>
            </header>

            <?php if ($registered): ?>
                <div class="auth-alert auth-alert-success">Akun berhasil dibuat. Silakan login.</div>
            <?php endif; ?>

            <?php if ($error_message !== ''): ?>
                <div class="auth-alert auth-alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST" class="auth-form" novalidate>
                <div class="auth-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" maxlength="50" value="<?= htmlspecialchars($username_prefill) ?>" placeholder="Masukkan username" required>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="auth-btn">Login</button>
            </form>

            <p class="auth-switch">Belum punya akun? <a href="register.php">Buat akun</a></p>
        </section>
    </main>
</body>
</html>
