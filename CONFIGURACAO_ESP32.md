# 🔧 Configuração ESP32 para Comunicação com a Plataforma

## 📋 Pré-requisitos

### Hardware Necessário
- **ESP32 Heltec WiFi LoRa 32 V2** (ou compatível)
- **Antenas LoRa** (433MHz)
- **Display OLED** (já integrado na Heltec)
- **Cabo USB** para programação

### Software Necessário
- **Arduino IDE 2.0+**
- **Bibliotecas ESP32**
- **PlatformIO** (opcional, recomendado)

## 🛠️ Instalação das Bibliotecas

### Arduino IDE
```cpp
// 1. File > Preferences
// 2. Additional Board Manager URLs:
https://dl.espressif.com/dl/package_esp32_index.json

// 3. Tools > Board > Boards Manager
// 4. Pesquisar "ESP32" e instalar

// 5. Tools > Manage Libraries
// 6. Instalar as seguintes bibliotecas:
```

**Bibliotecas Obrigatórias:**
- `LoRa` by Sandeep Mistry
- `ArduinoJson` by Benoit Blanchon
- `Adafruit SSD1306` by Adafruit
- `Adafruit GFX Library` by Adafruit
- `WiFi` (já incluída no ESP32)
- `HTTPClient` (já incluída no ESP32)

## ⚙️ Configuração do Firmware

### 1. Configurações de Rede

Edite o arquivo `base/src/main.cpp`:

```cpp
// WiFi - CONFIGURE SUAS CREDENCIAIS
const char* ssid = "SUA_REDE_WIFI";
const char* password = "SUA_SENHA_WIFI";

// API - CONFIGURE O IP DO SEU SERVIDOR
#define API_HOST "192.168.1.100"  // <<<< ALTERE PARA SEU IP
#define API_PORT 8000             // Porta do servidor
```

### 2. Encontrar o IP do Servidor

#### Windows:
```batch
ipconfig
```

#### Linux/macOS:
```bash
ifconfig
# ou
ip addr show
```

**Exemplo de configuração:**
```cpp
// Se seu IP for 192.168.1.150
#define API_HOST "192.168.1.150"
#define API_PORT 8000
```

### 3. Configurações LoRa

```cpp
// LoRa - Configurações padrão (não alterar sem necessidade)
#define LORA_FREQUENCY 433E6      // 433MHz
#define LORA_BANDWIDTH 125E3      // 125kHz
#define LORA_SPREADING_FACTOR 7   // SF7
#define LORA_CODING_RATE 5        // 4/5
#define LORA_TX_POWER 20          // 20dBm (máximo)
```

## 📡 Endpoints da API Disponíveis

### 1. Autorização de Veículos
```
GET http://SEU_IP:8000/api/v1/vehicles/authorize.php?mac=AA:BB:CC:DD:EE:FF
```

**Resposta de Sucesso:**
```json
{
  "authorized": true,
  "mac": "AA:BB:CC:DD:EE:FF",
  "name": "Veículo Teste",
  "action": "OPEN_BARRIER",
  "timestamp": "2025-01-24T10:30:00+00:00"
}
```

**Resposta de Negação:**
```json
{
  "authorized": false,
  "mac": "AA:BB:CC:DD:EE:FF",
  "reason": "MAC não autorizado",
  "action": "DENY_ACCESS",
  "timestamp": "2025-01-24T10:30:00+00:00"
}
```

### 2. Telemetria
```
POST http://SEU_IP:8000/api/v1/telemetry.php
Content-Type: application/json

{
  "mac_address": "AA:BB:CC:DD:EE:FF",
  "sensor_id": "BASE_001",
  "rssi": -45,
  "snr": 8.5,
  "direction": "NS",
  "distance": 500
}
```

### 3. Controle de Barreiras
```
POST http://SEU_IP:8000/api/v1/barrier/control.php
Content-Type: application/json

{
  "action": "OPEN",
  "barrier_id": "main",
  "duration": 5
}
```

## 🔧 Configuração Completa do ESP32

### Arquivo: `base/src/config.h`

Crie este arquivo com suas configurações:

```cpp
#ifndef CONFIG_H
#define CONFIG_H

// =================================================================================================
// CONFIGURAÇÕES PRINCIPAIS - ALTERE CONFORME SEU AMBIENTE
// =================================================================================================

// WiFi
#define WIFI_SSID "SUA_REDE_WIFI"
#define WIFI_PASSWORD "SUA_SENHA_WIFI"
#define WIFI_TIMEOUT_MS 15000

// API Server
#define API_HOST "192.168.1.100"  // <<<< ALTERE PARA SEU IP
#define API_PORT 8000
#define API_BASE_PATH "/api/v1"

// LoRa
#define LORA_FREQUENCY 433E6
#define LORA_BANDWIDTH 125E3
#define LORA_SPREADING_FACTOR 7
#define LORA_CODING_RATE 5
#define LORA_TX_POWER 20

// Pinos ESP32 Heltec V2
#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26

// Display OLED
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET 16
#define OLED_SDA 4
#define OLED_SCL 15

// LEDs para simulação de barreiras
#define LED_BARREIRA_NORTE 25  // LED_BUILTIN
#define LED_BARREIRA_SUL 2     // GPIO 2

// Sensores de Direção
#define SENSOR_ID_NORTE_ENTRADA "DIR_N_1"
#define SENSOR_ID_NORTE_SAIDA   "DIR_N_2"
#define SENSOR_ID_SUL_ENTRADA   "DIR_S_1"
#define SENSOR_ID_SUL_SAIDA     "DIR_S_2"

// Timeouts
#define DETECTION_SEQUENCE_TIMEOUT 30000  // 30 segundos
#define PENDING_DETECTION_CLEAR_TIMEOUT 60000  // 60 segundos
#define OTA_CHECK_INTERVAL_MS (1 * 60 * 60 * 1000)  // 1 hora

// Versão do Firmware
#define FIRMWARE_VERSION "2.0.0"

#endif
```

