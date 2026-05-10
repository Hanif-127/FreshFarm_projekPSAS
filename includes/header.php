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
$akun_href = $root . 'pages/akun_anda.php';
$pengaduan_href = $root . 'pages/pengaduan.php';
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 64px;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    gap: 16px;
}

.nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    flex-shrink: 0;
    margin-right: auto;
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
    position: relative;
}

.nav-menu li {
    position: relative;
    z-index: 1;
}

.nav-menu .nav-link {
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
    position: relative;
    z-index: 1;
}

.nav-menu .nav-link .nav-icon {
    height: 20px;
    width: 20px;
    object-fit: contain;
    flex-shrink: 0;
}

.nav-menu .nav-link:hover {
    background: rgba(46, 125, 50, 0.08);
    color: #1b5e20;
}

.nav-menu .nav-link.active {
    background: rgba(46, 125, 50, 0.15);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    color: #1b5e20;
    font-weight: 600;
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
    position: relative;
}

.nav-dashboard-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 36px;
    padding: 8px 12px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 600;
    color: #2e7d32;
    background: rgba(46, 125, 50, 0.1);
    transition: background 0.2s, color 0.2s;
}

.nav-dashboard-btn:hover {
    background: rgba(46, 125, 50, 0.18);
    color: #1b5e20;
}

.nav-dashboard-btn.active {
    background: rgba(46, 125, 50, 0.2);
    color: #1b5e20;
}

.nav-dashboard-btn .nav-icon {
    width: 18px;
    height: 18px;
    object-fit: contain;
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

.avatar-toggle {
    width: 38px;
    height: 38px;
    border: 0;
    padding: 0;
    border-radius: 50%;
    background: transparent;
    cursor: pointer;
}

.avatar-toggle:focus-visible {
    outline: 2px solid rgba(46, 125, 50, 0.5);
    outline-offset: 2px;
}

.profile-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 210px;
    display: none;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
    padding: 8px;
    z-index: 30;
}

.profile-menu.open {
    display: flex;
}

.profile-menu-link {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 8px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: #34513a;
    font-size: 0.84rem;
    font-weight: 500;
    transition: background 0.2s, color 0.2s;
}

.profile-menu-link:hover {
    background: rgba(46, 125, 50, 0.08);
    color: #1b5e20;
}

.profile-menu-divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
    margin: 6px 2px;
}

.profile-menu-link.logout-link {
    color: #b42318;
    font-weight: 600;
}

.profile-menu-link.logout-link:hover {
    background: rgba(244, 67, 54, 0.08);
    color: #8c1d18;
}

.logout-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
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
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        border-radius: 0 0 16px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.6);
    }

    .nav-menu.open {
        display: flex;
    }

    .nav-menu .nav-link {
        width: 100%;
        justify-content: flex-start;
        padding: 12px 16px;
        border-radius: 10px;
    }

    .nav-menu .nav-link.active {
        background: rgba(46, 125, 50, 0.15);
    }

    .user-info {
        display: none;
    }

    .nav-dashboard-btn {
        padding: 8px 10px;
    }

    .profile-menu {
        right: -10px;
        width: 190px;
    }
}
</style>

<nav class="navbar">
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
                    <span class="salam">Hai, <strong><?= $_SESSION['username'] ?></strong></span>
                    <span class="role">Petani</span>
                </div>
                <a href="<?= $dashboard_href ?>" class="nav-dashboard-btn <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                    <img src="<?= $root ?>assets/icons/nav_dashboard.svg" alt="Dashboard" class="nav-icon">
                    <span>Dashboard</span>
                </a>
                <button type="button" class="avatar-toggle" id="avatarToggle" aria-expanded="false" aria-haspopup="menu">
                    <span class="avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
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
            <a href="<?= $root ?>login.php" class="btn-login">Login</a>
        <?php endif; ?>
    </div>

    <button class="hamburger" type="button" aria-label="Buka menu" onclick="document.getElementById('navMenu').classList.toggle('open')">&#9776;</button>
</nav>

<script>
(function () {
    var navMenu = document.getElementById('navMenu');
    if (!navMenu) return;

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

    function getNavbarHeight() {
        var navbar = document.querySelector('.navbar');
        return navbar ? navbar.offsetHeight : 64;
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

    function pickLandingActive(entries) {
        if (!entries.length) return null;

        var headerOffset = getNavbarHeight() + 18;
        var probe = window.scrollY + headerOffset;

        if (probe < entries[0].section.offsetTop) {
            return null;
        }

        var active = entries[0].link;
        for (var i = 0; i < entries.length; i++) {
            if (probe >= entries[i].section.offsetTop) {
                active = entries[i].link;
            } else {
                break;
            }
        }
        return active;
    }

    function syncLandingActive() {
        if (!isLanding || stopped) return;

        if (isManualLocked()) {
            var lockedTarget = document.getElementById(manualTargetId);
            if (lockedTarget) {
                var delta = Math.abs(lockedTarget.getBoundingClientRect().top - getNavbarHeight() - 8);
                if (delta > 20) {
                    return;
                }
            }
            clearManualLock();
        }

        var entries = getSectionEntries();
        setActive(pickLandingActive(entries));
    }

    if (isLanding) {
        window.requestAnimationFrame(syncLandingActive);

        window.addEventListener('scroll', function () {
            if (stopped) return;
            if (rafId) return;
            rafId = window.requestAnimationFrame(function () {
                rafId = null;
                syncLandingActive();
            });
        }, { passive: true });
    } else {
        if (currentActiveLink) {
            setActive(currentActiveLink, true);
        }
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

            var navHeight = getNavbarHeight();
            var targetTop = target.getBoundingClientRect().top + window.scrollY - navHeight - 8;

            window.scrollTo({
                top: Math.max(0, targetTop),
                behavior: 'smooth'
            });

            navMenu.classList.remove('open');
        });
    });

    window.addEventListener('resize', function () {
        if (isLanding) {
            syncLandingActive();
        }
    });

    window.addEventListener('landing:leave', function () {
        stopped = true;
        clearManualLock();
    });
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
