#include "../include/Telegram.h"
#include "../include/Globals.h"
#include "../include/Config.h"
#include "../include/RtcTime.h"

void sendTelegramMessage(String message) {
  if (!internetAvailable) {
    Serial.println("Internet tidak tersedia, tidak bisa kirim Telegram");
    return;
  }

  HTTPClient https;
  String url = "https://api.telegram.org/bot" + TELEGRAM_BOT_TOKEN + "/sendMessage";

  clientSecure.setInsecure();
  clientSecure.setTimeout(5000);

  if (!https.begin(clientSecure, url)) {
    Serial.println("Gagal konek ke Telegram");
    return;
  }

  https.addHeader("Content-Type", "application/json");

  String jsonPayload = "{";
  jsonPayload += "\"chat_id\":\"" + TELEGRAM_CHAT_ID + "\",";
  jsonPayload += "\"text\":\"" + message + "\",";
  jsonPayload += "\"parse_mode\":\"HTML\",";
  jsonPayload += "\"disable_notification\":false";
  jsonPayload += "}";

  int httpCode = https.POST(jsonPayload);
  if (httpCode > 0) {
    if (httpCode == 200) {
      Serial.println("✅ Telegram terkirim");
    } else {
      Serial.println("Telegram error: " + String(httpCode));
    }
  }
  https.end();
}

void sendAttendanceNotification(String nama, String status, String uid) {
  if (!internetAvailable) return;

  String message = "✅ <b>ABSENSI BERHASIL</b>\n\n";
  message += "👤 <b>Nama:</b> " + nama + "\n";
  message += "📋 <b>Status:</b> " + status + "\n";
  message += "🆔 <b>UID:</b> " + uid + "\n";
  message += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  message += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  message += "🔧 <b>Device:</b> " + deviceName;
  sendTelegramMessage(message);
}

void sendOfflineSyncNotification(int count) {
  if (!internetAvailable) return;

  String message = "💾 <b>DATA OFFLINE DISINKRONKASIKAN</b> 💾\n\n";
  message += "📦 <b>Jumlah Data:</b> " + String(count) + " record\n";
  message += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  message += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  message += "🔧 <b>Device:</b> " + deviceName + "\n\n";
  message += "✅ <i>Data offline berhasil dikirim ke server</i>";
  sendTelegramMessage(message);
}

void sendErrorLog(String errorMsg, String uid) {
  if (!internetAvailable) return;

  String telegramMessage = "🚨 <b>ERROR REPORT</b>\n\n";
  telegramMessage += "📅 <b>Tanggal:</b> " + getCurrentDate() + "\n";
  telegramMessage += "⏰ <b>Waktu:</b> " + getCurrentTime() + "\n";
  telegramMessage += "🆔 <b>UID:</b> " + uid + "\n";
  telegramMessage += "🔧 <b>Device:</b> " + deviceName + "\n";
  telegramMessage += "📶 <b>Internet:</b> " + String(internetAvailable ? "Online" : "Offline") + "\n\n";
  telegramMessage += "❌ <b>ERROR:</b>\n<code>" + errorMsg + "</code>";
  sendTelegramMessage(telegramMessage);
}
