// ===============================================
// PROGRAM ABSENSI RFID ONLINE & OFFLINE (WEMOS D1 MINI)
// Revisi 2: Perbaikan HTTPS, Cloudflare, dan Captive Portal
// ===============================================

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
// #include <WiFiClientSecure.h>  // Diperlukan untuk HTTPS
#include <SPI.h>
#include <MFRC522.h>
#include <SD.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <DNSServer.h>
#include <RTClib.h>  // Tambahkan library untuk RTC

// =============================
// 🔹 PIN KONFIGURASI
// =============================
#define SS_RFID D4
#define RST_RFID D3
#define SS_SD D8
#define SDA_PIN D2
#define SCL_PIN D1
#define BUZZER_PIN D0
// SPI: MOSI:D7, MISO:D6, SCK:D5

// =============================
// 🔹 OBJEK
// =============================
MFRC522 rfid(SS_RFID, RST_RFID);
LiquidCrystal_I2C lcd(0x27, 16, 2);
ESP8266WebServer server(80);
DNSServer dnsServer;
RTC_DS3231 rtc;  // Tambahkan objek RTC
// BearSSL::WiFiClientSecure client;  // Gunakan WiFiClientSecure untuk inisialisasi client
WiFiClient client;

bool apModeActive = false;
bool rtcAvailable = false;  // Flag untuk menandai RTC tersedia

// =============================
// 🔹 WIFI & SERVER
// =============================
String ssid = "";
String password = "";
String serverUrl_DomainOnly = "";  // [DIUBAH] Hanya simpan domain, cth: absensi.sekolah.id
String serverUrl_Full = "";        // URL lengkap, cth: https://.../rekam_rfid.php
String apiKey = "";
String lastDate = "";
String uid = "";
const String UID_RESET = "33BB4B6";

void updateServerDate(String date) {
  lastDate = date;
}

const char *apSSID = "Konfigurasi_Absen_003";
const char *apPASS = NULL;  // Password AP terbuka

unsigned long lastCheck = 0;
bool wifiConnected = false;

// =========================
// 📦 EEPROM Layout
// =========================
#define EEPROM_SIZE 512
#define ADDR_SSID 0
#define ADDR_PASS 100
#define ADDR_URL 200  // Alamat untuk menyimpan domain
#define ADDR_API 400

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
    if (ch == '\0')
      break;
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
  } else {
    return "1970-01-01";
  }
}

String getCurrentTime() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char timeStr[9];
    sprintf(timeStr, "%02d:%02d:%02d", now.hour(), now.minute(), now.second());
    return String(timeStr);
  } else {
    return "00:00:00";
  }
}

