// ===============================================
// PROGRAM ABSENSI RFID ONLINE & OFFLINE (WEMOS D1 MINI)
// Revisi 8: Fix Memory Leak SD Card & Tambah OTA Update
// ===============================================

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <SD.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <DNSServer.h>
#include <RTClib.h>
#include <WiFiUdp.h>
#include <NTPClient.h>
#include <WiFiClientSecure.h>
#include <ESP8266httpUpdate.h> // 🌟 Library OTA Update

// =============================
// 🔹 PIN KONFIGURASI
// =============================
#define SS_RFID D5    
#define RST_RFID D4   
#define SS_SD D0      
#define SDA_PIN D2    
#define SCL_PIN D1    
#define BUZZER_PIN D3 

// =============================
// 🔹 KONFIGURASI ACCESS POINT
// =============================
const char *apSSID = "Konfigurasi_Absen_003";
const char *apPASS = NULL;

// =============================
// 🔹 OBJEK
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
NTPClient timeClient(ntpUDP, "id.pool.ntp.org", 25200, 60000);
unsigned long lastSyncTime = 0;
const unsigned long SYNC_INTERVAL = 3600000;
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
// 🔹 WIFI & SERVER
// =============================
String ssid = "";
String password = "";
String serverUrl_DomainOnly = "";
String serverUrl_Full = "";
String apiKey = "";
String deviceName = "";
String uid = "";

// 🌟 UID KHUSUS
const String UID_RESET = "4A6DC6"; // Kartu untuk Format Sistem
const String UID_UPDATE = "A1B2C3D4"; // Kartu untuk Trigger OTA Update (GANTI SESUAI KARTUMU)

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
const String TELEGRAM_BOT_TOKEN = "8462176461:AAH8yC5qTlJ9DeX0lHe88OFUEEBXx1C6mm4";
const String TELEGRAM_CHAT_ID = "7631557592";

// =============================
// 🔹 FUNGSI DISPLAY
// =============================
void displayReady() {
  if (apModeActive) return;
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Ready to Scan   ");
  updateStatusDisplay();
  returningToReady = false;
}

void displayDebug(String line1, String line2 = "", int displayTime = 3000, bool autoReturn = true) {
  lcd.clear();
  if (line1.length() > 16) line1 = line1.substring(0, 16);
  lcd.setCursor(0, 0); lcd.print(line1);

  if (line2 != "") {
    if (line2.length() > 16) line2 = line2.substring(0, 16);
    lcd.setCursor(0, 1); lcd.print(line2);
  }
  Serial.println("[DISPLAY] " + line1 + " | " + line2);

  if (!apModeActive && autoReturn && displayTime > 0) {
    returningToReady = true;
    readyReturnTime = millis() + displayTime;
  }
}

void displayError(String errorMsg) {
  lastError = errorMsg;
  displayDebug("ERROR:", errorMsg, 3000);
}

void beep() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(150);
  digitalWrite(BUZZER_PIN, LOW);
}

// =============================
// 🔹 FUNGSI SPI SELECT (RFID & SD berbagi bus SPI)
// =============================
void selectRFID() {
  digitalWrite(SS_SD, HIGH);   // SD nonaktif
  digitalWrite(SS_RFID, LOW);  // RFID aktif
}

void selectSD() {
  digitalWrite(SS_RFID, HIGH); // RFID nonaktif
  digitalWrite(SS_SD, LOW);    // SD aktif
}

void deselectAll() {
  digitalWrite(SS_RFID, HIGH);
  digitalWrite(SS_SD, HIGH);
}

// =============================
// 🔹 FUNGSI UPDATE BARIS STATUS LCD (baris ke-2)
// =============================
void updateStatusDisplay() {
  lcd.setCursor(0, 1);
  String line2;
  if (apModeActive) {
    line2 = "AP Mode Active";
  } else if (!wifiConnected) {
    line2 = "WiFi: Disconnect";
  } else {
    line2 = internetAvailable ? "WiFi OK  Net OK" : "WiFi OK  Net X";
  }
  if (line2.length() > 16) line2 = line2.substring(0, 16);
  while (line2.length() < 16) line2 += " "; // bersihkan sisa karakter lama
  lcd.print(line2);
}

