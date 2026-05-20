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
} elseif (strpos($current_script, '/dashboard.php') !== false) {
    $active_page = 'dashboard';
} elseif (strpos($current_script, '/pengaturan.php') !== false ||
          strpos($current_script, '/akun_anda.php') !== false ||
          strpos($current_script, '/pengaduan.php') !== false ||
          strpos($current_script, '/ringkasan.php') !== false ||
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
$pengaturan_href = $root . 'pages/pengaturan.php';
$akun_href = $root . 'pages/pengaturan.php?tab=account';
$pengaduan_href = $root . 'pages/pengaduan.php';
?>

<style>
:root {
    --header-bg: rgba(7, 20, 13, 0.9);
    --header-bg-solid: #07140d;
    --header-panel: rgba(255, 255, 255, 0.08);
    --header-panel-strong: rgba(255, 255, 255, 0.14);
    --header-line: rgba(217, 229, 220, 0.18);
    --header-line-strong: rgba(217, 229, 220, 0.3);
    --header-ink: #f7fbf0;
    --header-text: #dbe7df;
    --header-muted: #a9bdb0;
    --header-green: #2f6f42;
    --header-mint: #c4dfc9;
    --header-gold: #d9a441;
    --header-red: #ffb4a8;
    --header-radius: 8px;
    --header-ease: cubic-bezier(0.2, 0.8, 0.2, 1);
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
    background:
        linear-gradient(90deg, rgba(47, 125, 69, 0.11), rgba(217, 164, 65, 0.07)),
        var(--header-bg);
    border-bottom: 1px solid var(--header-line);
    box-shadow: 0 14px 32px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
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
    width: 42px;
    height: 42px;
    object-fit: contain;
    flex-shrink: 0;
    filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.22));
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
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
    transition: color 180ms ease, background 180ms ease, border-color 180ms ease, transform 180ms var(--header-ease);
}

.nav-dashboard-btn {
    font-size: 0.84rem;
    font-weight: 850;
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
    background: linear-gradient(90deg, var(--header-green), var(--header-gold));
    opacity: 0;
    transform: scaleX(0.45);
    transform-origin: center;
    transition: opacity 180ms ease, transform 220ms var(--header-ease);
}

.nav-menu .nav-link:hover,
.nav-dashboard-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.06);
}

.nav-menu .nav-link.active,
.nav-dashboard-btn.active {
    color: #ffffff;
    background: var(--header-panel);
    border-color: var(--header-line);
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
    opacity: 0.76;
}

.nav-menu .nav-link.active .nav-icon,
.nav-menu .nav-link:hover .nav-icon,
.nav-dashboard-btn.active .nav-icon,
.nav-dashboard-btn:hover .nav-icon {
    opacity: 0.96;
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
    border: 1px solid var(--header-green);
    background: var(--header-green);
    color: #07140d;
}

.btn-register {
    border: 1px solid var(--header-line-strong);
    background: var(--header-panel);
    color: var(--header-ink);
}

.btn-login:hover,
.btn-register:hover {
    transform: translateY(-1px);
}

.btn-login:hover {
    background: var(--header-mint);
    border-color: var(--header-mint);
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
    border: 1px solid var(--header-line);
    border-radius: var(--header-radius);
    background: rgba(8, 27, 18, 0.98);
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.3);
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
    color: var(--header-text);
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 800;
    transition: background 180ms ease, color 180ms ease;
}

.profile-menu-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}

.profile-menu-divider {
    height: 1px;
    margin: 6px 2px;
    background: var(--header-line);
}

.profile-menu-link.logout-link {
    color: var(--header-red);
}

.profile-menu-link.logout-link:hover {
    background: rgba(255, 180, 168, 0.1);
    color: #ffd2cc;
}

.logout-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
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
        background: rgba(6, 20, 13, 0.98);
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
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
        width: 36px;
        height: 36px;
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
                <img src="<?= $root ?>assets/icons/nav-fitur.svg" alt="Fitur" class="nav-icon">
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
                <a href="<?= $dashboard_href ?>" class="nav-dashboard-btn <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                    <img src="<?= $root ?>assets/icons/nav_dashboard.svg" alt="Dashboard" class="nav-icon">
                    <span>Dashboard</span>
                </a>
                <button type="button" class="avatar-toggle" id="avatarToggle" aria-expanded="false" aria-haspopup="menu">
                    <span class="avatar"><?= htmlspecialchars(strtoupper(substr((string) $_SESSION['username'], 0, 1))) ?></span>
                </button>
                <div class="profile-menu" id="profileMenu" role="menu" aria-label="Menu profil">
                    <a href="<?= $pengaturan_href ?>" class="profile-menu-link" role="menuitem">Pengaturan</a>
                    <a href="<?= $akun_href ?>" class="profile-menu-link" role="menuitem">Akun Anda</a>
                    <a href="<?= $pengaduan_href ?>" class="profile-menu-link" role="menuitem">Pengaduan</a>
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
            setActive(findLinkForSection(initialHashId), true);
            window.requestAnimationFrame(syncInitialHashScroll);
            window.setTimeout(syncInitialHashScroll, 120);
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
            manualTargetId = id;
            manualLockUntil = Date.now() + 900;
            setActive(link, true);

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