// =========================
// 🌐 HALAMAN WEB KONFIGURASI
// =========================
void handleRoot() {
  // [PERBAIKAN] String HTML dibangun dengan benar
  String html = F(R"(
<!DOCTYPE html>
<html>
<head>
 <title>Konfigurasi Absensi</title>
 <meta name='viewport' content='width=device-width, initial-scale=1'>
 <style>
  body { 
   font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
   background-color: #f0f2f5; margin: 0; padding: 20px; 
   display: flex; justify-content: center; align-items: center; min-height: 90vh; 
  }
  .container { 
   background-color: #ffffff; padding: 25px 30px; border-radius: 10px; 
   box-shadow: 0 6px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px; box-sizing: border-box; 
  }
  h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 25px; font-size: 24px; }
  label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; }
  input[type='text'], input[type='password'] { 
   width: 100%; padding: 12px; margin-bottom: 18px; border: 1px solid #ddd; 
   border-radius: 6px; box-sizing: border-box; font-size: 16px; 
  }
  input[type='submit'] { 
   width: 100%; background-color: #007bff; color: white; padding: 14px; 
   border: none; border-radius: 6px; cursor: pointer; font-size: 18px; 
   font-weight: 600; transition: background-color 0.2s;
  }

  input[type='submit']:hover { background-color: #0056b3; }
 </style>
</head>
<body>
 <div class='container'>
  <h2>Konfigurasi Alat Absensi</h2>
  <form action='/save' method='POST'>
   <label for='ssid'>SSID WiFi:</label>
   <input type='text' id='ssid' name='ssid' value=')");

  html += ssid;  // Tambahkan nilai SSID

  html += F(R"('>
    <div style='width:100%; font-weight:600; position:relative; display:flex; justify-content:space-between;'>
   <label for='password'>Password WiFi:</label>
   <button type='button' onclick='togglePassword()' style='background:none; border:none; cursor:pointer; font-size:14px; color:#007bff;'>Lihat</button>
    </div>
    
   <input class='password' type='password' id='password' name='password' value=')");

  html += password;  // Tambahkan nilai Password

  html += F(R"('>
   <label for='url'>Server URL: (cth: absensi.sekolah.id)</label>
   <input type='text' id='url' name='url' value=')");

  html += serverUrl_DomainOnly;  // [DIUBAH] Tampilkan HANYA domain

  html += F(R"('>
   <label for='api'>API Key:</label>
   <input type='text' id='api' name='api' value=')");

  html += apiKey;  // Tambahkan nilai API Key

  html += F(R"('>
   <input type='submit' value='Simpan & Restart'>
  </form>
 </div>
  <script>
    function togglePassword() {
      var pwdField = document.getElementById('password');
      if (pwdField.type === 'password') {
        pwdField.type = 'text';
      } else {
        pwdField.type = 'password';
      }
    }
  </script>
</body>
</html>)");

  server.send(200, "text/html", html);
}

void handleSave() {
  // Ambil nilai dari form
  ssid = server.arg("ssid");
  password = server.arg("password");
  String domainInput = server.arg("url");  // Ambil domain dari input 'url'
  apiKey = server.arg("api");

  // [PERBAIKAN] Bersihkan input domain
  domainInput.replace("https://", "");
  domainInput.replace("http://", "");
  domainInput.trim();
  while (domainInput.endsWith("/")) {
    domainInput = domainInput.substring(0, domainInput.length() - 1);
  }

  // Simpan ke EEPROM
  saveToEEPROM(ADDR_SSID, ssid);
  saveToEEPROM(ADDR_PASS, password);
  saveToEEPROM(ADDR_URL, domainInput);  // Simpan HANYA domain
  saveToEEPROM(ADDR_API, apiKey);

  // [PERBAIKAN] Bangun URL lengkap untuk sesi ini
  if (domainInput != "") {
    // [PERBAIKAN] Pastikan path ini SAMA dengan path API Anda di server
    serverUrl_Full = "http://" + domainInput + "/app/rekam_absen_rfid.php";
  } else {
    serverUrl_Full = "";
  }
  serverUrl_DomainOnly = domainInput;  // Update variabel global domain

  // Kirim halaman balasan (HTML Anda sudah benar)
  String html = R"(
<!DOCTYPE html>
<html>
<head>
  <title>Tersimpan!</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <meta http-equiv='refresh' content='3;url=http://192.168.4.1'>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f0f2f5; }
    .message { background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
    h3 { color: #28a745; }
    p { font-size: 18px; }
  </style>
</head>
<body>
  <div class='message'>
    <h3>Data Tersimpan!</h3>
    <p>Perangkat akan segera restart...</p>
    <p style='font-size:14px; color:#888;'>Halaman akan kembali jika masih terhubung ke AP.</p>
  </div>
</body>
</html>)";

  server.send(200, "text/html", html);
  delay(2000);
  ESP.restart();  // Restart untuk menerapkan konfigurasi baru
}

// =======================
// Masuk Mode AP
// =======================
void startAPConfig() {
  Serial.println("🔧 Mode konfigurasi aktif!");
  apModeActive = true;  // Tandai bahwa kita masuk mode AP
  WiFi.mode(WIFI_AP);
  delay(100);

  bool result = WiFi.softAP(apSSID, apPASS);
  if (!result) { /* ... (Handle AP Gagal) ... */
    Serial.println("❌ Gagal memulai AP!");
    lcd.clear();
    lcd.print("AP Gagal!");
    return;
  }

  IPAddress IP = WiFi.softAPIP();
  Serial.print("Akses: http://");
  Serial.println(IP);
  lcd.clear();
  lcd.print("AP Mode Aktif");
  lcd.setCursor(0, 1);
  lcd.print(IP.toString());

  dnsServer.start(53, "*", IP);  // Mulai DNS Server

  server.on("/", handleRoot);
  server.on("/save", HTTP_POST, handleSave);
  server.onNotFound(handleRoot);  // Redirect semua ke halaman utama
  server.begin();
  Serial.println("🌐 Web konfigurasi siap!");
}

// =============================
// ⚙️ SETUP
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
  lcd.clear();
  lcd.print("Init System...");
  delay(1000);

  // Inisialisasi RTC
  Wire.begin(SDA_PIN, SCL_PIN);
  if (rtc.begin()) {
    rtcAvailable = true;
    Serial.println("✅ RTC ditemukan!");
    
    // Jika RTC kehilangan daya, atur ke waktu kompilasi
    if (rtc.lostPower()) {
      Serial.println("⚠️ RTC kehilangan daya, mengatur waktu ke waktu kompilasi...");
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
  } else {
    Serial.println("❌ RTC tidak ditemukan!");
    rtcAvailable = false;
  }

  // [DIUBAH] Load konfigurasi
  ssid = readFromEEPROM(ADDR_SSID);
  password = readFromEEPROM(ADDR_PASS);
  serverUrl_DomainOnly = readFromEEPROM(ADDR_URL); // Baca HANYA domain
  apiKey = readFromEEPROM(ADDR_API);

  // Nonaktifkan fitur yang bisa menyebabkan disconnect
  wifi_set_sleep_type(NONE_SLEEP_T);   // Jangan tidur
  WiFi.setAutoReconnect(true);         // Auto reconnect
  WiFi.setSleepMode(WIFI_NONE_SLEEP);  // No sleep

  // Force 802.11n mode (lebih stabil)
  wifi_set_phy_mode(PHY_MODE_11N);

  // Atur output power (coba 20.5 untuk lebih stabil)
  WiFi.setOutputPower(20.5);

  // Bersihkan spasi
  ssid.trim();
  password.trim();
  serverUrl_DomainOnly.trim();
  apiKey.trim();

  // [DIUBAH] Bangun URL lengkap
  if (serverUrl_DomainOnly != "") {
    serverUrl_Full = "http://" + serverUrl_DomainOnly + "/api/rfid/catat";  // Sesuaikan path jika perlu
  }

  Serial.println("SSID: " + ssid);
  Serial.println("URL Server: " + serverUrl_Full);  // Tampilkan URL lengkap
  Serial.println("API Key: " + apiKey);

  // Inisialisasi Hardware
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
    Serial.println("❌ SD Card gagal!");
    lcd.clear();
    lcd.print("SD Error!");
  } else {
    Serial.println("💾 SD Card siap.");
    lcd.clear();
    lcd.print("SD Ready");
  }
  deselectAll();
  delay(1000);

  // Logika Koneksi / AP Mode
  if (ssid == "") {
    startAPConfig();
  } else {
    connectWiFi();
    if (!wifiConnected) {
      startAPConfig();  // Fallback ke AP jika config WiFi salah
    }
  }

  lcd.clear();
  lcd.print("Siap Scan Kartu");
  Serial.println("✅ Siap membaca kartu RFID...");
}

// =============================
// 🔁 LOOP
// =============================
void loop() {
  // Selalu jalankan handleClient, walaupun tidak di AP mode
  server.handleClient();
  if (apModeActive) {
    dnsServer.processNextRequest();
  }

  // Cek koneksi WiFi & sinkronisasi
  if (!apModeActive && millis() - lastCheck > 10000) {
    checkWiFiAndSync();
    lastCheck = millis();
  }

  // Baca kartu RFID
  selectRFID();
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    deselectAll();
    return;  // Tidak ada kartu, keluar dari loop
  }

  uid = "";  // PENTING: reset UID sebelumnya
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  deselectAll();

  // =============================
  // 🔥 LOGIKA KARTU RESET
  // =============================
  if (uid == UID_RESET) {
    resetSystemEEPROM();
    return;  // hentikan proses scan
  }

  Serial.println("🎫 UID Kartu : " + uid);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("UID:");
  lcd.setCursor(0, 1);
  lcd.print(uid);

  // Buat JSON untuk dikirim
  String jsonData = "{\"api_key\":\"" + apiKey + "\",\"uid\":\"" + uid + "\"}";

  if (wifiConnected && !apModeActive) {
    if (sendData(jsonData)) {
      // LCD akan di-update di dalam sendData()
    } else {
      saveOfflineData(jsonData);
      lcd.clear();
      lcd.print("Gagal Kirim!");
      lcd.setCursor(0, 1);
      lcd.print("Simpan Offline");
      beep();
    }
  } else {
    saveOfflineData(jsonData);
    delay(800);
    lcd.clear();
    lcd.print("Mode Offline");
    lcd.setCursor(0, 1);
    lcd.print("Simpan Data");
    beep();  // Beep 1x untuk offline
  }

  delay(2000);  // Tampilkan pesan di LCD selama 2 detik
  lcd.clear();
  lcd.print("Siap Scan Lagi");
}

// =============================
// 📶 WIFI
// =============================
void connectWiFi() {
  Serial.println("🔄 Menghubungkan WiFi...");
  lcd.clear();
  lcd.print("Koneksi WiFi...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid.c_str(), password.c_str());

  unsigned long startAttempt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttempt < 10000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Terhubung!");
    Serial.println(WiFi.localIP());
    lcd.clear();
    lcd.print("WiFi OK:");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    wifiConnected = true;
    apModeActive = false;
    dnsServer.stop();
    server.stop();  // Hentikan server AP
  } else {
    Serial.println("\n❌ WiFi Gagal!");
    lcd.clear();
    lcd.print("WiFi Offline");
    lcd.setCursor(0, 1);
    lcd.print("AP Mode");
    wifiConnected = false;
  }
  delay(1000);
}

void checkWiFiAndSync() {
  if (apModeActive)
    return;  // Jangan cek jika sedang mode AP

  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    Serial.println("Koneksi WiFi terputus, mencoba konek ulang...");
    connectWiFi();

    if (!wifiConnected) {
      Serial.println("Masih gagal konek. Masuk mode AP.");
      startAPConfig();  // Fallback ke mode AP
    }
  } else {
    wifiConnected = true;
    sendOfflineData();  // Jika terhubung, sinkronisasi data
  }
}

