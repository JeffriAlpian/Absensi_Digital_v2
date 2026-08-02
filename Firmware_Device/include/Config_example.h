#ifndef CONFIG_H
#define CONFIG_H

#include <Arduino.h>

// =============================
// 🔹 PIN KONFIGURASI
// =============================
#define SS_RFID D4
#define RST_RFID D3
// #define SS_SD D8
#define SDA_PIN D2
#define SCL_PIN D1
#define BUZZER_PIN D0

// =============================
// 🔹 KONFIGURASI ACCESS POINT
// =============================
extern const char *apSSID;
extern const char *apPASS;

// =============================
// 🔹 KARTU RESET
// =============================
extern const String UID_RESET;

// =========================
// 📦 EEPROM Layout
// =========================
#define EEPROM_SIZE 512
#define ADDR_SSID 0
#define ADDR_PASS 100
#define ADDR_URL 200
#define ADDR_API 400
#define ADDR_DEVICE 300

// =============================
// 🔹 KONFIGURASI TELEGRAM BOT
// =============================
extern const String TELEGRAM_BOT_TOKEN;
extern const String TELEGRAM_CHAT_ID;

// =============================
// 🔹 KONFIGURASI NTP
// =============================
#define NTP_SERVER "id.pool.ntp.org"
#define NTP_OFFSET 25200
#define NTP_UPDATE_INTERVAL 60000
#define SYNC_INTERVAL 3600000UL

#endif  // CONFIG_H