// =============================
// 🔹 FUNGSI WAKTU & INTERNET
// =============================
bool checkInternetConnection() {
  if (WiFi.status() != WL_CONNECTED) {
    internetAvailable = false; return false;
  }
  WiFiClient pingClient;
  pingClient.setTimeout(3000);
  internetAvailable = pingClient.connect("8.8.8.8", 80);
  if(internetAvailable) pingClient.stop();
  return internetAvailable;
}

String getCurrentDate() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char dateStr[11]; sprintf(dateStr, "%04d-%02d-%02d", now.year(), now.month(), now.day());
    return String(dateStr);
  }
  return "1970-01-01";
}

String getCurrentTime() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char timeStr[9]; sprintf(timeStr, "%02d:%02d:%02d", now.hour(), now.minute(), now.second());
    return String(timeStr);
  }
  return "00:00:00";
}

void syncRTCwithNTP() {
  if (!internetAvailable) return;
  timeClient.begin();
  if (timeClient.update() && rtcAvailable) {
    rtc.adjust(DateTime(timeClient.getEpochTime()));
    ntpSynced = true; lastSyncTime = millis();
    Serial.println("✅ RTC synced with NTP");
  }
  timeClient.end();
}

// =========================
// ⚙️ FUNGSI EEPROM
// =========================
void saveToEEPROM(int addr, const String &data) {
  for (unsigned int i = 0; i < data.length(); i++) EEPROM.write(addr + i, data[i]);
  EEPROM.write(addr + data.length(), '\0'); EEPROM.commit();
}

String readFromEEPROM(int addr) {
  String data = ""; char ch;
  for (int i = addr; i < addr + 100; i++) {
    ch = EEPROM.read(i); if (ch == '\0') break; data += ch;
  }
  return data;
}

// =========================
// 🌟 FUNGSI OTA UPDATE (NEW)
// =========================
void performOTAUpdate() {
  if (WiFi.status() != WL_CONNECTED || !internetAvailable || serverUrl_DomainOnly == "") {
    displayError("OTA: No Internet");
    return;
  }
  
  displayDebug("System Update", "Downloading...", 0, false);
  Serial.println("🔄 Memulai OTA Update...");
  
  // Asumsi URL firmware berada di servermu
  String fwUrl = "http://" + serverUrl_DomainOnly + "/api/rfid/firmware.bin";
  
  ESPhttpUpdate.rebootOnUpdate(true); // Auto restart jika sukses
  t_httpUpdate_return ret = ESPhttpUpdate.update(client, fwUrl);
  
  switch (ret) {
    case HTTP_UPDATE_FAILED:
      Serial.printf("❌ OTA Failed Error (%d): %s\n", ESPhttpUpdate.getLastError(), ESPhttpUpdate.getLastErrorString().c_str());
      displayError("Update Failed");
      break;
    case HTTP_UPDATE_NO_UPDATES:
      displayDebug("No Update Found", "System is up to date", 3000);
      break;
    case HTTP_UPDATE_OK:
      Serial.println("✅ Update OK!"); // (Tidak akan tereksekusi karena Wemos keburu restart)
      break;
  }
}

// Fungsi Trigger OTA via Web
void handleOTA() {
  server.send(200, "text/html", "<h3>Memulai Update dari Server... Perhatikan LCD Alat!</h3>");
  delay(1000);
  performOTAUpdate();
}

// =========================
// 💾 FUNGSI OFFLINE DATA (FIXED MEMORY LEAK)
// =========================
void saveOfflineData(const String &data) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, data)) return;
  doc["tanggal"] = getCurrentDate();
  doc["jam"] = getCurrentTime();
  String offlineData; serializeJson(doc, offlineData);

  selectSD();
  File file = SD.open("/offline.txt", FILE_WRITE);
  if (file) {
    file.seek(file.size());
    file.println(offlineData);
    file.close();
    Serial.println("💾 Data saved offline");
  }
  deselectAll();
}

