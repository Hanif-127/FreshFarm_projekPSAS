# Pairing Perangkat IoT

FreshFarm memakai satu Firebase Realtime Database untuk seluruh perangkat. Setiap ESP32 dibedakan menggunakan `device_uid`.

## Alur

1. Pengelola FreshFarm mendaftarkan ESP32 menggunakan tool provisioning.
2. Pengguna login dan membuka halaman **Monitoring IoT > Perangkat**.
3. Pengguna memasukkan Device UID dan kode pairing.
4. Perangkat terhubung ke akun pengguna dan otomatis menjadi perangkat aktif.

## Mendaftarkan ESP32 Baru

Jalankan dari folder utama proyek:

```powershell
php iot/tools/provision_device.php farm-esp32-02 FF-829174 api-key-rahasia "ESP32 Greenhouse" "Greenhouse Barat"
```

Nilai yang perlu dimasukkan ke firmware:

- `DEVICE_UID`: `farm-esp32-02`
- API key: `api-key-rahasia`, jika firmware memakai API PHP
- Firebase Database URL: tetap memakai Firebase FreshFarm

Kode pairing `FF-829174` diberikan kepada pemilik perangkat untuk diisikan pada halaman Perangkat.

## Keamanan

- Kode pairing dan API key disimpan sebagai hash di SQL.
- URL Firebase tidak perlu dimasukkan pengguna.
- Satu perangkat hanya dapat terhubung dengan satu akun.
- Firebase Rules tetap perlu diperketat sebelum aplikasi dipublikasikan.

