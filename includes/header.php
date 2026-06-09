<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$root = str_repeat('../', substr_count($current_script, '/') - 2);

$active_page = 'home';
if (strpos($current_script, '/artikel.php') !== false || strpos($current_script, '/detail_artikel.php') !== false) {
    $active_page = 'artikel';
} elseif (strpos($current_script, '/harga_pasar.php') !== false || strpos($current_script, '/detail_harga.php') !== false) {
    $active_page = 'harga_pasar';
} elseif (strpos($current_script, '/iot/') !== false) {
    $active_page = 'iot';
} elseif (strpos($current_script, '/pengaturan.php') !== false ||
          strpos($current_script, '/akun_anda.php') !== false) {
    $active_page = 'pengaturan';
} elseif (strpos($current_script, '/pengaduan.php') !== false) {
    $active_page = 'pengaduan';
} elseif (strpos($current_script, '/dashboard.php') !== false) {
    $active_page = 'dashboard';
} elseif (strpos($current_script, '/ringkasan.php') !== false ||
          strpos($current_script, '/kalender.php') !== false ||
          strpos($current_script, '/inventaris.php') !== false) {
    $active_page = 'dashboard';
} elseif (strpos($current_script, '/jurnal/') !== false || strpos($current_script, '/grafik.php') !== false) {
    $active_page = 'dashboard';
}

$is_landing_page = $active_page === 'home' && preg_match('~/index\.php$~', $current_script);

$tentang_href = $is_landing_page ? '#statistik' : $root . 'index.php#statistik';
$fitur_href = $is_landing_page ? '#fitur' : $root . 'index.php#fitur';
$artikel_href = $is_landing_page ? '#artikel' : $root . 'pages/artikel.php';
$harga_href = $is_landing_page ? '#harga' : $root . 'pages/harga_pasar.php';
$dashboard_href = isset($_SESSION['user_id']) ? $root . 'pages/dashboard.php' : $root . 'login.php';
$iot_href = isset($_SESSION['user_id']) ? $root . 'iot/dashboard.php' : $root . 'login.php';
$pengaturan_href = $root . 'pages/pengaturan.php';
$akun_href = $root . 'pages/pengaturan.php?tab=account';
$pengaduan_href = $root . 'pages/pengaduan.php';
$is_account_menu = $active_page === 'pengaturan' && (
    strpos($current_script, '/akun_anda.php') !== false ||
    ($_GET['tab'] ?? '') === 'account'
);
$is_pengaturan_menu = $active_page === 'pengaturan' && !$is_account_menu;
?>

<style>
:root {
    --header-bg: #1f6c40;
    --header-bg-solid: #15522f;
    --header-panel: rgba(255, 255, 255, 0.13);
    --header-panel-strong: rgba(255, 255, 255, 0.2);
    --header-line: rgba(233, 246, 235, 0.24);
    --header-line-strong: rgba(233, 246, 235, 0.42);
    --header-ink: #f7fbf0;
    --header-text: #e7f1e8;
    --header-muted: #c4dfc9;
    --header-green: #247243;
    --header-mint: #c4dfc9;
    --header-gold: #d9a441;
    --header-red: #ffb4a8;
    --header-radius: 8px;
    --header-ease: cubic-bezier(0.2, 0.8, 0.2, 1);
}

.ff-page-transition {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: grid;
    place-items: center;
    padding: 24px;
    background:
        radial-gradient(circle at center, rgba(255, 255, 255, 0.94) 0%, rgba(245, 250, 246, 0.86) 48%, rgba(238, 246, 240, 0.8) 100%);
    backdrop-filter: blur(12px) saturate(1.04);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 260ms var(--header-ease), visibility 260ms var(--header-ease);
}

.ff-page-transition__loader {
    position: relative;
    width: 128px;
    min-height: 152px;
    display: grid;
    grid-template-rows: 120px auto;
    align-content: center;
    justify-items: center;
    align-items: center;
    gap: 10px;
    color: var(--header-bg-solid);
    opacity: 0;
    transform: translateY(10px) scale(0.96);
    transition: opacity 260ms ease, transform 340ms var(--header-ease);
}

