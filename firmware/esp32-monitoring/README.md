# Firmware ESP32 Monitoring

Firmware ini membaca DHT11, BH1750, DS18B20, dan sensor soil moisture analog,
menampilkan data utama pada LCD I2C 16x2, lalu mengirim data ke Firebase
Realtime Database melalui internet.

Dengan Firebase, ESP32 tidak perlu satu jaringan dengan laptop dan tidak perlu
IP XAMPP lokal.

## Wiring ESP32 DevKit V1

| Sensor | Pin sensor | Pin ESP32 |
| --- | --- | --- |
| DHT11 | VCC | 3V3 |
| DHT11 | GND | GND |
| DHT11 | DATA | GPIO27 |
| BH1750 | VCC | 3V3 |
| BH1750 | GND | GND |
| BH1750 | SDA | GPIO21 |
| BH1750 | SCL | GPIO22 |
| LCD I2C 16x2 | SDA | GPIO21 |
| LCD I2C 16x2 | SCL | GPIO22 |
| LCD I2C 16x2 | GND | GND |
| LCD I2C 16x2 | VCC | 3V3 atau melalui level shifter jika memakai 5V |
| DS18B20 | VCC | 3V3 |
| DS18B20 | GND | GND |
| DS18B20 | DATA | GPIO4 |
| Soil moisture analog | VCC | 3V3 |
| Soil moisture analog | GND | GND |
| Soil moisture analog | AO | GPIO34 |

Untuk DHT11 polos berkaki empat, pasang resistor pull-up 10 kOhm antara DATA
dan 3V3. Modul DHT11 tiga pin biasanya sudah memiliki resistor tersebut.

Untuk DS18B20, pasang resistor pull-up 4.7 kOhm antara DATA dan 3V3.

Jangan memberi sensor soil moisture analog tegangan 5V. Gunakan 3V3 agar output
analog aman untuk input ADC ESP32.

LCD dan BH1750 berbagi bus I2C yang sama pada GPIO21 dan GPIO22. Alamat default
LCD di firmware adalah `0x27`, sedangkan BH1750 memakai `0x23`. Jika LCD tidak
menampilkan tulisan, cek apakah modul LCD menggunakan alamat `0x3F`, lalu ubah:

```cpp
#define LCD_I2C_ADDRESS 0x3F
```

Jika BH1750 belum dipasang, biarkan sensor tersebut dinonaktifkan:

```cpp
#define ENABLE_BH1750 false
```

Setelah BH1750 dipasang, ubah nilainya menjadi `true` dan upload ulang firmware.

Jika backpack LCD harus diberi daya 5V, gunakan bidirectional I2C level shifter
untuk jalur SDA dan SCL agar pin ESP32 tidak tertarik ke level 5V.

## Tampilan LCD

LCD diperbarui dan berganti halaman setiap 5 detik:

```text
Informasi Tanah
T=32C | H=80%
```

```text
Informasi Udara
T=30C | H=48%
```

LCD hanya menampilkan DHT11, DS18B20, dan kelembapan tanah. Status atau kekuatan
sinyal WiFi tidak ditampilkan.

## Konfigurasi Firebase

Buka `include/config.h`, lalu isi:

```cpp
#define WIFI_SSID "NAMA_WIFI"
#define WIFI_PASSWORD "PASSWORD_WIFI"
#define FIREBASE_DATABASE_URL "https://PROJECT_ID-default-rtdb.firebaseio.com"
#define FIREBASE_AUTH ""
```

Gunakan URL dari Firebase Console > Realtime Database. Untuk percobaan awal,
`FIREBASE_AUTH` boleh kosong jika rules masih test mode.

## Struktur Data Firebase

Firmware mengirim ke:

```text
/iot/devices/farm-esp32-01/latest
/iot/devices/farm-esp32-01/readings/{timestamp}
```

Contoh data:

```json
{
  "device_uid": "farm-esp32-01",
  "air_temperature_c": 29.0,
  "air_humidity_pct": 70.0,
  "light_lux": 12500.0,
  "soil_moisture_raw": 2400,
  "soil_moisture_mv": 1900,
  "soil_moisture_pct": 43.2,
  "soil_temperature_c": 27.5,
  "wifi_rssi": -50,
  "recorded_at": "2026-06-09 20:00:00"
}
```

## Upload dan Monitor

Jalankan dari folder `firmware/esp32-monitoring`:

```powershell
pio run
pio run --target upload
pio device monitor --baud 115200
```

Jika berhasil, Serial Monitor menampilkan `HTTP status: 200` untuk `latest` dan
`history`.

Firmware tetap berjalan otomatis selama ESP32 mendapatkan daya. Serial Monitor
hanya dipakai untuk melihat log. Saat Serial Monitor dibuka, beberapa board ESP32
akan otomatis reset, sehingga firmware langsung mengirim data pertama lagi. Data
berikutnya dikirim sesuai interval di `include/config.h`.

Firmware memakai dua interval:

```cpp
#define LATEST_INTERVAL_MS 15000UL
#define HISTORY_INTERVAL_MS 300000UL
```

`LATEST_INTERVAL_MS` dipakai untuk update `/latest`, yaitu sumber data card
dashboard. `HISTORY_INTERVAL_MS` dipakai untuk simpan `/readings/{timestamp}`,
yaitu sumber data grafik dan riwayat.

Dengan konfigurasi di atas:

- Card dashboard update setiap 15 detik.
- Grafik dan riwayat bertambah setiap 5 menit.

Jika nanti sudah stabil dan ingin lebih hemat traffic/daya, interval card bisa
dibuat lebih lambat, misalnya 1 menit:

```cpp
#define LATEST_INTERVAL_MS 60000UL
```

## Catatan Keamanan

Firmware saat ini memakai `secureClient.setInsecure()` agar HTTPS Firebase mudah
dicoba. Untuk produksi, lebih baik memakai root CA certificate.
