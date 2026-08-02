#include <ArduinoJson.h>
#include "../include/OfflineData.h"
#include "../include/Globals.h"
#include "../include/Display.h"
#include "../include/RtcTime.h"
#include "../include/Hardware.h"
#include "../include/ServerComm.h"
#include "../include/Telegram.h"

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
        String apiKeyLine = doc["api_key"].as<String>();
        String uidLine = doc["uid"].as<String>();
        String jsonToSend = "{\"api_key\":\"" + apiKeyLine + "\",\"uid\":\"" + uidLine + "\"}";

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
        String uidLine = doc["uid"].as<String>();
        if (uidLine != uidToRemove) {
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
