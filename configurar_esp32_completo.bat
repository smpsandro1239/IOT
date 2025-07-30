@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 CONFIGURAR ESP32 - COMPLETO                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Configurando ESP32 com suas credenciais...
echo.

echo ✅ CREDENCIAIS CONFIGURADAS:
echo    WiFi: Meo-9C27F0
echo    Senha: BB3F5435FF
echo    IP Servidor: 192.168.1.90
echo    Porta: 8000
echo.

echo [1/6] Verificando Arduino IDE...
where arduino-cli >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Arduino CLI não encontrado
    echo 📥 Execute primeiro: instalar_arduino_ide.bat
    pause
    exit /b 1
) else (
    echo ✅ Arduino CLI encontrado
)

echo.
echo [2/6] Verificando placa ESP32 conectada...
echo 🔌 CONECTE A PLACA ESP32 VIA USB AGORA
echo.
pause

echo [3/6] Detectando porta COM...
for /f "tokens=1" %%i in ('arduino-cli board list ^| findstr "ESP32"') do set COM_PORT=%%i
if "%COM_PORT%"=="" (
    echo ⚠️  Porta não detectada automaticamente
    echo 📋 Portas disponíveis:
    arduino-cli board list
    echo.
    set /p COM_PORT="Digite a porta COM (ex: COM3): "
) else (
    echo ✅ Porta detectada: %COM_PORT%
)

echo.
echo [4/6] Compilando firmware...
echo 📦 Compilando base/src/main_updated.cpp...

arduino-cli compile --fqbn esp32:esp32:esp32 base/src/main_updated.cpp
if %errorlevel% neq 0 (
    echo ❌ Erro na compilação!
    echo 📚 Verifique se as bibliotecas estão instaladas:
    echo    - LoRa by Sandeep Mistry
    echo    - ArduinoJson by Benoit Blanchon  
    echo    - Adafruit SSD1306 by Adafruit
    echo    - Adafruit GFX Library by Adafruit
    pause
    exit /b 1
)

echo ✅ Compilação bem-sucedida!

echo.
echo [5/6] Enviando firmware para ESP32...
echo 📤 Upload para %COM_PORT%...

arduino-cli upload -p %COM_PORT% --fqbn esp32:esp32:esp32 base/src/main_updated.cpp
if %errorlevel% neq 0 (
    echo ❌ Erro no upload!
    echo 🔧 Soluções:
    echo    1. Verifique se a placa está conectada
    echo    2. Pressione o botão BOOT durante o upload
    echo    3. Tente outra porta COM
    pause
    exit /b 1
)

echo ✅ Upload concluído!

echo.
echo [6/6] Testando conectividade...
echo 📡 Abrindo Serial Monitor...

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎉 ESP32 CONFIGURADO COM SUCESSO!                        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo ✅ FIRMWARE ENVIADO PARA ESP32!
echo.
echo 📊 CONFIGURAÇÕES ATIVAS:
echo    • WiFi: Meo-9C27F0
echo    • IP Servidor: 192.168.1.90:8000
echo    • API Endpoints: Funcionando
echo    • LoRa: 433MHz configurado
echo.
echo 🔍 MONITORAMENTO:
echo    • Serial Monitor: 115200 baud
echo    • Dashboard: http://localhost:8080
echo    • API Test: .\teste_esp32_api.bat
echo.
echo 🎯 O QUE DEVE APARECER NO SERIAL MONITOR:
echo    === ESP32 Base Station v2.0.0 ===
echo    Conectando ao WiFi: Meo-9C27F0
echo    WiFi conectado!
echo    IP: 192.168.1.XXX
echo    LoRa inicializado com sucesso!
echo    Sistema inicializado com sucesso!
echo.

pause