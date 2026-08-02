#include "../include/WifiManager.h"
#include "../include/Globals.h"
#include "../include/Display.h"
#include "../include/OfflineData.h"

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

void connectWiFi() {
  displayDebug("Connecting WiFi", ssid, 0, false);
  Serial.println("🔄 Connecting WiFi...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid.c_str(), password.c_str());

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
