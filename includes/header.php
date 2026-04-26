<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan root berdasarkan lokasi file yang sedang dibuka
$root = str_repeat('../', substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 2);

// Deteksi halaman aktif untuk active state
$current_script = $_SERVER['SCRIPT_NAME'];
$active_page = 'home';
if      (strpos($current_script, '/artikel.php') !== false)      $active_page = 'artikel';
elseif  (strpos($current_script, '/harga_pasar.php') !== false)  $active_page = 'harga_pasar';
elseif  (strpos($current_script, '/dashboard.php') !== false)    $active_page = 'dashboard';
elseif  (strpos($current_script, '/jurnal/') !== false)          $active_page = 'jurnal';
elseif  (strpos($current_script, '/grafik.php') !== false)       $active_page = 'grafik';
?>

<style>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* ===== NAVBAR ===== */
.navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 64px;
    /* Glassmorphism ringan */
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    gap: 16px;
}

/* ===== BRAND / HOME (KIRI – terpisah) ===== */
.nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    flex-shrink: 0;
    margin-right: auto; /* dorong ke kiri, beri jarak dengan menu */
}

.nav-logo img {
    height: 40px;
    width: 40px;
    object-fit: contain;
    border-radius: 10px;
    background: rgba(46, 125, 50, 0.08);
    padding: 4px;
}

.nav-brand {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2e7d32;
    line-height: 1.2;
    letter-spacing: -0.3px;
}

.nav-brand em {
    color: #66bb6a;
    font-style: normal;
}

/* ===== MENU NAVIGASI (TENGAH) ===== */
.nav-menu {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0;
    padding: 0;
    flex: 1;
    justify-content: center;
    flex-wrap: wrap;
}

.nav-menu a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #444;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 10px;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    white-space: nowrap;
    background: transparent;
}

/* Ikon di dalam menu (gambar lokal) */
.nav-menu a .nav-icon {
    height: 20px;
    width: 20px;
    object-fit: contain;
    flex-shrink: 0;
}

/* Hover ringan */
.nav-menu a:hover {
    background: rgba(46, 125, 50, 0.08);
    color: #1b5e20;
}

/* Active state */
.nav-menu a.active {
    background: rgba(46, 125, 50, 0.15);
    color: #1b5e20;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    font-weight: 600;
}

/* ===== USER AREA (KANAN) ===== */
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
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}

.salam {
    color: #333;
    font-size: 0.8rem;
}

.salam strong {
    color: #1b5e20;
}

.role {
    color: #66bb6a;
    font-size: 0.7rem;
}

.btn-login {
    background: #2e7d32;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(46, 125, 50, 0.2);
}

.btn-login:hover {
    background: #1b5e20;
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
}

.btn-logout {
    background: transparent;
    color: #555;
    padding: 7px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid rgba(0,0,0,0.12);
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.btn-logout:hover {
    background: rgba(244, 67, 54, 0.08);
    color: #c62828;
    border-color: rgba(244, 67, 54, 0.3);
}

/* ===== HAMBURGER (MOBILE) ===== */
.hamburger {
    display: none;
    background: none;
    border: none;
    color: #2e7d32;
    font-size: 26px;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .hamburger {
        display: block;
    }

    .nav-menu {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 64px;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        padding: 12px 20px;
        gap: 2px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-radius: 0 0 16px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.6);
    }

    .nav-menu.open {
        display: flex;
    }

    .nav-menu a {
        width: 100%;
        justify-content: flex-start;
        padding: 12px 16px;
        border-radius: 10px;
    }

    .user-info {
        display: none;
    }
}
</style>

<nav class="navbar">
    <!-- 1. HOME: brand terpisah di kiri -->
    <a class="nav-logo" href="<?= $root ?>index.php">
        <img src="<?= $root ?>assets/images/logo.png"
             alt="Logo"
             onerror="this.style.display='none'">
        <span class="nav-brand">Fresh <em>Smart Farm</em></span>
    </a>

    <!-- 2. MENU NAVIGASI (tengah) dengan ikon lokal -->
    <ul class="nav-menu" id="navMenu">
        <li>
            <a href="<?= $root ?>pages/artikel.php" class="<?= $active_page === 'artikel' ? 'active' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav_artikel.svg" alt="Artikel" class="nav-icon">
                <span>Artikel</span>
            </a>
        </li>
        <li>
            <a href="<?= $root ?>pages/harga_pasar.php" class="<?= $active_page === 'harga_pasar' ? 'active' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav_harga_pasar.svg" alt="Harga Pasar" class="nav-icon">
                <span>Harga Pasar</span>
            </a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li>
                <a href="<?= $root ?>pages/dashboard.php" class="<?= $active_page === 'dashboard' ? 'active' : '' ?>">
                    <img src="<?= $root ?>assets/icons/nav_dashboard.svg" alt="Dashboard" class="nav-icon">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= $root ?>pages/jurnal/index.php" class="<?= $active_page === 'jurnal' ? 'active' : '' ?>">
                    <img src="<?= $root ?>assets/icons/nav_jurnal_tanam.svg" alt="Jurnal Tanam" class="nav-icon">
                    <span>Jurnal Tanam</span>
                </a>
            </li>
            <li>
                <a href="<?= $root ?>pages/grafik.php" class="<?= $active_page === 'grafik' ? 'active' : '' ?>">
                    <img src="<?= $root ?>assets/icons/nav_grafik.svg" alt="Grafik" class="nav-icon">
                    <span>Grafik</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- 3. USER AREA (kanan) -->
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

    <!-- Hamburger (mobile) -->
    <button class="hamburger" onclick="document.getElementById('navMenu').classList.toggle('open')">☰</button>
</nav>