.ff-page-transition__loader::before,
.ff-page-transition__loader::after {
    content: "";
    position: absolute;
    top: 0;
    left: 50%;
    border-radius: 999px;
    pointer-events: none;
}

.ff-page-transition__loader::before {
    width: 112px;
    height: 112px;
    transform: translateX(-50%) translateY(4px);
    background: rgba(36, 114, 67, 0.08);
    box-shadow: 0 24px 54px rgba(21, 82, 47, 0.18);
    animation: ff-logo-halo 1400ms var(--header-ease) infinite;
}

.ff-page-transition__loader::after {
    width: 88px;
    height: 88px;
    transform: translateX(-50%) translateY(16px);
    background: rgba(255, 255, 255, 0.74);
    backdrop-filter: blur(8px);
    box-shadow: inset 0 0 0 1px rgba(47, 114, 67, 0.12);
}

.ff-page-transition__ring {
    position: absolute;
    top: 0;
    left: 50%;
    z-index: 2;
    width: 120px;
    height: 120px;
    transform: translateX(-50%);
    border-radius: 999px;
    background:
        conic-gradient(from 0deg, rgba(36, 114, 67, 0), rgba(36, 114, 67, 0.14), #247243, #d9a441, rgba(36, 114, 67, 0));
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #000 calc(100% - 2px));
    mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #000 calc(100% - 2px));
    animation: ff-loader-spin 1150ms linear infinite;
}

.ff-page-transition__logo {
    position: relative;
    z-index: 3;
    width: 74px;
    height: 74px;
    object-fit: contain;
    padding: 9px;
    border: 1px solid rgba(47, 114, 67, 0.14);
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 14px 34px rgba(21, 82, 47, 0.18);
    animation: ff-logo-float 1350ms var(--header-ease) infinite;
}

.ff-page-transition__text {
    position: relative;
    z-index: 3;
    color: #1f5635;
    font-size: 0.8rem;
    font-weight: 900;
    line-height: 1;
    opacity: 0.84;
    animation: ff-loading-text 1350ms ease-in-out infinite;
}

.ff-page-transition__loader::before,
.ff-page-transition__ring,
.ff-page-transition__logo,
.ff-page-transition__text {
    animation-play-state: paused;
}

body.ff-dashboard-loading .ff-page-transition {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

body.ff-dashboard-loading .ff-page-transition__loader {
    opacity: 1;
    transform: none;
}

body.ff-dashboard-loading .ff-page-transition__loader::before,
body.ff-dashboard-loading .ff-page-transition__ring,
body.ff-dashboard-loading .ff-page-transition__logo,
body.ff-dashboard-loading .ff-page-transition__text {
    animation-play-state: running;
}

@keyframes ff-loader-spin {
    from {
        transform: translateX(-50%) rotate(0deg);
    }

    to {
        transform: translateX(-50%) rotate(360deg);
    }
}

@keyframes ff-logo-float {
    0%,
    100% {
        transform: translateY(0) scale(1);
    }

    50% {
        transform: translateY(-4px) scale(1.035);
    }
}

@keyframes ff-logo-halo {
    0%,
    100% {
        opacity: 0.58;
        transform: translateX(-50%) translateY(4px) scale(0.94);
    }

    50% {
        opacity: 1;
        transform: translateX(-50%) translateY(4px) scale(1.06);
    }
}

@keyframes ff-loading-text {
    0%,
    100% {
        opacity: 0.62;
        transform: translateY(0);
    }

    50% {
        opacity: 0.94;
        transform: translateY(1px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ff-page-transition,
    .ff-page-transition__loader,
    .ff-page-transition__loader::before,
    .ff-page-transition__ring,
    .ff-page-transition__logo,
    .ff-page-transition__text {
        animation: none !important;
        transition: none !important;
        transform: none !important;
    }
}

.navbar,
.navbar * {
    box-sizing: border-box;
    font-family: "DM Sans", "Segoe UI", system-ui, -apple-system, sans-serif;
    letter-spacing: 0;
}

.navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: grid;
    grid-template-columns: minmax(220px, auto) minmax(0, 1fr) auto auto;
    align-items: center;
    min-height: 68px;
    padding: 0 clamp(16px, 3vw, 34px);
    gap: 14px;
    background: linear-gradient(135deg, var(--header-bg-solid) 0%, var(--header-bg) 58%, #247243 100%);
    border-bottom: 1px solid var(--header-line);
    box-shadow: 0 12px 28px rgba(9, 22, 13, 0.22);
}

.nav-logo {
    display: flex;
    align-items: center;
    gap: 11px;
    width: fit-content;
    max-width: 100%;
    min-width: 0;
    flex-shrink: 0;
    color: var(--header-ink);
    text-decoration: none;
}

.nav-logo img {
    width: 44px;
    height: 44px;
    padding: 5px;
    border: 1px solid rgba(255, 255, 255, 0.74);
    border-radius: var(--header-radius);
    background: #ffffff;
    object-fit: contain;
    flex-shrink: 0;
    box-shadow: 0 8px 18px rgba(9, 22, 13, 0.2);
    filter: saturate(1.06) contrast(1.04);
    transition: transform 180ms var(--header-ease), background 180ms ease, box-shadow 180ms ease;
}

.nav-logo:hover img {
    transform: translateY(-1px);
    background: #ffffff;
    box-shadow: 0 10px 22px rgba(9, 22, 13, 0.26);
}

.nav-brand {
    color: var(--header-ink);
    font-size: 1.05rem;
    font-weight: 900;
    line-height: 1.18;
    white-space: nowrap;
}

.nav-brand em {
    color: var(--header-mint);
    font-style: normal;
}

.nav-menu {
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 0;
    padding: 0;
    min-width: 0;
    justify-self: center;
    max-width: 620px;
}

.nav-menu .nav-link,
.nav-dashboard-btn {
    position: relative;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 12px;
    border: 1px solid transparent;
    border-radius: var(--header-radius);
    background: transparent;
    color: var(--header-muted);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 650;
    line-height: 1;
    white-space: nowrap;
    transition: color 180ms ease, background 180ms ease, border-color 180ms ease, transform 180ms var(--header-ease);
}

.nav-dashboard-btn {
    font-size: 0.84rem;
    font-weight: 700;
}

.nav-menu .nav-link::after,
.nav-dashboard-btn::after {
    content: "";
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 5px;
    height: 2px;
    border-radius: 999px;
    background: var(--header-gold);
    box-shadow: 0 0 12px rgba(217, 164, 65, 0.38);
    opacity: 0;
    transform: scaleX(0.45);
    transform-origin: center;
    transition: opacity 180ms ease, transform 220ms var(--header-ease);
}

.nav-menu .nav-link:hover,
.nav-dashboard-btn:hover {
    color: #ffffff;
    background: var(--header-panel);
}

.nav-menu .nav-link.active,
.nav-dashboard-btn.active {
    color: #ffffff;
    background: var(--header-panel);
    border-color: var(--header-line-strong);
    font-weight: 650;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.nav-dashboard-btn.active {
    font-weight: 700;
}

.nav-menu .nav-link.active::after,
.nav-dashboard-btn.active::after {
    opacity: 1;
    transform: scaleX(1);
}

.nav-menu .nav-link .nav-icon,
.nav-dashboard-btn .nav-icon {
    width: 19px;
    height: 19px;
    object-fit: contain;
    flex-shrink: 0;
    filter: brightness(0) invert(1);
    opacity: 0.82;
}

.nav-menu .nav-link.active .nav-icon,
.nav-dashboard-btn.active .nav-icon {
    filter: brightness(0) invert(1);
    opacity: 1;
}

.nav-menu .nav-link:hover .nav-icon,
.nav-dashboard-btn:hover .nav-icon {
    filter: brightness(0) invert(1);
    opacity: 1;
}

.nav-right {
    display: flex;
    align-items: center;
    justify-self: end;
    gap: 10px;
    min-width: 0;
    flex-shrink: 0;
}

.nav-auth {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-login,
.btn-register {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--header-radius);
    padding: 8px 14px;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 900;
    transition: background 180ms ease, border-color 180ms ease, color 180ms ease, transform 180ms var(--header-ease);
}

.btn-login {
    border: 1px solid rgba(255, 255, 255, 0.82);
    background: #ffffff;
    color: #15522f;
}

.btn-register {
    border: 1px solid var(--header-line-strong);
    background: var(--header-panel);
    color: #ffffff;
}

.btn-login:hover,
.btn-register:hover {
    transform: translateY(-1px);
}

.btn-login:hover {
    background: var(--header-mint);
    border-color: rgba(255, 255, 255, 0.94);
}

.btn-register:hover {
    background: var(--header-panel-strong);
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.nav-dashboard-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.nav-dashboard-btn svg.nav-icon {
    display: block;
}

.user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.28;
}

.salam {
    color: var(--header-muted);
    font-size: 0.8rem;
    white-space: nowrap;
}

.salam strong {
    color: var(--header-ink);
}

.role {
    color: var(--header-mint);
    font-size: 0.72rem;
    font-weight: 850;
}

.avatar-toggle {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid var(--header-line-strong);
    border-radius: 50%;
    background: var(--header-panel);
    cursor: pointer;
}

.avatar-toggle:focus-visible,
.hamburger:focus-visible,
.nav-link:focus-visible,
.nav-dashboard-btn:focus-visible,
.btn-login:focus-visible,
.btn-register:focus-visible {
    outline: 3px solid rgba(217, 164, 65, 0.34);
    outline-offset: 2px;
}

.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--header-mint), var(--header-green));
    color: #07140d;
    font-size: 0.95rem;
    font-weight: 950;
}

.profile-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 218px;
    display: none;
    flex-direction: column;
    padding: 8px;
    border: 1px solid #d9e5dc;
    border-radius: var(--header-radius);
    background: #ffffff;
    box-shadow: 0 18px 36px rgba(9, 22, 13, 0.24);
    z-index: 30;
}

.profile-menu.open {
    display: flex;
}

.profile-menu-link {
    min-height: 38px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: var(--header-radius);
    color: #2b4d38;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 800;
    transition: background 180ms ease, color 180ms ease;
}

.profile-menu-link:hover {
    background: #eef7ef;
    color: #15522f;
}

.profile-menu-link.active {
    background: #eef7ef;
    color: #15522f;
    box-shadow: inset 0 0 0 1px rgba(36, 114, 67, 0.08);
}

.profile-menu-divider {
    height: 1px;
    margin: 6px 2px;
    background: #d9e5dc;
}

.profile-menu-link.logout-link {
    color: #a7352e;
}

.profile-menu-link.logout-link:hover {
    background: #fdebea;
    color: #a7352e;
}

.profile-menu-icon,
.logout-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
    flex: 0 0 16px;
}

