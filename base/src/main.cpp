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
String ultimaDirecaoDisplay = ""; // Para o display, mostrará a direção final ou o sensor ativo
String dataHoraAtual = "A sincronizar...";
String ultimaDataVeiculo = "";
String ultimaDataDirecao = ""; // Timestamp do pacote do sensor

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
      // rssiDoVeiculo = doc["rssi_veiculo"] | 0;
      // macDoSensor = doc["mac_sensor"] | "N/A";
      timestampDoSensor = doc["datahora_sensor"] | "N/A";
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
    mostraNoDisplay(); // Atualiza o display com o status final do processamento deste pacote
  }
}
