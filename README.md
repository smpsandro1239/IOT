# 🚀 IOT Access Control & Barrier Management System

![Banner](https://raw.githubusercontent.com/smpsandro1239/IOT/main/docs/images/screenshots/interface-principal.png)

## 🎯 Visão Geral
O **IOT Access Control** é uma solução de nível empresarial para o gerenciamento de barreiras físicas e monitoramento de acesso veicular. Integrando hardware ESP32 de alta performance com um backend Laravel robusto, o sistema oferece controle operacional preciso, segurança baseada em endereços MAC e atualizações em tempo real via LoRa.

---

## ✨ Funcionalidades
- **📡 Long-Range Detection**: Detecção de veículos a até 500m utilizando tecnologia LoRa (433MHz).
- **🛡️ Segurança MAC-Based**: Filtragem rigorosa de dispositivos autorizados com gestão centralizada.
- **📊 Dashboard Operational**: Visualização em tempo real de métricas, status de conexão e logs de atividade.
- **🎯 Detecção Direcional**: Cálculo de trajetória (NS/SN) para automação inteligente de abertura.
- **📱 PWA Ready**: Interface adaptativa instalável com suporte a modo offline.
- **🔍 Simulação de Hardware**: Ambiente de teste integrado para validar lógicas sem dispositivos físicos.

---

## 🛠️ Stack Tecnológica

### **Backend & API**
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### **Frontend**
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)

### **Hardware & Firmware**
![ESP32](https://img.shields.io/badge/ESP32-E7352C?style=for-the-badge&logo=espressif&logoColor=white)
![C++](https://img.shields.io/badge/C++-00599C?style=for-the-badge&logo=c%2B%2B&logoColor=white)
![LoRa](https://img.shields.io/badge/LoRa-007ACC?style=for-the-badge&logo=lora&logoColor=white)

---

## 📸 Screenshots

### **Painel de Controle Central**
![Dashboard](https://raw.githubusercontent.com/smpsandro1239/IOT/main/docs/images/screenshots/interface-principal.png)
*Gestão completa de acessos e monitoramento de saúde do sistema.*

### **Métricas de Performance**
![Metrics](https://raw.githubusercontent.com/smpsandro1239/IOT/main/docs/images/screenshots/Metricas%20de%20acesso.png)
*Gráficos detalhados sobre o fluxo de veículos e horários de pico.*

### **Gestão de Dispositivos**
![MAC Management](https://raw.githubusercontent.com/smpsandro1239/IOT/main/docs/images/screenshots/gestao-macs.png)
*Whitelist de endereços MAC para controle de acesso seguro.*

---

## 🚀 Guia de Instalação

### **1. Ambiente Web (Backend)**
```bash
# Clone o repositório
git clone https://github.com/smpsandro1239/IOT.git
cd IOT/backend

# Instale dependências
composer install

# Configure o ambiente
cp .env.example .env
php artisan key:generate

# Execute as migrações e seeders
php artisan migrate --seed
```

### **2. Firmware (Hardware)**
1. Abra a pasta `base/` na **Arduino IDE**.
2. Instale as bibliotecas: `Heltec ESP32`, `ArduinoJson`, `LoRa`.
3. Configure as credenciais WiFi no arquivo `credentials.h`.
4. Realize o upload para o módulo **Heltec WiFi LoRa 32 V2**.

---

## 📦 Scripts de Utilidade (Windows)
O projeto inclui automações para facilitar o desenvolvimento:
- ⚡ `iniciar_sistema_otimizado.bat`: Sobe o ambiente completo (Laravel + PHP Server).
- 🧪 `teste_configuracao.bat`: Valida se todos os pré-requisitos estão ativos.
- 🧹 `clear_cache.bat`: Limpa caches do Laravel e otimiza o sistema.

---

## 📄 Licença
Distribuído sob a licença MIT. Veja `LICENSE` para mais informações.

---

**Desenvolvido para alta performance e estabilidade operacional.** 🚀
