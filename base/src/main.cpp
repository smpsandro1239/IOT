#include <WiFi.h>
#include <time.h>
#include <SPI.h>
#include <LoRa.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>
#include <HTTPClient.h>

// =================================================================================================
// CONFIGURAÇÕES PRINCIPAIS - ALTERE CONFORME SEU AMBIENTE
// =================================================================================================

// Incluir configurações do ficheiro config.h
#include "config.h"

// WiFi - Configurações carregadas de config.h
const char* ssid = WIFI_SSID;
const char* password = WIFI_PASSWORD;

// API Server - Configurações carregadas de config.h
// Valores definidos em config.h (copie de config.h.example)

// Versão do Firmware
#define FIRMWARE_VERSION "2.0.0"

// =================================================================================================
// CONFIGURAÇÕES DE HARDWARE
// =================================================================================================

// Pinos LoRa (ESP32 Heltec V2)
#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26
#define LORA_BAND 433E6

// Display OLED
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET 16
#define OLED_SDA 4
#define OLED_SCL 15

// LEDs para simulação de barreiras
#define LED_BARREIRA_NORTE 25  // LED_BUILTIN
#define LED_BARREIRA_SUL 2     // GPIO 2

// =================================================================================================
// VARIÁVEIS GLOBAIS
// =================================================================================================

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// Status do sistema
String ultimoStatus = "Aguardando...";
String ultimoMac = "";
String ultimoVeiculo = "";
String dataHoraAtual = "Sincronizando...";
unsigned long totalAutorizados = 0;
unsigned long totalNegados = 0;

// Controle de barreiras
enum class EstadoBarreira {
  FECHADA,
  ABERTA,
  ABRINDO,
  FECHANDO
};

EstadoBarreira estadoBarreiraNorte = EstadoBarreira::FECHADA;
EstadoBarreira estadoBarreiraSul = EstadoBarreira::FECHADA;

// Timeouts
#define WIFI_CONNECTION_TIMEOUT_MS 15000
#define HTTP_TIMEOUT_MS 5000
#define BARRIER_AUTO_CLOSE_MS 5000  // 5 segundos

unsigned long barrierCloseTime = 0;

// =================================================================================================
// FUNÇÕES DE INICIALIZAÇÃO
// =================================================================================================

void setup() {
  Serial.begin(115200);
  Serial.println("=== ESP32 Base Station v" + String(FIRMWARE_VERSION) + " ===");
  
  // Inicializar LEDs
  pinMode(LED_BARREIRA_NORTE, OUTPUT);
  pinMode(LED_BARREIRA_SUL, OUTPUT);
  digitalWrite(LED_BARREIRA_NORTE, LOW);
  digitalWrite(LED_BARREIRA_SUL, LOW);
  
  // Inicializar display
  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Erro ao inicializar display OLED");
  } else {
    display.clearDisplay();
    display.setTextSize(1);
    display.setTextColor(SSD1306_WHITE);
    display.setCursor(0, 0);
    display.println("ESP32 Base Station");
    display.println("v" + String(FIRMWARE_VERSION));
    display.println("Inicializando...");
    display.display();
  }
  
  // Conectar WiFi
  conectarWiFi();
  
  // Inicializar LoRa
  inicializarLoRa();
  
  // Sincronizar horário
  sincronizarHorario();
  
  Serial.println("Sistema inicializado com sucesso!");
  atualizarDisplay();
}

void conectarWiFi() {
  Serial.println("Conectando ao WiFi: " + String(ssid));
  
  WiFi.begin(ssid, password);
  
  unsigned long startTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startTime < WIFI_CONNECTION_TIMEOUT_MS) {
    delay(500);
    Serial.print(".");
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println();
    Serial.println("WiFi conectado!");
    Serial.println("IP: " + WiFi.localIP().toString());
  } else {
    Serial.println();
    Serial.println("ERRO: Falha ao conectar WiFi!");
  }
}

void inicializarLoRa() {
  Serial.println("Inicializando LoRa...");
  
  SPI.begin(5, 19, 27, 18);
  LoRa.setPins(LORA_SS, LORA_RST, LORA_DIO0);
  
  if (!LoRa.begin(LORA_BAND)) {
    Serial.println("ERRO: Falha ao inicializar LoRa!");
    return;
  }
  
  // Configurar LoRa
  LoRa.setSpreadingFactor(7);
  LoRa.setSignalBandwidth(125E3);
  LoRa.setCodingRate4(5);
  LoRa.setTxPower(20);
  
  Serial.println("LoRa inicializado com sucesso!");
}

