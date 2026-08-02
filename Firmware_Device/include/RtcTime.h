#ifndef RTC_TIME_H
#define RTC_TIME_H

#include <Arduino.h>

// =========================
// 📅 FUNGSI WAKTU RTC / NTP
// =========================
String getCurrentDate();
String getCurrentTime();
void syncRTCwithNTP();

#endif  // RTC_TIME_H
