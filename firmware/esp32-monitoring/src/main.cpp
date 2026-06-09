#include <Arduino.h>
#include <BH1750.h>
#include <DallasTemperature.h>
#include <DHT.h>
#include <HTTPClient.h>
#include <LiquidCrystal_I2C.h>
#include <OneWire.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <Wire.h>
#include <time.h>

#include "config.h"

DHT airSensor(DHT_PIN, DHT_TYPE);
BH1750 lightSensor;
OneWire oneWire(DS18B20_PIN);
DallasTemperature soilTemperatureSensor(&oneWire);
LiquidCrystal_I2C lcd(LCD_I2C_ADDRESS, LCD_COLUMNS, LCD_ROWS);
WiFiClientSecure secureClient;

unsigned long lastLatestSendAt = 0;
unsigned long lastHistorySendAt = 0;
unsigned long lastLcdUpdateAt = 0;
unsigned long sendCounter = 0;
bool bh1750Ready = false;
uint8_t lcdPage = 0;

struct SensorReadings {
    float airTemperature = NAN;
    float airHumidity = NAN;
    float lightLux = NAN;
    int soilMoistureRaw = 0;
    int soilMoistureMilliVolts = 0;
    float soilMoisturePercent = NAN;
    float soilTemperature = NAN;
};

SensorReadings sensorReadings;

void printSendSchedule()
{
    Serial.print("Interval update card (/latest): ");
    Serial.print(LATEST_INTERVAL_MS / 1000UL);
    Serial.println(" detik");
    Serial.print("Interval simpan grafik/riwayat (/readings): ");
    Serial.print(HISTORY_INTERVAL_MS / 1000UL);
    Serial.println(" detik");
    Serial.print("Interval pembaruan LCD: ");
    Serial.print(LCD_UPDATE_INTERVAL_MS / 1000UL);
    Serial.println(" detik");
    Serial.println("Firmware tetap berjalan selama ESP32 menyala, meskipun Serial Monitor ditutup.");
}

float calculateSoilMoisturePercent(int rawValue)
{
    const int adcRange = SOIL_DRY_ADC - SOIL_WET_ADC;

    if (adcRange == 0) {
        return 0.0F;
    }

    const float percentage =
        (static_cast<float>(SOIL_DRY_ADC - rawValue) / static_cast<float>(adcRange)) *
        100.0F;

    return constrain(percentage, 0.0F, 100.0F);
}

String jsonNumber(float value, unsigned int decimals)
{
    return isnan(value) ? "null" : String(value, decimals);
}

String lcdTemperature(float value)
{
    if (isnan(value)) {
        return "--C";
    }

    String output = String(value, 0);
    output += static_cast<char>(223);
    output += 'C';
    return output;
}

String lcdPercent(float value)
{
    return isnan(value) ? "--%" : String(value, 0) + '%';
}

void printLcdLine(uint8_t row, String text)
{
    if (text.length() > LCD_COLUMNS) {
        text.remove(LCD_COLUMNS);
    }

    while (text.length() < LCD_COLUMNS) {
        text += ' ';
    }

    lcd.setCursor(0, row);
    lcd.print(text);
}

void readSensors()
{
    sensorReadings.airTemperature = airSensor.readTemperature();
    sensorReadings.airHumidity = airSensor.readHumidity();

    sensorReadings.lightLux = NAN;
    if (bh1750Ready) {
        sensorReadings.lightLux = lightSensor.readLightLevel();
        if (sensorReadings.lightLux < 0) {
            sensorReadings.lightLux = NAN;
        }
    }

    soilTemperatureSensor.requestTemperatures();
    sensorReadings.soilTemperature = soilTemperatureSensor.getTempCByIndex(0);
    if (sensorReadings.soilTemperature == DEVICE_DISCONNECTED_C) {
        sensorReadings.soilTemperature = NAN;
    }

    sensorReadings.soilMoistureRaw = analogRead(SOIL_MOISTURE_PIN);
    sensorReadings.soilMoistureMilliVolts = analogReadMilliVolts(SOIL_MOISTURE_PIN);
    sensorReadings.soilMoisturePercent =
        calculateSoilMoisturePercent(sensorReadings.soilMoistureRaw);
}

