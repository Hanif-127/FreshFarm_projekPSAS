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
    <title>Buat Akun - Fresh Farm</title>
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

    <main class="register-page">

        <!-- BAGIAN KIRI -->
        <section class="register-left">

            <div class="register-box">

                <a href="index.php" class="logo-wrap">
                    <img src="assets/images/logo.png" alt="Fresh Farm Logo">
                </a>

                <h1>Sign up</h1>

                <div class="social-login">
                    <span>G</span>
                    <span>f</span>
                    <span>♥</span>
                </div>

                <?php if ($error_message !== ''): ?>
                    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <form action="proses_register.php" method="POST" class="register-form">

                    <input 
                        type="text" 
                        name="full_name" 
                        maxlength="120"
                        value="<?= htmlspecialchars($full_name) ?>" 
                        placeholder="Nama Lengkap"
                    >

                    <input 
                        type="email" 
                        name="email" 
                        maxlength="160"
                        value="<?= htmlspecialchars($email) ?>" 
                        placeholder="Email"
                    >

                    <input 
                        type="text" 
                        name="username" 
                        maxlength="50"
                        value="<?= htmlspecialchars($username) ?>" 
                        placeholder="Username"
                        required
                    >

                    <input 
                        type="password" 
                        name="password" 
                        minlength="6"
                        placeholder="Password"
                        required
                    >

                    <input 
                        type="password" 
                        name="confirm_password" 
                        minlength="6"
                        placeholder="Konfirmasi Password"
                        required
                    >

                    <button type="submit">SIGN UP</button>
                </form>

                <p class="login-text">
                    Sudah punya akun? <a href="login.php">Login sekarang</a>
                </p>

            </div>

        </section>

        <!-- BAGIAN KANAN -->
        <section class="register-right">
            <div class="right-content">
                <h2>Hello, SobatTani</h2>
                <p>
                    Buat akunmu sekarang dan mulai gunakan Fresh Farm untuk mengelola pertanian digital dengan lebih mudah.
                </p>
            </div>
        </section>

    </main>

</body>
</html>