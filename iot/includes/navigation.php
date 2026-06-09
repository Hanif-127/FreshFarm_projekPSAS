<?php
require_once __DIR__ . '/icons.php';

$iot_navigation_items = [
    'dashboard' => ['label' => 'Ringkasan', 'href' => 'dashboard.php', 'icon' => 'summary'],
    'riwayat' => ['label' => 'Riwayat Data', 'href' => 'riwayat.php', 'icon' => 'history'],
    'perangkat' => ['label' => 'Perangkat', 'href' => 'perangkat.php', 'icon' => 'device'],
    'pengaturan' => ['label' => 'Pengaturan', 'href' => 'pengaturan.php', 'icon' => 'settings'],
];
?>
<section class="iot-workspace-bar" aria-label="Navigasi Monitoring IoT">
    <div class="iot-workspace-context">
        <span class="iot-workspace-mark" aria-hidden="true"><?= iot_icon_svg('activity') ?></span>
        <div>
            <strong><?= htmlspecialchars($iot_device['location']) ?></strong>
            <span><?= htmlspecialchars($iot_device['name']) ?></span>
        </div>
    </div>

    <nav class="iot-workspace-nav">
        <?php foreach ($iot_navigation_items as $key => $item): ?>
            <a
                class="iot-workspace-link <?= $iot_active_page === $key ? 'is-active' : '' ?>"
                href="<?= htmlspecialchars($item['href']) ?>"
                <?= $iot_active_page === $key ? 'aria-current="page"' : '' ?>
            >
                <span class="iot-workspace-link__icon" aria-hidden="true"><?= iot_icon_svg($item['icon']) ?></span>
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="iot-workspace-status">
        <span class="iot-status-dot <?= $iot_device['status'] === 'online' ? '' : 'is-offline' ?>" data-iot-status-dot aria-hidden="true"></span>
        <span data-iot-workspace-status-text><?= ucfirst(htmlspecialchars($iot_device['status'])) ?>, <?= htmlspecialchars($iot_device['last_seen']) ?></span>
    </div>
</section>