void updateLcd()
{
    if (lcdPage == 0) {
        printLcdLine(0, "Informasi Tanah");
        printLcdLine(
            1,
            "T=" + lcdTemperature(sensorReadings.soilTemperature) +
                " | H=" + lcdPercent(sensorReadings.soilMoisturePercent)
        );
    } else {
        printLcdLine(0, "Informasi Udara");
        printLcdLine(
            1,
            "T=" + lcdTemperature(sensorReadings.airTemperature) +
                " | H=" + lcdPercent(sensorReadings.airHumidity)
        );
    }

    lcdPage = (lcdPage + 1) % 2;
}

void connectWifi()
{
    if (WiFi.status() == WL_CONNECTED) {
        return;
    }

    Serial.printf("Menghubungkan ke WiFi %s", WIFI_SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    const unsigned long startedAt = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startedAt < 20000UL) {
        delay(500);
        Serial.print('.');
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.printf("\nWiFi terhubung. IP ESP32: %s\n", WiFi.localIP().toString().c_str());
        return;
    }

    Serial.println("\nWiFi belum terhubung.");
}

void syncTime()
{
    configTime(7 * 3600, 0, "pool.ntp.org", "time.google.com", "time.nist.gov");

    Serial.print("Sinkronisasi waktu NTP");
    const unsigned long startedAt = millis();
    while (time(nullptr) < 1700000000 && millis() - startedAt < 15000UL) {
        delay(500);
        Serial.print('.');
    }
    Serial.println();

    if (time(nullptr) < 1700000000) {
        Serial.println("Waktu NTP belum tersinkron. Timestamp memakai millis().");
    }
}

String currentTimestamp()
{
    const time_t now = time(nullptr);

    if (now >= 1700000000) {
        return String(static_cast<unsigned long>(now));
    }

    return String("millis-") + String(millis());
}

String currentIsoTime()
{
    const time_t now = time(nullptr);

    if (now < 1700000000) {
        return "";
    }

    struct tm timeInfo;
    localtime_r(&now, &timeInfo);

    char buffer[24];
    strftime(buffer, sizeof(buffer), "%Y-%m-%d %H:%M:%S", &timeInfo);
    return String(buffer);
}

String firebaseUrl(const String &path)
{
    String url = FIREBASE_DATABASE_URL;

    if (url.endsWith("/")) {
        url.remove(url.length() - 1);
    }

    url += path;
    url += ".json";

    const String auth = FIREBASE_AUTH;
    if (auth.length() > 0) {
        url += "?auth=";
        url += auth;
    }

    return url;
}

bool sendToFirebase(const String &method, const String &path, const String &payload)
{
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Firebase dilewati karena WiFi belum terhubung.");
        return false;
    }

    HTTPClient http;
    const String url = firebaseUrl(path);

    if (!http.begin(secureClient, url)) {
        Serial.println("Gagal memulai koneksi HTTPS Firebase.");
        return false;
    }

    http.addHeader("Content-Type", "application/json");

    int statusCode = -1;
    if (method == "PUT") {
        statusCode = http.PUT(payload);
    } else if (method == "PATCH") {
        statusCode = http.PATCH(payload);
    } else {
        statusCode = http.POST(payload);
    }

    const String response = http.getString();

    Serial.printf("Firebase %s %s\n", method.c_str(), path.c_str());
    Serial.printf("HTTP status: %d\n", statusCode);
    Serial.println(response);

    http.end();
    return statusCode >= 200 && statusCode < 300;
}

