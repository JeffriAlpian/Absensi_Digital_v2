// ===============================================
// PROGRAM ABSENSI RFID ONLINE & OFFLINE (WEMOS D1 MINI)
// Revisi 7: Perbaikan Logika Mode AP & WiFi
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

// =============================
// 🔹 PIN KONFIGURASI
// =============================
#define SS_RFID D8    // Pin SDA/CS RFID pindah ke D8 (Wajib tambah resistor 10k dari D8 ke GND di hardware)
#define RST_RFID D4   // Pin RST RFID pindah ke D4 (Aman)
#define SS_SD D0      // Pin CS SD Card pindah ke D0 (Aman, tidak mengganggu booting)
#define SDA_PIN D2    // Tetap (I2C)
#define SCL_PIN D1    // Tetap (I2C)
#define BUZZER_PIN D3 // Pin Buzzer pindah ke D3 (Buzzer akan bunyi "tit" pelan saat baru nyala, ini normal)

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
const String UID_RESET = "4A6DC6";

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
WiFiClientSecure clientSecure;

// =============================
// 🔹 FUNGSI DISPLAY YANG DIPERBAIKI
// =============================
void displayReady() {
  if (apModeActive) return;

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Ready to Scan   ");
  updateStatusDisplay();
  returningToReady = false;
  Serial.println("[DISPLAY] Ready to Scan");
}

void displayDebug(String line1, String line2 = "", int displayTime = 3000, bool autoReturn = true) {
  lcd.clear();
  if (line1.length() > 16) line1 = line1.substring(0, 16);
  lcd.setCursor(0, 0);
  lcd.print(line1);

  if (line2 != "") {
    if (line2.length() > 16) line2 = line2.substring(0, 16);
    lcd.setCursor(0, 1);
    lcd.print(line2);
  }

  Serial.println("[DEBUG] " + line1 + " | " + line2);

  // Set timer untuk kembali ke Ready to Scan
  if (!apModeActive && autoReturn && displayTime > 0) {
    returningToReady = true;
    readyReturnTime = millis() + displayTime;
  }
}

void displayError(String errorMsg) {
  lastError = errorMsg;
  displayDebug("ERROR:", errorMsg, 3000);
  Serial.println("[ERROR] " + errorMsg);
}

// =============================
// 🔹 FUNGSI DETEKSI INTERNET - PERBAIKAN
// =============================
bool checkInternetConnection() {
  if (WiFi.status() != WL_CONNECTED) {
    internetAvailable = false;
    Serial.println("❌ WiFi tidak terhubung");
    return false;
  }

  Serial.println("🔍 Mengecek koneksi internet...");
  displayDebug("Checking", "Internet...");

  // Coba 1: Ping ke Google DNS (8.8.8.8)
  bool pingSuccess = false;
  unsigned long pingStart = millis();

  // Simulasi ping sederhana dengan koneksi TCP ke port 80
  WiFiClient pingClient;
  pingClient.setTimeout(3000);  // Timeout 3 detik

  if (pingClient.connect("8.8.8.8", 80)) {
    pingClient.stop();
    pingSuccess = true;
    Serial.println("✅ Ping ke 8.8.8.8 berhasil");
  } else {
    Serial.println("❌ Ping ke 8.8.8.8 gagal");
  }

  // Coba 2: Akses ke server NTP
  bool ntpSuccess = false;
  WiFiUDP udp;

  if (udp.begin(123)) {  // Port NTP
    unsigned long ntpStart = millis();
    while (millis() - ntpStart < 2000) {
      if (udp.parsePacket()) {
        ntpSuccess = true;
        break;
      }
      delay(10);
    }
    udp.stop();

    if (ntpSuccess) {
      Serial.println("✅ Koneksi NTP berhasil");
    } else {
      Serial.println("❌ Koneksi NTP gagal");
    }
  }

  // Coba 3: Akses ke server absensi (jika ada konfigurasi)
  bool serverSuccess = false;
  if (serverUrl_DomainOnly != "") {
    HTTPClient http;
    http.setTimeout(3000);

    String testUrl = "http://" + serverUrl_DomainOnly + "/ping";  // Endpoint ping sederhana

    if (http.begin(client, testUrl)) {
      int httpCode = http.GET();
      http.end();

      if (httpCode == 200 || httpCode == 404 || httpCode == 403) {
        // Status code apapun kecuali timeout menunjukkan server merespons
        serverSuccess = true;
        Serial.println("✅ Server absensi merespons");
      } else {
        Serial.println("❌ Server absensi tidak merespons");
      }
    }
  } else {
    // Jika tidak ada server, anggap server test success
    serverSuccess = true;
  }

  // Internet dianggap tersedia jika minimal 1 dari 3 test berhasil
  internetAvailable = (pingSuccess || ntpSuccess || serverSuccess);

  if (internetAvailable) {
    Serial.println("✅ Internet tersedia");
  } else {
    Serial.println("❌ Internet tidak tersedia");
  }

  return internetAvailable;
}

