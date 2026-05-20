<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$root = str_repeat('../', max(0, substr_count($current_script, '/') - 2));

$sidebar_items = [
    [
        'label' => 'Dashboard',
        'icon' => 'dashboard',
        'href' => $root . 'pages/dashboard.php',
        'match' => ['/pages/dashboard.php'],
    ],
    [
        'label' => 'Ringkasan',
        'icon' => 'ringkasan',
        'href' => $root . 'pages/ringkasan.php',
        'match' => ['/pages/ringkasan.php'],
    ],
    [
        'label' => 'Jurnal Tanam',
        'icon' => 'jurnal',
        'href' => $root . 'pages/jurnal/index.php',
        'match' => ['/pages/jurnal/'],
    ],
    [
        'label' => 'Kalender Tanam',
        'icon' => 'kalender',
        'href' => $root . 'pages/kalender.php',
        'match' => ['/pages/kalender.php'],
    ],
    [
        'label' => 'Inventaris',
        'icon' => 'inventaris',
        'href' => $root . 'pages/inventaris.php',
        'match' => ['/pages/inventaris.php'],
    ],
    [
        'label' => 'Laporan & Grafik',
        'icon' => 'grafik',
        'href' => $root . 'pages/grafik.php',
        'match' => ['/pages/grafik.php'],
    ],
];

function sidebar_item_active(string $script, array $matches): bool {
    foreach ($matches as $needle) {
        if ($needle !== '' && strpos($script, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function sidebar_icon_svg(string $name): string {
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 13.5a9 9 0 1 1 7.5 7.5"/><path d="M12 12l5-3"/></svg>',
        'ringkasan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 3h9l4 4v14H6z"/><path d="M15 3v4h4"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>',
        'jurnal' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4h10a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3z"/><path d="M8 4v16"/><path d="M11 9h4"/><path d="M11 13h4"/></svg>',
        'kalender' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 10h18"/></svg>',
        'inventaris' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 7.5 12 3l9 4.5-9 4.5z"/><path d="M3 7.5V16.5L12 21l9-4.5V7.5"/><path d="M12 12v9"/></svg>',
        'grafik' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20v-4"/><path d="M3 20h20"/></svg>',
        'pengaduan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 5h16v11H8l-4 3z"/><path d="M8 9h8"/><path d="M8 12h5"/></svg>',
        'pengaturan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a1.7 1.7 0 0 1 0 2.4 1.7 1.7 0 0 1-2.4 0l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9v.3a1.7 1.7 0 0 1-3.4 0v-.2a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a1.7 1.7 0 0 1-2.4 0 1.7 1.7 0 0 1 0-2.4l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6h-.2a1.7 1.7 0 0 1 0-3.4h.2a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a1.7 1.7 0 0 1 0-2.4 1.7 1.7 0 0 1 2.4 0l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4.5a1.7 1.7 0 0 1 3.4 0v.2a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a1.7 1.7 0 0 1 2.4 0 1.7 1.7 0 0 1 0 2.4l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6h.2a1.7 1.7 0 0 1 0 3.4h-.2a1 1 0 0 0-.9.6z"/></svg>',
    ];

    return $icons[$name] ?? $icons['dashboard'];
}
?>
<aside class="dashboard-sidebar-global" aria-label="Sidebar dashboard">
    <div class="sidebar-global-head">
        <span class="sidebar-global-title">Dashboard</span>
        <?php if (isset($_SESSION['username'])): ?>
            <span class="sidebar-global-user"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <?php endif; ?>
    </div>

    <nav class="sidebar-global-nav">
        <?php foreach ($sidebar_items as $item): ?>
            <?php $is_active = sidebar_item_active($current_script, $item['match']); ?>
            <a class="sidebar-global-link <?= $is_active ? 'is-active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>">
                <span class="sidebar-global-link__icon" aria-hidden="true"><?= sidebar_icon_svg($item['icon']) ?></span>
                <span class="sidebar-global-link__label"><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
