# Sistema de Controlo de Barreiras Bidirecionais com LoRa e Dashboard Web

## 1. Arquitetura do Sistema

### 1.1. Visão Geral

Este projeto implementa um sistema inteligente de controlo de acesso para duas barreiras de veículos (simulando um fluxo Norte-Sul e Sul-Norte). O sistema utiliza dispositivos baseados em ESP32 com LoRa para identificação de veículos, deteção de direção de aproximação e comunicação. Uma aplicação web central com dashboard permite a gestão de veículos autorizados, visualização de logs de acesso, monitorização de métricas básicas, gestão de firmwares para atualização Over-the-Air (OTA) das placas base e gestão de tokens de API para comunicação segura.

O sistema é composto pelos seguintes componentes principais:

1.  **Dispositivo Veicular (ESP32 + LoRa):** Instalado em cada veículo, transmite um ID único (MAC address do ESP32) e timestamp via LoRa.
2.  **Sensores de Direção (ESP32 + LoRa):** Posicionados estrategicamente antes de cada barreira para detetar a aproximação de veículos. Recebem o sinal do veículo, adicionam seu próprio ID de sensor e o RSSI do sinal do veículo, e retransmitem essa informação para a Placa Base via LoRa.
3.  **Placa Base (ESP32 + LoRa + WiFi):** Localizada junto às barreiras, recebe dados dos Sensores de Direção. Implementa a lógica de deteção de direção, comunica-se com o Servidor Backend via WiFi para validar a autorização do veículo e para enviar logs de acesso. Controla o acionamento das barreiras (simuladas por LEDs) e implementa o cliente para atualizações de firmware OTA.
4.  **Servidor Backend (API Laravel):** Uma aplicação PHP/Laravel que expõe uma API RESTful. É responsável por:
    *   Gerenciar a base de dados de veículos autorizados.
    *   Validar pedidos de autorização da Placa Base.
    *   Receber e armazenar logs de acesso.
    *   Gerenciar versões de firmware para OTA.
    *   Gerenciar tokens de API para autenticação da Placa Base.
5.  **Dashboard Web (Frontend Laravel Blade):** Interface administrativa web, parte da aplicação Laravel, para:
    *   Autenticação de administradores (não implementada neste escopo, mas prevista).
    *   CRUD (Criar, Ler, Atualizar, Excluir) para veículos autorizados.
    *   Visualização de logs de acesso detalhados com filtros.
    *   Visualização de métricas básicas do sistema.
    *   Upload e gestão de firmwares para as Placas Base (OTA).
    *   Gestão de tokens de API.

### 1.2. Fluxo de Comunicação Principal (Exemplo: Veículo Aproximando-se da Barreira Norte)

1.  **Veículo -> Sensor de Entrada Norte:** O Dispositivo Veicular transmite seu ID (MAC) e timestamp via LoRa.
2.  **Sensor de Entrada Norte -> Placa Base:** O Sensor de Entrada Norte (`DIR_N_1`) recebe o pacote do veículo, adiciona seu `SENSOR_ID` e o `RSSI_VEICULO`, e retransmite para a Placa Base via LoRa.
3.  **Veículo -> Sensor de Saída Norte:** O Dispositivo Veicular continua transmitindo e é captado pelo Sensor de Saída Norte (`DIR_N_2`).
4.  **Sensor de Saída Norte -> Placa Base:** O Sensor de Saída Norte (`DIR_N_2`) envia suas informações (incluindo o mesmo MAC do veículo) para a Placa Base.
5.  **Placa Base (Lógica de Direção):**
    *   Recebe os pacotes dos sensores.
    *   Se os pacotes de `DIR_N_1` e `DIR_N_2` para o mesmo MAC chegam na sequência correta e dentro de um timeout, a direção "Norte-Sul" é determinada.
6.  **Placa Base -> API Laravel (Autorização):**
    *   A Placa Base estabelece conexão WiFi.
    *   Envia uma requisição HTTP GET para `/api/v1/vehicles/authorize/{lora_id_veiculo}` incluindo o token de API no header `Authorization`.
7.  **API Laravel (Autorização):**
    *   Valida o token de API (via Sanctum).
    *   Consulta o banco de dados para verificar se o `lora_id_veiculo` está registrado e autorizado.
    *   Retorna uma resposta JSON para a Placa Base (ex: `{\"authorized\": true, \"name\": \"Veiculo ABC\"}`).
8.  **Placa Base (Controle da Barreira):**
    *   Interpreta a resposta da API.
    *   Se autorizado, aciona o relé/LED da Barreira Norte.
    *   Implementa bloqueio temporário da Barreira Sul para o mesmo veículo.
9.  **Placa Base -> API Laravel (Logging):**
    *   A Placa Base envia uma requisição HTTP POST para `/api/v1/access-logs` com os detalhes do evento (ID do veículo, direção, timestamp, status da autorização, dados dos sensores, ID da placa base), incluindo o token de API.
10. **API Laravel (Logging):**
    *   Valida o token e os dados.
    *   Armazena o log no banco de dados.

