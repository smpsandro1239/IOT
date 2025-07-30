#include <WiFi.h>
#include <time.h>
#include <SPI.h>
#include <LoRa.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>

#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26
#define LORA_BAND 868E6

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET 16
#define OLED_SDA 4
#define OLED_SCL 15

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Incluir configurações do ficheiro config.h
#include "config.h"

// WiFi - Configurações carregadas de config.h
const char* ssid = WIFI_SSID;
const char* password = WIFI_PASSWORD;

char chipid_str[13];
unsigned long contadorEnvios = 0;
String ultimoPacote = "";
String ultimaResposta = "Aguardando...";
int ultimoRSSI = 0;
String statusLoRa = "OK";
String dataHoraAtual = "A sincronizar...";

void getChipID(char* out) {
  uint64_t chipid = ESP.getEfuseMac();
  sprintf(out, "%04X%08X", (uint16_t)(chipid>>32), (uint32_t)chipid);
}

void sincronizaHora() {
  configTime(3600, 0, "pool.ntp.org", "time.nist.gov"); // UTC+1 Portugal Continental
  struct tm timeinfo;
  int tentativas = 0;
  while (!getLocalTime(&timeinfo) && tentativas < 20) {
    delay(500);
    tentativas++;
  }
}

String getDataHoraString() {
  struct tm timeinfo;
  if (getLocalTime(&timeinfo)) {
    char buf[25];
    strftime(buf, sizeof(buf), "%d/%m/%Y %H:%M:%S", &timeinfo);
    return String(buf);
  } else {
    return "Sem hora NTP";
  }
}

void mostraNoDisplay() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("== VEICULO LoRa ==");
  display.print("MAC: "); display.println(chipid_str);
  display.print("Enviados: "); display.println(contadorEnvios);
  display.print("Data/Hora:"); display.println(dataHoraAtual);
  display.print("Status: "); display.println(statusLoRa);
  // display.print("Ult. resposta:"); display.println(ultimaResposta); // Removido ACK
  // display.print("Ult. RSSI: "); display.println(ultimoRSSI); // Removido ACK
  display.println("Ult. pacote:");
  if (ultimoPacote.length() > 16) {
    display.println(ultimoPacote.substring(0, 16));
    display.println(ultimoPacote.substring(16));
  } else {
    display.println(ultimoPacote);
  }
  display.display();
}

void setup() {
  Serial.begin(115200);
  Wire.begin(OLED_SDA, OLED_SCL);
  pinMode(OLED_RESET, OUTPUT);
  digitalWrite(OLED_RESET, LOW); delay(50); digitalWrite(OLED_RESET, HIGH); delay(50);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { while (1); }
  getChipID(chipid_str);

  display.clearDisplay(); display.setCursor(0, 0); display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE); display.println("A ligar WiFi..."); display.display();

  WiFi.begin(ssid, password);
  int tentativas = 0;
  while (WiFi.status() != WL_CONNECTED && tentativas < 20) {
    delay(500); tentativas++;
    display.setCursor(0, 16); display.println("."); display.display();
  }
  if (WiFi.status() == WL_CONNECTED) {
    display.println("WiFi OK!"); display.display();
    sincronizaHora();
    WiFi.disconnect(true); WiFi.mode(WIFI_OFF);
  } else {
    display.println("WiFi Falhou!"); display.display();
  }
  delay(1000);

  display.clearDisplay(); display.setCursor(0, 0); display.println("Iniciando LoRa..."); display.display();

  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  if (!LoRa.begin(LORA_BAND)) {
    display.clearDisplay(); display.setCursor(0, 0);
    display.println("Erro LoRa!"); display.display();
    statusLoRa = "ERRO"; while (1);
  }
  statusLoRa = "OK";
  display.clearDisplay(); display.setCursor(0, 0); display.println("LoRa Sender OK!"); display.display();
  delay(1000);

  dataHoraAtual = getDataHoraString();
  mostraNoDisplay();
}

void loop() {
  dataHoraAtual = getDataHoraString();

  // Monta pacote JSON com MAC Address e data/hora
  StaticJsonDocument<128> doc;
  doc["mac"] = chipid_str;
  doc["datahora"] = dataHoraAtual;
  String pacote;
  serializeJson(doc, pacote);

  LoRa.beginPacket(); LoRa.print(pacote); LoRa.endPacket();

  contadorEnvios++;
  ultimoPacote = pacote;

  Serial.print("Enviado: "); Serial.println(pacote);

  // Bloco de recebimento de ACK removido conforme plano (Passo 1.2)
  // O veículo agora apenas transmite.
  // ultimaResposta = "N/A"; // Pode ser definido para um valor padrão
  // ultimoRSSI = 0;

  mostraNoDisplay();
  delay(2000); // Manter o delay entre envios
}
