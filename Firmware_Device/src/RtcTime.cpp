#include "../include/RtcTime.h"
#include "../include/Globals.h"
#include "../include/Display.h"

String getCurrentDate() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char dateStr[11];
    sprintf(dateStr, "%04d-%02d-%02d", now.year(), now.month(), now.day());
    return String(dateStr);
  }
  // JIKA RTC TIDAK ADA, GUNAKAN NTP
  else if (wifiConnected && internetAvailable) {
    timeClient.update();
    unsigned long epochTime = timeClient.getEpochTime();
    DateTime ntpTime(epochTime);
    char dateStr[11];
    sprintf(dateStr, "%04d-%02d-%02d", ntpTime.year(), ntpTime.month(), ntpTime.day());
    return String(dateStr);
  }
  return "1970-01-01";  // Fallback terakhir
}

String getCurrentTime() {
  if (rtcAvailable) {
    DateTime now = rtc.now();
    char timeStr[9];
    sprintf(timeStr, "%02d:%02d:%02d", now.hour(), now.minute(), now.second());
    return String(timeStr);
  }
  // JIKA RTC TIDAK ADA, GUNAKAN NTP
  else if (wifiConnected && internetAvailable) {
    timeClient.update();
    return timeClient.getFormattedTime();
  }
  return "00:00:00";  // Fallback terakhir
}

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
