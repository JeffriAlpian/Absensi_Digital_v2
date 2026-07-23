// ===============================================
// PROGRAM ABSENSI RFID ONLINE & OFFLINE (WEMOS D1 MINI)
// Revisi 7: Perbaikan Logika Mode AP & WiFi
// Struktur modular: absensi.ino + include/*.h + src/*.cpp
// ===============================================

#include "include/Config.h"
#include "include/Globals.h"
#include "include/Hardware.h"
#include "include/Storage.h"
#include "include/Display.h"
#include "include/RtcTime.h"
#include "include/Telegram.h"
#include "include/WifiManager.h"
#include "include/WebPortal.h"
#include "include/ServerComm.h"
#include "include/OfflineData.h"

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

  // Tambahkan ini agar NTP selalu update waktunya dari internet
  if (wifiConnected && internetAvailable) {
    timeClient.update();
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
