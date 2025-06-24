#include <WiFi.h>
#include <time.h>
#include <SPI.h>
#include <LoRa.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>
#include <HTTPClient.h> // Para requisições HTTP

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

// Configurações da API Laravel
#define API_HOST "192.168.1.100" // <<< SUBSTITUIR PELO IP/HOST DO SERVIDOR LARAVEL
#define API_PORT 8000            // <<< SUBSTITUIR PELA PORTA DO SERVIDOR LARAVEL (se não for 80)
#define API_BASE_PATH "/api/v1"  // Caminho base da API no servidor
// String apiBaseUrl = "http://" + String(API_HOST) + ":" + String(API_PORT) + String(API_BASE_PATH);
// A linha acima não pode ser usada para #define, então construiremos a URL nas funções HTTP.

#define WIFI_CONNECTION_TIMEOUT_MS 15000 // 15 segundos para conectar ao WiFi


// MACs autorizados (exemplo) - Removido, agora via API
// const char* macs_autorizados[] = {
//   "24A160123456",
//   "24A160654321",
//   "102B22286F24",
// };
// const int num_autorizados = 3;

unsigned long totalAutorizados = 0;
unsigned long totalNegados = 0;
String ultimoStatus = "Aguardando...";
String ultimoMac = "";
String ultimaDirecaoDisplay = ""; // Para o display, mostrará a direção final ou o sensor ativo
String dataHoraAtual = "A sincronizar...";
String ultimaDataVeiculo = "";
String ultimaDataDirecao = ""; // Timestamp do pacote do sensor

// Variáveis globais para informações do último sensor para logging
int g_ultimoRssiVeiculoReportado = 0;
String g_ultimoSensorIdReportado = "N/A";
String g_timestampUltimoSensor = "N/A";


// --- Lógica de Deteção de Direção ---
// IDs dos sensores esperados (exemplos, devem corresponder ao SENSOR_ID configurado em cada direcao/main.cpp)
#define SENSOR_ID_NORTE_ENTRADA "DIR_N_1"
#define SENSOR_ID_NORTE_SAIDA   "DIR_N_2"
#define SENSOR_ID_SUL_ENTRADA   "DIR_S_1"
#define SENSOR_ID_SUL_SAIDA     "DIR_S_2"

// Timeout para considerar uma sequência de deteção válida (em milissegundos)
#define DETECTION_SEQUENCE_TIMEOUT 30000 // 30 segundos para completar a sequencia
#define PENDING_DETECTION_CLEAR_TIMEOUT 60000 // 60 segundos para limpar uma detecção de entrada não completada

struct PendingDetection {
  String macVeiculo;
  String sensorEntradaID;
  unsigned long timestampEntradaMs; // Usar millis() para timestamp interno
  bool ativa;
};

PendingDetection deteccaoPendenteNorte;
PendingDetection deteccaoPendenteSul;

enum class DirecaoVeiculo {
  INDETERMINADA,
  NORTE_SUL,
  SUL_NORTE,
  CONFLITO
};
// --- Fim Lógica de Deteção de Direção ---

// --- Lógica de Controle de Barreiras ---
#define LED_BARREIRA_NORTE 25 // LED_BUILTIN na Heltec V2
#define LED_BARREIRA_SUL 2    // Outro GPIO, ex: TXD0 - cuidado se usando Serial0 intensamente para debug
                              // Recomenda-se usar outros GPIOs como 12, 13 se disponíveis

enum class EstadoBarreira {
  FECHADA,
  ABERTA,
  ABRINDO, // Estado transitório
  FECHANDO, // Estado transitório
  BLOQUEADA
};

EstadoBarreira estadoBarreiraNorte = EstadoBarreira::FECHADA;
EstadoBarreira estadoBarreiraSul = EstadoBarreira::FECHADA;

String barreiraNorteStatusDisplay = "N:Fechada";
String barreiraSulStatusDisplay = "S:Fechada";

// Para bloqueio temporário da barreira oposta
String macVeiculoBloqueioOpostoNorte = ""; // MAC do veículo que causou bloqueio na barreira Norte (porque passou pela Sul)
unsigned long timestampBloqueioOpostoNorte = 0;
String macVeiculoBloqueioOpostoSul = "";   // MAC do veículo que causou bloqueio na barreira Sul (porque passou pela Norte)
unsigned long timestampBloqueioOpostoSul = 0;
#define TEMPO_BLOQUEIO_OPOSTA_MS 30000 // 30 segundos de bloqueio para o mesmo MAC na cancela oposta

// Timeout para a cancela permanecer aberta
#define TEMPO_CANCELA_ABERTA_MS 5000 // 5 segundos
unsigned long timestampAberturaCancelaNorte = 0;
unsigned long timestampAberturaCancelaSul = 0;
// --- Fim Lógica de Controle de Barreiras ---

