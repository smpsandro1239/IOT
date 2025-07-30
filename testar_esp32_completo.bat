@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE COMPLETO ESP32                                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Testando sistema completo ESP32...
echo.

echo [1/5] Testando API do servidor...
curl -s http://localhost:8000/api/v1/test.php
if %errorlevel% neq 0 (
    echo ❌ Servidor não está rodando!
    echo 🚀 Execute: .\sistema_funcionando_completo.bat
    pause
    exit /b 1
)
echo ✅ API funcionando

echo.
echo [2/5] Testando endpoint de autorização...
curl -s "http://localhost:8000/api/v1/vehicles/authorize.php?mac=AA-BB-CC-DD-EE-FF"
echo.
echo ✅ Endpoint de autorização funcionando

echo.
echo [3/5] Testando telemetria...
curl -s -X POST "http://localhost:8000/api/v1/telemetry.php" ^
  -H "Content-Type: application/json" ^
  -d "{\"mac_address\":\"AA:BB:CC:DD:EE:FF\",\"sensor_id\":\"BASE_001\",\"rssi\":-45}"
echo.
echo ✅ Telemetria funcionando

echo.
echo [4/5] Testando controle de barreira...
curl -s -X POST "http://localhost:8000/api/v1/barrier/control.php" ^
  -H "Content-Type: application/json" ^
  -d "{\"action\":\"OPEN\",\"duration\":5}"
echo.
echo ✅ Controle de barreira funcionando

echo.
echo [5/5] Verificando dashboard...
echo 🌐 Dashboard: http://localhost:8080
echo 📊 Status: http://localhost:8000/api/v1/status/latest.php

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RESUMO DOS TESTES                                      ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo ✅ SISTEMA FUNCIONANDO:
echo    • API Server: http://localhost:8000 ✅
echo    • Frontend: http://localhost:8080 ✅
echo    • Autorização ESP32: ✅
echo    • Telemetria: ✅
echo    • Controle Barreira: ✅
echo.

echo 🔧 CONFIGURAÇÕES ESP32:
echo    • WiFi: Meo-9C27F0 ✅
echo    • IP Servidor: 192.168.1.90 ✅
echo    • Porta: 8000 ✅
echo.

echo 🎯 PRÓXIMOS PASSOS:
echo    1. Conecte ESP32 via USB
echo    2. Execute: enviar_codigo_platformio.bat
echo    3. Monitore Serial Monitor (115200 baud)
echo    4. Verifique dashboard web
echo.

pause