#include <WiFi.h>
#include <HTTPClient.h>
#include <LoRa.h>

// WiFi credentials
const char* ssid = "your_wifi_ssid";
const char* password = "your_wifi_password";

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

void loop() {
  // Simulate vehicle detection
  String mac = "24A160123456"; // Replace with actual MAC from LoRa
  float aoa = detectAoA();
  String direcao = (aoa < 90) ? "NS" : "SN";
  String status = checkAuthorization(mac) ? "AUTORIZADO" : "NEGADO";

  if (status == "AUTORIZADO") {
    // Open corresponding barrier (simulated)
    Serial.println("Opening barrier for " + direcao);
  }

  // Send telemetry data
  sendTelemetry(mac, direcao, status);

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
