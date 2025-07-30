# ⚡ Instalação Rápida ESP32 - Passo a Passo

## 🎯 **Suas Configurações (Já Prontas)**
```cpp
WiFi: Meo-9C27F0
Senha: BB3F5435FF
IP Servidor: 192.168.1.90
Porta: 8000
```

## 🚀 **Opção 1: PlatformIO (Recomendado)**

### **Passo 1: Instalar PlatformIO**
```batch
# Se não tiver Python, instale primeiro
# Depois execute:
pip install platformio
```

### **Passo 2: Conectar ESP32 e Enviar Código**
```batch
# Execute este comando:
.\enviar_codigo_platformio.bat
```

**Pronto! O código será compilado e enviado automaticamente.**

---

## 🛠️ **Opção 2: Arduino IDE**

### **Passo 1: Instalar Arduino IDE**
```batch
# Execute:
.\instalar_arduino_ide.bat
# Siga as instruções na tela
```

### **Passo 2: Instalar Bibliotecas**
```batch
# Execute:
.\instalar_bibliotecas_esp32.bat
```

### **Passo 3: Enviar Código**
```batch
# Execute:
.\configurar_esp32_completo.bat
```

---

## 🧪 **Testar Tudo**

### **Antes de Enviar o Código:**
```batch
.\testar_esp32_completo.bat
```

### **Depois de Enviar o Código:**
1. **Serial Monitor** deve mostrar:
```
=== ESP32 Base Station v2.0.0 ===
Conectando ao WiFi: Meo-9C27F0
WiFi conectado!
IP: 192.168.1.XXX
LoRa inicializado com sucesso!
Sistema inicializado com sucesso!
```

2. **Dashboard Web**: http://localhost:8080
3. **API Test**: `.\teste_esp32_api.bat`

---

## 🔧 **Solução de Problemas**

### **❌ Erro de Compilação:**
```batch
# Instalar bibliotecas manualmente:
.\instalar_bibliotecas_esp32.bat
```

### **❌ Erro de Upload:**
1. Pressione botão **BOOT** na placa durante upload
2. Verifique porta COM
3. Tente outra porta USB

### **❌ WiFi não conecta:**
- Verifique nome da rede: `Meo-9C27F0`
- Verifique senha: `BB3F5435FF`
- Verifique se está próximo do router

### **❌ API não responde:**
```batch
# Reiniciar servidor:
.\sistema_funcionando_completo.bat
```

---

## 📊 **Monitoramento**

### **Serial Monitor (115200 baud):**
- Status de conectividade
- Logs de detecção
- Respostas da API

### **Dashboard Web:**
- http://localhost:8080
- Métricas em tempo real
- Logs de acesso

### **API Endpoints:**
- Autorização: `/api/v1/vehicles/authorize.php`
- Telemetria: `/api/v1/telemetry.php`
- Controle: `/api/v1/barrier/control.php`

---

## ✅ **Checklist Final**

- [ ] ✅ Sistema backend rodando
- [ ] ✅ Credenciais WiFi configuradas
- [ ] ✅ IP servidor configurado (192.168.1.90)
- [ ] ✅ ESP32 conectado via USB
- [ ] ✅ Código compilado e enviado
- [ ] ✅ Serial Monitor mostra conectividade
- [ ] ✅ Dashboard web funcionando
- [ ] ✅ API respondendo

**Quando todos os itens estiverem ✅, o sistema está funcionando!**

---

## 🎉 **Sistema Funcionando!**

Quando tudo estiver correto:

### **ESP32 Display:**
```
ESP32 Base Station
WiFi: OK LoRa: OK
Hora: 10:30:15
MAC: Aguardando...
Status: Aguardando...
Barreira: FECHADA
OK:0 ERR:0
```

### **Dashboard Web:**
- Status das barreiras
- Métricas em tempo real
- Logs de detecção

### **API Funcionando:**
```json
{
  "authorized": true,
  "mac": "AA:BB:CC:DD:EE:FF",
  "action": "OPEN_BARRIER"
}
```

**Suas placas ESP32 estão prontas para funcionar!** 🚀