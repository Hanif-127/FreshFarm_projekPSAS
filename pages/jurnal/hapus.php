<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
include '../../includes/koneksi.php';

$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM jurnal_tanam WHERE id=$id AND user_id={$_SESSION['user_id']}");
header("Location: index.php?pesan=Data tanam berhasil dihapus!");
exit;
?>