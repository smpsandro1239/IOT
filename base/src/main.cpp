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

const char* ssid = "50N43Everywere 3";
const char* password = "Benfica456+++++";

// MACs autorizados (exemplo)
const char* macs_autorizados[] = {
  "24A160123456", // Substitua pelos MACs reais
  "24A160654321",
  "102B22286F24",
};
const int num_autorizados = 3;

unsigned long totalAutorizados = 0;
unsigned long totalNegados = 0;
String ultimoStatus = "Aguardando...";
String ultimoMac = "";
String ultimaDirecao = "";
String dataHoraAtual = "A sincronizar...";
String ultimaDataVeiculo = "";
String ultimaDataDirecao = "";

void sincronizaHora() {
  configTime(3600, 0, "pool.ntp.org", "time.nist.gov");
  struct tm timeinfo;
  int tentativas = 0;
  while (!getLocalTime(&timeinfo) && tentativas < 20) { delay(500); tentativas++; }
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
  display.println("== BASE LoRa ==");
  display.print("Status: "); display.println(ultimoStatus);
  display.print("MAC: "); display.println(ultimoMac);
  display.print("Dir: "); display.println(ultimaDirecao);
  display.print("DataVeic:"); display.println(ultimaDataVeiculo);
  display.print("DataDir:"); display.println(ultimaDataDirecao);
  display.print("Base:"); display.println(dataHoraAtual);
  display.print("OK:"); display.print(totalAutorizados);
  display.print(" NEG:"); display.println(totalNegados);
  display.display();
}

bool isMacAutorizado(const String& mac) {
  for (int i = 0; i < num_autorizados; i++) {
    if (mac == macs_autorizados[i]) return true;
  }
  return false;
}

void setup() {
  Serial.begin(115200);
  Wire.begin(OLED_SDA, OLED_SCL);
  pinMode(OLED_RESET, OUTPUT);
  digitalWrite(OLED_RESET, LOW); delay(50); digitalWrite(OLED_RESET, HIGH); delay(50);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { while (1); }

  display.clearDisplay(); display.setCursor(0, 0); display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE); display.println("A ligar WiFi..."); display.display();

  WiFi.begin(ssid, password);
  int tentativas = 0;
  while (WiFi.status() != WL_CONNECTED && tentativas < 20) { delay(500); tentativas++; }
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
    while (1);
  }
  display.clearDisplay(); display.setCursor(0, 0); display.println("LoRa OK!"); display.display();
  delay(1000);

  dataHoraAtual = getDataHoraString();
  mostraNoDisplay();
}

void loop() {
  dataHoraAtual = getDataHoraString();
  int packetSize = LoRa.parsePacket();
  if (packetSize) {
    String pacoteRecebido = "";
    while (LoRa.available()) { pacoteRecebido += (char)LoRa.read(); }

    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, pacoteRecebido);

    ultimoMac = "";
    ultimaDirecao = "";
    ultimaDataVeiculo = "";
    ultimaDataDirecao = "";

    if (!error) {
      // Espera estrutura enviada pela direção
      ultimaDirecao = doc["direcao"] | "";
      ultimaDataDirecao = doc["datahora_direcao"] | "";
      String payload = doc["payload"] | "";
      StaticJsonDocument<128> docPayload;
      DeserializationError err2 = deserializeJson(docPayload, payload);
      if (!err2) {
        ultimoMac = docPayload["mac"] | "";
        ultimaDataVeiculo = docPayload["datahora"] | "";
      }
    }

    if (ultimoMac == "") {
      ultimoStatus = "JSON INVÁLIDO";
      totalNegados++;
      mostraNoDisplay();
      return;
    }

    if (isMacAutorizado(ultimoMac)) {
      ultimoStatus = "AUTORIZADO";
      totalAutorizados++;
      // Aqui pode acionar a cancela!
      LoRa.beginPacket(); LoRa.print("AUTORIZADO"); LoRa.endPacket();
    } else {
      ultimoStatus = "NEGADO";
      totalNegados++;
      LoRa.beginPacket(); LoRa.print("NEGADO"); LoRa.endPacket();
    }
    mostraNoDisplay();
  }
}