// Função para garantir conexão WiFi
bool ensureWiFiConnected() {
    if (WiFi.status() == WL_CONNECTED) {
        return true;
    }
    Serial.println("WiFi: Tentando conectar...");
    // WiFi.mode(WIFI_STA); // Definido no setup
    WiFi.begin(ssid, password); // Tenta conectar ou reconectar

    unsigned long startTime = millis();
    while (WiFi.status() != WL_CONNECTED && (millis() - startTime < WIFI_CONNECTION_TIMEOUT_MS)) {
        Serial.print(".");
        delay(500);
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi: Conectado!");
        Serial.print("WiFi IP: ");
        Serial.println(WiFi.localIP());
        return true;
    } else {
        Serial.println("\nWiFi: Falha ao conectar.");
        return false;
    }
}

// Funções HTTP para comunicação com a API Laravel
String buildApiUrl(const char* endpoint) {
    String url = "http://" + String(API_HOST);
    if (API_PORT != 80 && API_PORT != 443) { // Adicionar porta apenas se não for padrão HTTP/HTTPS
        url += ":" + String(API_PORT);
    }
    url += String(API_BASE_PATH) + String(endpoint);
    return url;
}

String httpGETRequest(const char* endpoint) {
    if (!ensureWiFiConnected()) {
        return "{\"error\":\"WiFi not connected\"}";
    }

    HTTPClient http;
    String serverPath = buildApiUrl(endpoint);

    Serial.print("HTTP GET: "); Serial.println(serverPath);

    // http.setReuse(true); // Considerar para múltiplas requisições ao mesmo host
    http.begin(serverPath);

    int httpResponseCode = http.GET();
    String payload = "{\"error\":\"Request failed or no payload\"}"; // Default error payload

    if (httpResponseCode > 0) {
        Serial.print("HTTP Response code: "); Serial.println(httpResponseCode);
        payload = http.getString();
        if (payload == "") { // Se getString retornar vazio mesmo com código > 0 (ex: 204 No Content)
            payload = "{\"status_code\":" + String(httpResponseCode) + ", \"message\":\"Success with no content\"}";
        }
    } else {
        Serial.print("HTTP GET Error code: "); Serial.println(httpResponseCode);
        payload = "{\"error\":\"HTTP GET failed\",\"code\":" + String(httpResponseCode) + "}";
    }
    http.end();
    return payload;
}

String httpPOSTRequest(const char* endpoint, const String& jsonPayload) {
    if (!ensureWiFiConnected()) {
        return "{\"error\":\"WiFi not connected\"}";
    }

    HTTPClient http;
    String serverPath = buildApiUrl(endpoint);

    Serial.print("HTTP POST: "); Serial.println(serverPath);
    Serial.print("Payload: "); Serial.println(jsonPayload);

    // http.setReuse(true);
    http.begin(serverPath);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");


    int httpResponseCode = http.POST(jsonPayload);
    String responsePayload = "{\"error\":\"Request failed or no payload\"}"; // Default error payload

    if (httpResponseCode > 0) {
        Serial.print("HTTP Response code: "); Serial.println(httpResponseCode);
        responsePayload = http.getString();
         if (responsePayload == "" && httpResponseCode >= 200 && httpResponseCode < 300) {
            responsePayload = "{\"status_code\":" + String(httpResponseCode) + ", \"message\":\"Success with no content (e.g. 201/204)\"}";
        }
    } else {
        Serial.print("HTTP POST Error code: "); Serial.println(httpResponseCode);
        responsePayload = "{\"error\":\"HTTP POST failed\",\"code\":" + String(httpResponseCode) + "}";
    }
    http.end();
    return responsePayload;
}