// =========================
// ⚙️ FUNGSI SIMPAN/BACA EEPROM
// =========================
void saveToEEPROM(int addr, const String &data) {
  for (unsigned int i = 0; i < data.length(); i++) {
    EEPROM.write(addr + i, data[i]);
  }
  EEPROM.write(addr + data.length(), '\0');
  EEPROM.commit();
}

String readFromEEPROM(int addr) {
  String data = "";
  char ch;
  for (int i = addr; i < addr + 100; i++) {
    ch = EEPROM.read(i);
    if (ch == '\0') break;
    data += ch;
  }
  return data;
}

// =========================
// 📅 FUNGSI WAKTU RTC
// =========================
String getCurrentDate() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char dateStr[11];
    sprintf(dateStr, "%04d-%02d-%02d", now.year(), now.month(), now.day());
    return String(dateStr);
  }
  return "1970-01-01";
}

String getCurrentTime() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    uint8_t hour = now.hour();
    if (hour >= 24) hour -= 24;
    char timeStr[9];
    sprintf(timeStr, "%02d:%02d:%02d", hour, now.minute(), now.second());
    return String(timeStr);
  }
  return "00:00:00";
}

// =========================
// 🔄 FUNGSI SINKRONISASI NTP
// =========================
void syncRTCwithNTP() {
  if (!internetAvailable) {
    Serial.println("Internet tidak tersedia, skip NTP sync");
    return;
  }

  displayDebug("Sync NTP...", "Tunggu", 2000);
  Serial.println("🔄 Starting NTP sync...");

  timeClient.begin();
  unsigned long startTime = millis();
  bool updated = false;

  while (millis() - startTime < 3000) {
    if (timeClient.update()) {
      updated = true;
      break;
    }
    delay(100);
  }

  if (updated) {
    unsigned long epochTime = timeClient.getEpochTime();
    DateTime ntpTime(epochTime);

    if (rtcAvailable) {
      rtc.adjust(ntpTime);
      ntpSynced = true;
      lastSyncTime = millis();
      Serial.println("✅ RTC synchronized with NTP");
    }
  } else {
    Serial.println("❌ NTP sync failed");
  }

  timeClient.end();
}

// =========================
// 📱 FUNGSI TELEGRAM
// =========================
void sendTelegramMessage(String message) {
  if (!internetAvailable) {
    Serial.println("Internet tidak tersedia, tidak bisa kirim Telegram");
    return;
  }

  HTTPClient https;
  String url = "https://api.telegram.org/bot" + TELEGRAM_BOT_TOKEN + "/sendMessage";

  clientSecure.setInsecure();
  clientSecure.setTimeout(5000);

  if (!https.begin(clientSecure, url)) {
    Serial.println("Gagal konek ke Telegram");
    return;
  }

  https.addHeader("Content-Type", "application/json");

  String jsonPayload = "{";
  jsonPayload += "\"chat_id\":\"" + TELEGRAM_CHAT_ID + "\",";
  jsonPayload += "\"text\":\"" + message + "\",";
  jsonPayload += "\"parse_mode\":\"HTML\",";
  jsonPayload += "\"disable_notification\":false";
  jsonPayload += "}";

  int httpCode = https.POST(jsonPayload);
  if (httpCode > 0) {
    if (httpCode == 200) {
      Serial.println("✅ Telegram terkirim");
    } else {
      Serial.println("Telegram error: " + String(httpCode));
    }
  }
  https.end();
}

void sendAttendanceNotification(String nama, String status, String uid) {
  if (!internetAvailable) return;

  String message = "✅ <b>ABSENSI BERHASIL</b>\n\n";
  message += "👤 <b>Nama:</b> " + nama + "\n";
  message += "📋 <b>Status:</b> " + status + "\n";
  message += "🆔 <b>UID:</b> " + uid + "\n";
  message += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  message += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  message += "🔧 <b>Device:</b> " + deviceName;
  sendTelegramMessage(message);
}

