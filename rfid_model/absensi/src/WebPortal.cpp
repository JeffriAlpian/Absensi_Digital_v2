#include "../include/WebPortal.h"
#include "../include/Globals.h"
#include "../include/Config.h"
#include "../include/Display.h"
#include "../include/Storage.h"
#include "../include/RtcTime.h"

// =========================
// 📡 SCAN WIFI NETWORKS
// =========================
String getWifiScanHTML() {
  int n = WiFi.scanNetworks();
  String html = "";

  if (n == 0) {
    html = "<p>Tidak ada jaringan WiFi ditemukan.</p>";
  } else {
    html += "<table style='width:100%;border-collapse:collapse;margin-top:10px;'>";
    html += "<tr style='background:#007bff;color:white;'>";
    html += "<th style='padding:8px;text-align:left;'>SSID</th>";
    html += "<th style='padding:8px;text-align:center;'>Keamanan</th>";
    html += "<th style='padding:8px;text-align:center;'>Sinyal</th>";
    html += "<th style='padding:8px;text-align:center;'>Kekuatan</th>";
    html += "</tr>";

    for (int i = 0; i < n; i++) {
      int rssi = WiFi.RSSI(i);
      String ssidName = WiFi.SSID(i);
      String enc = (WiFi.encryptionType(i) == ENC_TYPE_NONE) ? "Open" : "🔒";

      // Konversi RSSI ke persentase kekuatan sinyal
      int strength = 0;
      if (rssi >= -50) strength = 100;
      else if (rssi >= -60) strength = 80;
      else if (rssi >= -70) strength = 60;
      else if (rssi >= -80) strength = 40;
      else if (rssi >= -90) strength = 20;
      else strength = 5;

      // Warna bar berdasarkan kekuatan
      String barColor = "#dc3545";                    // merah
      if (strength >= 80) barColor = "#28a745";       // hijau
      else if (strength >= 60) barColor = "#ffc107";  // kuning
      else if (strength >= 40) barColor = "#fd7e14";  // oranye

      // Warna baris tabel
      String rowBg = (i % 2 == 0) ? "#f8f9fa" : "#ffffff";

      html += "<tr style='background:" + rowBg + ";cursor:pointer;' onclick=\"document.getElementById('ssid').value='" + ssidName + "'\">";
      html += "<td style='padding:8px;'>" + ssidName + "</td>";
      html += "<td style='padding:8px;text-align:center;'>" + enc + "</td>";
      html += "<td style='padding:8px;text-align:center;font-family:monospace;'>" + String(rssi) + " dBm</td>";
      html += "<td style='padding:8px;'>";
      html += "<div style='background:#e9ecef;border-radius:4px;height:18px;width:100%;'>";
      html += "<div style='background:" + barColor + ";width:" + String(strength) + "%;height:18px;border-radius:4px;transition:width 0.3s;'></div>";
      html += "</div>";
      html += "<small style='color:#666;'>" + String(strength) + "%</small>";
      html += "</td>";
      html += "</tr>";
    }

    html += "</table>";
    html += "<small style='color:#888;display:block;margin-top:6px;'>💡 Klik baris untuk pilih jaringan</small>";
  }

  WiFi.scanDelete();
  return html;
}

String handleScanEndpoint() {
  return getWifiScanHTML();
}

void handleScanRoute() {
  server.send(200, "text/html", getWifiScanHTML());
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

  <div style='margin-bottom:20px;'>
    <div style='display:flex;justify-content:space-between;align-items:center;'>
      <strong>Jaringan WiFi Tersedia:</strong>
      <button type='button' onclick='scanWifi()' id='scanBtn'
        style='background:#17a2b8;color:white;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;font-size:13px;'>
        Scan
      </button>
    </div>
    <div id='wifiList' style='margin-top:8px;'>
      <p style='color:#888;font-size:13px;'>Klik tombol Scan untuk mencari WiFi...</p>
    </div>
  </div>

  <script>
  function scanWifi() {
    var btn = document.getElementById('scanBtn');
    btn.disabled = true;
    btn.innerText = 'Scanning...';
    document.getElementById('wifiList').innerHTML = '<p style="color:#888;font-size:13px;">Sedang scan jaringan...</p>';
    fetch('/scan')
      .then(r => r.text())
      .then(html => {
        document.getElementById('wifiList').innerHTML = html;
        btn.disabled = false;
        btn.innerText = 'Scan';
      })
      .catch(() => {
        document.getElementById('wifiList').innerHTML = '<p style="color:red;">Gagal scan WiFi</p>';
        btn.disabled = false;
        btn.innerText = 'Scan';
      });
  }
  </script>
)");

  html += F(R"(
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
  server.on("/scan", handleScanRoute);
  server.onNotFound(handleRoot);
  server.begin();
}