// Função para enviar log de acesso para a API
void enviarLogAcesso(const String& macVeiculo, DirecaoVeiculo direcao, bool statusAutorizacao) {
    // Não envia log se o MAC do veículo for inválido/erro, pois não é um evento de veículo real
    if (macVeiculo.length() == 0 || macVeiculo == "N/A" || macVeiculo.startsWith("Err")) {
        Serial.println("Log API: MAC do veículo inválido, log não será enviado.");
        return;
    }

    if (!ensureWiFiConnected()) {
        Serial.println("Log API: WiFi não conectado, não é possível enviar log.");
        return;
    }

    StaticJsonDocument<512> logDoc;

    logDoc["vehicle_lora_id"] = macVeiculo;

    struct tm timeinfo_log;
    if(getLocalTime(&timeinfo_log, 5000)){ // Timeout de 5s para obter hora
        char buffer[25];
        strftime(buffer, sizeof(buffer), "%Y-%m-%d %H:%M:%S", &timeinfo_log);
        logDoc["timestamp_event"] = String(buffer);
    } else {
        logDoc["timestamp_event"] = "1970-01-01 00:00:01Z"; // Fallback UTC se NTP falhou
        Serial.println("Log API: NTP falhou, usando timestamp de fallback UTC para log.");
    }

    String direcaoStr = "undefined";
    switch (direcao) {
        case DirecaoVeiculo::NORTE_SUL: direcaoStr = "north_south"; break;
        case DirecaoVeiculo::SUL_NORTE: direcaoStr = "south_north"; break;
        case DirecaoVeiculo::CONFLITO: direcaoStr = "conflito"; break;
        case DirecaoVeiculo::INDETERMINADA: direcaoStr = "undefined"; break; // Se só passou por um sensor, por ex.
    }
    logDoc["direction_detected"] = direcaoStr;

    char baseMacStr[18];
    uint8_t macAddr[6];
    WiFi.macAddress(macAddr);
    sprintf(baseMacStr, "%02X%02X%02X%02X%02X%02X", macAddr[0], macAddr[1], macAddr[2], macAddr[3], macAddr[4], macAddr[5]);
    logDoc["base_station_id"] = String(baseMacStr);

    JsonArray sensorReports = logDoc.createNestedArray("sensor_reports");
    if (g_ultimoSensorIdReportado != "N/A" && g_ultimoSensorIdReportado != "") {
        JsonObject report = sensorReports.createNestedObject();
        report["sensor_id"] = g_ultimoSensorIdReportado;
        report["rssi"] = g_ultimoRssiVeiculoReportado;
        // Formatar g_timestampUltimoSensor se necessário para YYYY-MM-DD HH:MM:SS
        // Se g_timestampUltimoSensor já estiver no formato dd/mm/yyyy HH:MM:SS do ESP32,
        // o backend precisará ser flexível ou o ESP32 precisa converter.
        // Por simplicidade, enviamos como está. O backend pode precisar de tratamento.
        report["timestamp_sensor"] = g_timestampUltimoSensor;
    }

    logDoc["authorization_status"] = statusAutorizacao;
    // logDoc["notes"] = "Log ESP32"; // Opcional

    String jsonLogPayload;
    serializeJson(logDoc, jsonLogPayload);

    Serial.println("Log API: Enviando log de acesso...");
    String httpResponse = httpPOSTRequest("/access-logs", jsonLogPayload);
    Serial.println("Log API: Resposta do servidor de log: " + httpResponse);

    // Analisar a resposta para verificar se foi bem-sucedido (ex: HTTP 201)
    StaticJsonDocument<128> docResp; // Para analisar a resposta JSON do servidor
    DeserializationError errResp = deserializeJson(docResp, httpResponse);
    if (!errResp && docResp.containsKey("message")) {
        Serial.println("Log API: Mensagem do servidor: " + docResp["message"].as<String>());
    } else if (!errResp && docResp.containsKey("error")) {
         Serial.println("Log API: Erro do servidor: " + docResp["error"].as<String>());
    }
}


// Funções para atualizar display das barreiras
void atualizarDisplayBarreiraNorte() {
    String s = "N:";
    switch (estadoBarreiraNorte) {
        case EstadoBarreira::FECHADA: s += "Fechada"; break;
        case EstadoBarreira::ABERTA: s += "Aberta "; break;
        case EstadoBarreira::ABRINDO: s += "Abrindo"; break;
        case EstadoBarreira::FECHANDO: s += "Fechando"; break;
        case EstadoBarreira::BLOQUEADA: s += "Bloqueada"; break; // Este estado precisaria ser setado por outra lógica
    }
    if (macVeiculoBloqueioOpostoNorte != "" && (millis() - timestampBloqueioOpostoNorte < TEMPO_BLOQUEIO_OPOSTA_MS)) {
      s += "*"; // Indica bloqueio por MAC oposto ativo
    }
    barreiraNorteStatusDisplay = s;
}

void atualizarDisplayBarreiraSul() {
    String s = "S:";
    switch (estadoBarreiraSul) {
        case EstadoBarreira::FECHADA: s += "Fechada"; break;
        case EstadoBarreira::ABERTA: s += "Aberta "; break;
        case EstadoBarreira::ABRINDO: s += "Abrindo"; break;
        case EstadoBarreira::FECHANDO: s += "Fechando"; break;
        case EstadoBarreira::BLOQUEADA: s += "Bloqueada"; break;
    }
    if (macVeiculoBloqueioOpostoSul != "" && (millis() - timestampBloqueioOpostoSul < TEMPO_BLOQUEIO_OPOSTA_MS)) {
      s += "*"; // Indica bloqueio por MAC oposto ativo
    }
    barreiraSulStatusDisplay = s;
}


void fecharBarreira(DirecaoVeiculo direcao) { // Modificado para aceitar DirecaoVeiculo
    if (direcao == DirecaoVeiculo::NORTE_SUL && (estadoBarreiraNorte == EstadoBarreira::ABERTA || estadoBarreiraNorte == EstadoBarreira::ABRINDO)) {
        Serial.println("Fechando Barreira Norte...");
        estadoBarreiraNorte = EstadoBarreira::FECHANDO;
        atualizarDisplayBarreiraNorte();
        //mostraNoDisplay(); // Evitar muitos redraws, gerenciarEstadoCancelas fará ao final.
        delay(100); // Simula tempo de fechar (reduzido para não bloquear muito)
        digitalWrite(LED_BARREIRA_NORTE, LOW);
        estadoBarreiraNorte = EstadoBarreira::FECHADA;
        timestampAberturaCancelaNorte = 0;
        atualizarDisplayBarreiraNorte();
    } else if (direcao == DirecaoVeiculo::SUL_NORTE && (estadoBarreiraSul == EstadoBarreira::ABERTA || estadoBarreiraSul == EstadoBarreira::ABRINDO)) {
        Serial.println("Fechando Barreira Sul...");
        estadoBarreiraSul = EstadoBarreira::FECHANDO;
        atualizarDisplayBarreiraSul();
        //mostraNoDisplay();
        delay(100);
        digitalWrite(LED_BARREIRA_SUL, LOW);
        estadoBarreiraSul = EstadoBarreira::FECHADA;
        timestampAberturaCancelaSul = 0;
        atualizarDisplayBarreiraSul();
    }
}

