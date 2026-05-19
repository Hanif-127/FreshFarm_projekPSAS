<?php
session_start();
include 'includes/koneksi.php';

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: login.php?error=empty&u=' . urlencode($username));
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    header('Location: login.php?error=invalid&u=' . urlencode($username));
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result) ?: null;
mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: login.php?error=invalid&u=' . urlencode($username));
    exit;
}

$stored_password = (string) ($user['password'] ?? '');
$is_valid = false;

if ($stored_password !== '') {
    $info = password_get_info($stored_password);
    if (($info['algo'] ?? null) !== null && (int) ($info['algo'] ?? 0) !== 0) {
        $is_valid = password_verify($password, $stored_password);
    } else {
        $legacy_hash = md5($password);
        $is_valid = hash_equals($stored_password, $legacy_hash);

        if ($is_valid) {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $upgrade_stmt = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id = ? LIMIT 1');
            if ($upgrade_stmt) {
                $user_id_int = (int) $user['id'];
                mysqli_stmt_bind_param($upgrade_stmt, 'si', $new_hash, $user_id_int);
                mysqli_stmt_execute($upgrade_stmt);
                mysqli_stmt_close($upgrade_stmt);
            }
        }
    }
}

if (!$is_valid) {
    header('Location: login.php?error=invalid&u=' . urlencode($username));
    exit;
}

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = (string) $user['username'];

header('Location: pages/dashboard.php');
exit;
?>
