# Fresh Smart Farm

Fresh Smart Farm adalah aplikasi web PHP/MySQL untuk membantu petani mencatat aktivitas tanam, jadwal, inventaris, harga pasar, artikel edukasi, pengaduan, dan ringkasan operasional kebun.

Dokumen ini dibuat sebagai pedoman cepat untuk developer atau AI yang melanjutkan pekerjaan ketika konteks percakapan sudah habis.

## Stack

- Backend: PHP native dengan `mysqli`
- Database: MySQL/MariaDB, default XAMPP
- Server lokal: XAMPP Apache
- Frontend: HTML, CSS native, JavaScript native, Chart.js untuk grafik
- Database name default: `db_fresh_farm`

## Cara Menjalankan

1. Simpan project di:
   `C:\xampp\htdocs\FreshFarm_projekPSAS-hanif`
2. Jalankan Apache dan MySQL dari XAMPP.
3. Import database dari file tunggal siap demo:
   `database/freshfarm_demo_lengkap.sql`
   Pastikan folder `assets/images_artikel/` ikut tersalin, karena artikel demo memakai gambar dari folder ini.
4. Buka:
   `http://localhost/FreshFarm_projekPSAS-hanif/`
5. Login demo dari seed lama biasanya:
   username `hanif`, password `123456`

## Struktur Penting

- `index.php`: landing page.
- `login.php`: halaman login.
- `register.php`: halaman pendaftaran.
- `lupa_sandi.php`: halaman reset sandi sederhana.
- `proses_login.php`: proses login dan upgrade hash password lama.
- `proses_register.php`: proses register.
- `logout.php`: logout.
- `includes/koneksi.php`: koneksi database.
- `includes/header.php`: satu-satunya header utama untuk landing, artikel, harga pasar, dashboard, dan modul.
- `includes/sidebar.php`: sidebar khusus halaman dashboard/module.
- `includes/user_settings.php`: helper preferensi user dan pengaturan dashboard.
- `pages/dashboard.php`: dashboard utama.
- `pages/ringkasan.php`: ringkasan.
- `pages/jurnal/`: CRUD jurnal tanam.
- `pages/kalender.php`: kalender tanam.
- `pages/inventaris.php`: inventaris.
- `pages/grafik.php`: laporan dan grafik.
- `pages/pengaduan.php`: pengaduan.
- `pages/pengaturan.php`: pengaturan.
- `pages/artikel.php` dan `pages/detail_artikel.php`: artikel.
- `pages/harga_pasar.php` dan `pages/detail_harga.php`: harga pasar.

## CSS Utama

- `assets/css/index.css`: landing page aktif untuk `index.php`.
- `assets/css/landing.css`: stylesheet landing lama yang masih disimpan sebagai referensi.
- `assets/css/login.css`: login.
- `assets/css/register.css`: register.
- `assets/css/lupa_sandi.css`: lupa sandi.
- `assets/css/dashboard_base.css`: shell dashboard, sidebar, dashboard home, dan style dasar modul.
- CSS modul tambahan:
  - `assets/css/ringkasan.css`
  - `assets/css/jurnal_tanam.css`
  - `assets/css/kalender_tanam.css`
  - `assets/css/inventaris.css`
  - `assets/css/laporan_grafik.css`
  - `assets/css/pengaduan.css`
  - `assets/css/pengaturan.css`

## Aturan UI Saat Melanjutkan

- Header utama harus tetap satu sumber di `includes/header.php`.
- Jangan membuat header baru di halaman lain.
- Jangan override style header dari `dashboard_base.css` atau CSS modul kecuali sangat perlu.
- Dashboard dan modul yang memakai sidebar harus include:
  - `includes/header.php`
  - `includes/sidebar.php`
  - `assets/css/dashboard_base.css`
- Dashboard memakai body class:
  `dashboard-home dashboard-compact` atau `dashboard-home dashboard-normal`
- Halaman modul memakai body class:
  `module-page`
- Card dashboard dan modul sebaiknya solid/opaque, bukan glass transparan, karena sebelumnya dianggap sulit dibaca.
- Warna UI saat ini sengaja dibuat terang, kontras, dan tidak terlalu hijau gelap.
- Jangan menambah dekorasi berat atau background ramai di area dashboard.
- Prioritaskan keterbacaan, jarak antar elemen, dan alur informasi yang mudah discan.