String readSensorPayload()
{
    const int wifiRssi = WiFi.status() == WL_CONNECTED ? WiFi.RSSI() : 0;
    const String timestamp = currentTimestamp();
    const String recordedAt = currentIsoTime();

    String payload = "{";
    payload += "\"device_uid\":\"" DEVICE_UID "\",";
    payload += "\"air_temperature_c\":" + jsonNumber(sensorReadings.airTemperature, 2) + ",";
    payload += "\"air_humidity_pct\":" + jsonNumber(sensorReadings.airHumidity, 2) + ",";
    payload += "\"light_lux\":" + jsonNumber(sensorReadings.lightLux, 2) + ",";
    payload += "\"soil_moisture_raw\":" + String(sensorReadings.soilMoistureRaw) + ",";
    payload += "\"soil_moisture_mv\":" + String(sensorReadings.soilMoistureMilliVolts) + ",";
    payload += "\"soil_moisture_pct\":" + jsonNumber(sensorReadings.soilMoisturePercent, 2) + ",";
    payload += "\"soil_temperature_c\":" + jsonNumber(sensorReadings.soilTemperature, 2) + ",";
    payload += "\"wifi_rssi\":" + String(wifiRssi) + ",";
    payload += "\"recorded_at_unix\":\"" + timestamp + "\",";
    payload += "\"recorded_at\":\"" + recordedAt + "\",";
    payload += "\"uptime_ms\":" + String(millis());
    payload += "}";

    Serial.println();
    Serial.println("========================================");
    Serial.print("Pengiriman ke-");
    Serial.print(sendCounter);
    Serial.print(" | uptime ");
    Serial.print(millis() / 1000);
    Serial.println(" detik");
    Serial.println("----------------------------------------");
    Serial.println(payload);

    return payload;
}

void readAndSendSensors(bool saveHistory)
{
    sendCounter++;
    connectWifi();

    const String payload = readSensorPayload();
    const String basePath = "/iot/devices/" DEVICE_UID;
    const String historyKey = currentTimestamp();

    const bool latestOk = sendToFirebase("PUT", basePath + "/latest", payload);
    bool historyOk = false;
    if (saveHistory) {
        historyOk = sendToFirebase("PUT", basePath + "/readings/" + historyKey, payload);
    }

    Serial.println("----------------------------------------");
    Serial.printf("Latest Firebase        : %s\n", latestOk ? "berhasil" : "gagal");
    Serial.printf("Simpan grafik/riwayat  : %s\n", saveHistory ? (historyOk ? "berhasil" : "gagal") : "dilewati");
}

void setup()
{
    Serial.begin(115200);
    delay(1500);

    Serial.println();
    Serial.println("FreshFarm ESP32 - Firebase Realtime Database");
    Serial.println("Sensor: DHT11, BH1750, DS18B20, soil moisture analog");
    printSendSchedule();
    Serial.println();

    analogReadResolution(12);
    analogSetPinAttenuation(SOIL_MOISTURE_PIN, ADC_11db);

    airSensor.begin();
    soilTemperatureSensor.begin();

    Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
    lcd.init();
    lcd.backlight();
    printLcdLine(0, "FreshFarm IoT");
    printLcdLine(1, "Menyiapkan...");

    if (ENABLE_BH1750) {
        bh1750Ready = lightSensor.begin(BH1750::CONTINUOUS_HIGH_RES_MODE, BH1750_ADDRESS, &Wire);
        if (!bh1750Ready) {
            Serial.println("BH1750 tidak terdeteksi. Nilai light_lux akan dikirim null.");
        }
    } else {
        Serial.println("BH1750 dinonaktifkan dari config.h.");
    }

    Serial.print("Jumlah sensor DS18B20 terdeteksi: ");
    Serial.println(soilTemperatureSensor.getDeviceCount());

    // Prototype mode: lebih mudah untuk Firebase HTTPS. Nanti bisa diganti CA cert.
    secureClient.setInsecure();

    connectWifi();
    if (WiFi.status() == WL_CONNECTED) {
        syncTime();
    }

    delay(2000);
    readSensors();
    updateLcd();
    readAndSendSensors(true);
    lastLatestSendAt = millis();
    lastHistorySendAt = millis();
    lastLcdUpdateAt = millis();
}

void loop()
{
    const unsigned long now = millis();

    if (now - lastLcdUpdateAt >= LCD_UPDATE_INTERVAL_MS) {
        readSensors();
        updateLcd();
        lastLcdUpdateAt = now;
    }

    if (now - lastLatestSendAt >= LATEST_INTERVAL_MS) {
        const bool saveHistory = now - lastHistorySendAt >= HISTORY_INTERVAL_MS;
        readAndSendSensors(saveHistory);
        lastLatestSendAt = now;

        if (saveHistory) {
            lastHistorySendAt = now;
        }
    }

    delay(100);
}
