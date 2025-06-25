# Backend e Dashboard Web (Laravel)

A aplicação backend é construída com Laravel (versão 10.x) e serve uma API RESTful para comunicação com as Placas Base ESP32, além de fornecer um Dashboard Web para administração.

## 1. Backend (API Laravel)

### 1.1. Configuração do Ambiente de Desenvolvimento Backend

1.  **Pré-requisitos:**
    *   PHP (versão ^8.1 recomendada pelo `composer.json`).
    *   Composer.
    *   Servidor de Banco de Dados (MySQL ou PostgreSQL).
    *   Servidor Web (Nginx, Apache, ou `php artisan serve` para desenvolvimento).
    *   Node.js e npm/yarn (para compilação de assets frontend, se aplicável no futuro).

2.  **Instalação:**
    *   Clone o repositório.
    *   Navegue até este diretório (`backend/`).
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

### 1.2. Autenticação da API

A API utiliza Laravel Sanctum para autenticação baseada em tokens. As Placas Base ESP32 devem incluir um token de API válido no header `Authorization` de cada requisição:

`Authorization: Bearer <SEU_TOKEN_PLAIN_TEXT_AQUI>`

Os tokens podem ser gerados e gerenciados através do Dashboard de Administração (seção "Tokens de API") por um usuário administrador autenticado. Ao criar um novo token, o `plainTextToken` é exibido **uma única vez** e deve ser copiado e configurado de forma segura no define `API_AUTH_TOKEN` do firmware da Placa Base (`base/src/main.cpp`).

Todos os endpoints da API sob o prefixo `/api/v1/` estão protegidos por este mecanismo.

### 1.3. Endpoints da API (`/api/v1/...`)

Todos os endpoints esperam e retornam dados no formato JSON.

#### 1.3.1. Autorização de Veículos

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

#### 1.3.2. Registro de Logs de Acesso

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

#### 1.3.3. Verificação de Atualização de Firmware (OTA)

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

#### 1.3.4. Download de Firmware (OTA)

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

### 1.4. Estrutura da Base de Dados

*   **`users`**: Armazena os usuários administradores do sistema.
*   **`personal_access_tokens`**: Tabela do Laravel Sanctum para armazenar os tokens de API.
*   **`vehicles`**: Contém os registros dos veículos, seus LoRa IDs (MACs), nomes opcionais e status de autorização.
    *   `lora_id`: Chave de identificação principal do veículo para a lógica de acesso.
*   **`access_logs`**: Registra cada evento de deteção/acesso, incluindo qual veículo, quando, onde (placa base), direção, dados dos sensores e se o acesso foi autorizado.
*   **`firmwares`**: Armazena metadados sobre as versões de firmware disponíveis para OTA, incluindo o caminho para o arquivo binário, versão, descrição, tamanho e se está ativo.

## 2. Dashboard Web (Administração)

O Dashboard Web é uma interface administrativa acessada via navegador, construída com Laravel Blade, permitindo a gestão e monitorização do sistema de controlo de barreiras.

### 2.1. Acesso

*   **URL:** O painel administrativo é acessível sob o prefixo `/admin/`. Por exemplo, se a aplicação estiver rodando em `http://localhost:8000`, o dashboard estará em `http://localhost:8000/admin/dashboard`.
*   **Autenticação:** As rotas do painel administrativo (`/admin/*`) estão agrupadas com um middleware de autenticação (`auth`).
    *   **Nota:** A implementação completa do sistema de autenticação de usuários (registro, login, recuperação de senha para administradores) não foi detalhada neste escopo de desenvolvimento, mas o Laravel fornece mecanismos robustos para isso (ex: Laravel Breeze ou Jetstream, ou implementação manual). Assume-se que um administrador precisa estar logado para acessar estas seções.

### 2.2. Funcionalidades Principais

O dashboard é composto pelas seguintes seções principais, acessíveis através de uma barra de navegação comum:

#### 2.2.1. Dashboard (Métricas)

*   **Rota:** `GET /admin/dashboard`
*   **View:** `admin.dashboard.index`
*   **Descrição:** Exibe um resumo com métricas chave do sistema:
    *   Total de Veículos Registrados (com detalhe de autorizados/não autorizados).
    *   Total de Logs de Acesso (com detalhe de acessos bem-sucedidos vs. falhados/negados).
    *   Contagem de Acessos por Direção Detectada (Norte-Sul, Sul-Norte, Indefinida, Conflito).

