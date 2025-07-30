@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📚 INSTALAR BIBLIOTECAS ESP32                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📦 Instalando bibliotecas necessárias para ESP32...
echo.

echo [1/5] Atualizando índice de bibliotecas...
arduino-cli lib update-index
echo ✅ Índice atualizado

echo.
echo [2/5] Instalando LoRa by Sandeep Mistry...
arduino-cli lib install "LoRa"
echo ✅ LoRa instalada

echo.
echo [3/5] Instalando ArduinoJson...
arduino-cli lib install "ArduinoJson"
echo ✅ ArduinoJson instalada

echo.
echo [4/5] Instalando Adafruit SSD1306...
arduino-cli lib install "Adafruit SSD1306"
echo ✅ Adafruit SSD1306 instalada

echo.
echo [5/5] Instalando Adafruit GFX Library...
arduino-cli lib install "Adafruit GFX Library"
echo ✅ Adafruit GFX instalada

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ BIBLIOTECAS INSTALADAS!                               ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📚 BIBLIOTECAS INSTALADAS:
echo    ✅ LoRa by Sandeep Mistry
echo    ✅ ArduinoJson by Benoit Blanchon
echo    ✅ Adafruit SSD1306 by Adafruit
echo    ✅ Adafruit GFX Library by Adafruit
echo.

echo 🎯 PRÓXIMO PASSO:
echo    Execute: configurar_esp32_completo.bat
echo.

pause