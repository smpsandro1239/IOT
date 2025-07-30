# 🔧 GUIA DE CONFIGURAÇÃO SEGURA - SISTEMA IOT CONTROLE DE BARREIRAS

## 🎯 **OBJETIVO**

Este documento fornece instruções detalhadas para configurar o Sistema IOT Controle de Barreiras de forma **100% segura**, garantindo que nenhuma credencial ou informação sensível seja exposta.

---

## 🚀 **CONFIGURAÇÃO INICIAL RÁPIDA**

### **Passo 1: Clonar e Configurar**
```bash
# Clonar repositório
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot

# Configuração automática segura
.\configurar_ambiente_seguro.bat
```

### **Passo 2: Verificar Segurança**
```bash
# Verificação rápida
.\verificar_seguranca_simples.bat
```

### **Passo 3: Configurar Credenciais**
Edite os ficheiros criados automaticamente com as suas credenciais.

---

## 🖥️ **CONFIGURAÇÃO BACKEND (Laravel)**

### **Ficheiro: `backend/.env`**

```env
# ============================================================================
# CONFIGURAÇÃO BACKEND - SUAS CREDENCIAIS
# ============================================================================

# Aplicação
APP_NAME="Sistema IOT Controle de Barreiras"
APP_ENV=local
APP_KEY=base64:SERÁ_GERADO_AUTOMATICAMENTE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Dados (SQLite - Recomendado para desenvolvimento)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Para MySQL (Produção)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nome_da_sua_base
# DB_USERNAME=seu_usuario
# DB_PASSWORD=sua_senha_segura

# Logs
LOG_CHANNEL=single
LOG_LEVEL=debug

# Email (Opcional)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# WebSockets (Desenvolvimento)
PUSHER_APP_ID=iot-app
PUSHER_APP_KEY=iot-key
PUSHER_APP_SECRET=iot-secret
PUSHER_HOST=localhost
PUSHER_PORT=6001

# Configurações IOT
LORA_FREQUENCY=433000000
BARRIER_AUTO_CLOSE_TIME=300
DETECTION_DISTANCE=500
```

### **Comandos de Configuração**
```bash
cd backend

# Instalar dependências
composer install

# Gerar chave da aplicação
php artisan key:generate

# Criar base de dados SQLite
touch database/database.sqlite

# Executar migrações
php artisan migrate

# Iniciar servidor
php artisan serve
```

---

## 📡 **CONFIGURAÇÃO ESP32 BASE**

### **Ficheiro: `base/src/config.h`**

```cpp
#ifndef CONFIG_H
#define CONFIG_H

// ============================================================================
// CONFIGURAÇÃO ESP32 BASE - SUAS CREDENCIAIS
// ============================================================================

// WiFi - ALTERE PARA AS SUAS CREDENCIAIS
#define WIFI_SSID "SUA_REDE_WIFI"
#define WIFI_PASSWORD "SUA_SENHA_WIFI"
#define WIFI_TIMEOUT_MS 15000

// API Server - ALTERE PARA O SEU SERVIDOR
#define API_HOST "192.168.1.100"  // IP do seu computador
#define API_PORT 8000
#define API_BASE_PATH "/api/v1"

// LoRa
#define LORA_FREQUENCY 433E6
#define LORA_BANDWIDTH 125E3
#define LORA_SPREADING_FACTOR 7
#define LORA_TX_POWER 20

// Hardware ESP32 Heltec V2
#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26
#define OLED_SDA 4
#define OLED_SCL 15

// Sistema
#define DEVICE_ID "BASE_001"
#define FIRMWARE_VERSION "2.0.0"

// Debug
#define DEBUG_SERIAL true
#define SERIAL_BAUD_RATE 115200

#endif
```

### **Como Descobrir o IP do Seu Computador**

#### **Windows**
```cmd
ipconfig | findstr "IPv4"
```

#### **Linux/Mac**
```bash
ifconfig | grep "inet "
```

#### **Alternativa Universal**
1. Abra o navegador
2. Vá para http://localhost:8000
3. Se funcionar, use "localhost" ou "127.0.0.1"
4. Para rede local, use o IP mostrado no comando acima

---

## 🤖 **CONFIGURAÇÃO ESP32 AUTO**

### **Ficheiro: `auto/src/config.h`**