.profile-menu-icon {
    filter: brightness(0) saturate(100%) invert(27%) sepia(34%) saturate(1045%) hue-rotate(91deg) brightness(93%) contrast(89%);
}

.logout-icon {
    filter: brightness(0) saturate(100%) invert(26%) sepia(75%) saturate(1161%) hue-rotate(332deg) brightness(89%) contrast(91%);
}

.hamburger {
    width: 40px;
    height: 40px;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid var(--header-line-strong);
    border-radius: var(--header-radius);
    background: var(--header-panel);
    color: var(--header-ink);
    cursor: pointer;
}

.hamburger-lines,
.hamburger-lines::before,
.hamburger-lines::after {
    display: block;
    width: 18px;
    height: 2px;
    border-radius: 999px;
    background: currentColor;
}

.hamburger-lines {
    position: relative;
}

.hamburger-lines::before,
.hamburger-lines::after {
    content: "";
    position: absolute;
    left: 0;
}

.hamburger-lines::before {
    top: -6px;
}

.hamburger-lines::after {
    top: 6px;
}

@media (max-width: 1120px) {
    .navbar {
        grid-template-columns: minmax(190px, 1fr) auto auto;
    }

    .nav-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 16px;
        right: 16px;
        max-width: none;
        display: none;
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
        padding: 10px;
        border: 1px solid var(--header-line);
        border-radius: var(--header-radius);
        background: linear-gradient(135deg, var(--header-bg-solid), var(--header-bg));
        box-shadow: 0 18px 34px rgba(9, 22, 13, 0.28);
    }

    .nav-menu.open {
        display: flex;
    }

    .nav-menu .nav-link {
        width: 100%;
        justify-content: flex-start;
        padding: 11px 12px;
    }

    .hamburger {
        display: inline-flex;
    }
}