// =============================
// 🚀 KIRIM DATA ONLINE
// =============================
bool sendData(String jsonData) {

  if (WiFi.status() != WL_CONNECTED)
    return false;

  HTTPClient http;

  Serial.print("[HTTP] Mengirim ke: ");
  Serial.println(serverUrl_Full);

  if (!http.begin(client, serverUrl_Full)) {  // Gunakan URL LENGKAP
    Serial.println("❌ Gagal memulai HTTPs!");
    return false;
  }

  http.addHeader("Content-Type", "application/json");
  // [DIUBAH] Kirim API Key di header (lebih aman)
  http.addHeader("X-API-Key", apiKey);
  http.setReuse(true);  // Jaga koneksi tetap terbuka

  int httpCode = http.POST(jsonData);  // Kirim JSON

  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("📡 Respon Server (" + String(httpCode) + "): " + response);

    StaticJsonDocument<256> doc;
    if (deserializeJson(doc, response) == DeserializationError::Ok) {
      const char *status = doc["status"];  // cth: "masuk", "pulang", "error"
      const char *nama = doc["nama"];
      const char *msg = doc["message"];

      if (status && nama) {
        lcd.clear();
        lcd.print(nama);  // Tampilkan nama
        lcd.setCursor(0, 1);

        // [PERBAIKAN] Cek status dari skrip PHP Anda
        if (String(status) == "masuk") {
          lcd.print("Masuk Sukses");
          beep();
        } else if (String(status) == "pulang") {
          lcd.print("Pulang Sukses");
          beep();
        } else if (String(status) == "sudah_masuk" || String(status) == "sudah_masuk_pulang") {
          lcd.print("Sudah Absen");
          beep();
        } else {                           // "error" atau status tidak dikenal
          lcd.print(msg ? msg : "Error");  // Tampilkan pesan error server
          sendErrorLog(msg ? String(msg) : "Unknown error from server", uid);
          beep();
        }

      } else if (String(status) == "tidak_terdaftar") {
        lcd.clear();
        lcd.print("UID:" + uid);
        lcd.setCursor(0, 1);
        lcd.print("Tidak Terdaftar");
        beep();
      } else {  // Gagal parse JSON atau format beda
        lcd.clear();
        lcd.print("Resp Server Err");
        lcd.setCursor(0, 1);
        lcd.print(response.substring(0, 16));  // Tampilkan potongan respon
        sendErrorLog("Format respon server tidak valid" + String(msg), "N/A");
        beep();
      }
    } else {  // JSON tidak valid
      lcd.clear();
      lcd.print("Gagal Parse JSON");
      beep();
    }

    http.end();
    return (httpCode == HTTP_CODE_OK);  // Sukses jika 200 OK
  } else {
    Serial.printf("❌ HTTP Gagal, error: %s\n", http.errorToString(httpCode).c_str());
    lcd.print("HTTP Gagal");
    http.end();
    return false;
  }
}

