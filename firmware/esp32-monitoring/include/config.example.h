#pragma once

// Salin file ini menjadi config.h, lalu isi nilai WiFi dan Firebase.
#define WIFI_SSID "NAMA_WIFI"
#define WIFI_PASSWORD "PASSWORD_WIFI"

// Firebase Realtime Database URL, tanpa slash di akhir.
// Contoh:
// https://freshfarm-iot-default-rtdb.asia-southeast1.firebasedatabase.app
#define FIREBASE_DATABASE_URL "https://PROJECT_ID-default-rtdb.firebaseio.com"

// Untuk percobaan awal boleh dikosongkan jika rules Firebase masih test mode.
// Untuk rules yang butuh auth, isi dengan token/database secret yang kamu pakai.
#define FIREBASE_AUTH ""

#define DEVICE_UID "farm-esp32-01"

// Card dashboard membaca /latest, jadi dibuat lebih cepat.
#define LATEST_INTERVAL_MS 15000UL

// Grafik dan riwayat membaca /readings, jadi disimpan lebih jarang agar
// history tidak penuh data yang terlalu rapat.
#define HISTORY_INTERVAL_MS 300000UL

#define DHT_PIN 27
#define DHT_TYPE DHT11

#define I2C_SDA_PIN 21
#define I2C_SCL_PIN 22
#define ENABLE_BH1750 false
#define BH1750_ADDRESS 0x23
#define LCD_I2C_ADDRESS 0x27
#define LCD_COLUMNS 16
#define LCD_ROWS 2
#define LCD_UPDATE_INTERVAL_MS 5000UL

#define DS18B20_PIN 4
#define SOIL_MOISTURE_PIN 34

// Sesuaikan setelah melakukan kalibrasi sensor tanah.
#define SOIL_DRY_ADC 3200
#define SOIL_WET_ADC 1350
