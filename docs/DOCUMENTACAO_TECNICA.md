# 📋 Documentação Técnica - Sistema de Controle de Barreiras IoT

## 🎯 Visão Geral do Sistema

O Sistema de Controle de Barreiras IoT é uma solução completa que integra hardware ESP32, comunicação LoRa, backend Laravel e frontend moderno para controle inteligente de barreiras físicas.

## 🏗️ Arquitetura Detalhada

### Componentes Principais

#### 1. **Frontend (Interface Web)**
- **Tecnologia**: HTML5, CSS3, JavaScript Vanilla
- **Framework CSS**: Tailwind CSS
- **Funcionalidades**:
  - Dashboard em tempo real
  - Gestão de MACs autorizados
  - Simulação de radar
  - Métricas e relatórios
  - PWA (Progressive Web App)
  - Modo offline com sincronização

#### 2. **Backend (API Laravel)**
- **Framework**: Laravel 10
- **PHP**: 8.1+
- **Banco de Dados**: MySQL/MariaDB/SQLite
- **Funcionalidades**:
  - API RESTful completa
  - Autenticação com Sanctum
  - WebSockets para tempo real
  - Validação de dados
  - Logs de auditoria

#### 3. **Firmware ESP32**
- **Plataforma**: ESP32 (Heltec WiFi LoRa 32 V2)
- **Comunicação**: LoRa 433MHz, WiFi
- **Funcionalidades**:
  - Detecção de veículos
  - Cálculo de AoA (Angle of Arrival)
  - Comunicação com API
  - Display OLED para status
  - Modo de baixo consumo

## 🔄 Fluxo de Operação Detalhado

### 1. Detecção de Veículo
```
ESP32 Base → Detecta sinal LoRa → Calcula AoA → Determina direção
```

### 2. Processamento
```
ESP32 → HTTP Request → Laravel API → Validação MAC → Resposta
```

### 3. Ação
```
API Response → ESP32 → Controle Barreira → Log Sistema → WebSocket Update
```

### 4. Interface
```
WebSocket → Frontend → Update Dashboard → Notificação Usuario
```

## 📡 Comunicação LoRa

### Configuração
- **Frequência**: 433MHz
- **Bandwidth**: 125kHz
- **Spreading Factor**: 7-12 (adaptativo)
- **Coding Rate**: 4/5
- **Power**: 20dBm (máximo)

### Protocolo de Mensagens
```json
{
  "type": "detection",
  "mac": "24:A1:60:12:34:56",
  "rssi": -45,
  "snr": 8.5,
  "timestamp": "2025-01-24T10:30:00Z",
  "direction": "NS",
  "distance": 500
}
```

## 🗄️ Estrutura da Base de Dados

### Tabelas Principais

#### `macs_autorizados`
```sql
CREATE TABLE macs_autorizados (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(17) UNIQUE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    placa VARCHAR(10),
    ultimo_acesso TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `access_logs`
```sql
CREATE TABLE access_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    mac_address VARCHAR(17) NOT NULL,
    nome VARCHAR(255),
    timestamp TIMESTAMP NOT NULL,
    direcao ENUM('NS', 'SN') NOT NULL,
    status ENUM('AUTORIZADO', 'NEGADO') NOT NULL,
    rssi INT,
    snr DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `system_status`
```sql
CREATE TABLE system_status (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    barrier_status ENUM('aberta', 'fechada') DEFAULT 'fechada',
    last_detection TIMESTAMP NULL,
    total_detections INT DEFAULT 0,
    authorized_today INT DEFAULT 0,
    denied_today INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🔌 API Endpoints Detalhados

### Autenticação
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

### Gestão de MACs
```http
# Listar MACs autorizados
GET /api/v1/macs-autorizados?page=1&search=termo

# Adicionar MAC
POST /api/v1/macs-autorizados
{
  "mac_address": "24:A1:60:12:34:56",
  "nome": "Veículo Teste",
  "placa": "ABC-1234"
}

# Remover MAC
DELETE /api/v1/macs-autorizados/24:A1:60:12:34:56
```

### Telemetria
```http
# Registrar acesso
POST /api/v1/access-logs
{
  "mac_address": "24:A1:60:12:34:56",
  "direcao": "NS",
  "status": "AUTORIZADO",
  "rssi": -45,
  "snr": 8.5
}

# Status atual
GET /api/v1/status/latest

# Métricas
GET /api/v1/metrics?period=today
```

## 🎛️ Configurações do Sistema

### Variáveis de Ambiente (.env)
```env
# Aplicação
APP_NAME=IOT
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=

