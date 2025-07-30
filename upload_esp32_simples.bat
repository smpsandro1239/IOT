@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 UPLOAD ESP32 - MÉTODO SIMPLES                         ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Upload do firmware para ESP32...
echo.

echo ✅ CONFIGURAÇÕES:
echo    WiFi: Meo-9C27F0
echo    Senha: BB3F5435FF
echo    IP: 192.168.1.90
echo.

echo [1/3] Preparando arquivo...
copy "base\src\main_updated.cpp" "base\src\main.cpp" >nul 2>&1
echo ✅ Arquivo preparado

echo.
echo [2/3] Detectando portas COM...
echo 🔌 CONECTE A PLACA ESP32 VIA USB AGORA!
echo.
pause

echo 📋 Portas COM disponíveis:
for /f "tokens=1" %%i in ('wmic path Win32_SerialPort get DeviceID /format:value ^| find "="') do echo %%i

echo.
set /p COM_PORT="Digite a porta COM da sua placa (ex: COM3): "

echo.
echo [3/3] Instruções para Arduino IDE...
echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📋 INSTRUÇÕES ARDUINO IDE                                ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🛠️ SIGA ESTES PASSOS NO ARDUINO IDE:
echo.
echo 1️⃣ ABRIR ARQUIVO:
echo    • File ^> Open
echo    • Navegue para: %CD%\base\src\main.cpp
echo    • Abra o arquivo
echo.
echo 2️⃣ CONFIGURAR PLACA:
echo    • Tools ^> Board ^> ESP32 Arduino ^> ESP32 Dev Module
echo    • Tools ^> Port ^> %COM_PORT%
echo    • Tools ^> Upload Speed ^> 115200
echo.
echo 3️⃣ VERIFICAR BIBLIOTECAS:
echo    Se der erro, instale via Tools ^> Manage Libraries:
echo    • LoRa by Sandeep Mistry
echo    • ArduinoJson by Benoit Blanchon
echo    • Adafruit SSD1306 by Adafruit
echo    • Adafruit GFX Library by Adafruit
echo.
echo 4️⃣ FAZER UPLOAD:
echo    • Pressione o botão BOOT na placa ESP32
echo    • Clique no botão Upload (seta para direita)
echo    • Mantenha BOOT pressionado até começar o upload
echo.
echo 5️⃣ MONITORAR:
echo    • Tools ^> Serial Monitor
echo    • Baud Rate: 115200
echo    • Deve aparecer: "WiFi conectado!"
echo.

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ ARQUIVO PRONTO PARA UPLOAD                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📁 ARQUIVO: %CD%\base\src\main.cpp
echo 🔌 PORTA: %COM_PORT%
echo 📊 BAUD: 115200
echo.

echo 🎯 APÓS O UPLOAD, DEVE APARECER NO SERIAL MONITOR:
echo    === ESP32 Base Station v2.0.0 ===
echo    Conectando ao WiFi: Meo-9C27F0
echo    WiFi conectado!
echo    IP: 192.168.1.XXX
echo    LoRa inicializado com sucesso!
echo.

pause