# Firmware ESP32 Monitoring

Firmware ini membaca DHT11, BH1750, sensor kelembapan tanah analog, dan
DS18B20, kemudian mengirim hasilnya ke FreshFarm IoT API setiap 5 menit.

## Wiring ESP32 DevKit V1

| Sensor | Pin sensor | Pin ESP32 |
| --- | --- | --- |
| DHT11 | VCC / GND / DATA | 3V3 / GND / GPIO27 |
| BH1750 | VCC / GND | 3V3 / GND |
| BH1750 | SDA / SCL | GPIO21 / GPIO22 |
| Sensor tanah analog | VCC / GND / AO | 3V3 / GND / GPIO34 |
| DS18B20 | VCC / GND / DATA | 3V3 / GND / GPIO4 |

Untuk DHT11 polos berkaki empat, pasang resistor pull-up 10 kOhm antara DATA
dan 3V3. Modul DHT11 tiga pin biasanya sudah memiliki resistor tersebut.

Pasang resistor 4,7 kOhm antara DATA dan 3V3 pada DS18B20. Jangan memberi modul
sensor tanah analog tegangan 5V karena output analog dapat melebihi batas input
ESP32.

## Menjalankan

1. Pasang ekstensi PlatformIO IDE di VS Code.
2. Buka folder `firmware/esp32-monitoring` sebagai proyek PlatformIO.
3. Isi `include/config.h` dengan nama dan password WiFi.
4. Pastikan komputer dan ESP32 berada pada WiFi yang sama.
5. Pastikan URL API masih menggunakan IP WiFi komputer yang benar.
6. Hubungkan ESP32, lalu jalankan **Upload**.
7. Buka **Serial Monitor** pada baud rate `115200`.

Respons berhasil dari API memiliki HTTP status `201`.
