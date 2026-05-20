-- Fresh Smart Farm - Database lengkap siap demo
-- Import file ini saja lewat phpMyAdmin / MySQL.
-- Demo login: username = hanif, password = 123456

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS db_fresh_farm;
CREATE DATABASE db_fresh_farm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_fresh_farm;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE jurnal_tanam (
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

CREATE TABLE harga_pasar (
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

CREATE TABLE artikel (
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

CREATE TABLE kalender_tanam (
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

CREATE TABLE inventaris (
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

CREATE TABLE pengaduan (
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

CREATE TABLE user_preferences (
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

CREATE TABLE user_security_settings (
    user_id INT(11) NOT NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    last_password_changed DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_security_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_settings (
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

START TRANSACTION;

SET @demo_user_id := 2;

INSERT INTO users (id, username, password) VALUES
(2, 'hanif', 'e10adc3949ba59abbe56e057f20f883e');

INSERT INTO user_settings (
    user_id, kebun_nama, kebun_lokasi, satuan_utama, format_tanggal, timezone, bahasa,
    notif_jadwal, notif_stok, notif_pengaduan, notif_ringkasan, notif_email,
    dashboard_mode, show_focus, show_quick_actions, show_schedule, show_market,
    show_complaint, show_critical_stock, show_plant_status,
    limit_recent_activities, limit_market_prices, limit_plant_status,
    account_full_name, account_email, account_phone
) VALUES (
    @demo_user_id, 'Fresh Smart Farm - Lahan Utama', 'Sleman, DI Yogyakarta', 'kg',
    'd M Y', 'Asia/Jakarta', 'id', 1, 1, 1, 1, 1, 'normal',
    1, 1, 1, 1, 1, 1, 1, 8, 8, 8,
    'Hanif Pratama', 'hanif@freshfarm.demo', '0812-3456-7788'
);

INSERT INTO user_preferences (user_id, notifikasi_email, notifikasi_reminder, notifikasi_harga, bahasa, zona_waktu, tema) VALUES
(@demo_user_id, 1, 1, 1, 'id', 'Asia/Jakarta', 'light');

INSERT INTO user_security_settings (user_id, two_factor_enabled, last_password_changed) VALUES
(@demo_user_id, 0, '2026-04-20 09:00:00');

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
    user_id, nama_kegiatan, tipe_kegiatan, komoditas, tanggal_jadwal, jam_jadwal,
    pengingat_hari, catatan, status
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
    user_id, nama_item, kategori, jumlah_stok, satuan, stok_minimum, lokasi_simpan, catatan
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
    user_id, judul, pesan, prioritas, status, respon_admin, lampiran, created_at, updated_at
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
('Selada Hijau', 14000, 'kg', '2026-05-10'),
('Cabai Rawit Merah', 77750, 'kg', '2026-05-15'),
('Cabai Merah Besar', 61400, 'kg', '2026-05-15'),
('Cabai Merah Keriting', 56950, 'kg', '2026-05-15'),
('Cabai Rawit Hijau', 47400, 'kg', '2026-05-15'),
('Bawang Merah', 52500, 'kg', '2026-05-15'),
('Bawang Putih', 42950, 'kg', '2026-05-15'),
('Beras Medium I', 16000, 'kg', '2026-05-15'),
('Beras Medium II', 15800, 'kg', '2026-05-15'),
('Beras Super I', 17250, 'kg', '2026-05-15'),
('Beras Super II', 17000, 'kg', '2026-05-15'),
('Gula Pasir Lokal', 19500, 'kg', '2026-05-15'),
('Minyak Goreng Curah', 20000, 'liter', '2026-05-15');

INSERT INTO artikel (id, judul, isi, gambar, tanggal_publish) VALUES
(1, 'Strategi Rotasi Tanam untuk Menjaga Kesuburan Tanah', 'Rotasi tanam adalah cara sederhana untuk menjaga tanah tetap produktif dari musim ke musim. Jangan menanam keluarga tanaman yang sama terus-menerus di bedengan yang sama, karena unsur hara tertentu akan cepat terkuras dan risiko penyakit tanah meningkat.\n\nMulailah dengan membagi lahan menjadi beberapa petak. Setelah menanam cabai atau tomat, pindahkan ke tanaman daun seperti sawi, bayam, atau kangkung. Musim berikutnya bisa diselingi kacang panjang atau legum untuk membantu memperbaiki nitrogen tanah.\n\nCatat tanggal tanam, jenis tanaman, pupuk yang digunakan, dan hasil panen. Dari catatan itu, petani bisa melihat pola petak mana yang paling subur, tanaman mana yang cocok setelah panen sebelumnya, dan kapan tanah perlu diberi kompos tambahan.', 'rotasi_tanam.jpg', '2026-05-16'),
(2, 'Teknik Irigasi Tetes untuk Lahan Cabai', 'Irigasi tetes membantu air langsung masuk ke area perakaran sehingga pemakaian air lebih hemat dan kelembapan tanah lebih stabil. Pada tanaman cabai, sistem ini juga mengurangi percikan air ke daun yang sering memicu penyakit jamur.\n\nPemasangan dasar bisa dimulai dari tangki air, filter sederhana, pipa utama, lalu selang drip di setiap bedengan. Pastikan lubang tetes berada dekat pangkal tanaman, tetapi tidak menempel langsung pada batang. Jalankan penyiraman lebih singkat namun rutin, terutama saat fase pembungaan dan pembesaran buah.\n\nPeriksa filter, sambungan, dan lubang tetes setiap minggu. Jika ada aliran tersumbat, bersihkan sebelum tanaman mengalami stres air. Catatan jadwal penyiraman akan membantu menentukan pola terbaik sesuai cuaca dan jenis tanah.', 'irigasi_tetes.jpg', '2026-05-15'),
(3, 'Membuat Kompos Organik yang Siap Pakai di Kebun', 'Kompos organik memperbaiki struktur tanah, meningkatkan aktivitas mikroba, dan membantu tanaman menyerap nutrisi dengan lebih stabil. Bahan yang bisa digunakan antara lain daun kering, sisa sayuran, rumput, sekam, kotoran ternak matang, dan sedikit tanah kebun.\n\nSusun bahan cokelat seperti daun kering dan sekam bergantian dengan bahan hijau seperti sisa sayuran. Jaga kelembapan seperti spons yang diperas, tidak terlalu basah dan tidak terlalu kering. Balik tumpukan setiap satu sampai dua minggu agar proses penguraian merata.\n\nKompos siap dipakai ketika warnanya gelap, remah, tidak panas, dan berbau tanah segar. Gunakan sebagai pupuk dasar sebelum tanam atau taburan tipis di sekitar tanaman yang sedang tumbuh.', 'kompos_organik.jpg', '2026-05-14'),
(4, 'Pengendalian Hama Terpadu pada Sayuran', 'Pengendalian hama terpadu tidak langsung mengandalkan pestisida. Prinsip utamanya adalah memantau tanaman secara rutin, mengenali gejala sejak awal, lalu memilih tindakan paling ringan yang tetap efektif.\n\nPeriksa bagian bawah daun, pucuk muda, dan area sekitar bunga. Gunakan perangkap kuning untuk memantau serangga kecil, bersihkan gulma, dan jaga jarak tanam agar sirkulasi udara baik. Tanaman yang sehat biasanya lebih tahan terhadap serangan ringan.\n\nJika populasi hama meningkat, gunakan pengendalian mekanis atau bahan hayati terlebih dahulu. Pestisida kimia sebaiknya menjadi pilihan terakhir, dipakai sesuai dosis, waktu aplikasi, dan masa tunggu panen.', 'pengendalian_hama.jpg', '2026-05-13'),
(5, 'Checklist Panen dan Pascapanen Sayuran Segar', 'Panen yang baik dimulai sebelum pisau menyentuh tanaman. Siapkan keranjang bersih, area sortasi teduh, air bersih bila diperlukan, dan catatan jumlah panen. Panen pada pagi hari membantu sayuran tetap segar karena suhu belum terlalu tinggi.\n\nPisahkan hasil panen berdasarkan ukuran, kondisi fisik, dan tingkat kematangan. Buang daun rusak atau bagian yang terlalu tua. Hindari menumpuk sayuran terlalu tinggi karena tekanan dapat membuat hasil panen cepat layu dan memar.\n\nSetelah sortasi, simpan di tempat teduh dan segera kirim ke pasar atau pembeli. Catat volume panen, harga jual, dan pembeli utama agar keputusan tanam berikutnya lebih berbasis data.', 'panen_pascapanen.jpg', '2026-05-12'),
(6, 'Membaca Harga Pasar Sebelum Menjual Hasil Panen', 'Harga pasar dapat berubah cepat, terutama untuk komoditas seperti cabai, bawang, dan sayuran segar. Petani perlu memantau harga beberapa hari sebelum panen agar bisa menentukan waktu jual, tujuan pasar, dan strategi sortasi.\n\nBandingkan harga dari pasar lokal, pengepul, dan sumber informasi nasional. Jangan hanya melihat harga tertinggi, tetapi perhatikan juga biaya transportasi, risiko susut, volume yang diminta, dan kecepatan pembayaran.\n\nData harga yang dicatat secara rutin akan membantu membaca pola musiman. Saat harga mulai naik dan kualitas panen baik, petani bisa memprioritaskan komoditas yang paling cepat memberi margin. Saat harga turun, sortasi dan pengemasan yang rapi bisa membantu menjaga nilai jual.', 'harga_pasar_petani.jpg', '2026-05-11'),
(7, 'Menyusun Kalender Tanam saat Cuaca Berubah', 'Perubahan cuaca membuat jadwal tanam perlu lebih fleksibel. Petani bisa mulai dengan mencatat pola hujan, suhu harian, dan kondisi tanah sebelum menentukan tanggal tanam berikutnya. Kalender tanam yang baik tidak hanya berisi tanggal mulai, tetapi juga jadwal siram, pemupukan, penyiangan, dan perkiraan panen.\n\nSaat cuaca terlalu basah, beri jeda untuk bedengan agar tidak tergenang. Saat cuaca kering, majukan jadwal penyiraman dan cek kelembapan tanah lebih sering. Catatan sederhana dari minggu ke minggu akan membantu petani membaca kapan waktu tanam paling aman untuk komoditas tertentu.\n\nGunakan pengingat pada setiap kegiatan penting agar pekerjaan kebun tidak bertumpuk. Dengan kalender yang rapi, risiko telat pupuk, lupa semai, atau panen terlalu lambat bisa dikurangi.', 'kalender_tanam_cuaca.jpg', '2026-05-10'),
(8, 'Mengatur Stok Pupuk dan Benih agar Tidak Terlambat', 'Stok pupuk, benih, dan perlengkapan kebun sering terlihat cukup sampai jadwal tanam sudah dekat. Karena itu, setiap item penting perlu punya batas minimum stok. Ketika jumlahnya menyentuh batas itu, petani bisa segera menyiapkan pembelian sebelum kegiatan kebun terganggu.\n\nPisahkan stok berdasarkan kategori seperti benih, pupuk, pestisida, alat, dan bahan pendukung. Catat satuan dengan konsisten agar perhitungan kebutuhan lebih mudah. Untuk benih, tulis juga tanggal beli dan masa simpan supaya kualitasnya tetap terpantau.\n\nEvaluasi stok setelah panen atau setelah siklus tanam selesai. Dari catatan tersebut, petani bisa memperkirakan kebutuhan musim berikutnya dengan lebih akurat dan menghindari pembelian mendadak.', 'stok_pupuk_benih.jpg', '2026-05-09'),
(9, 'Memilih Bibit Sehat Sebelum Pindah Tanam', 'Bibit yang sehat menjadi modal awal untuk pertumbuhan tanaman yang kuat. Pilih bibit dengan batang kokoh, warna daun segar, dan akar yang tidak membusuk. Hindari bibit yang terlalu tinggi, pucat, atau memiliki bercak penyakit pada daun.\n\nSebelum pindah tanam, lakukan adaptasi bertahap dengan menaruh bibit di area yang mendapat cahaya cukup namun tidak terlalu panas. Siram secukupnya agar media tetap lembap, tetapi tidak becek. Proses ini membantu bibit lebih siap menghadapi kondisi bedengan.\n\nSaat menanam, usahakan akar tidak rusak dan media semai tetap menempel. Setelah pindah tanam, beri naungan sementara bila matahari terlalu terik dan pantau kondisi tanaman selama beberapa hari pertama.', 'bibit_sehat_pindah_tanam.jpg', '2026-05-08'),
(10, 'Sanitasi Lahan untuk Menekan Penyakit Tanaman', 'Sanitasi lahan adalah kebiasaan sederhana yang berdampak besar pada kesehatan tanaman. Bersihkan sisa tanaman sakit, daun busuk, dan gulma yang menjadi tempat hama atau penyakit berkembang. Area kebun yang rapi memudahkan pemantauan gejala sejak awal.\n\nGunakan alat yang bersih saat memangkas atau memanen. Jika alat dipakai pada tanaman sakit, bersihkan sebelum digunakan ke tanaman lain. Langkah kecil ini membantu mengurangi perpindahan penyakit antarpetak.\n\nJangan menumpuk sisa tanaman sakit di dekat bedengan aktif. Pisahkan dan kelola dengan aman. Sanitasi yang dilakukan rutin akan membuat lingkungan tanam lebih sehat dan mengurangi kebutuhan pengendalian yang berat.', 'sanitasi_lahan.jpg', '2026-05-07'),
(11, 'Memakai Mulsa agar Kelembapan Bedengan Stabil', 'Mulsa membantu menjaga kelembapan tanah, mengurangi pertumbuhan gulma, dan melindungi permukaan bedengan dari pukulan air hujan. Pada lahan sayur, mulsa bisa membuat kondisi akar lebih stabil terutama saat cuaca berubah cepat.\n\nPastikan bedengan sudah rapi dan pupuk dasar sudah diberikan sebelum mulsa dipasang. Buat lubang tanam sesuai jarak yang dibutuhkan komoditas. Jarak yang rapi membantu sirkulasi udara dan memudahkan perawatan.\n\nPeriksa mulsa secara berkala. Jika ada bagian sobek atau terangkat, rapikan kembali agar fungsi perlindungan tetap berjalan. Dengan pengelolaan yang baik, mulsa bisa membantu tanaman tumbuh lebih seragam.', 'mulsa_bedengan.jpg', '2026-05-06'),
(12, 'Mengecek pH Tanah Sebelum Pemupukan', 'pH tanah memengaruhi kemampuan tanaman menyerap unsur hara. Jika pH terlalu rendah atau terlalu tinggi, pupuk yang diberikan belum tentu bisa dimanfaatkan tanaman secara optimal. Karena itu, pengecekan pH sebaiknya dilakukan sebelum pemupukan besar.\n\nGunakan alat ukur sederhana atau kit uji tanah. Ambil sampel dari beberapa titik bedengan agar hasilnya lebih mewakili kondisi lahan. Catat hasil pengukuran, lokasi petak, dan tanggal pengecekan.\n\nJika pH kurang sesuai, lakukan perbaikan bertahap sesuai kebutuhan tanah dan jenis tanaman. Jangan langsung memberi dosis berlebihan. Pemupukan yang didahului data pH akan lebih efisien dan membantu tanaman tumbuh lebih seimbang.', 'ph_tanah_pemupukan.jpg', '2026-05-05');

COMMIT;