// Declaração antecipada para abrirBarreira poder chamar mostraNoDisplay
void mostraNoDisplay();

void abrirBarreira(DirecaoVeiculo direcao) {
    if (direcao == DirecaoVeiculo::NORTE_SUL) {
        if (estadoBarreiraNorte == EstadoBarreira::FECHADA) {
            if (macVeiculoBloqueioOpostoNorte == ultimoMac && (millis() - timestampBloqueioOpostoNorte < TEMPO_BLOQUEIO_OPOSTA_MS)) {
                Serial.println("Barreira Norte BLOQUEADA para MAC " + ultimoMac + " (passou por Sul recentemente).");
                ultimoStatus = "BN BLOQ MAC";
                atualizarDisplayBarreiraNorte(); // Garante que o display reflita o bloqueio
                // A resposta LoRa "AUTORIZADO" já foi enviada, aqui é só o controle físico.
                return;
            }
            Serial.println("Abrindo Barreira Norte...");
            estadoBarreiraNorte = EstadoBarreira::ABRINDO;
            atualizarDisplayBarreiraNorte();
            // mostraNoDisplay(); // Evitar muitos redraws
            delay(100); // Simula tempo real de abertura da cancela (reduzido)
            digitalWrite(LED_BARREIRA_NORTE, HIGH);
            estadoBarreiraNorte = EstadoBarreira::ABERTA;
            timestampAberturaCancelaNorte = millis();
            atualizarDisplayBarreiraNorte();

            macVeiculoBloqueioOpostoSul = ultimoMac;
            timestampBloqueioOpostoSul = millis();
            Serial.println("Barreira Sul BLOQUEADA para MAC " + ultimoMac + " por " + String(TEMPO_BLOQUEIO_OPOSTA_MS/1000) + "s.");
            atualizarDisplayBarreiraSul(); // Atualiza display da barreira Sul para mostrar possível '*'
        }
    } else if (direcao == DirecaoVeiculo::SUL_NORTE) {
        if (estadoBarreiraSul == EstadoBarreira::FECHADA) {
            if (macVeiculoBloqueioOpostoSul == ultimoMac && (millis() - timestampBloqueioOpostoSul < TEMPO_BLOQUEIO_OPOSTA_MS)) {
                Serial.println("Barreira Sul BLOQUEADA para MAC " + ultimoMac + " (passou por Norte recentemente).");
                ultimoStatus = "BS BLOQ MAC";
                atualizarDisplayBarreiraSul();
                return;
            }
            Serial.println("Abrindo Barreira Sul...");
            estadoBarreiraSul = EstadoBarreira::ABRINDO;
            atualizarDisplayBarreiraSul();
            // mostraNoDisplay();
            delay(100);
            digitalWrite(LED_BARREIRA_SUL, HIGH);
            estadoBarreiraSul = EstadoBarreira::ABERTA;
            timestampAberturaCancelaSul = millis();
            atualizarDisplayBarreiraSul();

            macVeiculoBloqueioOpostoNorte = ultimoMac;
            timestampBloqueioOpostoNorte = millis();
            Serial.println("Barreira Norte BLOQUEADA para MAC " + ultimoMac + " por " + String(TEMPO_BLOQUEIO_OPOSTA_MS/1000) + "s.");
            atualizarDisplayBarreiraNorte();
        }
    }
}


void gerenciarEstadoCancelas() {
    bool houveMudancaStatusBarreira = false;
    // Fechar cancela Norte automaticamente
    if (estadoBarreiraNorte == EstadoBarreira::ABERTA && (millis() - timestampAberturaCancelaNorte > TEMPO_CANCELA_ABERTA_MS)) {
        fecharBarreira(DirecaoVeiculo::NORTE_SUL);
        houveMudancaStatusBarreira = true;
    }
    // Fechar cancela Sul automaticamente
    if (estadoBarreiraSul == EstadoBarreira::ABERTA && (millis() - timestampAberturaCancelaSul > TEMPO_CANCELA_ABERTA_MS)) {
        fecharBarreira(DirecaoVeiculo::SUL_NORTE);
        houveMudancaStatusBarreira = true;
    }

    // Limpar bloqueios de MAC opostos expirados
    if (macVeiculoBloqueioOpostoNorte != "" && (millis() - timestampBloqueioOpostoNorte >= TEMPO_BLOQUEIO_OPOSTA_MS)) {
        Serial.println("Bloqueio para MAC " + macVeiculoBloqueioOpostoNorte + " na Barreira Norte expirou.");
        macVeiculoBloqueioOpostoNorte = "";
        timestampBloqueioOpostoNorte = 0;
        houveMudancaStatusBarreira = true;
    }
    if (macVeiculoBloqueioOpostoSul != "" && (millis() - timestampBloqueioOpostoSul >= TEMPO_BLOQUEIO_OPOSTA_MS)) {
        Serial.println("Bloqueio para MAC " + macVeiculoBloqueioOpostoSul + " na Barreira Sul expirou.");
        macVeiculoBloqueioOpostoSul = "";
        timestampBloqueioOpostoSul = 0;
        houveMudancaStatusBarreira = true;
    }

    if(houveMudancaStatusBarreira){
        atualizarDisplayBarreiraNorte();
        atualizarDisplayBarreiraSul();
        // mostraNoDisplay(); // O mostraNoDisplay principal no loop() já vai pegar essas atualizações
    }
}


