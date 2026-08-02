#ifndef OFFLINE_DATA_H
#define OFFLINE_DATA_H

#include <Arduino.h>

// =============================
// 💾 FUNGSI OFFLINE DATA (SD CARD)
// =============================
void saveOfflineData(const String &data);
void sendOfflineData();
void removeFromOfflineData(String uidToRemove);

#endif  // OFFLINE_DATA_H