#### 2.2.2. Gerenciar Veículos

*   **Rota Base:** `/admin/vehicles`
*   **Controller:** `App\\Http\\Controllers\\VehicleController`
*   **Views:** `admin.vehicles.*` (`index`, `create`, `edit`, `_form`)
*   **Descrição:** Permite o gerenciamento completo dos registros de veículos.
    *   **Listagem (`/admin/vehicles`):** Exibe uma tabela paginada de todos os veículos registrados. Inclui campos para ID LoRa, Nome, Status de Autorização e Data de Criação. Oferece ações para Editar e Excluir cada veículo. Um formulário de filtros permite buscar veículos por ID LoRa (parcial), Nome (parcial) e Status de Autorização.
    *   **Criar Novo Veículo (`/admin/vehicles/create`):** Formulário para adicionar um novo veículo, solicitando ID LoRa (MAC address), Nome (opcional) e um checkbox para definir o status de Autorizado (marcado como verdadeiro por padrão na criação).
    *   **Editar Veículo (`/admin/vehicles/{vehicle}/edit`):** Formulário pré-preenchido para modificar os dados de um veículo existente (ID LoRa, Nome, Status de Autorização).
    *   **Excluir Veículo:** Botão na listagem que remove o veículo do sistema após confirmação.

#### 2.2.3. Logs de Acesso

*   **Rota:** `GET /admin/access-logs`
*   **Controller:** `App\\Http\\Controllers\\AccessLogController`
*   **View:** `admin.access-logs.index`
*   **Descrição:** Exibe uma tabela paginada com os logs de acesso registrados pelas Placas Base. Inclui informações como Timestamp do Evento, ID do Veículo (LoRa), Nome do Veículo (se encontrado), Direção Detectada, ID da Estação Base, Status da Autorização, detalhes dos sensores (ID, RSSI, timestamp do sensor, se disponíveis no JSON `sensor_reports`) e Notas.
    *   **Filtros:** Um formulário de filtros permite refinar a visualização dos logs por ID do Veículo, intervalo de datas do evento, direção detectada e status da autorização.

#### 2.2.4. Gerenciar Firmwares (OTA)

*   **Rota Base:** `/admin/firmwares`
*   **Controller:** `App\\Http\\Controllers\\FirmwareController`
*   **Views:** `admin.firmwares.*` (`index`, `create`, `edit`, `_form`)
*   **Descrição:** Permite o upload e gerenciamento de versões de firmware para as Placas Base.
    *   **Listagem (`/admin/firmwares`):** Exibe uma tabela paginada dos firmwares carregados, mostrando Versão, Descrição, Tamanho do Arquivo, Data do Upload e se o firmware está Ativo. Ações disponíveis:
        *   **Marcar como Ativo:** Define um firmware como a versão ativa para atualizações OTA (apenas um firmware pode ser ativo por vez).
        *   **Editar:** Permite modificar a Descrição e a Versão.
        *   **Excluir:** Remove o registro do firmware e o arquivo binário associado do storage do servidor.
    *   **Upload Novo Firmware (`/admin/firmwares/create`):** Formulário para fazer upload de um novo arquivo de firmware (`.bin`). Requer a definição de uma Versão (única) e permite uma Descrição opcional.
    *   **Editar Firmware (`/admin/firmwares/{firmware}/edit`):** Formulário para editar a Versão e a Descrição de um firmware existente.

#### 2.2.5. Gerenciar Tokens de API

*   **Rota Base:** `/admin/api-tokens`
*   **Controller:** `App\\Http\\Controllers\\ApiTokenController`
*   **View:** `admin.api-tokens.index`
*   **Descrição:** Permite que administradores gerenciem tokens de API para autenticação de dispositivos com a API.
    *   **Criar Novo Token:** Formulário para gerar um novo token (nome de referência). O valor do token (`plainTextToken`) é exibido **apenas uma vez** após a criação.
    *   **Listagem de Tokens:** Exibe tokens gerados (Nome, Habilidades, Último Uso, Criação).
    *   **Revogar Token:** Permite excluir (revogar) um token existente.