void sendOfflineSyncNotification(int count) {
  if (!internetAvailable) return;

  String message = "💾 <b>DATA OFFLINE DISINKRONKASIKAN</b> 💾\n\n";
  message += "📦 <b>Jumlah Data:</b> " + String(count) + " record\n";
  message += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  message += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  message += "🔧 <b>Device:</b> " + deviceName + "\n\n";
  message += "✅ <i>Data offline berhasil dikirim ke server</i>";
  sendTelegramMessage(message);
}

void sendErrorLog(String errorMsg, String uid) {
  if (!internetAvailable) return;

  String telegramMessage = "🚨 <b>ERROR REPORT</b>\n\n";
  telegramMessage += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  telegramMessage += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  telegramMessage += "🆔 <b>UID:</b> " + uid + "\n";
  telegramMessage += "🔧 <b>Device:</b> " + deviceName + "\n";
  telegramMessage += "📶 <b>Internet:</b> " + String(internetAvailable ? "Online" : "Offline") + "\n\n";
  telegramMessage += "❌ <b>ERROR:</b>\n<code>" + errorMsg + "</code>";
  sendTelegramMessage(telegramMessage);
}

// =========================
// 🌐 HALAMAN WEB KONFIGURASI
// =========================
void handleRoot() {
  String html = F(R"(
<!DOCTYPE html>
<html>
<head>
 <title>Konfigurasi Absensi</title>
 <meta name='viewport' content='width=device-width, initial-scale=1'>
 <style>
  body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
  .container { background-color: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); max-width: 450px; margin: auto; }
  h2 { text-align: center; color: #333; margin-bottom: 25px; }
  label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; }
  input[type='text'], input[type='password'] { width: 100%; padding: 12px; margin-bottom: 18px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
  input[type='submit'] { width: 100%; background-color: #007bff; color: white; padding: 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 18px; }
  input[type='submit']:hover { background-color: #0056b3; }
  .status { padding: 10px; margin-bottom: 20px; border-radius: 6px; background-color: #e7f3ff; border-left: 4px solid #007bff; }
 </style>
</head>
<body>
 <div class='container'>
  <h2>Konfigurasi Absensi RFID</h2>
  
  <div class='status'>
    <strong>Status Perangkat:</strong><br>
    WiFi: )");

  html += (WiFi.status() == WL_CONNECTED) ? "Terhubung" : "Tidak Terhubung";
  html += F(R"(<br>RTC: )");
  html += rtcAvailable ? "OK" : "Tidak Ditemukan";
  html += F(R"(<br>Internet: )");
  html += internetAvailable ? "Tersedia" : "Tidak Tersedia";
  html += F(R"(<br>Waktu: )");
  html += getCurrentDate() + " " + getCurrentTime();

  html += F(R"(
  </div>
  
  <form action='/save' method='POST'>
   <label for='ssid'>SSID WiFi:</label>
   <input type='text' id='ssid' name='ssid' value=')");
  html += ssid;

  html += F(R"('>
   <label for='password'>Password WiFi:</label>
   <input type='password' id='password' name='password' value=')");
  html += password;

  html += F(R"('>
   <label for='url'>Server Domain:</label>
   <input type='text' id='url' name='url' value=')");
  html += serverUrl_DomainOnly;

  html += F(R"('>
   <label for='api'>API Key:</label>
   <input type='text' id='api' name='api' value=')");
  html += apiKey;

  html += F(R"('>
   <label for='device'>Nama Device:</label>
   <input type='text' id='device' name='device' value=')");
  html += deviceName;

  html += F(R"('>
   <input type='submit' value='Simpan & Restart'>
  </form>
 </div>
</body>
</html>)");

  server.send(200, "text/html", html);
}

void handleSave() {
  ssid = server.arg("ssid");
  password = server.arg("password");
  String domainInput = server.arg("url");
  apiKey = server.arg("api");
  deviceName = server.arg("device");

  domainInput.replace("https://", "");
  domainInput.replace("http://", "");
  domainInput.trim();
  while (domainInput.endsWith("/")) {
    domainInput = domainInput.substring(0, domainInput.length() - 1);
  }

  saveToEEPROM(ADDR_SSID, ssid);
  saveToEEPROM(ADDR_PASS, password);
  saveToEEPROM(ADDR_URL, domainInput);
  saveToEEPROM(ADDR_API, apiKey);
  saveToEEPROM(ADDR_DEVICE, deviceName);

  serverUrl_DomainOnly = domainInput;
  serverUrl_Full = "http://" + domainInput + "/api/rfid/catat";

  String html = R"(
<!DOCTYPE html>
<html>
<head>
  <title>Tersimpan!</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <meta http-equiv='refresh' content='3;url=http://192.168.4.1'>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f0f2f5; }
    .message { background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); max-width: 450px; margin: 50px auto; }
    h3 { color: #28a745; text-align: center; }
    p { text-align: center; }
  </style>
</head>
<body>
  <div class='message'>
    <h3>✅ Data Tersimpan!</h3>
    <p>Perangkat akan restart dalam 3 detik...</p>
  </div>
</body>
</html>)";

  server.send(200, "text/html", html);
  delay(2000);
  ESP.restart();
}

// =======================
// Masuk Mode AP
// =======================
void startAPConfig() {
  Serial.println("🔧 Mode konfigurasi aktif!");
  displayDebug("AP Mode Active", "Config via WiFi", 0, false);
  apModeActive = true;
  wifiConnected = false;
  internetAvailable = false;

  WiFi.mode(WIFI_AP);
  delay(1000);

  WiFi.softAP(apSSID, apPASS);
  IPAddress IP = WiFi.softAPIP();
  Serial.print("Akses: http://");
  Serial.println(IP);
  displayDebug("AP Mode", IP.toString(), 0, false);

  dnsServer.start(53, "*", IP);
  server.on("/", handleRoot);
  server.on("/save", HTTP_POST, handleSave);
  server.onNotFound(handleRoot);
  server.begin();
}

// =============================
// ⚙️ SETUP - LOGIKA DIPERBAIKI
// =============================
void setup() {
  Serial.begin(115200);
  EEPROM.begin(EEPROM_SIZE);

  // Inisialisasi pin
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi LCD
  lcd.init();
  lcd.backlight();
  displayDebug("System Init...", "Please wait", 1500);
  delay(1500);

  // Inisialisasi RTC
  Wire.begin(SDA_PIN, SCL_PIN);
  if (rtc.begin()) {
    rtcAvailable = true;
    Serial.println("✅ RTC Found");

    if (rtc.lostPower()) {
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
  } else {
    Serial.println("❌ RTC Not Found!");
    rtcAvailable = false;
  }

  // Load konfigurasi dari EEPROM
  ssid = readFromEEPROM(ADDR_SSID);
  password = readFromEEPROM(ADDR_PASS);
  serverUrl_DomainOnly = readFromEEPROM(ADDR_URL);
  apiKey = readFromEEPROM(ADDR_API);
  deviceName = readFromEEPROM(ADDR_DEVICE);

  // Optimasi WiFi
  WiFi.setAutoReconnect(true);
  WiFi.setSleepMode(WIFI_NONE_SLEEP);

  // Bersihkan dan validasi konfigurasi
  ssid.trim();
  password.trim();
  serverUrl_DomainOnly.trim();
  apiKey.trim();
  deviceName.trim();

  if (deviceName == "") deviceName = "RFID_Absensi";
  if (serverUrl_DomainOnly != "") {
    serverUrl_Full = "http://" + serverUrl_DomainOnly + "/api/rfid/catat";
  }

  Serial.println("SSID: " + ssid);
  Serial.println("Device: " + deviceName);
  Serial.println("URL Server: " + serverUrl_Full);

  // Inisialisasi Hardware SPI
  SPI.begin();
  pinMode(SS_RFID, OUTPUT);
  pinMode(SS_SD, OUTPUT);
  deselectAll();

  // RFID Init
  selectRFID();
  rfid.PCD_Init();
  deselectAll();

  // SD Card Init
  selectSD();
  if (!SD.begin(SS_SD)) {
    Serial.println("❌ SD Card Error!");
    displayDebug("SD Card Error", "Check Card", 2000);
  } else {
    Serial.println("💾 SD Card Ready");
  }
  deselectAll();

  // ============================================
  // 🔧 LOGIKA KONEKSI WIFI YANG DIPERBAIKI 🔧
  // ============================================

  if (ssid == "") {
    // Jika SSID kosong, langsung mode AP
    Serial.println("SSID kosong, masuk mode AP");
    startAPConfig();
  } else {
    // Coba koneksi WiFi
    connectWiFi();

    // PERBAIKAN: Jika WiFi gagal, MASUK MODE AP
    if (!wifiConnected) {
      Serial.println("❌ WiFi gagal, masuk mode AP");
      displayDebug("WiFi Failed", "Entering AP Mode", 2000);
      delay(2000);
      startAPConfig();  // MASUK MODE AP JIKA WIFI GAGAL
    } else {
      // Cek koneksi internet setelah WiFi terhubung
      checkInternetConnection();
      if (internetAvailable) {
        syncRTCwithNTP();
      }
    }
  }



  // JIKA TIDAK DI MODE AP, tampilkan Ready to Scan
  if (!apModeActive) {
    displayReady();
    Serial.println("✅ System ready, waiting for RFID card...");
  }
}

// =============================
// 🔁 LOOP
// =============================
void loop() {
  // Handle web server jika di AP mode
  if (apModeActive) {
    server.handleClient();
    dnsServer.processNextRequest();
    return;
  }

  // Cek jika perlu kembali ke Ready to Scan
  if (returningToReady && millis() > readyReturnTime) {
    displayReady();
  }

  // Cek koneksi WiFi setiap 30 detik
  static unsigned long lastCheck = 0;
  if (millis() - lastCheck > 100000) {
    checkWiFiAndInternet();
    lastCheck = millis();
  }

  // Auto-sync RTC setiap 1 jam jika internet tersedia
  if (internetAvailable && millis() - lastSyncTime > SYNC_INTERVAL) {
    syncRTCwithNTP();
  }

  // Update status display setiap 3 detik
  static unsigned long statusUpdate = 0;
  if (millis() - statusUpdate > 100000) {
    updateStatusDisplay();
    statusUpdate = millis();
  }

  // Baca kartu RFID
  selectRFID();
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    deselectAll();
    delay(50);
    return;
  }

  // Baca UID kartu
  uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  deselectAll();

  // Logika kartu reset
  if (uid == UID_RESET) {
    resetSystemEEPROM();
    return;
  }

  Serial.println("🎫 UID Kartu: " + uid);
  displayDebug("Card Detected", "Processing...", 1500);

  // Buat JSON untuk dikirim
  String jsonData = "{\"api_key\":\"" + apiKey + "\",\"uid\":\"" + uid + "\"}";

  // LOGIKA: Selalu simpan data offline dulu
  saveOfflineData(jsonData);

  // Coba kirim ke server jika WiFi tersedia DAN internet tersedia
  if (WiFi.status() == WL_CONNECTED && internetAvailable) {
    if (sendData(jsonData)) {
      // Jika berhasil, hapus dari data offline
      removeFromOfflineData(uid);
    } else {
      // Jika gagal kirim, cek ulang koneksi internet
      internetAvailable = false;
      displayDebug("Send Failed", "Saved Offline", 2000);
      beep();
    }
  } else {
    displayDebug("Offline Mode", "Data Saved", 2000);
    beep();
  }
}

// =============================
// 📶 FUNGSI WIFI & INTERNET - DIPERBAIKI
// =============================
void connectWiFi() {
  displayDebug("Connecting WiFi", ssid, 0, false);
  Serial.println("🔄 Connecting WiFi...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid.c_str(), password.c_str());

  unsigned long startAttempt = millis();
  int attempts = 0;

  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    apModeActive = false;

    displayDebug("WiFi Connected", WiFi.localIP().toString(), 1500);
    Serial.println("\n✅ WiFi Connected!");

    // Cek koneksi internet setelah WiFi terhubung
    checkInternetConnection();

    // Sinkronisasi data offline jika internet tersedia
    if (internetAvailable) {
      sendOfflineData();
    }
  } else {
    // PERBAIKAN: Set wifiConnected ke false
    wifiConnected = false;
    internetAvailable = false;
    Serial.println("\n❌ WiFi Connection Failed");
  }
}

void checkWiFiAndInternet() {
  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    internetAvailable = false;
    Serial.println("⚠️ WiFi disconnected");

    // Coba reconnect hanya jika SSID tidak kosong
    if (ssid != "") {
      Serial.println("Trying to reconnect...");
      WiFi.begin(ssid.c_str(), password.c_str());

      // Tunggu 5 detik untuk koneksi
      unsigned long start = millis();
      while (WiFi.status() != WL_CONNECTED && millis() - start < 5000) {
        delay(100);
      }

      if (WiFi.status() == WL_CONNECTED) {
        wifiConnected = true;
        displayDebug("WiFi Reconnected", "Checking...", 1500);
        Serial.println("✅ WiFi reconnected!");
        // Cek koneksi internet setelah reconnect
        checkInternetConnection();

        // Kirim data offline jika internet tersedia
        if (internetAvailable) {
          sendOfflineData();
        }
      } else {
        internetAvailable = false;
      }
    }
  } else {
    wifiConnected = true;
    // Cek koneksi internet secara periodic
    checkInternetConnection();
  }
}

// =============================
// 🔄 UPDATE STATUS DISPLAY
// =============================
void updateStatusDisplay() {
  if (apModeActive) return;

  // Hanya update baris kedua jika sedang di mode Ready
  lcd.setCursor(0, 1);
  lcd.print("                ");

  String currentTime = getCurrentTime();
  String timeHM = currentTime.substring(0, 5);
  if (WiFi.status() == WL_CONNECTED) {
    if (internetAvailable) {
      timeHM += " W+I";
    } else {
      timeHM += " W-O";
    }
  } else {
    timeHM += " OFF";
  }

  if (!rtcAvailable) {
    timeHM += " R";
  }

  lcd.setCursor(0, 1);
  if (timeHM.length() > 16) timeHM = timeHM.substring(0, 16);
  lcd.print(timeHM);
}

// =============================
// 🚀 KIRIM DATA KE SERVER
// =============================
bool sendData(String jsonData) {
  if (WiFi.status() != WL_CONNECTED || !internetAvailable) {
    return false;
  }

  HTTPClient http;
  Serial.print("[HTTP] Sending to: ");
  Serial.println(serverUrl_Full);

  if (!http.begin(client, serverUrl_Full)) {
    Serial.println("HTTP begin failed");
    return false;
  }

  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  http.setTimeout(5000);

  displayDebug("Sending Data", "Please wait...", 0, false);
  int httpCode = http.POST(jsonData);

  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("📡 Server Response (" + String(httpCode) + "): " + response);

    StaticJsonDocument<256> doc;
    if (deserializeJson(doc, response) == DeserializationError::Ok) {
      const char *status = doc["status"];
      const char *nama = doc["nama"];
      const char *msg = doc["message"];

      if (status && nama) {
        displayDebug(nama, String(status), 2000);

        if (String(status) == "masuk" || String(status) == "pulang") {
          // Kirim notifikasi Telegram
          if (internetAvailable && TELEGRAM_BOT_TOKEN != "" && TELEGRAM_CHAT_ID != "") {
            sendAttendanceNotification(nama, String(status), uid);
          }
          beep();
        } else if (String(status) == "error" || String(status) == "tidak_terdaftar") {
          displayError(msg ? String(msg) : "Unknown");
          if (internetAvailable) {
            sendErrorLog(msg ? String(msg) : "Server error", uid);
          }
          beep();
        } else if (String(status) == "sudah_masuk_pulang") {
          displayDebug(nama, "Sudah Msk Plng", 2000);
          beep();
        } else if (String(status) == "sudah_masuk") {
          displayDebug(nama, "Belum Pulang", 2000);
          beep();
        }
      } else if (String(status) == "tidak_terdaftar") {
        displayError("Tidak Terdaftar");
        beep();
      } else {
        displayDebug("Error:", "Lihat Log Tele", 2000);
        beep();
        if (internetAvailable) {
          sendErrorLog(msg ? String(msg) : "Server error", uid);
        }
      }
    }

    http.end();
    return (httpCode == 200);
  } else {
    Serial.println("HTTP failed: " + String(http.errorToString(httpCode).c_str()));
    http.end();

    return false;
  }
}

// =============================
// 💾 FUNGSI OFFLINE DATA
// =============================
void saveOfflineData(const String &data) {
  StaticJsonDocument<256> doc;
  DeserializationError error = deserializeJson(doc, data);

  if (error) {
    Serial.println("Failed to parse JSON for offline");
    return;
  }

  doc["tanggal"] = getCurrentDate();
  doc["jam"] = getCurrentTime();

  String offlineData;
  serializeJson(doc, offlineData);

  selectSD();
  File file = SD.open("/offline.txt", FILE_WRITE);
  if (!file) {
    Serial.println("Failed to open offline.txt");
    deselectAll();
    return;
  }

  file.seek(file.size());
  file.println(offlineData);
  file.close();

  Serial.println("💾 Data saved offline");
  deselectAll();
}

void sendOfflineData() {
  if (WiFi.status() != WL_CONNECTED || !internetAvailable) {
    Serial.println("WiFi or Internet offline, can't sync");
    return;
  }

  selectSD();
  File file = SD.open("/offline.txt", FILE_READ);
  if (!file || file.size() == 0) {
    if (file) file.close();
    deselectAll();
    return;
  }

  displayDebug("Syncing Offline", "Data...", 0, false);
  Serial.println("🔁 Syncing offline data...");

  String buffer = "";
  int successCount = 0;

  while (file.available()) {
    String line = file.readStringUntil('\n');
    line.trim();

    if (line.length() > 0) {
      StaticJsonDocument<256> doc;
      DeserializationError error = deserializeJson(doc, line);

      if (!error) {
        String apiKey = doc["api_key"].as<String>();
        String uid = doc["uid"].as<String>();
        String jsonToSend = "{\"api_key\":\"" + apiKey + "\",\"uid\":\"" + uid + "\"}";

        if (sendData(jsonToSend)) {
          successCount++;
          delay(300);
        } else {
          // Simpan kembali jika gagal dikirim
          buffer += line + "\n";
          break;  // Stop jika ada kegagalan
        }
      }
    }
  }

  file.close();

  // Update file offline dengan data yang gagal dikirim
  SD.remove("/offline.txt");
  if (buffer.length() > 0) {
    File f = SD.open("/offline.txt", FILE_WRITE);
    f.print(buffer);
    f.close();
  }

  deselectAll();

  if (successCount > 0) {
    Serial.println("✅ Offline sync complete: " + String(successCount) + " data sent");
    displayDebug("Sync Complete", String(successCount) + " sent", 1500);
    sendOfflineSyncNotification(successCount);
  }
}

void removeFromOfflineData(String uidToRemove) {
  selectSD();
  File file = SD.open("/offline.txt", FILE_READ);
  if (!file) {
    deselectAll();
    return;
  }

  String buffer = "";
  bool found = false;

  while (file.available()) {
    String line = file.readStringUntil('\n');
    line.trim();

    if (line.length() > 0) {
      StaticJsonDocument<256> doc;
      DeserializationError error = deserializeJson(doc, line);

      if (!error) {
        String uid = doc["uid"].as<String>();
        if (uid != uidToRemove) {
          buffer += line + "\n";
        } else {
          found = true;
        }
      }
    }
  }

  file.close();

  // Tulis ulang file tanpa data yang sudah dikirim
  SD.remove("/offline.txt");
  if (buffer.length() > 0) {
    File f = SD.open("/offline.txt", FILE_WRITE);
    f.print(buffer);
    f.close();
  }

  deselectAll();

  if (found) {
    Serial.println("Removed sent data from offline file");
  }
}

// =============================
// ⚙️ UTILITY FUNCTIONS
// =============================
void selectRFID() {
  digitalWrite(SS_SD, HIGH);
  digitalWrite(SS_RFID, LOW);
}

void selectSD() {
  digitalWrite(SS_RFID, HIGH);
  digitalWrite(SS_SD, LOW);
}

void deselectAll() {
  digitalWrite(SS_RFID, HIGH);
  digitalWrite(SS_SD, HIGH);
}

void beep() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(150);
  digitalWrite(BUZZER_PIN, LOW);
}

// =============================
// 🔄 RESET SISTEM
// =============================
void resetSystemEEPROM() {
  displayDebug("RESET SYSTEM!", "Please wait...", 0, false);
  Serial.println("⚠️ RESET CARD DETECTED!");

  digitalWrite(BUZZER_PIN, HIGH);
  delay(800);
  digitalWrite(BUZZER_PIN, LOW);

  // Hapus EEPROM
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();

  // Hapus file offline
  selectSD();
  SD.remove("/offline.txt");
  deselectAll();

  displayDebug("System Reset", "Restarting...", 2000);
  delay(2000);
  ESP.restart();
}