# LoRa
LORA_FREQUENCY=433000000
LORA_BANDWIDTH=125000
LORA_SPREADING_FACTOR=7
LORA_CODING_RATE=5
LORA_TX_POWER=20

# Sistema
BARRIER_AUTO_CLOSE_TIME=300
DETECTION_DISTANCE=500
AoA_THRESHOLD=15
```

### Configuração ESP32
```cpp
// WiFi
const char* ssid = "SUA_REDE_WIFI";
const char* password = "SUA_SENHA_WIFI";

// API
const char* api_url = "http://192.168.1.100:8000/api/v1";
const char* api_token = "seu_token_api";

// LoRa
#define LORA_FREQUENCY 433E6
#define LORA_BANDWIDTH 125E3
#define LORA_SPREADING_FACTOR 7
#define LORA_CODING_RATE 5
#define LORA_TX_POWER 20
```

## 🔧 Algoritmo de AoA (Angle of Arrival)

### Princípio
O sistema utiliza múltiplas antenas para calcular o ângulo de chegada do sinal LoRa, determinando a direção de aproximação do veículo.

### Implementação
```cpp
float calculateAoA(float rssi1, float rssi2, float rssi3) {
    // Diferença de fase entre antenas
    float phase_diff_12 = atan2(rssi2, rssi1);
    float phase_diff_13 = atan2(rssi3, rssi1);
    
    // Cálculo do ângulo
    float angle = (phase_diff_12 - phase_diff_13) * (180.0 / PI);
    
    // Normalização
    if (angle < 0) angle += 360;
    
    return angle;
}
```

### Calibração
- **Norte-Sul (NS)**: 0° ± 15°
- **Sul-Norte (SN)**: 180° ± 15°
- **Margem de erro**: ±5°

## 📊 Métricas e Monitoramento

### KPIs Principais
- **Taxa de Detecção**: % de veículos detectados
- **Precisão AoA**: Margem de erro na direção
- **Tempo de Resposta**: Latência API
- **Uptime Sistema**: Disponibilidade
- **Falsos Positivos**: Detecções incorretas

### Alertas
- Falha de comunicação LoRa
- Erro na API
- Barreira não responde
- Bateria baixa (ESP32)
- Temperatura alta

## 🛡️ Segurança

### Autenticação
- Laravel Sanctum tokens
- Middleware de autenticação
- Rate limiting
- CORS configurado

### Validação
- Sanitização de inputs
- Validação de MAC addresses
- Prevenção SQL injection
- XSS protection

### Comunicação
- HTTPS em produção
- Tokens JWT
- Criptografia LoRa
- Validação de integridade

## 🚀 Deploy e Produção

### Requisitos de Servidor
- **CPU**: 2 cores mínimo
- **RAM**: 4GB mínimo
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04+ / CentOS 8+
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **Nginx**: 1.18+

### Configuração de Produção
```bash
# Otimizações Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Supervisor para queues
sudo apt install supervisor

# SSL com Let's Encrypt
sudo certbot --nginx -d seu-dominio.com
```

### Monitoramento
- **Logs**: Centralizados com ELK Stack
- **Métricas**: Prometheus + Grafana
- **Uptime**: Pingdom / UptimeRobot
- **Erros**: Sentry

## 🔄 Backup e Recuperação

### Backup Automático
```bash
#!/bin/bash
# backup_daily.sh
mysqldump -u root -p laravel_barrier_control > backup_$(date +%Y%m%d).sql
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/
```

### Recuperação
```bash
# Restaurar banco
mysql -u root -p laravel_barrier_control < backup_20250124.sql

# Restaurar arquivos
tar -xzf storage_backup_20250124.tar.gz
```

## 📈 Escalabilidade

### Horizontal
- Load balancer (Nginx)
- Múltiplas instâncias Laravel
- Redis para cache/sessions
- Queue workers distribuídos

### Vertical
- Otimização de queries
- Índices de banco
- Cache de aplicação
- CDN para assets

## 🧪 Testes

### Testes Automatizados
```bash
# Backend
cd backend
php artisan test

# Frontend
cd frontend
npm test

# E2E
npm run test:e2e
```

### Testes de Hardware
- Alcance LoRa
- Precisão AoA
- Tempo de resposta
- Consumo energético
- Resistência ambiental

## 📚 Referências Técnicas

- [Laravel Documentation](https://laravel.com/docs)
- [ESP32 Arduino Core](https://github.com/espressif/arduino-esp32)
- [LoRa Library](https://github.com/sandeepmistry/arduino-LoRa)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Chart.js](https://www.chartjs.org/docs/)

---

**Documentação atualizada em: Janeiro 2025**