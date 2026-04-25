<?php
session_start();

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

// Ambil pesan error kalau ada
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Fresh Smart Farm</title>
</head>
<body>

<h2>Login Petani</h2>

<?php if ($error): ?>
    <p style="color:red;">Data tidak valid, silahkan coba lagi.</p>
<?php endif; ?>

<form action="proses_login.php" method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>