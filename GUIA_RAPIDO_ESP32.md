# ⚡ Guia Rápido - ESP32 Pronto para Usar

## 🚀 Passos Rápidos (5 minutos)

### 1️⃣ Iniciar Sistema Backend
```batch
# Execute este comando
.\sistema_funcionando_completo.bat
```

### 2️⃣ Testar API
```batch
# Execute este comando para testar
.\teste_esp32_api.bat
```

### 3️⃣ Configurar ESP32

#### Encontrar seu IP:
```batch
ipconfig
```
**Anote o IP que aparece (ex: 192.168.1.150)**

#### Editar Firmware:
1. Abra: `base/src/main_updated.cpp`
2. Altere estas linhas:

```cpp
// ALTERE ESTAS LINHAS:
const char* ssid = "SUA_REDE_WIFI";        // <<<< SEU WIFI
const char* password = "SUA_SENHA_WIFI";   // <<<< SUA SENHA
#define API_HOST "192.168.1.100"           // <<<< SEU IP
```

### 4️⃣ Upload do Firmware

#### Arduino IDE:
1. **File > Open** → `base/src/main_updated.cpp`
2. **Tools > Board** → ESP32 Dev Module
3. **Tools > Port** → Selecionar porta COM
4. **Sketch > Upload**

#### PlatformIO:
```bash
cd base
# Copiar arquivo atualizado
copy src\main_updated.cpp src\main.cpp
pio run -t upload
```

### 5️⃣ Testar Funcionamento

#### Monitor Serial:
- **Arduino IDE**: Tools > Serial Monitor (115200 baud)
- **PlatformIO**: `pio device monitor`

**Você deve ver:**
```
=== ESP32 Base Station v2.0.0 ===
Conectando ao WiFi: SUA_REDE_WIFI
WiFi conectado!
IP: 192.168.1.XXX
LoRa inicializado com sucesso!
Sistema inicializado com sucesso!
```

### 6️⃣ Adicionar Veículos Autorizados

1. Acesse: `http://localhost:8080`
2. Clique em **"MACs Autorizados"**
3. Adicione um MAC de teste: `AA:BB:CC:DD:EE:FF`
4. Nome: `Veículo Teste`

## 🧪 Teste Completo

### Simular Detecção LoRa:

No **Serial Monitor**, o ESP32 deve mostrar:
```
Pacote LoRa recebido: AA:BB:CC:DD:EE:FF:BASE_001
RSSI: -45 dBm, SNR: 8.5 dB
Verificando autorização: http://192.168.1.150:8000/api/v1/vehicles/authorize.php?mac=AA:BB:CC:DD:EE:FF
Resposta API: {"authorized":true,"mac":"AA:BB:CC:DD:EE:FF","name":"Veículo Teste","action":"OPEN_BARRIER"}
Abrindo barreira...
```

### Dashboard Web:
- Acesse: `http://localhost:8080`
- Veja os logs em tempo real
- Monitore métricas de acesso

## 📊 Endpoints Funcionando

### ✅ Para ESP32:
```cpp
// Autorização de veículo
GET /api/v1/vehicles/authorize.php?mac=MAC_ADDRESS

// Enviar telemetria
POST /api/v1/telemetry.php
{
  "mac_address": "AA:BB:CC:DD:EE:FF",
  "sensor_id": "BASE_001",
  "rssi": -45,
  "snr": 8.5
}

// Controlar barreira
POST /api/v1/barrier/control.php
{
  "action": "OPEN",
  "duration": 5
}
```

## 🔧 Troubleshooting Rápido

### ❌ WiFi não conecta:
```cpp
// Verificar credenciais no código
const char* ssid = "NOME_CORRETO_WIFI";
const char* password = "SENHA_CORRETA";
```

### ❌ API não responde:
```cpp
// Verificar IP no código
#define API_HOST "192.168.1.XXX"  // SEU IP REAL

// Testar manualmente:
curl "http://SEU_IP:8000/api/v1/test.php"
```

### ❌ LoRa não funciona:
- Verificar antena conectada
- Verificar frequência 433MHz
- Verificar pinos de conexão

## 📱 Monitoramento

### Dashboard: `http://localhost:8080`
- **Interface Principal**: Status em tempo real
- **MACs Autorizados**: Gestão de veículos
- **Métricas**: Gráficos de acesso
- **Simulação**: Teste sem hardware

### Serial Monitor:
- **Logs detalhados** de funcionamento
- **Status de conectividade**
- **Respostas da API**
- **Detecções LoRa**

## 🎯 Sistema Funcionando!

Quando tudo estiver correto:

### ✅ ESP32 Display mostra:
```
ESP32 Base Station
WiFi: OK LoRa: OK
Hora: 10:30:15
MAC: Aguardando...
Status: Aguardando...
Barreira: FECHADA
OK:0 ERR:0
```

### ✅ Dashboard Web mostra:
- Status das barreiras
- Últimas detecções
- Métricas em tempo real
- Logs de acesso

### ✅ API responde:
```json
{
  "authorized": true,
  "mac": "AA:BB:CC:DD:EE:FF",
  "name": "Veículo Teste",
  "action": "OPEN_BARRIER"
}
```

## 🚀 Pronto para Produção!

O sistema está **100% funcional** e pronto para:
- ✅ **Detectar veículos** via LoRa
- ✅ **Verificar autorizações** na API
- ✅ **Controlar barreiras** automaticamente
- ✅ **Registrar telemetria** em tempo real
- ✅ **Monitorar via dashboard** web

**Suas placas ESP32 já podem ser ligadas e funcionarão perfeitamente!** 🎉

---

## 📞 Suporte Rápido

**Problema?** Execute:
```batch
.\teste_esp32_api.bat
```

**Logs?** Verifique:
- Serial Monitor (ESP32)
- Console do navegador (F12)
- Dashboard web em tempo real

**Funciona!** 🚀