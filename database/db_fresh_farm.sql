-- Clone DB Fresh Farm untuk import di XAMPP (phpMyAdmin)
-- Dibuat otomatis dari struktur project pada 2026-05-12
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP DATABASE IF EXISTS db_fresh_farm;
CREATE DATABASE db_fresh_farm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_fresh_farm;
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS jurnal_tanam (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nama_tanaman VARCHAR(120) NOT NULL,
    tanggal_tanam DATE NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'Sedang Tanam',
    hasil_panen DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_jurnal_user_tanggal (user_id, tanggal_tanam),
    CONSTRAINT fk_jurnal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS harga_pasar (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama_komoditas VARCHAR(120) NOT NULL,
    harga DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    satuan VARCHAR(30) NOT NULL DEFAULT 'kg',
    tanggal DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_harga_tanggal (tanggal),
    KEY idx_harga_komoditas (nama_komoditas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS artikel (
    id INT(11) NOT NULL AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    tanggal_publish DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artikel_publish (tanggal_publish)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO users (id, username, password) VALUES
(2, 'hanif', 'e10adc3949ba59abbe56e057f20f883e')
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    password = VALUES(password);

-- Tabel modul dashboard

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

CREATE TABLE IF NOT EXISTS user_settings (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Data demo

USE db_fresh_farm;

START TRANSACTION;

SET @demo_user_id := 2;

INSERT INTO user_settings (
    user_id,
    kebun_nama,
    kebun_lokasi,
    satuan_utama,
    format_tanggal,
    timezone,
    bahasa,
    notif_jadwal,
    notif_stok,
    notif_pengaduan,
    notif_ringkasan,
    notif_email,
    dashboard_mode,
    show_focus,
    show_quick_actions,
    show_schedule,
    show_market,
    show_complaint,
    show_critical_stock,
    show_plant_status,
    limit_recent_activities,
    limit_market_prices,
    limit_plant_status,
    account_full_name,
    account_email,
    account_phone
) VALUES (
    @demo_user_id,
    'Fresh Smart Farm - Lahan Utama',
    'Sleman, DI Yogyakarta',
    'kg',
    'd M Y',
    'Asia/Jakarta',
    'id',
    1,
    1,
    1,
    1,
    1,
    'normal',
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    8,
    8,
    8,
    'Hanif Pratama',
    'hanif@freshfarm.demo',
    '0812-3456-7788'
)
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
    account_phone = VALUES(account_phone);

INSERT INTO user_preferences (
    user_id,
    notifikasi_email,
    notifikasi_reminder,
    notifikasi_harga,
    bahasa,
    zona_waktu,
    tema
) VALUES (
    @demo_user_id,
    1,
    1,
    1,
    'id',
    'Asia/Jakarta',
    'light'
)
ON DUPLICATE KEY UPDATE
    notifikasi_email = VALUES(notifikasi_email),
    notifikasi_reminder = VALUES(notifikasi_reminder),
    notifikasi_harga = VALUES(notifikasi_harga),
    bahasa = VALUES(bahasa),
    zona_waktu = VALUES(zona_waktu),
    tema = VALUES(tema);

INSERT INTO user_security_settings (
    user_id,
    two_factor_enabled,
    last_password_changed
) VALUES (
    @demo_user_id,
    0,
    '2026-04-20 09:00:00'
)
ON DUPLICATE KEY UPDATE
    two_factor_enabled = VALUES(two_factor_enabled),
    last_password_changed = VALUES(last_password_changed);

INSERT INTO jurnal_tanam (user_id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen) VALUES
(@demo_user_id, 'Bayam Hijau', '2026-05-10', 450, 'Sedang Tanam', 0),
(@demo_user_id, 'Selada Hijau', '2026-05-09', 320, 'Sedang Tanam', 0),
(@demo_user_id, 'Cabai Rawit', '2026-05-08', 280, 'Sedang Tanam', 0),
(@demo_user_id, 'Pakcoy', '2026-05-07', 220, 'Sudah Panen', 140),
(@demo_user_id, 'Kangkung', '2026-05-06', 350, 'Sudah Panen', 190),
(@demo_user_id, 'Terong Ungu', '2026-05-05', 180, 'Sedang Tanam', 0),
(@demo_user_id, 'Jagung Manis Premium', '2026-05-03', 500, 'Sudah Panen', 320),
(@demo_user_id, 'Tomat Cherry', '2026-04-28', 260, 'Sedang Tanam', 0),
(@demo_user_id, 'Timun Jepang', '2026-04-24', 300, 'Sudah Panen', 210),
(@demo_user_id, 'Cabai Merah Besar', '2026-04-19', 410, 'Gagal', 30),
(@demo_user_id, 'Sawi Putih', '2026-04-12', 270, 'Sudah Panen', 170),
(@demo_user_id, 'Bawang Daun', '2026-03-30', 200, 'Sudah Panen', 120),
(@demo_user_id, 'Pepaya California', '2026-03-22', 95, 'Sedang Tanam', 0),
(@demo_user_id, 'Melon Hijau', '2026-02-28', 140, 'Gagal', 20);

INSERT INTO kalender_tanam (
    user_id,
    nama_kegiatan,
    tipe_kegiatan,
    komoditas,
    tanggal_jadwal,
    jam_jadwal,
    pengingat_hari,
    catatan,
    status
) VALUES
(@demo_user_id, 'Penyiraman Blok Timur', 'siram', 'Cabai Rawit', '2026-05-11', '06:30:00', 1, 'Pastikan debit air stabil pada bedeng cabai.', 'terjadwal'),
(@demo_user_id, 'Pemupukan NPK Tahap 2', 'pupuk', 'Tomat Cherry', '2026-05-12', '07:15:00', 1, 'Gunakan dosis 2.5 kg per 1000 m2.', 'terjadwal'),
(@demo_user_id, 'Penyiangan Gulma', 'lainnya', 'Jagung Manis Premium', '2026-05-13', '08:00:00', 1, 'Fokus pada baris 3 sampai 6.', 'terjadwal'),
(@demo_user_id, 'Panen Parsial', 'panen', 'Selada Hijau', '2026-05-14', '06:45:00', 1, 'Prioritaskan petak dengan ukuran daun optimal.', 'terjadwal'),
(@demo_user_id, 'Tanam Ulang Bibit', 'tanam', 'Pakcoy', '2026-05-15', '07:30:00', 2, 'Siapkan 4 tray bibit cadangan.', 'terjadwal'),
(@demo_user_id, 'Pengecekan Hama Daun', 'lainnya', 'Terong Ungu', '2026-05-16', '16:00:00', 1, 'Inspeksi terutama setelah hujan sore.', 'terjadwal'),
(@demo_user_id, 'Panen Kangkung', 'panen', 'Kangkung', '2026-05-09', '06:20:00', 1, 'Panen selesai dan masuk gudang dingin.', 'selesai'),
(@demo_user_id, 'Penyiraman Rutin', 'siram', 'Bayam Hijau', '2026-05-08', '06:40:00', 1, 'Sesi penyiraman pagi terpenuhi.', 'selesai'),
(@demo_user_id, 'Pemupukan Dasar', 'pupuk', 'Selada Hijau', '2026-05-07', '07:10:00', 1, 'Dosis sesuai standar budidaya.', 'selesai'),
(@demo_user_id, 'Sanitasi Bedengan', 'lainnya', 'Cabai Merah Besar', '2026-05-06', '15:00:00', 1, 'Jadwal terlewat karena hujan deras.', 'terlewat'),
(@demo_user_id, 'Tanam Ulang Cabai', 'tanam', 'Cabai Merah Besar', '2026-05-04', '08:30:00', 2, 'Dibatalkan karena menunggu benih baru.', 'dibatalkan');

INSERT INTO inventaris (
    user_id,
    nama_item,
    kategori,
    jumlah_stok,
    satuan,
    stok_minimum,
    lokasi_simpan,
    catatan
) VALUES
(@demo_user_id, 'Benih Cabai Rawit', 'benih', 2.50, 'kg', 1.00, 'Gudang A1', 'Batch baru untuk tanam Mei.'),
(@demo_user_id, 'Benih Tomat Cherry', 'benih', 1.20, 'kg', 0.80, 'Gudang A1', 'Sisa benih masih layak tanam.'),
(@demo_user_id, 'Pupuk NPK 16-16-16', 'pupuk', 15.00, 'kg', 10.00, 'Gudang B2', 'Cukup untuk dua minggu.'),
(@demo_user_id, 'Pupuk Organik Cair', 'pupuk', 8.00, 'liter', 5.00, 'Gudang B1', 'Gunakan untuk selada dan pakcoy.'),
(@demo_user_id, 'Pestisida Nabati', 'pestisida', 3.00, 'liter', 4.00, 'Gudang C1', 'Perlu restok minggu ini.'),
(@demo_user_id, 'Mulsa Plastik', 'lainnya', 6.00, 'roll', 3.00, 'Gudang C2', 'Masih aman untuk 3 bedeng.'),
(@demo_user_id, 'Selang Tetes', 'alat', 120.00, 'meter', 80.00, 'Gudang D1', 'Siap untuk perluasan blok tanam.'),
(@demo_user_id, 'Polybag Ukuran 30x30', 'lainnya', 65.00, 'pcs', 100.00, 'Gudang D2', 'Stok kritis, disarankan restok.'),
(@demo_user_id, 'Sarung Tangan Kebun', 'alat', 20.00, 'unit', 12.00, 'Gudang E1', 'Masih cukup untuk tim lapangan.'),
(@demo_user_id, 'Ember Panen', 'alat', 5.00, 'unit', 6.00, 'Gudang E2', 'Perlu tambah 4 unit.'),
(@demo_user_id, 'Alat Semprot Manual', 'alat', 2.00, 'unit', 1.00, 'Gudang E2', 'Kondisi baik dan siap pakai.');

INSERT INTO pengaduan (
    user_id,
    judul,
    pesan,
    prioritas,
    status,
    respon_admin,
    lampiran,
    created_at,
    updated_at
) VALUES
(@demo_user_id, 'Permintaan Kalibrasi Sensor Kelembapan', 'Sensor kelembapan blok timur menunjukkan selisih data cukup besar dibanding alat manual. Mohon dijadwalkan kalibrasi.', 'tinggi', 'diproses', 'Tim teknis sudah dijadwalkan datang pada 12 Mei 2026.', NULL, '2026-05-09 09:10:00', '2026-05-10 08:45:00'),
(@demo_user_id, 'Akses Dashboard Lambat saat Jam Sibuk', 'Saat akses pukul 08.00-09.00 WIB, dashboard terasa lambat saat membuka grafik.', 'sedang', 'dikirim', NULL, NULL, '2026-05-10 07:42:00', '2026-05-10 07:42:00'),
(@demo_user_id, 'Data Harga Pasar Jagung Belum Terbaru', 'Harga jagung di wilayah kami sudah naik, mohon update rentang harga terbaru.', 'rendah', 'selesai', 'Harga pasar telah diperbarui pada 10 Mei 2026.', NULL, '2026-05-08 14:15:00', '2026-05-10 10:05:00'),
(@demo_user_id, 'Notifikasi Jadwal Tidak Masuk Email', 'Notifikasi jadwal harian tidak terkirim ke email dalam dua hari terakhir.', 'sedang', 'ditolak', 'Fitur email nonaktif pada pengaturan akun. Silakan aktifkan kembali untuk menerima notifikasi.', NULL, '2026-05-07 18:20:00', '2026-05-08 09:30:00');

INSERT INTO harga_pasar (nama_komoditas, harga, satuan, tanggal) VALUES
('Cabai Merah Keriting', 60500, 'kg', '2026-05-10'),
('Bawang Merah', 43800, 'kg', '2026-05-10'),
('Tomat Sayur', 12800, 'kg', '2026-05-10'),
('Jagung Pipil', 7600, 'kg', '2026-05-10'),
('Padi Gabah Kering', 7100, 'kg', '2026-05-10'),
('Kentang Dieng', 18500, 'kg', '2026-05-10'),
('Bayam Hijau', 9000, 'ikat', '2026-05-10'),
('Selada Hijau', 14000, 'kg', '2026-05-10');

INSERT INTO artikel (judul, isi, gambar, tanggal_publish) VALUES
('Strategi Rotasi Tanam untuk Menjaga Kesuburan Tanah', 'Rotasi tanam membantu menjaga struktur tanah, menekan penyakit, dan meningkatkan hasil panen secara berkelanjutan. Terapkan pola rotasi sederhana berdasarkan keluarga tanaman agar nutrisi tidak cepat menurun.', 'artikel_default.png', '2026-05-10'),
('Checklist Persiapan Panen Mingguan di Kebun Sayur', 'Sebelum panen mingguan, pastikan kesiapan tenaga kerja, kebersihan alat panen, serta area sortasi. Checklist sederhana ini membantu menjaga kualitas hasil dan mengurangi kehilangan pascapanen.', 'artikel_default.png', '2026-05-09');

COMMIT;