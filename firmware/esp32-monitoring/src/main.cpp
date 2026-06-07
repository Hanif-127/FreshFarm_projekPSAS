#include <Arduino.h>
#include <BH1750.h>
#include <DallasTemperature.h>
#include <DHT.h>
#include <HTTPClient.h>
#include <OneWire.h>
#include <WiFi.h>
#include <Wire.h>

#include "config.h"

DHT airSensor(DHT_PIN, DHT_TYPE);
BH1750 lightMeter;
OneWire oneWire(DS18B20_PIN);
DallasTemperature soilTemperatureSensor(&oneWire);

unsigned long lastSendAt = 0;

float readAirTemperature()
{
    const float value = airSensor.readTemperature();

    if (isnan(value)) {
        Serial.println("DHT11 gagal membaca suhu udara.");
    }

    return value;
}

float readAirHumidity()
{
    const float value = airSensor.readHumidity();

    if (isnan(value)) {
        Serial.println("DHT11 gagal membaca kelembapan udara.");
    }

    return value;
}

float calculateSoilMoisturePercent(int rawValue)
{
    const float percentage =
        (static_cast<float>(SOIL_DRY_ADC - rawValue) /
         static_cast<float>(SOIL_DRY_ADC - SOIL_WET_ADC)) *
        100.0F;

    return constrain(percentage, 0.0F, 100.0F);
}

String jsonNumber(float value, unsigned int decimals)
{
    return isnan(value) ? "null" : String(value, decimals);
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
    } else {
        Serial.println("\nWiFi belum terhubung.");
    }
}

void readAndSendSensors()
{
    connectWifi();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Pengiriman dilewati karena WiFi belum terhubung.");
        return;
    }

    const float airTemperature = readAirTemperature();
    const float airHumidity = readAirHumidity();
    float lightLux = lightMeter.readLightLevel();
    if (lightLux < 0) {
        lightLux = NAN;
    }
    const int soilMoistureRaw = analogRead(SOIL_MOISTURE_PIN);
    const float soilMoisturePercent = calculateSoilMoisturePercent(soilMoistureRaw);

    soilTemperatureSensor.requestTemperatures();
    float soilTemperature = soilTemperatureSensor.getTempCByIndex(0);
    if (soilTemperature == DEVICE_DISCONNECTED_C) {
        soilTemperature = NAN;
    }

    const int wifiRssi = WiFi.RSSI();

    String payload = "{";
    payload += "\"device_uid\":\"" DEVICE_UID "\",";
    payload += "\"air_temperature_c\":" + jsonNumber(airTemperature, 2) + ",";
    payload += "\"air_humidity_pct\":" + jsonNumber(airHumidity, 2) + ",";
    payload += "\"light_lux\":" + jsonNumber(lightLux, 2) + ",";
    payload += "\"soil_moisture_raw\":" + String(soilMoistureRaw) + ",";
    payload += "\"soil_moisture_pct\":" + jsonNumber(soilMoisturePercent, 2) + ",";
    payload += "\"soil_temperature_c\":" + jsonNumber(soilTemperature, 2) + ",";
    payload += "\"wifi_rssi\":" + String(wifiRssi);
    payload += "}";

    HTTPClient http;
    http.begin(API_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", API_KEY);

    Serial.println("Mengirim data sensor:");
    Serial.println(payload);

    const int statusCode = http.POST(payload);
    const String response = http.getString();

    Serial.printf("HTTP status: %d\n", statusCode);
    Serial.println(response);

    http.end();
}

void setup()
{
    Serial.begin(115200);
    delay(1000);

    Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
    analogSetPinAttenuation(SOIL_MOISTURE_PIN, ADC_11db);
    airSensor.begin();
    delay(2000);

    if (!lightMeter.begin(BH1750::CONTINUOUS_HIGH_RES_MODE, 0x23, &Wire)) {
        Serial.println("BH1750 tidak terdeteksi.");
    }

    soilTemperatureSensor.begin();
    connectWifi();
    readAndSendSensors();
    lastSendAt = millis();
}

void loop()
{
    if (millis() - lastSendAt >= SEND_INTERVAL_MS) {
        readAndSendSensors();
        lastSendAt = millis();
    }

    delay(100);
}
