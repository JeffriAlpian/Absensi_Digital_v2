# Absensi RFID – Struktur Modular

Struktur folder:

```
absensi/
├── absensi.ino          <- file induk (hanya berisi setup() dan loop())
├── include/              <- semua file header (.h)
│   ├── Config.h           deklarasi pin, EEPROM layout, konstanta Telegram
│   ├── Globals.h          deklarasi objek & variabel status global
│   ├── Hardware.h         select SPI (RFID/SD) & buzzer
│   ├── Storage.h          simpan/baca EEPROM & reset sistem
│   ├── Display.h          fungsi tampilan LCD
│   ├── RtcTime.h          fungsi tanggal/jam (RTC & NTP)
│   ├── Telegram.h         notifikasi bot Telegram
│   ├── WifiManager.h      koneksi WiFi & cek internet
│   ├── WebPortal.h        portal web mode AP (konfigurasi)
│   ├── ServerComm.h       kirim data absensi ke server
│   └── OfflineData.h      simpan/kirim/hapus data offline di SD card
└── src/                   <- semua file implementasi (.cpp)
    ├── Config.cpp
    ├── Globals.cpp
    ├── Hardware.cpp
    ├── Storage.cpp
    ├── Display.cpp
    ├── RtcTime.cpp
    ├── Telegram.cpp
    ├── WifiManager.cpp
    ├── WebPortal.cpp
    ├── ServerComm.cpp
    └── OfflineData.cpp
```

Semua logika program sama persis dengan kode asli — hanya dipecah per fitur
supaya lebih mudah dibaca, di-maintain, dan dikembangkan.

## Cara pakai di Arduino IDE

1. Salin seluruh folder `absensi/` (termasuk isi `include/` dan `src/`) ke
   folder sketchbook Arduino, sehingga path-nya menjadi
   `.../Arduino/absensi/absensi.ino`.
2. Buka `absensi.ino` di Arduino IDE.
3. Arduino IDE (1.6.6 ke atas) otomatis mengkompilasi semua file di dalam
   subfolder `src/` secara rekursif, dan file `.cpp` di `src/` meng-include
   header dari `../include/...` menggunakan path relatif — jadi tidak perlu
   pengaturan tambahan, tinggal klik **Verify/Upload** seperti biasa.
4. Pastikan library berikut sudah terpasang lewat Library Manager:
   `MFRC522`, `LiquidCrystal_I2C`, `ArduinoJson`, `RTClib`, `NTPClient`,
   `ESP8266WiFi`/`ESP8266WebServer`/`ESP8266HTTPClient` (bawaan board
   package ESP8266), `DNSServer`, `SD`.

## Cara pakai di PlatformIO

Struktur ini juga cocok dengan konvensi PlatformIO (`src/` untuk kode,
`include/` untuk header). Cukup taruh folder ini sebagai root project dan
tambahkan `platformio.ini` untuk board Wemos D1 Mini (`env:d1_mini`,
`platform = espressif8266`). PlatformIO akan mengenali `absensi.ino` di
dalam `src/` sebagai sketch utama — jika ingin pakai PlatformIO, pindahkan
`absensi.ino` ke dalam folder `src/` (sejajar dengan file .cpp lainnya).

## Catatan konfigurasi

- Ganti nilai `TELEGRAM_BOT_TOKEN` dan `TELEGRAM_CHAT_ID` di
  `src/Config.cpp` sesuai bot Telegram Anda sendiri.
- Pin, alamat EEPROM, dan SSID Access Point konfigurasi ada di
  `include/Config.h`.