void removeFromOfflineData(String uidToRemove) {
  selectSD();
  File file = SD.open("/offline.txt", FILE_READ);
  if (!file) { deselectAll(); return; }

  // 🌟 PERBAIKAN: Gunakan Temp File agar RAM tidak jebol
  File tempFile = SD.open("/temp.txt", FILE_WRITE);
  if (!tempFile) { file.close(); deselectAll(); return; }

  while (file.available()) {
    String line = file.readStringUntil('\n');
    line.trim();
    if (line.length() > 0) {
      StaticJsonDocument<256> doc;
      if (!deserializeJson(doc, line)) {
        String uidData = doc["uid"].as<String>();
        // Jika UID tidak cocok dengan yang mau dihapus, salin ke temp
        if (uidData != uidToRemove) {
          tempFile.println(line);
        }
      }
    }
  }

  file.close();
  tempFile.close();

  // Hapus yang lama, ganti nama yang baru
  SD.remove("/offline.txt");
  SD.rename("/temp.txt", "/offline.txt");
  deselectAll();
}

void sendOfflineData() {
  if (WiFi.status() != WL_CONNECTED || !internetAvailable) return;

  selectSD();
  File file = SD.open("/offline.txt", FILE_READ);
  if (!file || file.size() == 0) {
    if (file) file.close();
    deselectAll(); return;
  }

  displayDebug("Syncing Offline", "Data...", 0, false);
  int successCount = 0;
  
  // Buat file temp untuk menampung data yang GAGAL terkirim
  File tempFile = SD.open("/temp.txt", FILE_WRITE);

  while (file.available()) {
    String line = file.readStringUntil('\n'); line.trim();
    if (line.length() > 0) {
      StaticJsonDocument<256> doc;
      if (!deserializeJson(doc, line)) {
        String jsonToSend = "{\"api_key\":\"" + doc["api_key"].as<String>() + "\",\"uid\":\"" + doc["uid"].as<String>() + "\"}";
        
        if (sendData(jsonToSend)) {
          successCount++; delay(300);
        } else {
          // Jika gagal kirim, simpan kembali ke tempFile
          if (tempFile) tempFile.println(line);
        }
      }
    }
  }

  file.close();
  if (tempFile) tempFile.close();

  SD.remove("/offline.txt");
  SD.rename("/temp.txt", "/offline.txt");
  deselectAll();

  if (successCount > 0) {
    displayDebug("Sync Complete", String(successCount) + " sent", 1500);
  }
}

// =============================
// 🚀 KIRIM DATA KE SERVER (DIPERSINGKAT)
// =============================
bool sendData(String jsonData) {
  if (WiFi.status() != WL_CONNECTED || !internetAvailable) return false;

  HTTPClient http;
  if (!http.begin(client, serverUrl_Full)) return false;

  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  http.setTimeout(5000);

  int httpCode = http.POST(jsonData);
  if (httpCode == 200) {
    String response = http.getString();
    StaticJsonDocument<256> doc;
    if (!deserializeJson(doc, response)) {
      const char *status = doc["status"]; const char *nama = doc["nama"];
      displayDebug(nama ? String(nama) : "Berhasil", status ? String(status) : "OK", 2000);
      beep();
    }
    http.end(); return true;
  }
  
  http.end(); return false;
}

// =========================
// 🌐 WEBSERVER & AP MODE
// =========================
// (Fungsi handleRoot dan handleSave tetap sama persis seperti buatanmu, saya singkat di sini agar rapi)
void handleRoot() { /* Gunakan HTML kamu sebelumnya */ }
void handleSave() { /* Gunakan logika Save kamu sebelumnya */ }

void startAPConfig() {
  apModeActive = true; WiFi.mode(WIFI_AP); delay(1000);
  WiFi.softAP(apSSID, apPASS);
  dnsServer.start(53, "*", WiFi.softAPIP());
  server.on("/", handleRoot);
  server.on("/save", HTTP_POST, handleSave);
  server.begin();
  displayDebug("AP Mode Active", WiFi.softAPIP().toString(), 0, false);
}