### 1.3. Tecnologias Utilizadas

*   **Hardware Embarcado:**
    *   Placas: Heltec WiFi LoRa 32 (V2) para todos os dispositivos (Veículo, Sensor de Direção, Placa Base).
    *   Comunicação Primária (longo alcance): LoRa (configurado para 868MHz).
    *   Comunicação Placa Base <-> Servidor: WiFi.
    *   Interface com Barreira: GPIOs controlando LEDs (simulando relés).
    *   Display: OLED SSD1306.
*   **Firmware (ESP32):**
    *   Linguagem: C/C++ (Arduino Framework).
    *   Ambiente de Desenvolvimento: PlatformIO.
    *   Bibliotecas Principais: `LoRa` (sandeepmistry), `Adafruit_SSD1306`, `Adafruit_GFX`, `ArduinoJson`, `HTTPClient`, `HTTPUpdate`.
*   **Backend (Servidor):**
    *   Framework: PHP / Laravel (versão 10.x).
    *   API: RESTful JSON.
    *   Autenticação API: Laravel Sanctum (tokens Bearer).
    *   Base de Dados: MySQL (ou PostgreSQL, configurável).
*   **Frontend (Dashboard Web):**
    *   Laravel Blade templates.
    *   HTML, CSS básico (sem framework CSS específico neste escopo inicial).
    *   JavaScript (mínimo, para confirmações `onsubmit`).

## 2. Firmware ESP32

O sistema utiliza três tipos de firmwares baseados em ESP32 (Heltec WiFi LoRa 32 V2), desenvolvidos com PlatformIO e o framework Arduino.

### 2.1. Configuração Comum a Todos os Firmwares

*   **Bibliotecas Principais:**
    *   `sandeepmistry/LoRa`: Para comunicação LoRa.
    *   `Adafruit_SSD1306` & `Adafruit_GFX`: Para o display OLED.
    *   `ArduinoJson`: Para serialização e desserialização de mensagens JSON (LoRa e HTTP).
    *   `WiFi` e `HTTPClient`: Para conectividade WiFi e requisições HTTP (usado na Placa Base e para NTP nos outros).
    *   `HTTPUpdate`: Para atualizações OTA (usado na Placa Base).
    *   `time.h`: Para sincronização de hora via NTP.
*   **Pinos Comuns (Heltec WiFi LoRa 32 V2):**
    *   LoRa: `LORA_SS (18)`, `LORA_RST (14)`, `LORA_DIO0 (26)`.
    *   OLED: `OLED_SDA (4)`, `OLED_SCL (15)`, `OLED_RESET (16)`.
*   **Sincronização NTP:** Todos os firmwares tentam sincronizar a hora via NTP no `setup()` para timestamps precisos. A Placa Base mantém o WiFi para comunicação com a API, enquanto o Dispositivo Veicular e o Sensor de Direção desligam o WiFi após a sincronização para economizar energia.

### 2.2. Firmware do Dispositivo Veicular (`auto/`)

*   **Arquivo Principal:** `auto/src/main.cpp`
*   **Objetivo:** Transmitir periodicamente a identificação do veículo.
*   **Lógica Principal:**
    1.  No `setup()`: Inicializa WiFi para NTP, sincroniza hora, depois desliga WiFi. Inicializa LoRa e display OLED.
    2.  No `loop()`:
        *   Obtém o MAC address do ESP32 como ID do veículo (`chipid_str`).
        *   Obtém o timestamp atual.
        *   Monta um pacote JSON: `{\"mac\": \"CHIPID_VEICULO\", \"datahora\": \"DD/MM/YYYY HH:MM:SS\"}`.
        *   Envia o pacote JSON via LoRa.
        *   Atualiza o display OLED com informações de status (MAC, contador de envios, último pacote, etc.).
        *   Não espera por ACKs dos sensores de direção (simplificado na Fase 1).
*   **Configuração Chave:** Nenhuma configuração específica de ID é necessária além das credenciais WiFi (se o NTP for crítico em um ambiente sem acesso à rede definida no código). As credenciais WiFi (`ssid`, `password`) estão hardcoded.

### 2.3. Firmware do Sensor de Direção (`direcao/`)