void sincronizarHorario() {
  if (WiFi.status() == WL_CONNECTED) {
    configTime(0, 0, "pool.ntp.org", "time.nist.gov");
    Serial.println("Sincronizando horário...");
    
    time_t now;
    struct tm timeinfo;
    int attempts = 0;
    
    while (!getLocalTime(&timeinfo) && attempts < 10) {
      delay(1000);
      attempts++;
    }
    
    if (attempts < 10) {
      Serial.println("Horário sincronizado!");
    } else {
      Serial.println("Falha ao sincronizar horário");
    }
  }
}

// =================================================================================================
// FUNÇÕES DE COMUNICAÇÃO HTTP
// =================================================================================================

bool verificarAutorizacaoVeiculo(String macAddress) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi desconectado!");
    return false;
  }
  
  HTTPClient http;
  String url = "http://" + String(API_HOST) + ":" + String(API_PORT) + 
               String(API_BASE_PATH) + "/vehicles/authorize.php?mac=" + macAddress;
  
  Serial.println("Verificando autorização: " + url);
  
  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT_MS);
  http.addHeader("Content-Type", "application/json");
  
  int httpResponseCode = http.GET();
  
  if (httpResponseCode == 200) {
    String response = http.getString();
    Serial.println("Resposta API: " + response);
    
    // Parse JSON
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error) {
      bool authorized = doc["authorized"];
      String name = doc["name"] | "Desconhecido";
      String action = doc["action"] | "";
      
      if (authorized && action == "OPEN_BARRIER") {
        ultimoVeiculo = name;
        totalAutorizados++;
        ultimoStatus = "AUTORIZADO";
        http.end();
        return true;
      }
    }
  } else {
    Serial.println("Erro HTTP: " + String(httpResponseCode));
  }
  
  totalNegados++;
  ultimoStatus = "NEGADO";
  http.end();
  return false;
}

void enviarTelemetria(String macAddress, String sensorId, int rssi, float snr) {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String url = "http://" + String(API_HOST) + ":" + String(API_PORT) + 
               String(API_BASE_PATH) + "/telemetry.php";
  
  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT_MS);
  http.addHeader("Content-Type", "application/json");
  
  // Criar JSON
  DynamicJsonDocument doc(1024);
  doc["mac_address"] = macAddress;
  doc["sensor_id"] = sensorId;
  doc["rssi"] = rssi;
  doc["snr"] = snr;
  doc["timestamp"] = obterTimestamp();
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode == 200) {
    Serial.println("Telemetria enviada com sucesso");
  } else {
    Serial.println("Erro ao enviar telemetria: " + String(httpResponseCode));
  }
  
  http.end();
}

void controlarBarreira(String action, String barrierId = "main", int duration = 5) {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String url = "http://" + String(API_HOST) + ":" + String(API_PORT) + 
               String(API_BASE_PATH) + "/barrier/control.php";
  
  http.begin(url);
  http.setTimeout(HTTP_TIMEOUT_MS);
  http.addHeader("Content-Type", "application/json");
  
  // Criar JSON
  DynamicJsonDocument doc(512);
  doc["action"] = action;
  doc["barrier_id"] = barrierId;
  doc["duration"] = duration;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode == 200) {
    Serial.println("Comando de barreira enviado: " + action);
  } else {
    Serial.println("Erro ao controlar barreira: " + String(httpResponseCode));
  }
  
  http.end();
}

// =================================================================================================
// FUNÇÕES DE CONTROLE DE BARREIRA LOCAL
// =================================================================================================

void abrirBarreira() {
  Serial.println("Abrindo barreira...");
  
  // Acender LED (simular abertura)
  digitalWrite(LED_BARREIRA_NORTE, HIGH);
  estadoBarreiraNorte = EstadoBarreira::ABERTA;
  
  // Programar fechamento automático
  barrierCloseTime = millis() + BARRIER_AUTO_CLOSE_MS;
  
  // Notificar servidor
  controlarBarreira("OPEN", "main", BARRIER_AUTO_CLOSE_MS / 1000);
  
  atualizarDisplay();
}

