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

// =================================================================================================
// == CONFIGURAÇÕES PRINCIPAIS DO SENSOR DE DIREÇÃO ==
// =================================================================================================
// ATENÇÃO: Revise e configure estas definições antes de compilar e carregar o firmware.

// -- ID Único do Sensor --
// Este ID deve ser único para cada dispositivo sensor e corresponder
// a uma das definições SENSOR_ID_*_ENTRADA/SAIDA no firmware da Placa Base.
// Exemplos: "DIR_N_1", "DIR_N_2", "DIR_S_1", "DIR_S_2"
const char* SENSOR_ID = "DIR_DEFAULT_01";

// -- Configuração de Direção (Opcional/Informativo) --
// Usado para display local, não essencial para a lógica da Placa Base se SENSOR_ID for único.
const char* direcao_config = "NS"; // Ex: "NS" (Norte-Sul), "SN" (Sul-Norte), "PONTO_A", etc.

// -- Credenciais WiFi (usadas apenas para sincronização NTP no setup) --
const char* ssid = "50N43Everywere 3"; // Substitua pela sua rede WiFi
const char* password = "Benfica456+++++";    // Substitua pela sua senha WiFi
// =================================================================================================


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
  display.println("== SENSOR LoRa =="); // Renomeado para Sensor
  display.print("MAC: "); display.println(chipid_str);
  display.print("ID: "); display.println(SENSOR_ID); // Mostrar SENSOR_ID
  display.print("Cfg: "); display.println(direcao_config); // Mostrar config original
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
      int rssiVeiculo = LoRa.packetRssi(); // Captura RSSI do veículo
      contadorRecebidos++;
      dataHoraAtual = getDataHoraString();

      // Monta pacote para a base com novo formato
      StaticJsonDocument<384> doc; // Aumentar um pouco o tamanho para os novos campos
      doc["sensor_id"] = SENSOR_ID;
      doc["rssi_veiculo"] = rssiVeiculo;
      doc["mac_sensor"] = chipid_str;
      doc["datahora_sensor"] = dataHoraAtual;
      doc["payload_veiculo"] = pacoteRecebido; // Este é o JSON do veículo como string
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
      // Alinhado com as respostas da base: "AUTORIZADO" ou "NEGADO" são considerados ACKs
      if (resposta.indexOf("AUTORIZADO") >= 0 || resposta.indexOf("NEGADO") >= 0) {
        ultimaResposta = "Resp. base: " + resposta; // Mostrar a resposta real
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