// =============================
// 💾 SIMPAN/KIRIM OFFLINE
// =============================
void saveOfflineData(const String &data) {
  // Parse JSON untuk menambahkan tanggal dan jam
  StaticJsonDocument<256> doc;
  DeserializationError error = deserializeJson(doc, data);
  
  if (error) {
    Serial.println("⚠️ Gagal parse JSON untuk data offline!");
    return;
  }
  
  // Tambahkan tanggal dan jam dari RTC
  doc["tanggal"] = getCurrentDate();
  doc["jam"] = getCurrentTime();
  
  // Serialize ke string
  String offlineData;
  serializeJson(doc, offlineData);
  
  selectSD();
  File file = SD.open("/offline.txt", FILE_WRITE);
  if (!file) {
    Serial.println("⚠️ Tidak bisa buka offline.txt!");
    deselectAll();
    return;
  }
  file.seek(file.size());  // Pindah ke akhir file
  file.println(offlineData);
  file.close();
  Serial.println("💾 Data disimpan offline!");
  deselectAll();
}

void sendOfflineData() {
  selectSD();
  File file = SD.open("/offline.txt", FILE_READ);
  if (!file || file.size() == 0) {
    file.close();
    deselectAll();
    return;
  }

  Serial.println("🔁 Sinkronisasi data offline...");
  lcd.clear();
  lcd.print("Sinkronisasi...");

  String buffer = "";  // Buffer untuk menyimpan data yg gagal dikirim ulang
  while (file.available()) {
    String line = file.readStringUntil('\n');
    line.trim();
    if (line.length() > 0) {
      // Parse JSON yang ada tanggal dan jam
      StaticJsonDocument<256> doc;
      DeserializationError error = deserializeJson(doc, line);
      
      if (!error) {
        // Buat data baru tanpa tanggal dan jam (hanya api_key dan uid)
        String apiKey = doc["api_key"].as<String>();
        String uid = doc["uid"].as<String>();
        String jsonToSend = "{\"api_key\":\"" + apiKey + "\",\"uid\":\"" + uid + "\"}";
        
        if (!sendData(jsonToSend)) {
          // Jika gagal kirim, simpan kembali ke buffer (dengan tanggal dan jam asli)
          buffer += line + "\n";
          Serial.println("Gagal kirim, sinkronisasi berhenti.");
          break;
        }
      } else {
        Serial.println("⚠️ Gagal parse JSON dari file offline");
      }
    }
  }
  file.close();

  // Tulis ulang file offline.txt HANYA dengan data yg gagal
  SD.remove("/offline.txt");
  if (buffer.length() > 0) {
    File f = SD.open("/offline.txt", FILE_WRITE);
    f.print(buffer);
    f.close();
  }

  Serial.println("✅ Sinkronisasi selesai!");
  deselectAll();
  // Kembalikan ke layar siaga
  lcd.clear();
  lcd.print("Siap Scan Kartu");
}

// =============================
// ⚙️ ERROR LOG
// =============================
void sendErrorLog(String errorMsg, String uid) {
  if (WiFi.status() != WL_CONNECTED)
    return;

  HTTPClient http;
  http.begin(client, "http://" + serverUrl_DomainOnly + "/app/log_error.php");
  http.addHeader("Content-Type", "application/json");

  String json =
    "{\"uid\":\"" + uid + "\","
                          "\"error\":\""
    + errorMsg + "\","
                 "\"device\":\"RFID_GATE_1\"}";

  http.POST(json);
  http.end();
}

// =============================
// ⚙️ UTILITY (SPI & BUZZER)
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
  delay(250);
  digitalWrite(BUZZER_PIN, LOW);
}

// =============================
// RESET SISTEM
// =============================
void resetSystemEEPROM() {
  Serial.println("⚠️ KARTU RESET TERDETEKSI!");

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Reset System");
  lcd.setCursor(0, 1);
  lcd.print("Mohon Tunggu");

  // Beep panjang tanda reset
  digitalWrite(BUZZER_PIN, HIGH);
  delay(800);
  digitalWrite(BUZZER_PIN, LOW);

  // Hapus EEPROM
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  SD.remove("/offline.txt");

  delay(1500);
  ESP.restart();
}