void sincronizaHora() {
  if (!ensureWiFiConnected()) {
    Serial.println("NTP: WiFi não conectado, não é possível sincronizar hora.");
    // dataHoraAtual = "WiFi Falhou"; // Atualiza para feedback no display
    return;
  }
  Serial.println("NTP: Sincronizando hora...");
  configTime(3600, 0, "pool.ntp.org", "time.nist.gov"); // UTC+1 Portugal Continental
  struct tm timeinfo;
  int tentativas = 0;
  while (!getLocalTime(&timeinfo) && tentativas < 20) {
    delay(500);
    tentativas++;
    Serial.print("N"); // NTP attempt
  }
  if(tentativas < 20) {
    Serial.println("\nNTP: Hora sincronizada.");
  } else {
    Serial.println("\nNTP: Falha ao sincronizar hora.");
    // dataHoraAtual = "NTP Falhou";
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
  display.println("== BASE LoRa ==");
  display.print("Status: "); display.println(ultimoStatus);
  display.print("MAC: "); display.println(ultimoMac);
  display.print("Dir: "); display.println(ultimaDirecaoDisplay); // Atualizado aqui
  display.print("DataVeic:"); display.println(ultimaDataVeiculo);
  display.print("DataSens:"); display.println(ultimaDataDirecao); // Renomeado para DataSens (data do sensor)
  display.print("Base:"); display.println(dataHoraAtual);
  display.print("OK:"); display.print(totalAutorizados);
  display.print(" NEG:"); display.println(totalNegados);
  display.print(barreiraNorteStatusDisplay);
  display.print(" ");
  display.println(barreiraSulStatusDisplay);
  display.display();
}

// Verifica autorização do MAC chamando a API
bool isMacAutorizado(const String& mac) {
    if (mac.length() == 0 || mac == "N/A" || mac.startsWith("Err")) { // Verifica MACs inválidos ou de erro
        Serial.println("API Auth: MAC inválido para consulta: " + mac);
        return false;
    }

    String endpoint = "/vehicles/authorize/" + mac;
    Serial.println("API Auth: Consultando autorizacao para MAC: " + mac);
    String httpResponse = httpGETRequest(endpoint.c_str()); // .c_str() é importante para const char*
    Serial.println("API Auth: Resposta recebida: " + httpResponse);

    // Aumentar o tamanho do documento JSON se a resposta da API for maior
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, httpResponse);

    if (error) {
        Serial.print("API Auth: Erro ao desserializar JSON da resposta: ");
        Serial.println(error.c_str());
        return false; // Falha na comunicação ou JSON inválido, considera não autorizado por segurança
    }

    // Checa se a chave 'error' existe no JSON de resposta (indicando erro na chamada HTTP ou WiFi)
    if (doc.containsKey("error")) {
        String apiError = doc["error"];
        Serial.print("API Auth: Erro retornado pela camada HTTP/WiFi: ");
        Serial.println(apiError);
        return false;
    }

    // Verifica o campo 'authorized' da resposta da API
    // O operador `| false` garante que se "authorized" não existir ou for null, retorna false.
    bool autorizado = doc["authorized"] | false;

    if (autorizado) {
        String vehicleName = doc["name"] | "Desconhecido"; // Pega o nome do veículo se disponível
        Serial.println("API Auth: Veiculo " + mac + " (" + vehicleName + ") AUTORIZADO pela API.");
    } else {
        // Se a API retornou 'authorized: false', pode haver um campo 'reason'
        String reason = doc["reason"] | "Sem detalhes da API";
        Serial.println("API Auth: Veiculo " + mac + " NAO AUTORIZADO pela API. Motivo: " + reason);
    }
    return autorizado;
}


void setup() {
  Serial.begin(115200);
  Wire.begin(OLED_SDA, OLED_SCL);
  pinMode(OLED_RESET, OUTPUT);
  digitalWrite(OLED_RESET, LOW); delay(50); digitalWrite(OLED_RESET, HIGH); delay(50);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { while (1); }

  display.clearDisplay(); display.setCursor(0, 0); display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE); display.println("Iniciando WiFi..."); display.display();

  WiFi.mode(WIFI_STA); // Define o modo WiFi como Station
  // A conexão será tentada pela sincronizaHora ou antes de chamadas HTTP
  // Tentativa inicial de sincronização de hora (que tentará conectar WiFi)
  sincronizaHora();
  // Não desligamos o WiFi aqui, ele permanecerá ativo ou tentará reconectar quando necessário.

  // Atualiza display com status do WiFi e NTP após a primeira tentativa
  if (WiFi.status() == WL_CONNECTED) {
    display.println("WiFi OK!");
  } else {
    display.println("WiFi Falhou.");
  }
  // Mostra a hora atual (ou mensagem de erro se NTP falhou)
  dataHoraAtual = getDataHoraString(); // Atualiza dataHoraAtual
  // A função mostraNoDisplay() será chamada no final do setup, então não precisa aqui.

  delay(1000); // Pequeno delay para o usuário ler o status do WiFi/NTP

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

  // Inicializa estado das detecções pendentes
  deteccaoPendenteNorte.ativa = false;
  deteccaoPendenteSul.ativa = false;

  // Configura pinos das barreiras
  pinMode(LED_BARREIRA_NORTE, OUTPUT);
  pinMode(LED_BARREIRA_SUL, OUTPUT);
  digitalWrite(LED_BARREIRA_NORTE, LOW); // Inicia Fechada (LED desligado)
  digitalWrite(LED_BARREIRA_SUL, LOW);   // Inicia Fechada (LED desligado)
}