@media (max-width: 860px) {
    .navbar {
        min-height: 64px;
        padding-inline: 14px;
    }

    .user-info {
        display: none;
    }

    .nav-dashboard-btn {
        min-width: 40px;
        padding: 8px;
    }

    .nav-dashboard-btn span {
        display: none;
    }

    .nav-dashboard-group {
        gap: 4px;
    }

    .profile-menu {
        right: -10px;
        width: 190px;
    }
}

@media (max-width: 520px) {
    .navbar {
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 8px;
    }

    .nav-logo {
        gap: 8px;
    }

    .nav-logo img {
        width: 38px;
        height: 38px;
        padding: 4px;
    }

    .nav-brand {
        font-size: 0.92rem;
    }

    .nav-auth {
        gap: 6px;
    }

    .btn-register {
        display: none;
    }
}

@media (max-width: 390px) {
    .nav-brand {
        max-width: 116px;
        white-space: normal;
    }

    .btn-login {
        padding-inline: 12px;
    }
}
</style>

<nav class="navbar" aria-label="Navigasi utama">
    <a class="nav-logo" href="<?= $root ?>index.php">
        <img src="<?= $root ?>assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
        <span class="nav-brand">Fresh <em>Smart Farm</em></span>
    </a>

    <ul class="nav-menu" id="navMenu" data-landing="<?= $is_landing_page ? '1' : '0' ?>">
        <li>
            <a class="nav-link" href="<?= $tentang_href ?>" data-scroll-section="<?= $is_landing_page ? 'statistik' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav-tentang.svg" alt="Tentang" class="nav-icon">
                <span>Tentang</span>
            </a>
        </li>
        <li>
            <a class="nav-link" href="<?= $fitur_href ?>" data-scroll-section="<?= $is_landing_page ? 'fitur' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav_fitur.svg" alt="Fitur" class="nav-icon">
                <span>Fitur</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?= $active_page === 'artikel' ? 'active' : '' ?>" href="<?= $artikel_href ?>" data-scroll-section="<?= $is_landing_page ? 'artikel' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav_artikel.svg" alt="Artikel" class="nav-icon">
                <span>Artikel</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?= $active_page === 'harga_pasar' ? 'active' : '' ?>" href="<?= $harga_href ?>" data-scroll-section="<?= $is_landing_page ? 'harga' : '' ?>">
                <img src="<?= $root ?>assets/icons/nav_harga_pasar.svg" alt="Harga Pasar" class="nav-icon">
                <span>Harga Pasar</span>
            </a>
        </li>
    </ul>

    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="nav-user" id="navUserMenu">
                <div class="user-info">
                    <span class="salam">Hai, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                    <span class="role">Petani</span>
                </div>
                <div class="nav-dashboard-group">
                    <a href="<?= htmlspecialchars($dashboard_href) ?>" class="nav-dashboard-btn <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                        <img src="<?= $root ?>assets/icons/nav_dashboard.svg" alt="" class="nav-icon">
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= htmlspecialchars($iot_href) ?>" class="nav-dashboard-btn <?= $active_page === 'iot' ? 'active' : '' ?>" aria-label="Monitoring IoT">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="5" y="3" width="14" height="15" rx="2"></rect>
                            <path d="M8 8h8"></path>
                            <path d="M8 12h2"></path>
                            <path d="M14 12h2"></path>
                            <path d="M12 18v3"></path>
                            <path d="M8 21h8"></path>
                        </svg>
                        <span>Monitoring IoT</span>
                    </a>
                </div>
                <button type="button" class="avatar-toggle" id="avatarToggle" aria-expanded="false" aria-haspopup="menu">
                    <span class="avatar"><?= htmlspecialchars(strtoupper(substr((string) $_SESSION['username'], 0, 1))) ?></span>
                </button>
                <div class="profile-menu" id="profileMenu" role="menu" aria-label="Menu profil">
                    <a href="<?= $pengaturan_href ?>" class="profile-menu-link <?= $is_pengaturan_menu ? 'active' : '' ?>" role="menuitem">
                        <img src="<?= $root ?>assets/icons/pengaturan.svg" alt="" class="profile-menu-icon">
                        <span>Pengaturan</span>
                    </a>
                    <a href="<?= $akun_href ?>" class="profile-menu-link <?= $is_account_menu ? 'active' : '' ?>" role="menuitem">
                        <img src="<?= $root ?>assets/icons/akun.svg" alt="" class="profile-menu-icon">
                        <span>Akun Anda</span>
                    </a>
                    <a href="<?= $pengaduan_href ?>" class="profile-menu-link <?= $active_page === 'pengaduan' ? 'active' : '' ?>" role="menuitem">
                        <img src="<?= $root ?>assets/icons/pengaduan.svg" alt="" class="profile-menu-icon">
                        <span>Pengaduan</span>
                    </a>
                    <div class="profile-menu-divider" aria-hidden="true"></div>
                    <a href="<?= $root ?>logout.php" class="profile-menu-link logout-link" role="menuitem">
                        <img src="<?= $root ?>assets/icons/logout.svg" alt="" class="logout-icon">
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="nav-auth">
                <a href="<?= $root ?>register.php" class="btn-register">Buat Akun</a>
                <a href="<?= $root ?>login.php" class="btn-login">Login</a>
            </div>
        <?php endif; ?>
    </div>

    <button class="hamburger" type="button" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navMenu">
        <span class="hamburger-lines" aria-hidden="true"></span>
    </button>
