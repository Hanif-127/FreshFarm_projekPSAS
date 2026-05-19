<?php
session_start();
session_destroy(); // hapus semua sesi
header("Location: login.php");
exit;
?>