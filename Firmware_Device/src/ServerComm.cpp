#include <ArduinoJson.h>
#include "../include/ServerComm.h"
#include "../include/Globals.h"
#include "../include/Display.h"
#include "../include/Telegram.h"
#include "../include/Hardware.h"
#include "../include/Config.h"

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
