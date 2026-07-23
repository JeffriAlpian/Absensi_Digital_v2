#include <Arduino.h>
#include "../include/Hardware.h"
#include "../include/Config.h"

void selectRFID() {
  digitalWrite(SS_SD, HIGH);
  digitalWrite(SS_RFID, LOW);
}

void selectSD() {
  digitalWrite(SS_RFID, HIGH);
  digitalWrite(SS_SD, LOW);
}

void deselectAll() {
  digitalWrite(SS_RFID, HIGH);
  digitalWrite(SS_SD, HIGH);
}

void beep() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(150);
  digitalWrite(BUZZER_PIN, LOW);
}
