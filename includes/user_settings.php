<?php

function user_settings_defaults(): array
{
    return [
        'kebun_nama' => 'Kebun Saya',
        'kebun_lokasi' => '',
        'satuan_utama' => 'kg',
        'format_tanggal' => 'd M Y',
        'timezone' => 'Asia/Jakarta',
        'bahasa' => 'id',
        'notif_jadwal' => 1,
        'notif_stok' => 1,
        'notif_pengaduan' => 1,
        'notif_ringkasan' => 1,
        'notif_email' => 0,
        'dashboard_mode' => 'compact',
        'show_focus' => 1,
        'show_quick_actions' => 1,
        'show_schedule' => 1,
        'show_market' => 1,
        'show_complaint' => 1,
        'show_critical_stock' => 1,
        'show_plant_status' => 1,
        'limit_recent_activities' => 4,
        'limit_market_prices' => 4,
        'limit_plant_status' => 5,
        'account_full_name' => '',
        'account_email' => '',
        'account_phone' => '',
    ];
}

function user_settings_ensure_table(mysqli $koneksi): void
{
    $sql = "CREATE TABLE IF NOT EXISTS user_settings (
        user_id INT(11) NOT NULL,
        kebun_nama VARCHAR(120) NOT NULL DEFAULT 'Kebun Saya',
        kebun_lokasi VARCHAR(180) DEFAULT NULL,
        satuan_utama VARCHAR(20) NOT NULL DEFAULT 'kg',
        format_tanggal VARCHAR(30) NOT NULL DEFAULT 'd M Y',
        timezone VARCHAR(60) NOT NULL DEFAULT 'Asia/Jakarta',
        bahasa VARCHAR(20) NOT NULL DEFAULT 'id',
        notif_jadwal TINYINT(1) NOT NULL DEFAULT 1,
        notif_stok TINYINT(1) NOT NULL DEFAULT 1,
        notif_pengaduan TINYINT(1) NOT NULL DEFAULT 1,
        notif_ringkasan TINYINT(1) NOT NULL DEFAULT 1,
        notif_email TINYINT(1) NOT NULL DEFAULT 0,
        dashboard_mode VARCHAR(20) NOT NULL DEFAULT 'compact',
        show_focus TINYINT(1) NOT NULL DEFAULT 1,
        show_quick_actions TINYINT(1) NOT NULL DEFAULT 1,
        show_schedule TINYINT(1) NOT NULL DEFAULT 1,
        show_market TINYINT(1) NOT NULL DEFAULT 1,
        show_complaint TINYINT(1) NOT NULL DEFAULT 1,
        show_critical_stock TINYINT(1) NOT NULL DEFAULT 1,
        show_plant_status TINYINT(1) NOT NULL DEFAULT 1,
        limit_recent_activities INT(11) NOT NULL DEFAULT 4,
        limit_market_prices INT(11) NOT NULL DEFAULT 4,
        limit_plant_status INT(11) NOT NULL DEFAULT 5,
        account_full_name VARCHAR(120) DEFAULT NULL,
        account_email VARCHAR(160) DEFAULT NULL,
        account_phone VARCHAR(30) DEFAULT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id),
        CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    mysqli_query($koneksi, $sql);
}

function user_settings_get(mysqli $koneksi, int $user_id): array
{
    user_settings_ensure_table($koneksi);

    $defaults = user_settings_defaults();
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user_settings WHERE user_id = ? LIMIT 1");
    if (!$stmt) {
        return $defaults;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result) ?: [];
    mysqli_stmt_close($stmt);

    return array_merge($defaults, $row);
}

function user_settings_upsert(mysqli $koneksi, int $user_id, array $settings): bool
{
    user_settings_ensure_table($koneksi);

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO user_settings (
            user_id, kebun_nama, kebun_lokasi, satuan_utama, format_tanggal, timezone, bahasa,
            notif_jadwal, notif_stok, notif_pengaduan, notif_ringkasan, notif_email,
            dashboard_mode, show_focus, show_quick_actions, show_schedule, show_market,
            show_complaint, show_critical_stock, show_plant_status,
            limit_recent_activities, limit_market_prices, limit_plant_status,
            account_full_name, account_email, account_phone
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            kebun_nama = VALUES(kebun_nama),
            kebun_lokasi = VALUES(kebun_lokasi),
            satuan_utama = VALUES(satuan_utama),
            format_tanggal = VALUES(format_tanggal),
            timezone = VALUES(timezone),
            bahasa = VALUES(bahasa),
            notif_jadwal = VALUES(notif_jadwal),
            notif_stok = VALUES(notif_stok),
            notif_pengaduan = VALUES(notif_pengaduan),
            notif_ringkasan = VALUES(notif_ringkasan),
            notif_email = VALUES(notif_email),
            dashboard_mode = VALUES(dashboard_mode),
            show_focus = VALUES(show_focus),
            show_quick_actions = VALUES(show_quick_actions),
            show_schedule = VALUES(show_schedule),
            show_market = VALUES(show_market),
            show_complaint = VALUES(show_complaint),
            show_critical_stock = VALUES(show_critical_stock),
            show_plant_status = VALUES(show_plant_status),
            limit_recent_activities = VALUES(limit_recent_activities),
            limit_market_prices = VALUES(limit_market_prices),
            limit_plant_status = VALUES(limit_plant_status),
            account_full_name = VALUES(account_full_name),
            account_email = VALUES(account_email),
            account_phone = VALUES(account_phone)"
    );

    if (!$stmt) {
        return false;
    }

    $bind_types = "i" . str_repeat("s", 6) . str_repeat("i", 5) . "s" . str_repeat("i", 10) . str_repeat("s", 3);

    mysqli_stmt_bind_param(
        $stmt,
        $bind_types,
        $user_id,
        $settings['kebun_nama'],
        $settings['kebun_lokasi'],
        $settings['satuan_utama'],
        $settings['format_tanggal'],
        $settings['timezone'],
        $settings['bahasa'],
        $settings['notif_jadwal'],
        $settings['notif_stok'],
        $settings['notif_pengaduan'],
        $settings['notif_ringkasan'],
        $settings['notif_email'],
        $settings['dashboard_mode'],
        $settings['show_focus'],
        $settings['show_quick_actions'],
        $settings['show_schedule'],
        $settings['show_market'],
        $settings['show_complaint'],
        $settings['show_critical_stock'],
        $settings['show_plant_status'],
        $settings['limit_recent_activities'],
        $settings['limit_market_prices'],
        $settings['limit_plant_status'],
        $settings['account_full_name'],
        $settings['account_email'],
        $settings['account_phone']
    );

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
