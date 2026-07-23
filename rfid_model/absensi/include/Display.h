#ifndef DISPLAY_H
#define DISPLAY_H

#include <Arduino.h>

// =============================
// 🔹 FUNGSI DISPLAY
// =============================
void displayReady();
void displayDebug(String line1, String line2 = "", int displayTime = 3000, bool autoReturn = true);
void displayError(String errorMsg);
void updateStatusDisplay();

#endif  // DISPLAY_H