</nav>

<div class="ff-page-transition" id="freshFarmPageTransition" aria-hidden="true">
    <div class="ff-page-transition__loader" role="status" aria-live="polite">
        <span class="ff-page-transition__ring" aria-hidden="true"></span>
        <img src="<?= $root ?>assets/images/logo.png" alt="" class="ff-page-transition__logo" onerror="this.style.display='none'">
        <span class="ff-page-transition__text">Memuat</span>
    </div>
</div>

<script>
(function () {
    var body = document.body;
    if (!body) {
        return;
    }

    var isDashboardShell = body.classList.contains('dashboard-home') || body.classList.contains('module-page');
    var isLandingPage = body.classList.contains('landing-page');

    if (!isDashboardShell && !isLandingPage) {
        return;
    }

    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var overlay = document.getElementById('freshFarmPageTransition');
    var isLeaving = false;

    if (isDashboardShell) {
        body.classList.add('ff-page-entering');
    }

    function finishEntering() {
        if (!isDashboardShell) return;

        window.requestAnimationFrame(function () {
            window.setTimeout(function () {
                body.classList.remove('ff-page-entering');
            }, reducedMotion ? 0 : 70);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', finishEntering, { once: true });
    } else {
        finishEntering();
    }

    function showDashboardLoading() {
        if (isLeaving) return;
        isLeaving = true;
        body.classList.add('ff-dashboard-loading');
        if (overlay) {
            overlay.setAttribute('aria-hidden', 'false');
        }
    }

    function showRouteLeaving() {
        if (isLeaving) return;
        isLeaving = true;
        if (isDashboardShell) {
            body.classList.add('ff-route-leaving');
        }
    }

    function hideTransitions() {
        isLeaving = false;
        body.classList.remove('ff-dashboard-loading');
        body.classList.remove('ff-route-leaving');
        if (overlay) {
            overlay.setAttribute('aria-hidden', 'true');
        }
    }

    function shouldSkipLink(link, event) {
        if (!link || event.defaultPrevented || event.button !== 0) return true;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return true;
        if (link.hasAttribute('download') || link.closest('[data-no-transition]')) return true;

        var target = (link.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return true;

        var href = link.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
            return true;
        }

        var url;
        try {
            url = new URL(href, window.location.href);
        } catch (error) {
            return true;
        }

        if (url.origin !== window.location.origin) return true;
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return true;
        if (url.href === window.location.href) return true;

        return false;
    }

    function isWorkspaceEntryUrl(url) {
        return url.origin === window.location.origin &&
            /\/(?:pages|iot)\/dashboard\.php$/.test(url.pathname);
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[href]') : null;
        if (shouldSkipLink(link, event)) return;

        var url;
        try {
            url = new URL(link.href, window.location.href);
        } catch (error) {
            return;
        }

        if (isLandingPage && isWorkspaceEntryUrl(url)) {
            event.preventDefault();
            link.classList.add('is-pending');
            body.classList.add('is-leaving');
            window.dispatchEvent(new CustomEvent('landing:leave'));
            showDashboardLoading();

            window.setTimeout(function () {
                window.location.href = link.href;
            }, reducedMotion ? 0 : 3000);
            return;
        }

        if (!isDashboardShell) {
            return;
        }

        event.preventDefault();
        link.classList.add('is-pending');
        showRouteLeaving();

        window.setTimeout(function () {
            window.location.href = link.href;
        }, reducedMotion ? 0 : 110);
    });

    document.addEventListener('submit', function (event) {
        if (!isDashboardShell) return;
        if (event.defaultPrevented) return;

        var form = event.target;
        if (!form || form.tagName !== 'FORM') return;

        var target = (form.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return;

        showRouteLeaving();
    });

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            hideTransitions();
            body.classList.remove('ff-page-entering');
        }
    });
})();
</script>

