<?php
session_start();
include 'includes/koneksi.php';

// Ambil data dari form
$username = $_POST['username'];
$password = md5($_POST['password']); // enkripsi sama seperti waktu insert user

// Cari user di database
$query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$hasil = mysqli_query($koneksi, $query);

if (mysqli_num_rows($hasil) == 1) {
    // Data valid → simpan sesi
    $user = mysqli_fetch_assoc($hasil);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];

    header("Location: pages/dashboard.php");
    exit;
} else {
    // Data tidak valid → kembali ke login dengan pesan error
    header("Location: login.php?error=1");
    exit;
}
?>