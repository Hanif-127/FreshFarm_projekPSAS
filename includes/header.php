<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan root berdasarkan lokasi file yang sedang dibuka
$root = str_repeat('../', substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 2);
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

.navbar {
    background: #2e7d32;
    padding: 12px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    gap: 16px;
}

.nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    flex-shrink: 0;
}

.nav-logo img {
    height: 42px;
    width: 42px;
    object-fit: contain;
    border-radius: 10px;
    background: rgba(255,255,255,0.15);
    padding: 4px;
}

.nav-brand {
    color: white;
    font-size: 18px;
    font-weight: bold;
    line-height: 1.2;
}

.nav-brand em {
    color: #a5d6a7;
    font-style: normal;
}

.nav-menu {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    margin: 0;
    padding: 0;
}

.nav-menu a {
    color: rgba(255,255,255,0.88);
    text-decoration: none;
    font-size: 14px;
    padding: 7px 12px;
    border-radius: 8px;
    transition: background 0.2s, color 0.2s;
    white-space: nowrap;
}

.nav-menu a:hover {
    background: rgba(255,255,255,0.15);
    color: white;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 38px;
    height: 38px;
    background: #a5d6a7;
    color: #1b5e20;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}

.salam {
    color: white;
    font-size: 13px;
}

.salam strong {
    color: #c8e6c9;
}

.role {
    color: #a5d6a7;
    font-size: 11px;
}

.btn-login {
    background: white;
    color: #2e7d32;
    padding: 8px 18px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    transition: background 0.2s;
}

.btn-login:hover {
    background: #c8e6c9;
}

.btn-logout {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 7px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 13px;
    border: 1px solid rgba(255,255,255,0.3);
    transition: background 0.2s;
}

.btn-logout:hover {
    background: rgba(255,0,0,0.25);
}

.hamburger {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .hamburger { display: block; }
    .nav-menu {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 66px;
        left: 0;
        right: 0;
        background: #2e7d32;
        padding: 12px 20px;
        gap: 4px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .nav-menu.open { display: flex; }
    .user-info { display: none; }
}
</style>

<nav class="navbar">
    <a class="nav-logo" href="<?= $root ?>index.php">
        <img src="<?= $root ?>assets/images/logo.png"
             alt="Logo"
             onerror="this.style.display='none'">
        <span class="nav-brand">Fresh <em>Smart Farm</em></span>
    </a>

    <ul class="nav-menu" id="navMenu">
        <li><a href="<?= $root ?>index.php">🏠 Beranda</a></li>
        <li><a href="<?= $root ?>pages/artikel.php">📰 Artikel</a></li>
        <li><a href="<?= $root ?>pages/harga_pasar.php">💰 Harga Pasar</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?= $root ?>pages/dashboard.php">📋 Dashboard</a></li>
            <li><a href="<?= $root ?>pages/jurnal/index.php">🌱 Jurnal Tanam</a></li>
            <li><a href="<?= $root ?>pages/grafik.php">📊 Grafik</a></li>
        <?php endif; ?>
    </ul>

    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="nav-user">
                <div class="avatar">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <div class="user-info">
                    <span class="salam">Hai, <strong><?= $_SESSION['username'] ?></strong> 👋</span>
                    <span class="role">Petani</span>
                </div>
            </div>
            <a href="<?= $root ?>logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="<?= $root ?>login.php" class="btn-login">Login</a>
        <?php endif; ?>
    </div>

    <button class="hamburger" onclick="document.getElementById('navMenu').classList.toggle('open')">☰</button>
</nav>