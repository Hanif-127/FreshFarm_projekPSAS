<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

header("Location: pengaturan.php?tab=account", true, 302);
exit;
?>
