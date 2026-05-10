USE db_fresh_farm;

CREATE TABLE IF NOT EXISTS kalender_tanam (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nama_kegiatan VARCHAR(120) NOT NULL,
    tipe_kegiatan ENUM('tanam', 'pupuk', 'siram', 'panen', 'lainnya') NOT NULL DEFAULT 'tanam',
    komoditas VARCHAR(120) DEFAULT NULL,
    tanggal_jadwal DATE NOT NULL,
    jam_jadwal TIME DEFAULT NULL,
    pengingat_hari INT(11) NOT NULL DEFAULT 1,
    catatan TEXT DEFAULT NULL,
    status ENUM('terjadwal', 'selesai', 'terlewat', 'dibatalkan') NOT NULL DEFAULT 'terjadwal',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_kalender_user_tanggal (user_id, tanggal_jadwal),
    CONSTRAINT fk_kalender_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS inventaris (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nama_item VARCHAR(120) NOT NULL,
    kategori ENUM('benih', 'pupuk', 'pestisida', 'alat', 'lainnya') NOT NULL DEFAULT 'lainnya',
    jumlah_stok DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    satuan VARCHAR(30) NOT NULL DEFAULT 'unit',
    stok_minimum DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    lokasi_simpan VARCHAR(120) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inventaris_user_kategori (user_id, kategori),
    CONSTRAINT fk_inventaris_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pengaduan (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    judul VARCHAR(180) NOT NULL,
    pesan TEXT NOT NULL,
    prioritas ENUM('rendah', 'sedang', 'tinggi') NOT NULL DEFAULT 'sedang',
    status ENUM('dikirim', 'diproses', 'selesai', 'ditolak') NOT NULL DEFAULT 'dikirim',
    respon_admin TEXT DEFAULT NULL,
    lampiran VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pengaduan_user_status (user_id, status),
    CONSTRAINT fk_pengaduan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS user_preferences (
    user_id INT(11) NOT NULL,
    notifikasi_email TINYINT(1) NOT NULL DEFAULT 1,
    notifikasi_reminder TINYINT(1) NOT NULL DEFAULT 1,
    notifikasi_harga TINYINT(1) NOT NULL DEFAULT 1,
    bahasa VARCHAR(20) NOT NULL DEFAULT 'id',
    zona_waktu VARCHAR(60) NOT NULL DEFAULT 'Asia/Jakarta',
    tema VARCHAR(20) NOT NULL DEFAULT 'light',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_preferences_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS user_security_settings (
    user_id INT(11) NOT NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    last_password_changed DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_security_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
