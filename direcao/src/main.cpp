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
const char* direcao = "NS"; // "NS" ou "SN" conforme a placa

char chipid_str[13];
unsigned long contadorRecebidos = 0;
unsigned long contadorEnvios = 0;
unsigned long tentativasEnvio = 0;
String ultimoPacote = "";
String ultimaResposta = "Aguardando...";
String dataHoraAtual = "A sincronizar...";

void getChipID(char* out) {
  uint64_t chipid = ESP.getEfuseMac();
  sprintf(out, "%04X%08X", (uint16_t)(chipid>>32), (uint32_t)chipid);
}

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
  display.println("== DIRECAO LoRa ==");
  display.print("MAC: "); display.println(chipid_str);
  display.print("Dir: "); display.println(direcao);
  display.print("Recebidos: "); display.println(contadorRecebidos);
  display.print("Envios base: "); display.println(contadorEnvios);
  display.print("Tentativas: "); display.println(tentativasEnvio);
  display.print("Data/Hora:"); display.println(dataHoraAtual);
  display.print("Ult. resposta:"); display.println(ultimaResposta);
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
  static String pacoteSalvo = "";
  static unsigned long tempoInicio = 0;
  static bool esperandoACK = false;
  static unsigned long ultimoEnvio = 0;
  const unsigned long timeoutACK = 300000; // 30 segundos
  const unsigned long intervaloReenvio = 1000; // 1 segundo

  if (!esperandoACK) {
    int packetSize = LoRa.parsePacket();
    if (packetSize) {
      String pacoteRecebido = "";
      while (LoRa.available()) { pacoteRecebido += (char)LoRa.read(); }
      contadorRecebidos++;
      dataHoraAtual = getDataHoraString();

      // Monta pacote para a base
      StaticJsonDocument<256> doc;
      doc["direcao"] = direcao;
      doc["mac_direcao"] = chipid_str;
      doc["datahora_direcao"] = dataHoraAtual;
      doc["payload"] = pacoteRecebido;
      String pacote;
      serializeJson(doc, pacote);

      pacoteSalvo = pacote;
      ultimoPacote = pacote;
      esperandoACK = true;
      tempoInicio = millis();
      ultimoEnvio = millis();
      tentativasEnvio = 0;
      ultimaResposta = "A enviar para base...";
      mostraNoDisplay();
      Serial.print("Pacote para base: "); Serial.println(pacoteSalvo);
      delay(100);
    }
  } else {
    // Esperando ACK da base
    if (LoRa.parsePacket()) {
      String resposta = "";
      while (LoRa.available()) { resposta += (char)LoRa.read(); }
      if (resposta.indexOf("RECEBIDO") >= 0 || resposta.indexOf("OK") >= 0) {
        ultimaResposta = "ACK recebido da base";
        esperandoACK = false;
        pacoteSalvo = "";
        tentativasEnvio = 0;
        contadorEnvios++;
        mostraNoDisplay();
        Serial.println("ACK recebido da base, pronto para novo pacote.");
        delay(100);
        return;
      }
    }
    // Reenvia se passou intervalo
    if (millis() - ultimoEnvio >= intervaloReenvio) {
      LoRa.beginPacket(); LoRa.print(pacoteSalvo); LoRa.endPacket();
      ultimoEnvio = millis();
      tentativasEnvio++;
      ultimaResposta = "A reenviar para base...";
      mostraNoDisplay();
      Serial.print("Reenviando para base (tentativa ");
      Serial.print(tentativasEnvio);
      Serial.println(")");
    }
    // Timeout
    if (millis() - tempoInicio >= timeoutACK) {
      ultimaResposta = "Timeout ACK, novo pacote";
      esperandoACK = false;
      pacoteSalvo = "";
      mostraNoDisplay();
      Serial.println("Timeout sem ACK, pronto para novo pacote.");
    }
  }
}