// =============================
// ⚙️ SETUP
// =============================
void setup() {
  Serial.begin(115200);
  EEPROM.begin(EEPROM_SIZE);
  pinMode(BUZZER_PIN, OUTPUT); digitalWrite(BUZZER_PIN, LOW);

  lcd.init(); lcd.backlight(); displayDebug("System Init...", "Please wait", 1500); delay(1500);

  Wire.begin(SDA_PIN, SCL_PIN);
  if (rtc.begin()) { rtcAvailable = true; if (rtc.lostPower()) rtc.adjust(DateTime(F(__DATE__), F(__TIME__))); }

  ssid = readFromEEPROM(ADDR_SSID); password = readFromEEPROM(ADDR_PASS);
  serverUrl_DomainOnly = readFromEEPROM(ADDR_URL); apiKey = readFromEEPROM(ADDR_API);
  deviceName = readFromEEPROM(ADDR_DEVICE);

  ssid.trim(); password.trim(); serverUrl_DomainOnly.trim(); apiKey.trim(); deviceName.trim();
  if (serverUrl_DomainOnly != "") serverUrl_Full = "http://" + serverUrl_DomainOnly + "/api/rfid/catat";

  SPI.begin(); pinMode(SS_RFID, OUTPUT); pinMode(SS_SD, OUTPUT); deselectAll();

  selectRFID(); rfid.PCD_Init(); deselectAll();
  selectSD(); SD.begin(SS_SD); deselectAll();

  if (ssid == "") {
    startAPConfig();
  } else {
    WiFi.mode(WIFI_STA); WiFi.begin(ssid.c_str(), password.c_str());
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) { delay(500); attempts++; }
    
    if (WiFi.status() != WL_CONNECTED) {
      startAPConfig();
    } else {
      wifiConnected = true; checkInternetConnection();
      if (internetAvailable) syncRTCwithNTP();
      
      // 🌟 Daftarkan Route Server Tambahan untuk WiFi STA (Lokal)
      server.on("/do_update", HTTP_GET, handleOTA);
      server.begin();
    }
  }

  if (!apModeActive) displayReady();
}

// =============================
// 🔁 LOOP
// =============================
void loop() {
  if (apModeActive) { dnsServer.processNextRequest(); server.handleClient(); return; }
  
  // Dengarkan route OTA lokal
  server.handleClient();

  if (returningToReady && millis() > readyReturnTime) displayReady();

  // Sinkronisasi SD Offline secara berkala
  static unsigned long lastCheck = 0;
  if (millis() - lastCheck > 60000) { 
    checkInternetConnection();
    if (internetAvailable) sendOfflineData();
    lastCheck = millis();
  }

  selectRFID();
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) { deselectAll(); return; }

  uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) uid += String(rfid.uid.uidByte[i], HEX);
  uid.toUpperCase(); deselectAll();

  // 🌟 LOGIKA KARTU KHUSUS
  if (uid == UID_RESET) {
    displayDebug("RESET SYSTEM!", "Formatting...", 2000);
    for (int i = 0; i < EEPROM_SIZE; i++) EEPROM.write(i, 0); EEPROM.commit();
    selectSD(); SD.remove("/offline.txt"); deselectAll();
    ESP.restart();
  } 
  else if (uid == UID_UPDATE) {
    performOTAUpdate(); // Trigger OTA via Kartu RFID
    return;
  }

  displayDebug("Card Detected", "Processing...", 1500);
  String jsonData = "{\"api_key\":\"" + apiKey + "\",\"uid\":\"" + uid + "\"}";
  saveOfflineData(jsonData);

  if (WiFi.status() == WL_CONNECTED && internetAvailable) {
    if (sendData(jsonData)) removeFromOfflineData(uid);
  } else {
    displayDebug("Offline Mode", "Data Saved", 2000); beep();
  }
}