<script>
(function () {
    function initFreshFarmHeaderNav() {
    var navMenu = document.getElementById('navMenu');
    if (!navMenu) return;

    var navToggle = document.getElementById('navToggle');
    var isLanding = navMenu.getAttribute('data-landing') === '1';
    var links = Array.prototype.slice.call(navMenu.querySelectorAll('a.nav-link'));
    var scrollLinks = links.filter(function (link) {
        return !!link.getAttribute('data-scroll-section');
    });
    var stopped = false;
    var rafId = null;
    var currentActiveLink = navMenu.querySelector('a.nav-link.active') || null;
    var manualTargetId = null;
    var manualLockUntil = 0;
    var initialHashId = isLanding && window.location.hash ? window.location.hash.slice(1) : null;

    function getNavbarHeight() {
        var navbar = document.querySelector('.navbar');
        return navbar ? navbar.offsetHeight : 64;
    }

    function setMenuOpen(isOpen) {
        navMenu.classList.toggle('open', isOpen);
        if (navToggle) {
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    }

    function setActive(activeLink, forceUpdate) {
        if (!forceUpdate && currentActiveLink === activeLink) {
            return;
        }

        currentActiveLink = activeLink;

        links.forEach(function (link) {
            link.classList.toggle('active', link === activeLink);
        });
    }

    function clearManualLock() {
        manualTargetId = null;
        manualLockUntil = 0;
    }

    function isManualLocked() {
        return manualTargetId && Date.now() < manualLockUntil;
    }

    function lockActiveSection(id, duration) {
        var link = findLinkForSection(id);
        if (!link) return false;

        manualTargetId = id;
        manualLockUntil = Date.now() + (duration || 1100);
        setActive(link, true);

        return true;
    }

    function getSectionEntries() {
        return scrollLinks.map(function (link) {
            var id = link.getAttribute('data-scroll-section');
            var section = id ? document.getElementById(id) : null;
            return section ? { link: link, section: section } : null;
        }).filter(Boolean);
    }

    function findLinkForSection(id) {
        if (!id) return null;
        for (var i = 0; i < scrollLinks.length; i++) {
            if (scrollLinks[i].getAttribute('data-scroll-section') === id) {
                return scrollLinks[i];
            }
        }
        return null;
    }

    function scrollToSection(id, behavior) {
        var target = id ? document.getElementById(id) : null;
        if (!target) return false;

        window.scrollTo({
            top: Math.max(0, target.getBoundingClientRect().top + window.scrollY - getNavbarHeight() - 8),
            behavior: behavior || 'smooth'
        });

        return true;
    }

    function syncInitialHashScroll() {
        if (!initialHashId) return;

        lockActiveSection(initialHashId, 1600);

        if (scrollToSection(initialHashId, 'auto')) {
            setActive(findLinkForSection(initialHashId), true);
        }
    }

    function pickLandingActive(entries) {
        if (!entries.length) return null;

        var probe = getNavbarHeight() + 28;
        var active = null;

        entries.forEach(function (entry) {
            if (entry.section.getBoundingClientRect().top <= probe) {
                active = entry.link;
            }
        });

        return active;
    }

    function syncLandingActive() {
        if (!isLanding || stopped) return;

        if (isManualLocked()) {
            var lockedTarget = document.getElementById(manualTargetId);
            var lockedLink = findLinkForSection(manualTargetId);
            if (lockedLink) {
                setActive(lockedLink, true);
            }

            if (lockedTarget) {
                var delta = Math.abs(lockedTarget.getBoundingClientRect().top - getNavbarHeight() - 8);
                if (delta > 24) {
                    return;
                }
            }
            clearManualLock();
        }

        setActive(pickLandingActive(getSectionEntries()));
    }

    if (isLanding) {
        if (initialHashId) {
            lockActiveSection(initialHashId, 1600);
            window.requestAnimationFrame(syncInitialHashScroll);
            window.setTimeout(syncInitialHashScroll, 120);
            window.setTimeout(syncLandingActive, 260);
        }

        window.requestAnimationFrame(syncLandingActive);
        window.addEventListener('load', function () {
            syncInitialHashScroll();
            window.setTimeout(syncInitialHashScroll, 120);
            syncLandingActive();
        });

        window.addEventListener('scroll', function () {
            if (stopped || rafId) return;
            rafId = window.requestAnimationFrame(function () {
                rafId = null;
                syncLandingActive();
            });
        }, { passive: true });

        window.addEventListener('hashchange', function () {
            initialHashId = window.location.hash ? window.location.hash.slice(1) : null;
            if (initialHashId) {
                lockActiveSection(initialHashId, 1600);
            }
            syncInitialHashScroll();
            syncLandingActive();
        });
    } else if (currentActiveLink) {
        setActive(currentActiveLink, true);
    }

    scrollLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (!isLanding) return;

            var id = link.getAttribute('data-scroll-section');
            var target = id ? document.getElementById(id) : null;
            if (!target) return;

            event.preventDefault();
            lockActiveSection(id, 1200);

            scrollToSection(id, 'smooth');

            setMenuOpen(false);
        });
    });

    links.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 1120) {
                setMenuOpen(false);
            }
        });
    });

    if (navToggle) {
        navToggle.addEventListener('click', function () {
            setMenuOpen(!navMenu.classList.contains('open'));
        });
    }

    document.addEventListener('click', function (event) {
        if (!navToggle || !navMenu.classList.contains('open')) return;
        if (navMenu.contains(event.target) || navToggle.contains(event.target)) return;
        setMenuOpen(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 1120) {
            setMenuOpen(false);
        }

        if (isLanding) {
            syncLandingActive();
        }
    });

    window.addEventListener('landing:leave', function () {
        stopped = true;
        clearManualLock();
    });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFreshFarmHeaderNav, { once: true });
    } else {
        initFreshFarmHeaderNav();
    }
})();

(function () {
    var toggle = document.getElementById('avatarToggle');
    var menu = document.getElementById('profileMenu');
    var container = document.getElementById('navUserMenu');

    if (!toggle || !menu || !container) return;

    function closeMenu() {
        menu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
        menu.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
    }

    toggle.addEventListener('click', function () {
        if (menu.classList.contains('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    document.addEventListener('click', function (event) {
        if (!container.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
</script>
