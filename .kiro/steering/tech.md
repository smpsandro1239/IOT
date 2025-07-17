# Tecnologias e Stack

## Stack Principal

### Backend
- **Framework:** Laravel 10
- **PHP:** 8.1+
- **Banco de Dados:** MySQL 8.0+ / MariaDB 10.5+
- **Autenticação:** Laravel Sanctum
- **WebSockets:** Laravel WebSockets (beyondcode/laravel-websockets)
- **Eventos em Tempo Real:** Pusher

### Frontend
- **HTML/CSS/JavaScript** (Vanilla)
- **CSS Framework:** Tailwind CSS
- **PWA:** Service Worker para funcionalidade offline
- **Ícones:** Font Awesome

### Firmware
- **Plataforma:** ESP32 (Heltec WiFi LoRa 32 V2)
- **Framework:** Arduino
- **Bibliotecas Principais:**
  - LoRa (sandeepmistry/LoRa)
  - ArduinoJson
  - Adafruit SSD1306 (display OLED)
  - Adafruit GFX Library
  - micro-ecc (criptografia)

## Ferramentas de Build

### Frontend
- **Tailwind CLI:** Para compilação do CSS
- **Node.js:** Para gerenciamento de dependências e scripts de build

### Firmware
- **PlatformIO:** Para compilação e upload do firmware

## Comandos Comuns

### Configuração Inicial
```batch
setup_desenvolvimento.bat
```

### Iniciar Sistema
```batch
iniciar_sistema_otimizado.bat
```
ou manualmente:
```batch
# Backend
cd backend && php artisan serve

# Frontend
cd frontend && php -S localhost:8080
```

### Compilar CSS
```batch
cd frontend && npm run build:css
```

### Monitorar Alterações CSS
```batch
cd frontend && npm run watch:css
```

### Reiniciar Sistema
```batch
reiniciar_sistema.bat
```
ou
```batch
reiniciar_sistema_corrigido.bat
```

### Resetar Banco de Dados
```batch
cd backend && php artisan migrate:fresh --seed
```

### Compilar e Carregar Firmware
Usando PlatformIO:
```
cd base
pio run -t upload
```

## Portas e Endpoints

- **Backend API:** http://localhost:8000/api
- **Frontend:** http://localhost:8080
- **WebSockets:** ws://localhost:6001

## Credenciais Padrão

- **Email:** admin@example.com
- **Senha:** password

## Boas Práticas de Desenvolvimento

### Backend
- Utilizar Repository Pattern para acesso a dados
- Implementar validação de dados em FormRequests
- Usar Resource Classes para formatação de respostas API
- Implementar testes unitários para lógica crítica

### Frontend
- Evitar manipulação direta do DOM quando possível
- Utilizar módulos JavaScript para organização do código
- Implementar cache de dados para operação offline
- Seguir princípios de design responsivo

### Firmware
- Implementar tratamento de erros robusto
- Utilizar modo de baixo consumo quando possível
- Documentar parâmetros de configuração
- Validar integridade dos dados recebidos
