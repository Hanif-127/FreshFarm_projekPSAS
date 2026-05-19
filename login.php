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
    <title>Login - Fresh Farm</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <main class="login-page">

        <!-- BAGIAN KIRI -->
        <section class="login-left">

            <div class="login-box">

                <a href="index.php" class="logo-wrap">
                    <img src="assets/images/logo.png" alt="Fresh Farm Logo">
                </a>

                <h1>Sign in</h1>

                <div class="social-login">
                    <span>G</span>
                    <span>f</span>
                    <span>♥</span>
                </div>

                <?php if ($registered): ?>
                    <div class="alert success">Akun berhasil dibuat. Silakan login.</div>
                <?php endif; ?>

                <?php if ($error_message !== ''): ?>
                    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <form action="proses_login.php" method="POST" class="login-form">
                    <input
                        type="text"
                        name="username"
                        maxlength="50"
                        value="<?= htmlspecialchars($username_prefill) ?>"
                        placeholder="Username"
                        required
                    >

                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                    >

                    <a href="lupa_sandi.php" class="forgot">Lupa kata sandi anda?</a>

                    <button type="submit">SIGN IN</button>
                </form>

                <p class="register-text">
                    Belum punya akun? <a href="register.php">Buat akun</a>
                </p>

            </div>

        </section>

        <!-- BAGIAN KANAN -->
        <section class="login-right">
            <div class="right-content">
                <h2>Welcome, SobatTani</h2>
                <p>
                    Ayo masukkan akunmu yang sudah dibuat, dan mulai untuk ke halaman landing page
                </p>
            </div>
        </section>

    </main>

</body>
</html>