```cpp
#ifndef CONFIG_H
#define CONFIG_H

// ============================================================================
// CONFIGURAÇÃO ESP32 AUTO - SUAS CREDENCIAIS
// ============================================================================

// WiFi (se necessário para este sensor)
#define WIFI_SSID "SUA_REDE_WIFI"
#define WIFI_PASSWORD "SUA_SENHA_WIFI"

// LoRa (mesmo que a base)
#define LORA_FREQUENCY 433E6
#define LORA_BANDWIDTH 125E3
#define LORA_SPREADING_FACTOR 7

// Hardware
#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26

// Sistema
#define DEVICE_ID "AUTO_001"
#define DEVICE_TYPE "SENSOR_AUTO"

// Detecção
#define DETECTION_RANGE_METERS 500
#define DETECTION_SENSITIVITY 0.8

#endif
```

---

## 🧭 **CONFIGURAÇÃO ESP32 DIREÇÃO**

### **Ficheiro: `direcao/src/config.h`**

```cpp
#ifndef CONFIG_H
#define CONFIG_H

// ============================================================================
// CONFIGURAÇÃO ESP32 DIREÇÃO - SUAS CREDENCIAIS
// ============================================================================

// WiFi (se necessário)
#define WIFI_SSID "SUA_REDE_WIFI"
#define WIFI_PASSWORD "SUA_SENHA_WIFI"

// LoRa (mesmo que a base)
#define LORA_FREQUENCY 433E6
#define LORA_BANDWIDTH 125E3
#define LORA_SPREADING_FACTOR 7

// Hardware
#define LORA_SS 18
#define LORA_RST 14
#define LORA_DIO0 26

// Sistema
#define DEVICE_ID "DIR_001"
#define DEVICE_TYPE "SENSOR_DIRECAO"

// Posicionamento
#define SENSOR_POSITION "NORTE"  // ou "SUL"
#define SENSOR_LANE "ENTRADA"    // ou "SAIDA"

// AoA (Angle of Arrival)
#define AOA_CONFIDENCE_THRESHOLD 0.7
#define AOA_CALCULATION_SAMPLES 10

#endif
```

---

## 🔧 **CONFIGURAÇÃO GIT**

### **Ficheiro: `.gitconfig`**

```ini
[user]
    # ALTERE PARA AS SUAS INFORMAÇÕES
    name = Seu Nome Completo
    email = seu.email@exemplo.com

[core]
    editor = code --wait
    autocrlf = true

[init]
    defaultBranch = main

[alias]
    # Aliases úteis
    st = status
    co = checkout
    ci = commit
    cm = commit -m
    
    # Aliases em português
    cmp = commit -m
    
[remote "origin"]
    # ALTERE PARA O SEU REPOSITÓRIO
    url = https://github.com/SEU_USUARIO/controle-barreiras-iot.git
```

---

## 🌐 **CONFIGURAÇÃO DE REDE**

### **Cenários Comuns**

#### **Desenvolvimento Local (Recomendado)**
```cpp
// ESP32 config.h
#define API_HOST "192.168.1.100"  // IP do seu PC
#define API_PORT 8000
```

```env
# Backend .env
APP_URL=http://localhost:8000
CORS_ALLOWED_ORIGINS="http://localhost:8080"
```

#### **Rede Doméstica**
```cpp
// Descobrir IP do router
#define API_HOST "192.168.1.1"    // Gateway padrão
#define API_HOST "192.168.0.1"    // Alternativa comum
```

#### **Rede Empresarial**
```cpp
// Consultar administrador de rede
#define API_HOST "10.0.0.100"     // Exemplo rede empresarial
#define API_HOST "172.16.0.100"   // Exemplo rede interna
```

---

## 🔍 **VERIFICAÇÃO DE CONFIGURAÇÃO**

### **1. Verificar Backend**
```bash
cd backend
php artisan serve

# Testar em outro terminal
curl http://localhost:8000/api/v1/status
```

### **2. Verificar Frontend**
```bash
cd frontend
php -S localhost:8080

# Abrir navegador
# http://localhost:8080
```

### **3. Verificar ESP32**
```bash
# Compilar e carregar
cd base
pio run -t upload

# Monitorizar serial
pio device monitor
```

### **4. Verificar Conectividade**
```bash
# Ping do ESP32 para servidor
ping 192.168.1.100

# Teste HTTP do ESP32
curl http://192.168.1.100:8000/api/v1/status
```

---

## 🚨 **RESOLUÇÃO DE PROBLEMAS**

### **Problema: ESP32 não conecta ao WiFi**

#### **Soluções:**
1. **Verificar credenciais WiFi**
   ```cpp
   #define WIFI_SSID "Nome_Exato_Da_Rede"
   #define WIFI_PASSWORD "Senha_Exata"
   ```

2. **Verificar força do sinal**
   - Aproximar ESP32 do router
   - Verificar se rede é 2.4GHz (ESP32 não suporta 5GHz)

