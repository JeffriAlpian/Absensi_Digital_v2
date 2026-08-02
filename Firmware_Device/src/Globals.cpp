#include "../include/Globals.h"
#include "../include/Config.h"

// =============================
// 🔹 OBJEK HARDWARE & JARINGAN
// =============================
MFRC522 rfid(SS_RFID, RST_RFID);
LiquidCrystal_I2C lcd(0x27, 16, 2);
ESP8266WebServer server(80);
DNSServer dnsServer;
RTC_DS3231 rtc;
WiFiClient client;
WiFiClientSecure clientSecure;

// =============================
// 🔹 NTP CLIENT
// =============================
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, NTP_SERVER, NTP_OFFSET, NTP_UPDATE_INTERVAL);
unsigned long lastSyncTime = 0;
bool ntpSynced = false;

// =============================
// 🔹 STATUS VARIABEL
// =============================
bool apModeActive = false;
bool rtcAvailable = false;
bool wifiConnected = false;
bool internetAvailable = false;
String lastError = "";
String lastStatus = "System Ready";
bool returningToReady = false;
unsigned long readyReturnTime = 0;

// =============================
// 🔹 WIFI & SERVER KONFIGURASI
// =============================
String ssid = "";
String password = "";
String serverUrl_DomainOnly = "";
String serverUrl_Full = "";
String apiKey = "";
String deviceName = "";
String uid = "";
