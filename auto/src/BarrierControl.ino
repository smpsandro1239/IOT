#include <WiFi.h>
#include <HTTPClient.h>
#include <LoRa.h>

// Incluir configurações do ficheiro config.h
#include "config.h"

// WiFi - Configurações carregadas de config.h
const char* ssid = WIFI_SSID;
const char* password = WIFI_PASSWORD;

// Backend API
const char* serverUrl = "http://127.0.0.1:8000/api/v1/access-logs";
const char* authUrl = "http://127.0.0.1:8000/api/v1/vehicles/authorize";

// LoRa settings
#define LORA_FREQ 868E6 // EU frequency
#define LORA_SF 7       // Spreading factor
#define LORA_BW 125E3   // Bandwidth
#define LORA_CR 5       // Coding rate

// TODO: Implement actual LoRa communication
// This function should receive a LoRa packet and parse the MAC address
String receiveLoRaPacket() {
  // Simulate receiving a MAC address
  return "24A160123456";
}

// TODO: Implement OTA update logic
void checkForOTAUpdate() {
  // Add your OTA update logic here
}

void setup() {
  Serial.begin(115200);

  // Initialize WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  // Initialize LoRa
  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("LoRa init failed!");
    while (1);
  }
  LoRa.setSpreadingFactor(LORA_SF);
  LoRa.setBandwidth(LORA_BW);
  LoRa.setCodingRate4(LORA_CR);
  Serial.println("LoRa initialized");
}

float detectAoA() {
    // Implement actual LoRa AoA calculation (requires 3-antenna array)
    // Placeholder: Use RSSI and phase difference
    return random(0, 180); // Replace with real AoA
}

float detectDistance() {
    // Estimate distance based on RSSI (simplified)
    int rssi = LoRa.packetRssi();
    return map(rssi, -120, -50, 500, 0); // Map RSSI to 0-500m
}

float detectSignalStrength() {
    // Convert RSSI to percentage
    int rssi = LoRa.packetRssi();
    return map(rssi, -120, -50, 0, 100);
}

void loop() {
  // Simulate vehicle detection
  String mac = receiveLoRaPacket(); // Replace with actual MAC from LoRa
  float aoa = detectAoA();
  String direcao = (aoa < 90) ? "NS" : "SN";
    float distance = detectDistance();
    float signalStrength = detectSignalStrength();
  String status = checkAuthorization(mac) ? "AUTORIZADO" : "NEGADO";

  if (status == "AUTORIZADO") {
    // Open corresponding barrier (simulated)
    Serial.println("Opening barrier for " + direcao);
  }

  // Send telemetry data
  sendTelemetry(mac, direcao, status, distance, signalStrength);

  delay(5000); // Check every 5 seconds
}

bool checkAuthorization(String mac) {
  HTTPClient http;
  http.begin(authUrl + "?mac=" + mac);
  int httpCode = http.GET();
  if (httpCode == 200) {
    String payload = http.getString();
    return payload.indexOf("true") != -1;
  }
  Serial.println("Authorization check failed: " + String(httpCode));
  http.end();
  return false;
}

void sendTelemetry(String mac, String direcao, String status) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    String payload = "{\"mac\":\"" + mac + "\",\"direcao\":\"" + direcao + "\",\"datahora\":\"" + getCurrentTime() + "\",\"status\":\"" + status + "\"}";
    int httpCode = http.POST(payload);
    if (httpCode == 201) {
      Serial.println("Telemetry sent successfully");
    } else {
      Serial.println("Telemetry send failed: " + String(httpCode));
    }
    http.end();
  }
}

String getCurrentTime() {
  // Simulate current time (replace with NTP if needed)
  return "2025-07-16T01:49:00Z";
}