void fecharBarreira() {
  Serial.println("Fechando barreira...");
  
  // Apagar LED (simular fechamento)
  digitalWrite(LED_BARREIRA_NORTE, LOW);
  estadoBarreiraNorte = EstadoBarreira::FECHADA;
  
  // Notificar servidor
  controlarBarreira("CLOSE", "main");
  
  atualizarDisplay();
}

void verificarFechamentoAutomatico() {
  if (estadoBarreiraNorte == EstadoBarreira::ABERTA && 
      barrierCloseTime > 0 && 
      millis() >= barrierCloseTime) {
    fecharBarreira();
    barrierCloseTime = 0;
  }
}

// =================================================================================================
// FUNÇÕES DE COMUNICAÇÃO LORA
// =================================================================================================

void processarPacoteLoRa() {
  int packetSize = LoRa.parsePacket();
  if (packetSize == 0) return;
  
  String receivedData = "";
  while (LoRa.available()) {
    receivedData += (char)LoRa.read();
  }
  
  int rssi = LoRa.packetRssi();
  float snr = LoRa.packetSnr();
  
  Serial.println("Pacote LoRa recebido: " + receivedData);
  Serial.println("RSSI: " + String(rssi) + " dBm, SNR: " + String(snr) + " dB");
  
  // Parse do pacote (formato: MAC:SENSOR_ID ou similar)
  int separatorIndex = receivedData.indexOf(':');
  if (separatorIndex > 0) {
    String macAddress = receivedData.substring(0, separatorIndex);
    String sensorId = receivedData.substring(separatorIndex + 1);
    
    ultimoMac = macAddress;
    
    // Enviar telemetria
    enviarTelemetria(macAddress, sensorId, rssi, snr);
    
    // Verificar autorização
    if (verificarAutorizacaoVeiculo(macAddress)) {
      abrirBarreira();
    }
    
    atualizarDisplay();
  }
}

// =================================================================================================
// FUNÇÕES DE DISPLAY
// =================================================================================================

String obterTimestamp() {
  time_t now;
  struct tm timeinfo;
  
  if (getLocalTime(&timeinfo)) {
    char buffer[20];
    strftime(buffer, sizeof(buffer), "%H:%M:%S", &timeinfo);
    return String(buffer);
  }
  
  return String(millis() / 1000) + "s";
}

void atualizarDisplay() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  
  // Linha 1: Título
  display.setCursor(0, 0);
  display.println("ESP32 Base Station");
  
  // Linha 2: Status WiFi e LoRa
  display.setCursor(0, 10);
  if (WiFi.status() == WL_CONNECTED) {
    display.print("WiFi: OK ");
  } else {
    display.print("WiFi: ERR ");
  }
  display.println("LoRa: OK");
  
  // Linha 3: Horário
  display.setCursor(0, 20);
  display.println("Hora: " + obterTimestamp());
  
  // Linha 4: Último MAC
  display.setCursor(0, 30);
  if (ultimoMac.length() > 0) {
    display.println("MAC: " + ultimoMac.substring(0, 12));
  } else {
    display.println("MAC: Aguardando...");
  }
  
  // Linha 5: Status
  display.setCursor(0, 40);
  display.println("Status: " + ultimoStatus);
  
  // Linha 6: Barreira
  display.setCursor(0, 50);
  if (estadoBarreiraNorte == EstadoBarreira::ABERTA) {
    display.println("Barreira: ABERTA");
  } else {
    display.println("Barreira: FECHADA");
  }
  
  // Linha 7: Contadores
  display.setCursor(0, 58);
  display.println("OK:" + String(totalAutorizados) + " ERR:" + String(totalNegados));
  
  display.display();
}

// =================================================================================================
// LOOP PRINCIPAL
// =================================================================================================

void loop() {
  // Processar pacotes LoRa
  processarPacoteLoRa();
  
  // Verificar fechamento automático da barreira
  verificarFechamentoAutomatico();
  
  // Atualizar display periodicamente
  static unsigned long lastDisplayUpdate = 0;
  if (millis() - lastDisplayUpdate > 1000) {
    dataHoraAtual = obterTimestamp();
    atualizarDisplay();
    lastDisplayUpdate = millis();
  }
  
  // Reconectar WiFi se necessário
  static unsigned long lastWiFiCheck = 0;
  if (millis() - lastWiFiCheck > 30000) {  // Verificar a cada 30 segundos
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("Reconectando WiFi...");
      conectarWiFi();
    }
    lastWiFiCheck = millis();
  }
  
  delay(100);  // Pequeno delay para não sobrecarregar
}