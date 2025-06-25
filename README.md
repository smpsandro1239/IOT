# Sistema de Controlo de Barreiras Bidirecionais com LoRa e Dashboard Web

Este documento descreve a arquitetura, componentes e funcionamento do Sistema de Controlo de Barreiras.

## Documentação Detalhada

*   [Arquitetura do Sistema](#1-arquitetura-do-sistema) (neste arquivo)
*   [Documentação do Firmware ESP32](./firmware_docs/README.md)
*   [Documentação do Backend e Dashboard](./backend/README.md)
*   [Manutenção e Solução de Problemas](#5-manuteno-e-soluo-de-problemas) (neste arquivo)

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
*   **Inicialização:** Verifique os logs seriais para mensagens de erro durante a inicialização do display no `setup()`.
*   **Lógica de Atualização do Display:** Revise as chamadas a `mostraNoDisplay()` e as funções que atualizam as variáveis de status que são exibidas.

### 5.3. Logs do Sistema

*   **Firmware ESP32:** A principal forma de logging é via `Serial.print()` e `Serial.println()`. Monitore a saída serial conectando o ESP32 a um computador.
*   **Backend Laravel:** Os logs são armazenados em `backend/storage/logs/laravel.log`. O nível de logging pode ser configurado no arquivo `.env` (`LOG_LEVEL`).
