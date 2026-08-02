#ifndef GLOBALS_H
#define GLOBALS_H

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <SD.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <DNSServer.h>
#include <RTClib.h>
#include <WiFiUdp.h>
#include <NTPClient.h>
#include <WiFiClientSecure.h>

// =============================
// 🔹 OBJEK HARDWARE & JARINGAN
// =============================
extern MFRC522 rfid;
extern LiquidCrystal_I2C lcd;
extern ESP8266WebServer server;
extern DNSServer dnsServer;
extern RTC_DS3231 rtc;
extern WiFiClient client;
extern WiFiClientSecure clientSecure;

// =============================
// 🔹 NTP CLIENT
// =============================
extern WiFiUDP ntpUDP;
extern NTPClient timeClient;
extern unsigned long lastSyncTime;
extern bool ntpSynced;

// =============================
// 🔹 STATUS VARIABEL
// =============================
extern bool apModeActive;
extern bool rtcAvailable;
extern bool wifiConnected;
extern bool internetAvailable;
extern String lastError;
extern String lastStatus;
extern bool returningToReady;
extern unsigned long readyReturnTime;

// =============================
// 🔹 WIFI & SERVER KONFIGURASI (dimuat dari EEPROM)
// =============================
extern String ssid;
extern String password;
extern String serverUrl_DomainOnly;
extern String serverUrl_Full;
extern String apiKey;
extern String deviceName;
extern String uid;

#endif  // GLOBALS_H
