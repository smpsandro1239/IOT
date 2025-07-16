# Sistema de Controle de Barreiras IoT

Sistema completo de controle de barreiras baseado em IoT, utilizando Laravel para o backend, HTML/JavaScript/Tailwind CSS para o frontend, e ESP32 com LoRa para a placa base.

## Características

- **Detecção de Veículos:** Detecta veículos usando LoRa e estima o Ângulo de Chegada (AoA) para determinar a direção de aproximação.
- **Autorização por MAC:** Abre a barreira para veículos autorizados com base em seu endereço MAC.
- **Dashboard em Tempo Real:** Um dashboard web exibe o status da barreira, movimentos de veículos e logs do sistema em tempo real.
- **Endpoints API:** Conjunto completo de endpoints API para gerenciar o sistema, incluindo adição de MACs autorizados, registro de dados de telemetria e verificação de atualizações de firmware.
- **Modo de Simulação:** Modo de simulação incluído no frontend para testar o sistema sem hardware físico.
- **Atualizações OTA:** O sistema suporta atualizações de firmware Over-the-Air para o ESP32.
- **Modo Offline:** O frontend suporta operação offline com sincronização quando a conexão é restaurada.
- **PWA:** O frontend é uma Progressive Web App que pode ser instalada em dispositivos móveis.

## Arquitetura do Sistema

O sistema é composto por três componentes principais:

1. **Backend (Laravel):** Uma aplicação Laravel que fornece os endpoints API para o sistema.
2. **Frontend (HTML/JavaScript):** Uma aplicação HTML/JavaScript que fornece o dashboard web.
3. **Firmware ESP32:** Um sketch Arduino que roda no ESP32 e controla a barreira.

## Instruções de Configuração

### Pré-requisitos

- [Laragon](https://laragon.org/download/) (ou qualquer outro ambiente de desenvolvimento local para PHP)
- [MySQL](https://www.mysql.com/downloads/)
- [Node.js](https://nodejs.org/en/download/)
- [Arduino IDE](https://www.arduino.cc/en/software)
- [Suporte ESP32 para Arduino IDE](https://docs.espressif.com/projects/arduino-esp32/en/latest/installing.html)
- Bibliotecas para ESP32: `LoRa`, `WiFi`, `HTTPClient`

### 1. Clonar o Repositório

```bash
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot
```

### 2. Configuração do Backend

Abra um terminal e navegue até o diretório `backend`. Em seguida, execute os seguintes comandos:

```bash
composer install
cp .env.example .env
```

Após copiar o arquivo `.env.example`, você precisa atualizar o arquivo `.env` com suas credenciais de banco de dados:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Em seguida, execute os seguintes comandos para gerar a chave da aplicação e executar as migrações do banco de dados:

```bash
php artisan key:generate
php artisan migrate --seed
```

### 3. Iniciar os Servidores

Você precisa iniciar três servidores em terminais separados:

1. **Servidor Backend:**
   ```bash
   cd backend
   php artisan serve
   ```

2. **Servidor WebSocket:**
   ```bash
   cd backend
   php artisan websockets:serve
   ```

3. **Servidor Frontend:**
   ```bash
   cd frontend
   npm install
   http-server
   ```

O frontend estará disponível em `http://127.0.0.1:8080`.

### 4. Configuração do ESP32

1. Abra o arquivo `base/src/main.cpp` no Arduino IDE.
2. Atualize as credenciais WiFi e a URL do servidor no arquivo:

   ```cpp
   const char* ssid = "seu_wifi_ssid";
   const char* password = "sua_senha_wifi";
   const char* serverUrl = "http://sua-url-laravel-app/api/v1/access-logs";
   const char* authUrl = "http://sua-url-laravel-app/api/v1/macs-autorizados/authorize";
   ```

3. Carregue o firmware para sua placa ESP32.

### 5. Firewall

Se você estiver usando um firewall, você precisa abrir a porta `6001` para acesso WebSocket:

```bash
netsh advfirewall firewall add rule name="WebSocket 6001" dir=in action=allow protocol=TCP localport=6001
```

## Teste da API

Você pode usar os seguintes comandos `curl` para testar os endpoints da API:

### Adicionar um endereço MAC

```bash
curl -X POST http://127.0.0.1:8000/api/v1/macs-autorizados -H "Content-Type: application/json" -d '{"mac":"24A160123456","placa":"ABC123"}'
```

### Testar telemetria

```bash
curl -X POST http://127.0.0.1:8000/api/v1/access-logs -H "Content-Type: application/json" -d '{"mac":"24A160123456","direcao":"NS","datahora":"2025-07-16 01:49:00","status":"AUTORIZADO"}'
```

### Obter último status

```bash
curl http://127.0.0.1:8000/api/v1/status/latest
```

### Obter todos os MACs autorizados

```bash
curl http://127.0.0.1:8000/api/v1/macs-autorizados
```

### Verificar autorização de MAC

```bash
curl http://127.0.0.1:8000/api/v1/macs-autorizados/authorize?mac=24A160123456
```

## Melhorias Implementadas

1. **Frontend Moderno:**
   - Arquitetura modular com componentes reutilizáveis
   - Suporte a PWA para instalação em dispositivos móveis
   - Modo offline com sincronização quando online
   - Interface de usuário responsiva e intuitiva

2. **Backend Robusto:**
   - API RESTful com autenticação Sanctum
   - Eventos em tempo real com Laravel WebSockets
   - Métricas e análises de acesso
   - Suporte a atualizações OTA para firmware

3. **Firmware Otimizado:**
   - Detecção de direção precisa
   - Comunicação segura com a API
   - Suporte a atualizações OTA
   - Modo de baixo consumo de energia

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.
