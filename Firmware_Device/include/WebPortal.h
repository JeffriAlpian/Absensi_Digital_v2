#ifndef WEB_PORTAL_H
#define WEB_PORTAL_H

#include <Arduino.h>

// =========================
// 📡 SCAN WIFI & HALAMAN WEB KONFIGURASI (MODE AP)
// =========================
String getWifiScanHTML();
String handleScanEndpoint();
void handleScanRoute();
void handleRoot();
void handleSave();
void startAPConfig();

#endif  // WEB_PORTAL_H
