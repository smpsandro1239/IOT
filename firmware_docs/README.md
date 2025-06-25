# Documentação do Firmware ESP32

O sistema utiliza três tipos de firmwares baseados em ESP32 (Heltec WiFi LoRa 32 V2), desenvolvidos com PlatformIO e o framework Arduino.

## 2.1. Configuração Comum a Todos os Firmwares

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

## 2.2. Firmware do Dispositivo Veicular (`auto/`)

*   **Localização do Código:** Diretório `auto/` no repositório.
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
*   **Configuração Chave:** As credenciais WiFi (`ssid`, `password`) estão hardcoded em `auto/src/main.cpp` para a sincronização NTP.

## 2.3. Firmware do Sensor de Direção (`direcao/`)

*   **Localização do Código:** Diretório `direcao/` no repositório.
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
        *   Implementa uma lógica de ACK: Aguarda uma resposta (\"AUTORIZADO\" ou \"NEGADO\") da Placa Base. Se não receber, tenta reenviar o pacote algumas vezes antes de desistir.
        *   Atualiza o display OLED com status (MAC do sensor, `SENSOR_ID` configurado, contadores, última resposta da base, etc.).
*   **Configuração Chave (em `direcao/src/main.cpp` - veja bloco de comentários no topo do arquivo):**
    *   `const char* SENSOR_ID = \"DIR_DEFAULT_01\";`: **Este ID deve ser único para cada dispositivo sensor físico** e deve corresponder a uma das definições `SENSOR_ID_*_ENTRADA/SAIDA` no firmware da Placa Base. Mude este valor para cada sensor (ex: \"DIR_N_1\", \"DIR_S_2\").
    *   `const char* direcao_config = \"NS\";`: Informativo para o display local.
    *   Credenciais WiFi (`ssid`, `password`) hardcoded para NTP.

## 2.4. Firmware da Placa Base (`base/`)

*   **Localização do Código:** Diretório `base/` no repositório.
*   **Arquivo Principal:** `base/src/main.cpp`
*   **Objetivo:** Orquestrar a deteção de direção, validar veículos com a API, controlar barreiras e gerenciar atualizações OTA.
*   **Lógica Principal:** Detalhada na seção de Arquitetura do `README.md` principal, mas os pontos chave incluem: gestão de WiFi, NTP, LoRa, OLED, lógica de deteção de direção por sequência de sensores, comunicação HTTP com a API (autorização, logging), controle de LEDs simulando barreiras (com timeouts e bloqueio oposto), e cliente OTA para auto-atualização.
*   **Configuração Chave (em `base/src/main.cpp` - veja bloco de comentários no topo do arquivo):**
    *   `FIRMWARE_VERSION`: **Deve ser incrementado a cada novo build/upload manual de firmware** para que o OTA funcione corretamente.
    *   `ssid`, `password`: Credenciais da rede WiFi.
    *   `API_HOST`, `API_PORT`: Endereço e porta do servidor Laravel.
    *   `API_AUTH_TOKEN`: **Token Bearer gerado no dashboard Laravel para autenticação da API.**
    *   `SENSOR_ID_NORTE_ENTRADA`, `SENSOR_ID_NORTE_SAIDA`, `SENSOR_ID_SUL_ENTRADA`, `SENSOR_ID_SUL_SAIDA`: Devem corresponder aos `SENSOR_ID`s configurados nos dispositivos Sensor de Direção.
    *   `LED_BARREIRA_NORTE`, `LED_BARREIRA_SUL`: Pinos GPIO para os LEDs que simulam as barreiras.
    *   Timeouts diversos.

## 2.5. Compilação e Upload

1.  Abra cada subdiretório de firmware (`auto/`, `direcao/`, `base/`) em seu editor com PlatformIO (ex: VS Code com a extensão PlatformIO IDE).
2.  Modifique os arquivos `main.cpp` com as configurações específicas do dispositivo e da sua rede/servidor, conforme detalhado acima.
3.  Use os comandos do PlatformIO para Compilar (`Build`) e Carregar (`Upload`) o firmware para a placa ESP32 correspondente.

## 2.6. Formato das Mensagens LoRa (Internas)

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