## Aturan Backend

- Jangan ubah logic/backend kalau request hanya UI.
- Gunakan prepared statement untuk input user baru.
- Query lama di beberapa halaman masih pakai `mysqli_query`; jangan refactor besar tanpa diminta.
- Session login menggunakan:
  - `$_SESSION['user_id']`
  - `$_SESSION['username']`
- Halaman dashboard/module harus redirect ke `login.php` kalau belum login.
- `user_settings_get()` dan `user_settings_upsert()` ada di `includes/user_settings.php`.
- Password baru memakai `password_hash()`.
- `proses_login.php` masih mendukung password MD5 lama dan otomatis upgrade ke hash modern setelah login berhasil.

## Catatan Halaman Lupa Sandi

File:
- `lupa_sandi.php`
- `assets/css/lupa_sandi.css`

Reset sandi menggunakan username dan email jika akun punya email di `user_settings.account_email`.
Jika akun lama belum punya email, reset masih bisa dilakukan dengan username.
Ini dibuat sederhana untuk kebutuhan project sekolah, bukan alur produksi dengan token email.

## Dashboard Saat Ini

`pages/dashboard.php` mengambil data dari:
- `jurnal_tanam`
- `kalender_tanam`
- `inventaris`
- `pengaduan`
- `harga_pasar`

Urutan tampilan dashboard:
1. Command center: sapaan, tanggal, mode, aksi cepat.
2. KPI utama: total jurnal, tanaman tercatat, jadwal hari ini, stok menipis, pengaduan aktif.
3. Prioritas hari ini: fokus, kesehatan operasional, jadwal, stok kritis.
4. Analitik: grafik jumlah tanam dan aktivitas terbaru.
5. Support grid: harga pasar, status tanaman, pengaduan, kelengkapan data.

Chart.js dimuat dari CDN di `pages/dashboard.php` dan `pages/grafik.php`.

## Header Saat Ini

Header ada di `includes/header.php`.

Karakter desain:
- Background terang warm-white, bukan hijau gelap.
- Active menu tetap hijau agar terlihat jelas.
- Logo dan brand punya kolom sendiri.
- Menu pindah ke hamburger pada viewport sedang agar tidak tabrakan dengan logo, icon, dashboard button, atau avatar.
- Login/register dan user menu berada di sisi kanan.
- Mobile menu memakai tombol `#navToggle` dan list `#navMenu`.

Jika memperbaiki header lagi:
- Edit hanya `includes/header.php`.
- Cek juga tampilan landing, artikel, harga pasar, dashboard, dan modul.
- Jangan menambah navbar lain di halaman.

## Verifikasi Cepat

Jalankan lint PHP:

```bash
php -l includes/header.php
php -l pages/dashboard.php
```

Lint semua PHP di PowerShell:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Cek akses lokal:

```powershell
Invoke-WebRequest -Uri 'http://localhost/FreshFarm_projekPSAS-hanif/' -UseBasicParsing
Invoke-WebRequest -Uri 'http://localhost/FreshFarm_projekPSAS-hanif/pages/artikel.php' -UseBasicParsing
Invoke-WebRequest -Uri 'http://localhost/FreshFarm_projekPSAS-hanif/lupa_sandi.php' -UseBasicParsing
```

Dashboard akan redirect ke login jika belum ada session.

## Catatan Git

Saat dokumen ini dibuat, workspace sudah punya beberapa perubahan lain di luar request terakhir. Jangan melakukan reset atau revert tanpa instruksi eksplisit.

File yang sebelumnya memang sudah berubah/baru sebelum beberapa perubahan UI:
- `assets/css/dashboard_base.css`
- `pages/dashboard.php`
- `login.php`
- beberapa file landing/artikel/grafik/register
- aset artikel dan background

Prinsip aman:
- Jangan pakai `git reset --hard`.
- Jangan checkout file untuk menghapus perubahan user.
- Kalau hanya diminta UI, batasi perubahan ke file UI terkait.