### Exemplo de Código para Requisição HTTP

```cpp
#include <HTTPClient.h>
#include <ArduinoJson.h>

bool checkVehicleAuthorization(String macAddress) {
    HTTPClient http;
    
    // Construir URL
    String url = "http://" + String(API_HOST) + ":" + String(API_PORT) + 
                 "/api/v1/vehicles/authorize.php?mac=" + macAddress;
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    
    int httpResponseCode = http.GET();
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        
        // Parse JSON
        DynamicJsonDocument doc(1024);
        deserializeJson(doc, response);
        
        bool authorized = doc["authorized"];
        String action = doc["action"];
        
        http.end();
        
        if (authorized && action == "OPEN_BARRIER") {
            return true;
        }
    }
    
    http.end();
    return false;
}

void sendTelemetry(String macAddress, String sensorId, int rssi, float snr) {
    HTTPClient http;
    
    String url = "http://" + String(API_HOST) + ":" + String(API_PORT) + 
                 "/api/v1/telemetry.php";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    
    // Criar JSON
    DynamicJsonDocument doc(1024);
    doc["mac_address"] = macAddress;
    doc["sensor_id"] = sensorId;
    doc["rssi"] = rssi;
    doc["snr"] = snr;
    doc["timestamp"] = "2025-01-24T10:30:00Z"; // Use NTP para timestamp real
    
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
```

## 🚀 Upload do Firmware

### Arduino IDE
1. **Conectar ESP32** via USB
2. **Tools > Board** > ESP32 Dev Module
3. **Tools > Port** > Selecionar porta COM
4. **Sketch > Upload**

### PlatformIO
```bash
cd base
pio run -t upload
```

## 🧪 Teste de Conectividade

### 1. Teste Manual da API

```bash
# Testar autorização
curl "http://SEU_IP:8000/api/v1/vehicles/authorize.php?mac=AA-BB-CC-DD-EE-FF"

# Testar telemetria
curl -X POST "http://SEU_IP:8000/api/v1/telemetry.php" \
  -H "Content-Type: application/json" \
  -d '{"mac_address":"AA:BB:CC:DD:EE:FF","sensor_id":"BASE_001","rssi":-45}'

# Testar controle de barreira
curl -X POST "http://SEU_IP:8000/api/v1/barrier/control.php" \
  -H "Content-Type: application/json" \
  -d '{"action":"OPEN","duration":5}'
```

### 2. Monitor Serial

No Arduino IDE:
- **Tools > Serial Monitor**
- **Baud Rate: 115200**

Você deve ver:
```
Conectando ao WiFi...
WiFi conectado!
IP: 192.168.1.XXX
LoRa inicializado
Sistema pronto!
```

## 🔍 Troubleshooting

### Problemas Comuns

#### 1. WiFi não conecta
```cpp
// Verificar credenciais
const char* ssid = "SUA_REDE_WIFI";      // <<<< CORRETO?
const char* password = "SUA_SENHA_WIFI";  // <<<< CORRETO?
```

#### 2. API não responde
```cpp
// Verificar IP do servidor
#define API_HOST "192.168.1.100"  // <<<< IP CORRETO?

// Testar ping
ping 192.168.1.100
```

#### 3. LoRa não funciona
- Verificar antena conectada
- Verificar frequência (433MHz)
- Verificar pinos de conexão

#### 4. Display não funciona
- Verificar pinos I2C (SDA=4, SCL=15)
- Verificar biblioteca Adafruit_SSD1306

### Logs de Debug

Adicione ao código:
```cpp
#define DEBUG_SERIAL true

void debugPrint(String message) {
    if (DEBUG_SERIAL) {
        Serial.println("[DEBUG] " + message);
    }
}
```

## 📊 Monitoramento

### Dashboard Web
Acesse: `http://SEU_IP:8080`

### Logs em Tempo Real
- **Telemetria**: Seção "Métricas de Acesso"
- **Status**: Dashboard principal
- **Logs**: Console do navegador (F12)

## 🔄 Próximos Passos

1. **✅ Configure o WiFi** no firmware
2. **✅ Configure o IP** do servidor
3. **✅ Faça upload** do firmware
4. **✅ Teste conectividade** via Serial Monitor
5. **✅ Adicione MACs** autorizados no dashboard
6. **✅ Teste detecção** de veículos
7. **✅ Monitore logs** no dashboard

---

## 📞 Suporte

Se encontrar problemas:
1. **Verifique Serial Monitor** para erros
2. **Teste API manualmente** com curl
3. **Verifique conectividade** de rede
4. **Consulte logs** do servidor

**O sistema está pronto para funcionar com as placas ESP32!** 🚀