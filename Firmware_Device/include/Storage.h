#ifndef STORAGE_H
#define STORAGE_H

#include <Arduino.h>

// =========================
// ⚙️ FUNGSI SIMPAN/BACA EEPROM
// =========================
void saveToEEPROM(int addr, const String &data);
String readFromEEPROM(int addr);

// =============================
// 🔄 RESET SISTEM (EEPROM + data offline)
// =============================
void resetSystemEEPROM();

#endif  // STORAGE_H
