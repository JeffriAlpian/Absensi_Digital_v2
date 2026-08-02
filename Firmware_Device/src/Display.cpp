#include "../include/Display.h"
#include "../include/Globals.h"
#include "../include/RtcTime.h"

void displayReady() {
  if (apModeActive) return;

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Ready to Scan   ");
  updateStatusDisplay();
  returningToReady = false;
  Serial.println("[DISPLAY] Ready to Scan");
}

void displayDebug(String line1, String line2, int displayTime, bool autoReturn) {
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
