# 🚀 Guia de Upload - Arduino IDE

## ✅ **Arquivo Pronto**
- **Localização**: `C:\laragon\www\IOT\base\src\main.cpp`
- **WiFi**: `Meo-9C27F0` ✅
- **Senha**: `BB3F5435FF` ✅
- **IP**: `192.168.1.90` ✅

## 📋 **Passo a Passo no Arduino IDE**

### **1️⃣ Abrir Arduino IDE**
- Abra o Arduino IDE 2.0
- Se não tiver, baixe em: https://www.arduino.cc/en/software

### **2️⃣ Abrir o Arquivo**
```
File > Open
Navegue para: C:\laragon\www\IOT\base\src\main.cpp
Clique em "Open"
```

### **3️⃣ Configurar a Placa**
```
Tools > Board > ESP32 Arduino > ESP32 Dev Module
Tools > Port > [Selecione a porta COM da sua placa]
Tools > Upload Speed > 115200
```

### **4️⃣ Instalar Bibliotecas (se necessário)**
Se aparecer erro de bibliotecas:
```
Tools > Manage Libraries...
Pesquise e instale:
- LoRa (by Sandeep Mistry)
- ArduinoJson (by Benoit Blanchon)
- Adafruit SSD1306 (by Adafruit)
- Adafruit GFX Library (by Adafruit)
```

### **5️⃣ Fazer Upload**
```
1. Conecte ESP32 via USB
2. Pressione e MANTENHA o botão BOOT na placa
3. Clique no botão Upload (→) no Arduino IDE
4. Quando aparecer "Connecting...", solte o botão BOOT
5. Aguarde "Hard resetting via RTS pin..."
```

### **6️⃣ Monitorar**
```
Tools > Serial Monitor
Baud Rate: 115200
```

## ✅ **O que deve aparecer no Serial Monitor:**

```
=== ESP32 Base Station v2.0.0 ===
Conectando ao WiFi: Meo-9C27F0
WiFi conectado!
IP: 192.168.1.XXX
LoRa inicializado com sucesso!
Sistema inicializado com sucesso!
```

## 🔧 **Solução de Problemas**

### **❌ Erro "espcomm_upload_mem failed"**
- Pressione BOOT durante todo o upload
- Tente outra porta USB
- Verifique se o cabo USB transmite dados

### **❌ Erro de bibliotecas**
- Instale as bibliotecas listadas acima
- Reinicie o Arduino IDE

### **❌ WiFi não conecta**
- Verifique se está próximo do router
- Confirme nome: `Meo-9C27F0`
- Confirme senha: `BB3F5435FF`

### **❌ API não responde**
- Verifique se o servidor está rodando
- Execute: `.\sistema_funcionando_completo.bat`

## 🎯 **Após Upload Bem-sucedido**

### **Dashboard Web:**
- Acesse: http://localhost:8080
- Veja logs em tempo real

### **Teste API:**
```batch
.\teste_esp32_api.bat
```

### **Serial Monitor deve mostrar:**
- Conectividade WiFi
- Inicialização LoRa
- Status do sistema
- Detecções de veículos (quando houver)

## 📊 **Monitoramento Contínuo**

### **Serial Monitor (115200 baud):**
```
Pacote LoRa recebido: AA:BB:CC:DD:EE:FF:BASE_001
RSSI: -45 dBm, SNR: 8.5 dB
Verificando autorização: http://192.168.1.90:8000/api/v1/vehicles/authorize.php
Resposta API: {"authorized":true,"action":"OPEN_BARRIER"}
Abrindo barreira...
```

### **Dashboard Web:**
- Status das barreiras
- Métricas em tempo real
- Logs de detecção

**Quando tudo estiver funcionando, o sistema estará completo!** 🎉