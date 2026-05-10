<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

include 'includes/koneksi.php';
include 'includes/user_settings.php';

$full_name = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirm_password = (string) ($_POST['confirm_password'] ?? '');

$query_args = [
    'full_name' => $full_name,
    'email' => $email,
    'username' => $username,
];

$redirect_with_error = static function (string $error_code) use ($query_args): void {
    $query_args['error'] = $error_code;
    header('Location: register.php?' . http_build_query($query_args));
    exit;
};

if ($username === '' || $password === '' || $confirm_password === '') {
    $redirect_with_error('empty');
}

if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
    $redirect_with_error('username_format');
}

if (strlen($password) < 6) {
    $redirect_with_error('password_short');
}

if (!hash_equals($password, $confirm_password)) {
    $redirect_with_error('password_mismatch');
}

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $redirect_with_error('email_invalid');
}

$check_stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE username = ? LIMIT 1');
if (!$check_stmt) {
    $redirect_with_error('failed');
}

mysqli_stmt_bind_param($check_stmt, 's', $username);
mysqli_stmt_execute($check_stmt);
$exists_result = mysqli_stmt_get_result($check_stmt);
$exists_user = mysqli_fetch_assoc($exists_result) ?: null;
mysqli_stmt_close($check_stmt);

if ($exists_user) {
    $redirect_with_error('username_taken');
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$insert_stmt = mysqli_prepare($koneksi, 'INSERT INTO users (username, password) VALUES (?, ?)');
if (!$insert_stmt) {
    $redirect_with_error('failed');
}

mysqli_stmt_bind_param($insert_stmt, 'ss', $username, $password_hash);
$insert_ok = mysqli_stmt_execute($insert_stmt);
$new_user_id = $insert_ok ? (int) mysqli_insert_id($koneksi) : 0;
mysqli_stmt_close($insert_stmt);

if (!$insert_ok || $new_user_id <= 0) {
    if ((int) mysqli_errno($koneksi) === 1062) {
        $redirect_with_error('username_taken');
    }
    $redirect_with_error('failed');
}

$settings = user_settings_defaults();
if ($full_name !== '') {
    $settings['account_full_name'] = $full_name;

    $name_parts = preg_split('/\s+/', $full_name);
    $first_name = trim((string) ($name_parts[0] ?? ''));
    if ($first_name !== '') {
        $settings['kebun_nama'] = 'Kebun ' . $first_name;
    }
}

if ($email !== '') {
    $settings['account_email'] = $email;
}

user_settings_upsert($koneksi, $new_user_id, $settings);

header('Location: login.php?registered=1&u=' . urlencode($username));
exit;
?>
