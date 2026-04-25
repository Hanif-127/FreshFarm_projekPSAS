<?php

// Informasi koneksi ke database
$host     = "localhost";   // alamat server database (di XAMPP selalu localhost)
$user     = "root";        // username default XAMPP
$password = "";            // password default XAMPP (kosong)
$database = "db_fresh_farm";  // nama database yang tadi dibuat

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set karakter UTF-8 agar bahasa Indonesia tampil dengan benar
mysqli_set_charset($koneksi, "utf8");

?>