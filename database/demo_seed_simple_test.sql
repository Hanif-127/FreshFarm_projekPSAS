USE db_fresh_farm;

START TRANSACTION;

SET @demo_user_id := 2;

DELETE FROM kalender_tanam WHERE user_id = @demo_user_id;
DELETE FROM inventaris WHERE user_id = @demo_user_id;
DELETE FROM pengaduan WHERE user_id = @demo_user_id;
DELETE FROM jurnal_tanam WHERE user_id = @demo_user_id;

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
    'Fresh Smart Farm - Uji Coba',
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
    'compact',
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    4,
    4,
    4,
    'Hanif Pratama',
    'hanif@freshfarm.test',
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

INSERT INTO jurnal_tanam (user_id, nama_tanaman, tanggal_tanam, jumlah, status, hasil_panen) VALUES
(@demo_user_id, 'Tomat', '2026-05-09', 180, 'Sedang Tanam', 0),
(@demo_user_id, 'Cabai Merah', '2026-05-07', 140, 'Sedang Tanam', 0),
(@demo_user_id, 'Pakcoy', '2026-05-05', 120, 'Sudah Panen', 85),
(@demo_user_id, 'Kangkung', '2026-05-02', 160, 'Sudah Panen', 110),
(@demo_user_id, 'Jagung Manis', '2026-04-26', 200, 'Gagal', 15);

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
(@demo_user_id, 'Penyiraman Pagi', 'siram', 'Tomat', '2026-05-11', '06:30:00', 1, 'Fokus petak barat.', 'terjadwal'),
(@demo_user_id, 'Pemupukan NPK', 'pupuk', 'Cabai Merah', '2026-05-12', '07:00:00', 1, 'Dosis ringan untuk fase vegetatif.', 'terjadwal'),
(@demo_user_id, 'Panen Pakcoy', 'panen', 'Pakcoy', '2026-05-08', '06:45:00', 1, 'Panen selesai tepat waktu.', 'selesai'),
(@demo_user_id, 'Pembersihan Gulma', 'lainnya', 'Jagung Manis', '2026-05-06', '15:30:00', 1, 'Terlewat karena hujan.', 'terlewat');

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
(@demo_user_id, 'Benih Tomat', 'benih', 1.40, 'kg', 1.00, 'Rak A1', 'Stok aman untuk pekan ini.'),
(@demo_user_id, 'Benih Cabai', 'benih', 0.70, 'kg', 1.00, 'Rak A1', 'Perlu restok ringan.'),
(@demo_user_id, 'Pupuk NPK', 'pupuk', 9.00, 'kg', 8.00, 'Gudang B1', 'Masih cukup.'),
(@demo_user_id, 'Pestisida Nabati', 'pestisida', 2.00, 'liter', 3.00, 'Gudang C1', 'Mendekati batas minimum.'),
(@demo_user_id, 'Selang Tetes', 'alat', 80.00, 'meter', 60.00, 'Gudang D2', 'Siap dipakai.'),
(@demo_user_id, 'Polybag 30x30', 'lainnya', 45.00, 'pcs', 50.00, 'Gudang D3', 'Perlu tambah untuk semai baru.');

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
(@demo_user_id, 'Sensor Kelembapan Tidak Stabil', 'Nilai kelembapan naik turun tidak wajar pada blok cabai.', 'sedang', 'diproses', 'Tim teknis dijadwalkan pengecekan besok pagi.', NULL, '2026-05-10 08:10:00', '2026-05-10 10:00:00'),
(@demo_user_id, 'Permintaan Update Harga Harian', 'Mohon update harga tomat dan cabai untuk pasar lokal.', 'rendah', 'dikirim', NULL, NULL, '2026-05-10 09:20:00', '2026-05-10 09:20:00');

COMMIT;