3. **Verificar caracteres especiais**
   - Evitar acentos no nome da rede
   - Usar aspas duplas para senhas com espaços

### **Problema: ESP32 não conecta ao servidor**

#### **Soluções:**
1. **Verificar IP do servidor**
   ```bash
   ipconfig  # Windows
   ifconfig  # Linux/Mac
   ```

2. **Verificar firewall**
   ```bash
   # Windows: Permitir porta 8000
   # Linux: sudo ufw allow 8000
   ```

3. **Testar conectividade**
   ```bash
   # Do computador
   curl http://localhost:8000/api/v1/status
   
   # Da rede local
   curl http://192.168.1.100:8000/api/v1/status
   ```

### **Problema: Laravel não inicia**

#### **Soluções:**
1. **Verificar PHP**
   ```bash
   php --version  # Deve ser 8.1+
   ```

2. **Instalar dependências**
   ```bash
   cd backend
   composer install
   ```

3. **Verificar .env**
   ```bash
   # Deve existir e ter APP_KEY
   cat backend/.env | grep APP_KEY
   ```

4. **Gerar chave se necessário**
   ```bash
   cd backend
   php artisan key:generate
   ```

### **Problema: Base de dados não funciona**

#### **Soluções:**
1. **SQLite (Recomendado)**
   ```bash
   cd backend
   touch database/database.sqlite
   php artisan migrate
   ```

2. **MySQL**
   ```bash
   # Criar base de dados
   mysql -u root -p
   CREATE DATABASE laravel_barrier_control;
   
   # Configurar .env
   DB_CONNECTION=mysql
   DB_DATABASE=laravel_barrier_control
   DB_USERNAME=root
   DB_PASSWORD=sua_senha
   ```

---

## 📋 **CHECKLIST DE CONFIGURAÇÃO**

### **✅ Backend**
- [ ] Ficheiro `.env` criado e configurado
- [ ] `APP_KEY` gerada
- [ ] Base de dados configurada
- [ ] Migrações executadas
- [ ] Servidor Laravel a funcionar (http://localhost:8000)

### **✅ Frontend**
- [ ] Servidor frontend a funcionar (http://localhost:8080)
- [ ] Conectividade com backend verificada
- [ ] Interface carrega sem erros

### **✅ ESP32 Base**
- [ ] Ficheiro `config.h` criado e configurado
- [ ] Credenciais WiFi corretas
- [ ] IP do servidor correto
- [ ] Código compila sem erros
- [ ] Conecta ao WiFi
- [ ] Conecta ao servidor

### **✅ ESP32 Auto**
- [ ] Ficheiro `config.h` criado e configurado
- [ ] Configurações LoRa corretas
- [ ] Código compila sem erros

### **✅ ESP32 Direção**
- [ ] Ficheiro `config.h` criado e configurado
- [ ] Posicionamento configurado
- [ ] Configurações AoA corretas
- [ ] Código compila sem erros

### **✅ Git**
- [ ] Ficheiro `.gitconfig` configurado
- [ ] Nome e email corretos
- [ ] Repositório remoto configurado

### **✅ Segurança**
- [ ] Verificação de segurança aprovada
- [ ] Nenhum ficheiro sensível no staging
- [ ] `.gitignore` funcionando corretamente

---

## 🎯 **CONFIGURAÇÕES POR AMBIENTE**

### **Desenvolvimento Local**
```env
# Backend
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite

# ESP32
#define API_HOST "localhost"
#define DEBUG_SERIAL true
```

### **Teste/Staging**
```env
# Backend
APP_ENV=staging
APP_DEBUG=true
DB_CONNECTION=mysql

# ESP32
#define API_HOST "192.168.1.100"
#define DEBUG_SERIAL true
```

### **Produção**
```env
# Backend
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_DRIVER=redis

# ESP32
#define API_HOST "10.0.0.100"
#define DEBUG_SERIAL false
#define ENABLE_WATCHDOG true
```

---

## 📞 **SUPORTE**

### **Problemas de Configuração**
1. Consulte este documento
2. Execute `verificar_seguranca_simples.bat`
3. Verifique logs de erro
4. Consulte documentação específica (README.md)

### **Problemas de Segurança**
1. Execute `limpar_credenciais_emergencia.bat`
2. Reconfigure com `configurar_ambiente_seguro.bat`
3. Consulte SEGURANCA.md

### **Contactos**
- **Documentação**: README.md
- **Segurança**: SEGURANCA.md
- **Contribuição**: CONTRIBUTING.md

---

**🔧 Configuração segura é a base de um projeto bem-sucedido!**