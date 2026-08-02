#ifndef TELEGRAM_H
#define TELEGRAM_H

#include <Arduino.h>

// =========================
// 📱 FUNGSI TELEGRAM
// =========================
void sendTelegramMessage(String message);
void sendAttendanceNotification(String nama, String status, String uid);
void sendOfflineSyncNotification(int count);
void sendErrorLog(String errorMsg, String uid);

#endif  // TELEGRAM_H
