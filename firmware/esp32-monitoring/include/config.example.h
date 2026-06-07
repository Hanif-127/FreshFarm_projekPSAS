#pragma once

// Salin file ini menjadi config.h, lalu isi data jaringan milikmu.
#define WIFI_SSID "NAMA_WIFI"
#define WIFI_PASSWORD "PASSWORD_WIFI"

#define API_URL "http://192.168.1.12/FreshFarm_projekPSAS-hanif/iot/api/v1/readings.php"
#define DEVICE_UID "farm-esp32-01"
#define API_KEY "GANTI_DENGAN_API_KEY_PERANGKAT"

#define SEND_INTERVAL_MS 300000UL

#define I2C_SDA_PIN 21
#define I2C_SCL_PIN 22
#define DHT_PIN 27
#define DHT_TYPE DHT11
#define SOIL_MOISTURE_PIN 34
#define DS18B20_PIN 4

// Sesuaikan setelah melakukan kalibrasi sensor tanah.
#define SOIL_DRY_ADC 3200
#define SOIL_WET_ADC 1350
