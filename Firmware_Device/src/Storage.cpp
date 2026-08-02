#include "../include/Storage.h"
#include "../include/Globals.h"
#include "../include/Config.h"
#include "../include/Display.h"
#include "../include/Hardware.h"

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
