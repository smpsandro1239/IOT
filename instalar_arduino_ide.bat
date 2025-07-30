@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📥 INSTALAR ARDUINO IDE PARA ESP32                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Instalando Arduino IDE e configurações para ESP32...
echo.

echo [1/3] Baixando Arduino IDE 2.0...
echo.
echo 📥 DOWNLOAD MANUAL NECESSÁRIO:
echo    1. Acesse: https://www.arduino.cc/en/software
echo    2. Baixe: "Arduino IDE 2.3.2 for Windows"
echo    3. Execute o instalador
echo    4. Instale com configurações padrão
echo.

pause

echo [2/3] Configurações pós-instalação...
echo.
echo ⚙️ CONFIGURAR ARDUINO IDE:
echo    1. Abra Arduino IDE
echo    2. File ^> Preferences
echo    3. Additional Board Manager URLs:
echo       https://dl.espressif.com/dl/package_esp32_index.json
echo    4. Clique OK
echo.

pause

echo [3/3] Instalar suporte ESP32...
echo.
echo 📦 INSTALAR ESP32:
echo    1. Tools ^> Board ^> Boards Manager
echo    2. Pesquise: "ESP32"
echo    3. Instale: "ESP32 by Espressif Systems"
echo    4. Aguarde instalação completa
echo.

pause

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📚 INSTALAR BIBLIOTECAS                                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📚 BIBLIOTECAS NECESSÁRIAS:
echo    Tools ^> Manage Libraries... e instale:
echo.
echo    1. LoRa by Sandeep Mistry
echo    2. ArduinoJson by Benoit Blanchon
echo    3. Adafruit SSD1306 by Adafruit
echo    4. Adafruit GFX Library by Adafruit
echo.

pause

echo.
echo ✅ INSTALAÇÃO CONCLUÍDA!
echo.
echo 🎯 PRÓXIMOS PASSOS:
echo    1. Conecte a placa ESP32 via USB
echo    2. Execute: configurar_esp32_completo.bat
echo.

pause