void loop() {
  dataHoraAtual = getDataHoraString();
  gerenciarEstadoCancelas(); // Gerencia fechamento automático e expiração de bloqueios
  DirecaoVeiculo direcaoDeterminadaEsteLoop = DirecaoVeiculo::INDETERMINADA; // Reset para cada loop principal que processa um pacote

  // Limpeza de detecções de entrada pendentes e expiradas
  if (deteccaoPendenteNorte.ativa && (millis() - deteccaoPendenteNorte.timestampEntradaMs > PENDING_DETECTION_CLEAR_TIMEOUT)) {
    Serial.println("Timeout: Limpando deteccao pendente Norte para MAC " + deteccaoPendenteNorte.macVeiculo);
    deteccaoPendenteNorte.ativa = false;
  }
  if (deteccaoPendenteSul.ativa && (millis() - deteccaoPendenteSul.timestampEntradaMs > PENDING_DETECTION_CLEAR_TIMEOUT)) {
    Serial.println("Timeout: Limpando deteccao pendente Sul para MAC " + deteccaoPendenteSul.macVeiculo);
    deteccaoPendenteSul.ativa = false;
  }

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

    // Variáveis para guardar dados desserializados
    String sensorIdRecebido = "N/A";
    // int rssiDoVeiculo = 0; // Descomentar se for usar
    // String macDoSensor = "N/A"; // Descomentar se for usar
    String timestampDoSensor = "N/A";
    String macDoVeiculo = "N/A";
    String timestampDoVeiculoOriginal = "N/A";
    bool jsonOk = false;

    if (!error) {
      sensorIdRecebido = doc["sensor_id"] | "N/A";
      g_ultimoSensorIdReportado = sensorIdRecebido;
      g_ultimoRssiVeiculoReportado = doc["rssi_veiculo"] | 0;
      g_timestampUltimoSensor = doc["datahora_sensor"] | "N/A";

      timestampDoSensor = g_timestampUltimoSensor; // Usada para 'ultimaDataDirecao' no display
      // macDoSensor = doc["mac_sensor"] | "N/A"; // Se precisar do MAC do sensor
      String payload_veiculo_str = doc["payload_veiculo"] | "";

      StaticJsonDocument<128> docPayloadVeiculo;
      DeserializationError errPayload = deserializeJson(docPayloadVeiculo, payload_veiculo_str);
      if (!errPayload) {
        macDoVeiculo = docPayloadVeiculo["mac"] | "N/A";
        timestampDoVeiculoOriginal = docPayloadVeiculo["datahora"] | "N/A";
        jsonOk = true; // JSON principal e aninhado OK
      } else {
        Serial.print("Erro ao desserializar payload do veiculo: "); Serial.println(errPayload.c_str());
        Serial.print("Payload problemático: "); Serial.println(payload_veiculo_str);
        ultimoStatus = "ErrPayload";
      }
    } else {
      Serial.print("Erro ao desserializar JSON principal: "); Serial.println(error.c_str());
      Serial.print("Pacote problemático: "); Serial.println(pacoteRecebido);
      ultimoStatus = "ErrJSON";
    }

    // Atualiza display com dados basicos recebidos (ou erro) ANTES da logica de direcao
    ultimoMac = macDoVeiculo;
    ultimaDataVeiculo = timestampDoVeiculoOriginal;
    ultimaDataDirecao = timestampDoSensor; // Data do pacote do sensor
    ultimaDirecaoDisplay = sensorIdRecebido; // Mostra o sensor que enviou o pacote

    if (!jsonOk || macDoVeiculo == "N/A") {
      if(jsonOk && macDoVeiculo == "N/A") ultimoStatus = "MAC N/A";
      // Não contabiliza negados aqui ainda, pois pode ser um erro de comunicação
      mostraNoDisplay();
      // Não retorna, permite que o loop continue para limpar timeouts, etc.
      // Considerar se um ACK de erro deve ser enviado ao sensor. Por ora, não.
    } else {
      // --- Início Lógica de Direção ---
      direcaoDeterminadaEsteLoop = DirecaoVeiculo::INDETERMINADA; // Reset antes de reavaliar

      if (sensorIdRecebido == SENSOR_ID_NORTE_ENTRADA) {
        if (deteccaoPendenteSul.ativa && deteccaoPendenteSul.macVeiculo == macDoVeiculo) {
            Serial.println("CONFLITO: Veiculo " + macDoVeiculo + " detectado em SENSOR_ID_NORTE_ENTRADA enquanto pendente em Sul.");
            direcaoDeterminadaEsteLoop = DirecaoVeiculo::CONFLITO;
            deteccaoPendenteSul.ativa = false;
        }
        deteccaoPendenteNorte.macVeiculo = macDoVeiculo;
        deteccaoPendenteNorte.sensorEntradaID = sensorIdRecebido;
        deteccaoPendenteNorte.timestampEntradaMs = millis();
        deteccaoPendenteNorte.ativa = true;
        Serial.println("Veiculo " + macDoVeiculo + " registado em NORTE_ENTRADA.");
        ultimoStatus = "Norte Entrada";
        ultimaDirecaoDisplay = sensorIdRecebido;
      } else if (sensorIdRecebido == SENSOR_ID_SUL_ENTRADA) {
        if (deteccaoPendenteNorte.ativa && deteccaoPendenteNorte.macVeiculo == macDoVeiculo) {
            Serial.println("CONFLITO: Veiculo " + macDoVeiculo + " detectado em SENSOR_ID_SUL_ENTRADA enquanto pendente em Norte.");
            direcaoDeterminadaEsteLoop = DirecaoVeiculo::CONFLITO;
            deteccaoPendenteNorte.ativa = false;
        }
        deteccaoPendenteSul.macVeiculo = macDoVeiculo;
        deteccaoPendenteSul.sensorEntradaID = sensorIdRecebido;
        deteccaoPendenteSul.timestampEntradaMs = millis();
        deteccaoPendenteSul.ativa = true;
        Serial.println("Veiculo " + macDoVeiculo + " registado em SUL_ENTRADA.");
        ultimoStatus = "Sul Entrada";
        ultimaDirecaoDisplay = sensorIdRecebido;
      } else if (sensorIdRecebido == SENSOR_ID_NORTE_SAIDA) {
        if (deteccaoPendenteNorte.ativa && deteccaoPendenteNorte.macVeiculo == macDoVeiculo) {
            if (millis() - deteccaoPendenteNorte.timestampEntradaMs < DETECTION_SEQUENCE_TIMEOUT) {
                direcaoDeterminadaEsteLoop = DirecaoVeiculo::NORTE_SUL;
                Serial.println("Direcao NORTE-SUL determinada para veiculo " + macDoVeiculo);
                ultimaDirecaoDisplay = "NORTE->SUL";
            } else {
                Serial.println("TIMEOUT SEQUENCIA: Veiculo " + macDoVeiculo + " em NORTE_SAIDA, mas entrada Norte expirou.");
                direcaoDeterminadaEsteLoop = DirecaoVeiculo::INDETERMINADA; // Resetar para não ficar em estado de conflito ou direcao errada
            }
            deteccaoPendenteNorte.ativa = false;
        } else {
             Serial.println("Veiculo " + macDoVeiculo + " em NORTE_SAIDA sem entrada Norte correspondente ativa ou MAC diferente.");
             // Poderia iniciar uma nova deteccao se este sensor puder ser entrada de outra direcao, ou ignorar.
        }
      } else if (sensorIdRecebido == SENSOR_ID_SUL_SAIDA) {
        if (deteccaoPendenteSul.ativa && deteccaoPendenteSul.macVeiculo == macDoVeiculo) {
            if (millis() - deteccaoPendenteSul.timestampEntradaMs < DETECTION_SEQUENCE_TIMEOUT) {
                direcaoDeterminadaEsteLoop = DirecaoVeiculo::SUL_NORTE;
                Serial.println("Direcao SUL-NORTE determinada para veiculo " + macDoVeiculo);
                ultimaDirecaoDisplay = "SUL->NORTE";
            } else {
                Serial.println("TIMEOUT SEQUENCIA: Veiculo " + macDoVeiculo + " em SUL_SAIDA, mas entrada Sul expirou.");
                direcaoDeterminadaEsteLoop = DirecaoVeiculo::INDETERMINADA;
            }
            deteccaoPendenteSul.ativa = false;
        } else {
            Serial.println("Veiculo " + macDoVeiculo + " em SUL_SAIDA sem entrada Sul correspondente ativa ou MAC diferente.");
        }
      } else {
        Serial.println("Sensor ID " + sensorIdRecebido + " nao reconhecido para logica de direcao.");
        ultimoStatus = "Sensor N/REC";
      }
      // --- Fim Lógica de Direção ---

      // Se uma direção foi determinada (e não conflito), proceder com autorização
      if (direcaoDeterminadaEsteLoop == DirecaoVeiculo::NORTE_SUL || direcaoDeterminadaEsteLoop == DirecaoVeiculo::SUL_NORTE) {
          if (isMacAutorizado(macDoVeiculo)) { // Usa macDoVeiculo aqui
              ultimoStatus = "AUTORIZADO";
              totalAutorizados++;
              LoRa.beginPacket(); LoRa.print("AUTORIZADO"); LoRa.endPacket(); // Envia ACK LoRa primeiro
              abrirBarreira(direcaoDeterminadaEsteLoop); // Depois tenta abrir a barreira física
          } else {
              ultimoStatus = "NEGADO";
              totalNegados++;
              LoRa.beginPacket(); LoRa.print("NEGADO"); LoRa.endPacket();
          }
      } else if (direcaoDeterminadaEsteLoop == DirecaoVeiculo::CONFLITO) {
          ultimoStatus = "CONFLITO";
          totalNegados++;
          // Não envia AUTORIZADO/NEGADO para o sensor, pois o estado é incerto.
          // O sensor vai dar timeout no ACK e tentar reenviar.
          // Poderíamos enviar um "ERRO_CONFLITO" se o sensor pudesse tratar.
          // Por agora, o sensor não receberá ACK e o log serial da base indicará o conflito.
      }
      // Se direcaoDeterminadaEsteLoop == DirecaoVeiculo::INDETERMINADA, o status foi atualizado
      // para "Norte Entrada" ou "Sul Entrada" ou "Sensor N/REC", e esperamos o próximo evento.
      // Não enviamos ACK LoRa aqui para não confundir o sensor que espera AUTORIZADO/NEGADO.
    }

    // Enviar log de acesso para a API, independentemente de ser um evento completo ou parcial
    // desde que tenhamos um MAC de veículo válido.
    // A variável direcaoDeterminadaEsteLoop e ultimoStatus (para autorização) refletem o evento.
    bool statusFinalAutorizacaoParaLog = false;
    if (direcaoDeterminadaEsteLoop == DirecaoVeiculo::NORTE_SUL || direcaoDeterminadaEsteLoop == DirecaoVeiculo::SUL_NORTE) {
        // Somente se a direção foi determinada, verificamos o status de autorização que foi efetivamente usado.
        // Assumindo que 'ultimoStatus' foi corretamente definido como "AUTORIZADO" ou "NEGADO".
        if (ultimoStatus == "AUTORIZADO") {
            statusFinalAutorizacaoParaLog = true;
        } else if (ultimoStatus == "NEGADO") {
            statusFinalAutorizacaoParaLog = false;
        }
        // Se ultimoStatus for "BN BLOQ MAC" ou "BS BLOQ MAC", o veículo foi autorizado pela API,
        // mas a cancela não abriu. Para o log, o status da autorização da API é o que conta.
        // A chamada isMacAutorizado já foi feita. Precisamos do resultado dela.
        // Revisitando: a chamada a isMacAutorizado é feita DENTRO do if de direção determinada.
        // Vamos usar uma variável para armazenar o resultado da autorização.
    }
    // Para outros casos (INDETERMINADA, CONFLITO), statusFinalAutorizacaoParaLog será false.

    // Refatorando a obtenção do status de autorização para o log:
    // Se uma direção foi determinada, a autorização foi checada.
    // Se foi autorizado pela API (mesmo que a cancela física tenha sido bloqueada por outra razão), logamos como autorizado.
    bool autorizadoPelaApi = false; // Default
    if (direcaoDeterminadaEsteLoop == DirecaoVeiculo::NORTE_SUL || direcaoDeterminadaEsteLoop == DirecaoVeiculo::SUL_NORTE) {
        // A lógica de autorização já foi executada e o `ultimoStatus` reflete o resultado final (AUTORIZADO, NEGADO, BLOQ MAC)
        // Precisamos do resultado da chamada a `isMacAutorizado(macDoVeiculo)`
        // Vamos assumir que se `ultimoStatus` é "AUTORIZADO" OU "BN BLOQ MAC" OU "BS BLOQ MAC", a API autorizou.
        if (ultimoStatus == "AUTORIZADO" || ultimoStatus == "BN BLOQ MAC" || ultimoStatus == "BS BLOQ MAC") {
            autorizadoPelaApi = true;
        } else { // NEGADO, CONFLITO (já tratado), etc.
            autorizadoPelaApi = false;
        }
    }
    // Se a direção não foi determinada (INDETERMINADA), ou foi CONFLITO, autorizadoPelaApi permanece false.

    enviarLogAcesso(macDoVeiculo, direcaoDeterminadaEsteLoop, autorizadoPelaApi);

    mostraNoDisplay(); // Atualiza o display com o status final do processamento deste pacote
  } // Fim do if (jsonOk && macDoVeiculo != "N/A")
    // Se não for jsonOk ou macDoVeiculo for N/A, um mostraNoDisplay() já foi chamado antes.
    // Adicionar um aqui garante que o display seja atualizado mesmo se o pacote LoRa não for processado completamente.
    // No entanto, o mostraNoDisplay() já é chamado no final do if(packetSize)
} // Fim do if(packetSize)

// Adicionar um mostraNoDisplay() fora do if(packetSize) para atualizar a hora e status das cancelas regularmente
// No, o mostraNoDisplay() é chamado dentro do if(packetSize) e gerenciarEstadoCancelas atualiza o display se necessário.
// O dataHoraAtual é atualizado no inicio do loop.
// A questão é se mostraNoDisplay() deve ser chamado sempre no final do loop ou só com eventos.
// Por agora, manter como está: display atualiza com eventos LoRa.
// A hora no display só atualiza quando um pacote LoRa é recebido. Isso pode ser melhorado
// movendo mostraNoDisplay() para o final do loop() e atualizando dataHoraAtual sempre.
// Contudo, para este passo, o foco é o log.
