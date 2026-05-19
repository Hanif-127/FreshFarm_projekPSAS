<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

include 'includes/koneksi.php';
include 'includes/user_settings.php';

$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirm_password = (string) ($_POST['confirm_password'] ?? '');
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($username === '' || $password === '' || $confirm_password === '') {
        $error_message = 'Username, password baru, dan konfirmasi password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password baru minimal 6 karakter.';
    } elseif (!hash_equals($password, $confirm_password)) {
        $error_message = 'Konfirmasi password baru tidak cocok.';
    } elseif ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $error_message = 'Format email tidak valid.';
    } else {
        user_settings_ensure_table($koneksi);

        $stmt = mysqli_prepare(
            $koneksi,
            'SELECT u.id, COALESCE(us.account_email, "") AS account_email
             FROM users u
             LEFT JOIN user_settings us ON us.user_id = u.id
             WHERE u.username = ?
             LIMIT 1'
        );

        if (!$stmt) {
            $error_message = 'Reset sandi belum bisa diproses. Silakan coba lagi.';
        } else {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result) ?: null;
            mysqli_stmt_close($stmt);

            if (!$user) {
                $error_message = 'Data akun tidak ditemukan.';
            } else {
                $stored_email = trim((string) ($user['account_email'] ?? ''));
                if ($stored_email !== '' && !hash_equals(strtolower($stored_email), strtolower($email))) {
                    $error_message = 'Email tidak cocok dengan akun tersebut.';
                } else {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $user_id = (int) $user['id'];
                    $update_stmt = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id = ? LIMIT 1');

                    if (!$update_stmt) {
                        $error_message = 'Reset sandi gagal. Silakan coba lagi.';
                    } else {
                        mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $user_id);
                        $updated = mysqli_stmt_execute($update_stmt);
                        mysqli_stmt_close($update_stmt);

                        if ($updated) {
                            $success_message = 'Password berhasil diperbarui. Silakan login dengan password baru.';
                            $username = '';
                            $email = '';
                        } else {
                            $error_message = 'Reset sandi gagal. Silakan coba lagi.';
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Sandi - Fresh Farm</title>
    <link rel="stylesheet" href="assets/css/lupa_sandi.css">
</head>
<body>
    <main class="forgot-page">
        <section class="forgot-panel">
            <a href="index.php" class="logo-wrap">
                <img src="assets/images/logo.png" alt="Fresh Farm Logo">
            </a>

            <span class="forgot-eyebrow">Pemulihan akun</span>
            <h1>Lupa sandi?</h1>
            <p class="forgot-copy">Masukkan username, email akun jika ada, lalu buat password baru. Akun lama yang belum punya email tetap bisa dipulihkan dengan username.</p>

            <?php if ($success_message !== ''): ?>
                <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if ($error_message !== ''): ?>
                <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="lupa_sandi.php" method="POST" class="forgot-form">
                <label>
                    <span>Username</span>
                    <input type="text" name="username" maxlength="50" value="<?= htmlspecialchars($username) ?>" placeholder="Masukkan username" required>
                </label>

                <label>
                    <span>Email akun</span>
                    <input type="email" name="email" maxlength="160" value="<?= htmlspecialchars($email) ?>" placeholder="Opsional jika akun belum punya email">
                </label>

                <label>
                    <span>Password baru</span>
                    <input type="password" name="password" minlength="6" placeholder="Minimal 6 karakter" required>
                </label>

                <label>
                    <span>Konfirmasi password</span>
                    <input type="password" name="confirm_password" minlength="6" placeholder="Ulangi password baru" required>
                </label>

                <button type="submit">Reset Sandi</button>
            </form>

            <p class="login-text">
                Sudah ingat password? <a href="login.php">Kembali login</a>
            </p>
        </section>

        <section class="forgot-side" aria-label="Fresh Farm">
            <div>
                <span>Fresh Smart Farm</span>
                <h2>Akun kembali aman, kerja tani lanjut rapi.</h2>
                <p>Setelah sandi diganti, gunakan password baru untuk masuk ke dashboard.</p>
            </div>
        </section>
    </main>
</body>
</html>