*   **Arquivo Principal:** `direcao/src/main.cpp`
*   **Objetivo:** Receber pacotes LoRa do veículo, adicionar informações do sensor (ID do sensor, RSSI) e retransmitir para a Placa Base.
*   **Lógica Principal:**
    1.  No `setup()`: Similar ao Dispositivo Veicular (NTP, LoRa, OLED).
    2.  No `loop()`:
        *   Aguarda pacotes LoRa do veículo.
        *   Ao receber um pacote: Lê o payload (que é o JSON do veículo), captura o RSSI do sinal do veículo.
        *   Monta um novo pacote JSON para a Placa Base:
            `{\"sensor_id\": \"ID_DO_SENSOR\", \"rssi_veiculo\": RSSI, \"mac_sensor\": \"MAC_DO_SENSOR_ESP32\", \"datahora_sensor\": \"TIMESTAMP_ATUAL_SENSOR\", \"payload_veiculo\": \"JSON_ORIGINAL_DO_VEICULO_COMO_STRING\"}`
        *   Envia o pacote para a Placa Base via LoRa.
        *   Implementa uma lógica de ACK: Aguarda uma resposta (\"AUTORIZADO\" ou \"NEGADO\") da Placa Base. Se não receber, tenta reenviar o pacote algumas vezes antes de desistir (timeout de 30 segundos, reenvio a cada 1 segundo).
        *   Atualiza o display OLED com status (MAC do sensor, `SENSOR_ID` configurado, contadores, última resposta da base, etc.).
*   **Configuração Chave (em `direcao/src/main.cpp` - veja bloco de comentários no topo do arquivo):**
    *   `const char* SENSOR_ID = \"DIR_DEFAULT_01\";`: **Este ID deve ser único para cada dispositivo sensor físico** e deve corresponder a uma das definições `SENSOR_ID_*_ENTRADA/SAIDA` no firmware da Placa Base. Mude este valor para cada sensor (ex: \"DIR_N_1\", \"DIR_S_2\").
    *   `const char* direcao_config = \"NS\";`: Informativo para o display local.
    *   Credenciais WiFi (`ssid`, `password`) hardcoded para NTP.

### 2.4. Firmware da Placa Base (`base/`)

*   **Arquivo Principal:** `base/src/main.cpp`
*   **Objetivo:** Orquestrar a deteção de direção, validar veículos com a API, controlar barreiras e gerenciar atualizações OTA.
*   **Lógica Principal:**
    1.  **Setup:** Inicializa WiFi (e o mantém ativo), NTP, LoRa, OLED, e pinos GPIO para as barreiras (LEDs).
    2.  **Loop Principal (`loop()`):**
        *   **Gerenciar Cancelas:** Verifica timeouts para fechar cancelas abertas e expiração de bloqueios de cancela oposta.
        *   **Limpar Deteções Pendentes:** Remove registros de veículos em sensores de entrada se o timeout (`PENDING_DETECTION_CLEAR_TIMEOUT`) expirou.
        *   **Receber Pacotes LoRa:** Aguarda pacotes dos Sensores de Direção.
        *   **Processar Pacote do Sensor:**
            *   Desserializa o JSON do sensor, extraindo `sensor_id`, `rssi_veiculo`, MAC do veículo, etc.
            *   Armazena dados do sensor (ID, RSSI, timestamp) para possível uso em logging.
            *   **Lógica de Deteção de Direção:** Compara o `sensor_id` recebido com os IDs esperados (`SENSOR_ID_NORTE_ENTRADA`, etc.). Mantém estado de veículos que passaram por um sensor de entrada (`deteccaoPendenteNorte`, `deteccaoPendenteSul`). Se um veículo é detectado em um sensor de saída correspondente dentro do `DETECTION_SEQUENCE_TIMEOUT`, a direção é determinada.
            *   **Autorização:** Se a direção é determinada:
                *   Chama `isMacAutorizado(mac_veiculo)` que faz uma requisição HTTP GET para a API Laravel (`/api/v1/vehicles/authorize/{lora_id}`) com o token de autenticação.
                *   Interpreta a resposta JSON da API.
            *   **Controle de Barreira e Resposta LoRa:**
                *   Se autorizado pela API: Envia \"AUTORIZADO\" de volta para o Sensor de Direção (como ACK). Chama `abrirBarreira()` para a direção correta. A função `abrirBarreira()` aciona o LED, define timeout para fechar, e configura bloqueio da cancela oposta para o mesmo MAC.
                *   Se não autorizado: Envia \"NEGADO\" para o Sensor de Direção.
                *   Se a barreira física não puder abrir devido a um bloqueio oposto (mesmo que a API tenha autorizado), o status é atualizado, mas o ACK \"AUTORIZADO\" já foi enviado.
            *   **Logging para API:** Chama `enviarLogAcesso()` que formata um JSON com os detalhes do evento (MAC do veículo, direção determinada, status da autorização, ID da placa base, dados do último sensor) e envia via HTTP POST para `/api/v1/access-logs` com token de autenticação.
        *   **Atualização OTA:** Periodicamente (controlado por `OTA_CHECK_INTERVAL_MS`), chama `checkAndApplyOTA()`:
            *   Faz uma requisição HTTP GET para `/api/v1/firmware/check` (com token) enviando `FIRMWARE_VERSION` atual.
            *   Se a API indicar uma atualização, baixa o novo firmware da URL fornecida e aplica a atualização usando `httpUpdate.update()`. O ESP32 reinicia automaticamente após uma atualização bem-sucedida.
        *   **Display OLED:** Atualiza o display com status geral, MAC do último veículo, direção, status das barreiras, e status OTA.
*   **Configuração Chave (em `base/src/main.cpp` - veja bloco de comentários no topo do arquivo):**
    *   `FIRMWARE_VERSION`: **Deve ser incrementado a cada novo build/upload manual de firmware** para que o OTA funcione corretamente.
    *   `ssid`, `password`: Credenciais da rede WiFi.
    *   `API_HOST`, `API_PORT`: Endereço e porta do servidor Laravel.
    *   `API_AUTH_TOKEN`: **Token Bearer gerado no dashboard Laravel para autenticação da API.**
    *   `SENSOR_ID_NORTE_ENTRADA`, `SENSOR_ID_NORTE_SAIDA`, `SENSOR_ID_SUL_ENTRADA`, `SENSOR_ID_SUL_SAIDA`: Devem corresponder aos `SENSOR_ID`s configurados nos dispositivos Sensor de Direção.
    *   `LED_BARREIRA_NORTE`, `LED_BARREIRA_SUL`: Pinos GPIO para os LEDs que simulam as barreiras.
    *   Timeouts diversos (`DETECTION_SEQUENCE_TIMEOUT`, `PENDING_DETECTION_CLEAR_TIMEOUT`, `TEMPO_BLOQUEIO_OPOSTA_MS`, `TEMPO_CANCELA_ABERTA_MS`, `OTA_CHECK_INTERVAL_MS`).

### 2.5. Compilação e Upload

1.  Abra cada subdiretório de firmware (`auto/`, `direcao/`, `base/`) em seu editor com PlatformIO (ex: VS Code com a extensão PlatformIO IDE).
2.  Modifique os arquivos `main.cpp` com as configurações específicas do dispositivo e da sua rede/servidor.
3.  Use os comandos do PlatformIO para Compilar (`Build`) e Carregar (`Upload`) o firmware para a placa ESP32 correspondente.

### 2.6. Formato das Mensagens LoRa (Internas)

*   **Veículo -> Sensor de Direção:**
    *   Formato: JSON String
    *   Exemplo: `{\"mac\":\"24A160123456\",\"datahora\":\"27/10/2023 10:30:00\"}`

*   **Sensor de Direção -> Placa Base:**
    *   Formato: JSON String
    *   Exemplo: `{\"sensor_id\":\"DIR_N_1\",\"rssi_veiculo\":-55,\"mac_sensor\":\"30AEA4123458\",\"datahora_sensor\":\"27/10/2023 10:30:01\",\"payload_veiculo\":\"{\\\"mac\\\":\\\"24A160123456\\\",\\\"datahora\\\":\\\"27/10/2023 10:30:00\\\"}\"}`
    *   Nota: `payload_veiculo` é o JSON original do veículo, escapado como uma string dentro do JSON maior.

*   **Placa Base -> Sensor de Direção (ACK):**
    *   Formato: String simples
    *   Exemplo: `\"AUTORIZADO\"` ou `\"NEGADO\"`

## 3. Backend (API Laravel)

A aplicação backend é construída com Laravel (versão 10.x) e serve uma API RESTful para comunicação com as Placas Base ESP32, além de fornecer um Dashboard Web para administração.

### 3.1. Configuração do Ambiente de Desenvolvimento Backend

1.  **Pré-requisitos:**
    *   PHP (versão ^8.1 recomendada pelo `composer.json`).
    *   Composer.
    *   Servidor de Banco de Dados (MySQL ou PostgreSQL).
    *   Servidor Web (Nginx, Apache, ou `php artisan serve` para desenvolvimento).
    *   Node.js e npm/yarn (para compilação de assets frontend, se aplicável no futuro).

2.  **Instalação:**
    *   Clone o repositório.
    *   Navegue até o diretório `backend/`.
    *   Copie o arquivo de ambiente: `cp .env.example .env`.
    *   Edite o arquivo `.env` e configure as variáveis de ambiente, especialmente:
        *   `APP_NAME`, `APP_URL`
        *   `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (para sua base de dados local).
        *   `APP_KEY` (se estiver vazio, gere com `php artisan key:generate`).
    *   Instale as dependências do PHP: `composer install`.
    *   Execute as migrations para criar as tabelas do banco de dados: `php artisan migrate`.
    *   (Opcional) Execute os seeders para popular dados iniciais (ex: veículos de teste, usuário admin): `php artisan db:seed` (após criar um usuário admin e o `UserSeeder` se necessário).

3.  **Servidor de Desenvolvimento:**
    *   Inicie o servidor de desenvolvimento Laravel: `php artisan serve`.
    *   Por padrão, estará acessível em `http://localhost:8000` (ou a porta configurada).

### 3.2. Autenticação da API

A API utiliza Laravel Sanctum para autenticação baseada em tokens. As Placas Base ESP32 devem incluir um token de API válido no header `Authorization` de cada requisição:

`Authorization: Bearer <SEU_TOKEN_PLAIN_TEXT_AQUI>`

Os tokens podem ser gerados e gerenciados através do Dashboard de Administração (seção "Tokens de API") por um usuário administrador autenticado. Ao criar um novo token, o `plainTextToken` é exibido **uma única vez** e deve ser copiado e configurado de forma segura no define `API_AUTH_TOKEN` do firmware da Placa Base (`base/src/main.cpp`).

Todos os endpoints da API sob o prefixo `/api/v1/` estão protegidos por este mecanismo.

### 3.3. Endpoints da API (`/api/v1/...`)

Todos os endpoints esperam e retornam dados no formato JSON.

#### 3.3.1. Autorização de Veículos

*   **Endpoint:** `GET /api/v1/vehicles/authorize/{lora_id}`
*   **Método:** `GET`
*   **Descrição:** Verifica se um veículo, identificado pelo seu `lora_id` (MAC address do ESP32 do veículo), está autorizado a passar.
*   **Parâmetros de Rota:**
    *   `lora_id` (string, obrigatório): O ID LoRa (MAC address) do veículo.
*   **Headers Esperados:**
    *   `Authorization: Bearer <token>`
    *   `Accept: application/json`
*   **Resposta de Sucesso (Veículo Autorizado):**
    *   Código HTTP: `200 OK`
    *   Corpo JSON: `{\"authorized\": true, \"lora_id\": \"string\", \"name\": \"string|null\"}`
*   **Resposta de Sucesso (Veículo Encontrado, Não Autorizado):**
    *   Código HTTP: `403 Forbidden`
    *   Corpo JSON: `{\"authorized\": false, \"lora_id\": \"string\", \"reason\": \"Vehicle not authorized\"}`
*   **Resposta de Erro (Veículo Não Encontrado):**
    *   Código HTTP: `404 Not Found`
    *   Corpo JSON: `{\"authorized\": false, \"lora_id\": \"string\", \"reason\": \"Vehicle not found\"}`
*   **Resposta de Erro (Não Autenticado):**
    *   Código HTTP: `401 Unauthorized`

#### 3.3.2. Registro de Logs de Acesso

*   **Endpoint:** `POST /api/v1/access-logs`
*   **Método:** `POST`
*   **Descrição:** Permite que a Placa Base registre um evento de acesso (ou tentativa de acesso).
*   **Headers Esperados:**
    *   `Authorization: Bearer <token>`
    *   `Content-Type: application/json`
    *   `Accept: application/json`
*   **Corpo da Requisição (JSON):**
    ```json
    {
        "vehicle_lora_id": "string (max:16)",
        "timestamp_event": "string (YYYY-MM-DD HH:MM:SS)",
        "direction_detected": "string (north_south|south_north|undefined|conflito)",
        "base_station_id": "string (max:16, MAC da placa base)",
        "sensor_reports": "json_string_ou_array_de_objetos (opcional, [{sensor_id, rssi, timestamp_sensor}, ...])",
        "authorization_status": "boolean",
        "notes": "string (opcional, max:500)"
    }
    ```
*   **Resposta de Sucesso (Log Criado):**
    *   Código HTTP: `201 Created`
    *   Corpo JSON: `{\"message\": \"Access log created successfully\", \"log_id\": integer}`
*   **Resposta de Erro (Falha na Validação):**
    *   Código HTTP: `422 Unprocessable Entity`
    *   Corpo JSON: (Estrutura de erro de validação padrão do Laravel)
*   **Resposta de Erro (Não Autenticado):**
    *   Código HTTP: `401 Unauthorized`
*   **Resposta de Erro (Falha no Servidor):**
    *   Código HTTP: `500 Internal Server Error`

#### 3.3.3. Verificação de Atualização de Firmware (OTA)

*   **Endpoint:** `GET /api/v1/firmware/check`
*   **Método:** `GET`
*   **Descrição:** Permite que a Placa Base verifique se há uma nova versão de firmware ativa disponível.
*   **Parâmetros Query String:**
    *   `current_version` (string, obrigatório): A versão atual do firmware na Placa Base (ex: "0.1.0").
    *   `board_id` (string, opcional): O MAC address da Placa Base (não utilizado atualmente pela lógica do controller, mas pode ser útil para firmwares específicos por placa no futuro).
*   **Headers Esperados:**
    *   `Authorization: Bearer <token>`
    *   `Accept: application/json`
*   **Resposta de Sucesso (Atualização Disponível):**
    *   Código HTTP: `200 OK`
    *   Corpo JSON:
        ```json
        {
            "update_available": true,
            "new_version": "string (ex: 0.2.0)",
            "description": "string|null",
            "size_bytes": integer,
            "download_url": "string (URL absoluta para download)"
        }
        ```
*   **Resposta de Sucesso (Sem Atualização Disponível / Já na Última Versão):**
    *   Código HTTP: `200 OK`
    *   Corpo JSON: `{\"update_available\": false, \"current_version_is_latest\": true}`
*   **Resposta de Sucesso (Nenhum Firmware Ativo no Servidor):**
    *   Código HTTP: `200 OK`
    *   Corpo JSON: `{\"update_available\": false, \"reason\": \"No active firmware found on server.\"}`
*   **Resposta de Erro (Parâmetros Inválidos):**
    *   Código HTTP: `422 Unprocessable Entity`
*   **Resposta de Erro (Não Autenticado):**
    *   Código HTTP: `401 Unauthorized`

#### 3.3.4. Download de Firmware (OTA)

*   **Endpoint:** `GET /api/v1/firmware/download/{firmware}`
*   **Método:** `GET`
*   **Descrição:** Permite que a Placa Base baixe o arquivo binário de um firmware específico (o ID do firmware é obtido da resposta do endpoint `/firmware/check`).
*   **Parâmetros de Rota:**
    *   `{firmware}` (integer, obrigatório): O ID do registro de firmware no banco de dados.
*   **Headers Esperados:**
    *   `Authorization: Bearer <token>`
*   **Resposta de Sucesso (Download do Arquivo):**
    *   Código HTTP: `200 OK`
    *   Headers:
        *   `Content-Type: application/octet-stream`
        *   `Content-Disposition: attachment; filename=\"firmware_vX-Y-Z.bin\"` (o nome do arquivo pode variar)
    *   Corpo: O conteúdo binário do arquivo de firmware.
*   **Resposta de Erro (Firmware Não Encontrado ou Arquivo Físico Ausente):**
    *   Código HTTP: `404 Not Found`
    *   Corpo JSON: `{\"error\": \"Arquivo de firmware não encontrado.\"}`
*   **Resposta de Erro (Não Autenticado):**
    *   Código HTTP: `401 Unauthorized`

### 3.4. Estrutura da Base de Dados

*   **`users`**: Armazena os usuários administradores do sistema.
*   **`personal_access_tokens`**: Tabela do Laravel Sanctum para armazenar os tokens de API.
*   **`vehicles`**: Contém os registros dos veículos, seus LoRa IDs (MACs), nomes opcionais e status de autorização.
    *   `lora_id`: Chave de identificação principal do veículo para a lógica de acesso.
*   **`access_logs`**: Registra cada evento de deteção/acesso, incluindo qual veículo, quando, onde (placa base), direção, dados dos sensores e se o acesso foi autorizado.
*   **`firmwares`**: Armazena metadados sobre as versões de firmware disponíveis para OTA, incluindo o caminho para o arquivo binário, versão, descrição, tamanho e se está ativo.

## 4. Dashboard Web (Administração)

O Dashboard Web é uma interface administrativa acessada via navegador, construída com Laravel Blade, permitindo a gestão e monitorização do sistema de controlo de barreiras.

### 4.1. Acesso

*   **URL:** O painel administrativo é acessível sob o prefixo `/admin/`. Por exemplo, se a aplicação estiver rodando em `http://localhost:8000`, o dashboard estará em `http://localhost:8000/admin/dashboard`.
*   **Autenticação:** As rotas do painel administrativo (`/admin/*`) estão agrupadas com um middleware de autenticação (`auth`).
    *   **Nota:** A implementação completa do sistema de autenticação de usuários (registro, login, recuperação de senha para administradores) não foi detalhada neste escopo de desenvolvimento, mas o Laravel fornece mecanismos robustos para isso (ex: Laravel Breeze ou Jetstream, ou implementação manual). Assume-se que um administrador precisa estar logado para acessar estas seções.

### 4.2. Funcionalidades Principais

O dashboard é composto pelas seguintes seções principais, acessíveis através de uma barra de navegação comum:

#### 4.2.1. Dashboard (Métricas)

*   **Rota:** `GET /admin/dashboard`
*   **View:** `admin.dashboard.index`
*   **Descrição:** Exibe um resumo com métricas chave do sistema:
    *   Total de Veículos Registrados (com detalhe de autorizados/não autorizados).
    *   Total de Logs de Acesso (com detalhe de acessos bem-sucedidos vs. falhados/negados).
    *   Contagem de Acessos por Direção Detectada (Norte-Sul, Sul-Norte, Indefinida, Conflito).

#### 4.2.2. Gerenciar Veículos

*   **Rota Base:** `/admin/vehicles`
*   **Controller:** `App\\Http\\Controllers\\VehicleController`
*   **Views:** `admin.vehicles.*` (`index`, `create`, `edit`, `_form`)
*   **Descrição:** Permite o gerenciamento completo dos registros de veículos.
    *   **Listagem (`/admin/vehicles`):** Exibe uma tabela paginada de todos os veículos registrados. Inclui campos para ID LoRa, Nome, Status de Autorização e Data de Criação. Oferece ações para Editar e Excluir cada veículo. Um formulário de filtros permite buscar veículos por ID LoRa (parcial), Nome (parcial) e Status de Autorização.
    *   **Criar Novo Veículo (`/admin/vehicles/create`):** Formulário para adicionar um novo veículo, solicitando ID LoRa (MAC address), Nome (opcional) e um checkbox para definir o status de Autorizado (marcado como verdadeiro por padrão na criação).
    *   **Editar Veículo (`/admin/vehicles/{vehicle}/edit`):** Formulário pré-preenchido para modificar os dados de um veículo existente (ID LoRa, Nome, Status de Autorização).
    *   **Excluir Veículo:** Botão na listagem que remove o veículo do sistema após confirmação. (Nota: Se houver logs de acesso referenciando o `lora_id` do veículo, a exclusão pode falhar devido a restrições de chave estrangeira, dependendo da configuração do banco de dados. A mensagem de erro na interface foi melhorada para refletir isso).

#### 4.2.3. Logs de Acesso

*   **Rota:** `GET /admin/access-logs`
*   **Controller:** `App\\Http\\Controllers\\AccessLogController`
*   **View:** `admin.access-logs.index`
*   **Descrição:** Exibe uma tabela paginada com os logs de acesso registrados pelas Placas Base. Inclui informações como Timestamp do Evento, ID do Veículo (LoRa), Nome do Veículo (se encontrado), Direção Detectada, ID da Estação Base, Status da Autorização, detalhes dos sensores (ID, RSSI, timestamp do sensor, se disponíveis no JSON `sensor_reports`) e Notas.
    *   **Filtros:** Um formulário de filtros permite refinar a visualização dos logs por ID do Veículo, intervalo de datas do evento, direção detectada e status da autorização.

#### 4.2.4. Gerenciar Firmwares (OTA)

*   **Rota Base:** `/admin/firmwares`
*   **Controller:** `App\\Http\\Controllers\\FirmwareController`
*   **Views:** `admin.firmwares.*` (`index`, `create`, `edit`, `_form`)
*   **Descrição:** Permite o upload e gerenciamento de versões de firmware para as Placas Base.
    *   **Listagem (`/admin/firmwares`):** Exibe uma tabela paginada dos firmwares carregados, mostrando Versão, Descrição, Tamanho do Arquivo, Data do Upload e se o firmware está Ativo. Ações disponíveis:
        *   **Marcar como Ativo:** Define um firmware como a versão ativa para atualizações OTA (apenas um firmware pode ser ativo por vez).
        *   **Editar:** Permite modificar a Descrição e a Versão (se nenhuma placa já o utiliza, e com cuidado).
        *   **Excluir:** Remove o registro do firmware e o arquivo binário associado do storage do servidor.
    *   **Upload Novo Firmware (`/admin/firmwares/create`):** Formulário para fazer upload de um novo arquivo de firmware (`.bin`). Requer a definição de uma Versão (única) e permite uma Descrição opcional.
    *   **Editar Firmware (`/admin/firmwares/{firmware}/edit`):** Formulário para editar a Versão e a Descrição de um firmware existente. O arquivo binário não pode ser alterado aqui; para isso, um novo upload é necessário.

#### 4.2.5. Gerenciar Tokens de API

*   **Rota Base:** `/admin/api-tokens`
*   **Controller:** `App\\Http\\Controllers\\ApiTokenController`
*   **View:** `admin.api-tokens.index`
*   **Descrição:** Permite que administradores gerenciem tokens de API para autenticação de dispositivos (como a Placa Base ESP32) com a API do sistema.
    *   **Criar Novo Token:** Formulário para gerar um novo token. O administrador fornece um nome para o token (para referência). As habilidades do token são definidas como `api:access` por padrão.
        *   **Importante:** O valor do token (`plainTextToken`) é exibido **apenas uma vez**, imediatamente após a criação. Este valor deve ser copiado e armazenado de forma segura para ser configurado no dispositivo cliente (ESP32).
    *   **Listagem de Tokens:** Exibe uma tabela com os tokens gerados para o usuário administrador logado, mostrando o Nome do Token, Habilidades, Data do Último Uso e Data de Criação. Não exibe o valor do token por motivos de segurança.
    *   **Revogar Token:** Permite excluir (revogar) um token existente, tornando-o inválido para futuras requisições à API.

## 5. Manutenção e Solução de Problemas

Esta seção fornece dicas para a manutenção do sistema e para diagnosticar problemas comuns.

### 5.1. Manutenção Regular

*   **Backup da Base de Dados (Backend):** Realize backups regulares da base de dados do Laravel. A frequência dependerá da criticidade dos dados e do volume de tráfego. Utilize as ferramentas padrão do seu sistema de gerenciamento de banco de dados (MySQL, PostgreSQL).
*   **Logs do Servidor (Backend):** Monitore os logs do Laravel em `backend/storage/logs/laravel.log` para identificar erros ou avisos que possam indicar problemas na aplicação.
*   **Atualizações de Software (Backend):** Mantenha o servidor (PHP, Laravel, dependências do Composer, sistema operacional) atualizado com patches de segurança e novas versões estáveis, conforme apropriado.
*   **Espaço em Disco (Backend):** Verifique periodicamente o espaço em disco no servidor, especialmente se os logs de acesso ou uploads de firmware forem volumosos.
*   **Dispositivos ESP32 (Firmware):**
    *   Verifique a alimentação e conexões físicas dos dispositivos.
    *   Monitore a vida útil esperada dos componentes, especialmente se expostos a condições ambientais adversas.

### 5.2. Solução de Problemas Comuns

#### 5.2.1. Placa Base ESP32 Não Conecta ao WiFi

*   **Verificar Credenciais:** Confirme se o `ssid` e `password` no `base/src/main.cpp` (ou onde forem configurados) estão corretos para a rede WiFi alvo.
*   **Sinal WiFi:** Certifique-se de que a Placa Base está dentro do alcance do roteador WiFi e que o sinal é estável.
*   **Logs Seriais:** Conecte a Placa Base a um computador via USB e monitore a saída serial (baud rate 115200). O firmware imprime mensagens sobre o status da conexão WiFi.
*   **DHCP:** Verifique se o servidor DHCP da rede está funcionando e atribuindo IPs corretamente.
*   **Firewall da Rede:** Certifique-se de que a rede local não está bloqueando o acesso da Placa Base ao servidor da API (IP e porta).

#### 5.2.2. Placa Base Não Autentica com a API (Erro 401)

*   **Token de API:** Verifique se o `API_AUTH_TOKEN` no `base/src/main.cpp` corresponde exatamente ao `plainTextToken` gerado no Dashboard de Administração. Lembre-se que o token completo só é visível no momento da criação.
*   **Geração de Novo Token:** Se houver dúvida sobre o token, gere um novo no dashboard e atualize o firmware da Placa Base.
*   **Logs do Laravel:** Verifique `backend/storage/logs/laravel.log` por mensagens de erro relacionadas à autenticação Sanctum.

#### 5.2.3. API Retorna Erros (4xx, 5xx) para a Placa Base

*   **Logs Seriais da Placa Base:** O firmware tenta imprimir o código de erro HTTP e, às vezes, o payload da resposta de erro. Isso pode dar pistas.
*   **Logs do Laravel (`backend/storage/logs/laravel.log`):** Esta é a fonte mais detalhada para erros do lado do servidor. Verifique por exceções, erros de validação, ou problemas de banco de dados.
*   **Validação de Dados (Erro 422):** Se a API retornar 422, significa que os dados enviados pela Placa Base (ex: para registrar um log de acesso) não passaram na validação do Laravel. Verifique se o formato JSON e os tipos de dados enviados pelo ESP32 correspondem ao esperado pelo `AccessLogController@store` (ex: formato da data, tipos booleanos, etc.).

#### 5.2.4. Lógica de Deteção de Direção Não Funciona Corretamente

*   **Logs Seriais da Placa Base:** O firmware imprime quando registra um veículo em um sensor de entrada, quando um sensor de saída é ativado, timeouts de sequência, e a direção determinada. Use estes logs para depurar o fluxo.
*   **Configuração dos `SENSOR_ID`s:** Certifique-se de que os `SENSOR_ID` definidos no firmware de cada Sensor de Direção (`direcao/src/main.cpp`) correspondem exatamente às defines `SENSOR_ID_NORTE_ENTRADA`, `SENSOR_ID_NORTE_SAIDA`, etc., no firmware da Placa Base (`base/src/main.cpp`).
*   **Comunicação LoRa:** Verifique se todos os sensores estão comunicando com a Placa Base. O display OLED de cada sensor pode indicar problemas de envio/ACK.
*   **Timeouts:** Ajuste os valores de `DETECTION_SEQUENCE_TIMEOUT` e `PENDING_DETECTION_CLEAR_TIMEOUT` na Placa Base se necessário para o seu ambiente e velocidade dos veículos.

#### 5.2.5. Atualização OTA Falha

*   **Logs Seriais da Placa Base:** O processo OTA imprime várias mensagens de status, incluindo erros do `HTTPUpdate`. Verifique estes logs.
*   **Conexão WiFi:** OTA requer uma conexão WiFi estável.
*   **Acesso à API:** Verifique se a Placa Base consegue acessar o endpoint `/api/v1/firmware/check` e `/api/v1/firmware/download/...` (autenticação, URL correta, servidor Laravel rodando).
*   **Firmware Ativo no Dashboard:** Certifique-se de que há um firmware marcado como \"Ativo\" no dashboard e que sua versão é superior à `FIRMWARE_VERSION` definida no `base/src/main.cpp` da placa que está tentando atualizar.
*   **Arquivo de Firmware no Servidor:** Verifique se o arquivo binário do firmware existe no caminho correto em `backend/storage/app/firmwares_storage/` e se o servidor web tem permissão para lê-lo.
*   **Espaço Livre no ESP32:** Atualizações OTA requerem espaço suficiente na partição do ESP32. Se a partição estiver muito cheia, a atualização pode falhar.

#### 5.2.6. Display OLED Não Mostra Informação ou Mostra Informação Incorreta

*   **Conexões:** Verifique as conexões físicas do display OLED (SDA, SCL, RST, VCC, GND).
*   **Inicialização:** Verifique os logs seriais para mensagens de erro durante a inicialização do display no `setup()`.\n*   **Lógica de Atualização do Display:** Revise as chamadas a `mostraNoDisplay()` e as funções que atualizam as variáveis de status que são exibidas.

### 5.3. Logs do Sistema

*   **Firmware ESP32:** A principal forma de logging é via `Serial.print()` e `Serial.println()`. Monitore a saída serial conectando o ESP32 a um computador.
*   **Backend Laravel:** Os logs são armazenados em `backend/storage/logs/laravel.log`. O nível de logging pode ser configurado no arquivo `.env` (`LOG_LEVEL`).
