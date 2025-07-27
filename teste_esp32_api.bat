@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE API ESP32 - ENDPOINTS                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Testando endpoints da API para ESP32...
echo.

set "API_BASE=http://localhost:8000/api/v1"

echo [1/6] Testando endpoint de teste básico...
curl -s "%API_BASE%/test.php" | echo.
echo.

echo [2/6] Testando endpoint de status...
curl -s "%API_BASE%/status/latest.php" | echo.
echo.

echo [3/6] Testando endpoint de MACs autorizados...
curl -s "%API_BASE%/macs-autorizados.php" | echo.
echo.

echo [4/6] Testando endpoint de autorização de veículo...
curl -s "%API_BASE%/vehicles/authorize.php?mac=AA-BB-CC-DD-EE-FF" | echo.
echo.

echo [5/6] Testando endpoint de telemetria (GET)...
curl -s "%API_BASE%/telemetry.php" | echo.
echo.

echo [6/6] Testando endpoint de controle de barreira (GET)...
curl -s "%API_BASE%/barrier/control.php" | echo.
echo.

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE POST ENDPOINTS                                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo [POST 1/3] Testando registro de telemetria...
curl -s -X POST "%API_BASE%/telemetry.php" ^
  -H "Content-Type: application/json" ^
  -d "{\"mac_address\":\"AA:BB:CC:DD:EE:FF\",\"sensor_id\":\"BASE_001\",\"rssi\":-45,\"snr\":8.5}" | echo.
echo.

echo [POST 2/3] Testando controle de barreira (OPEN)...
curl -s -X POST "%API_BASE%/barrier/control.php" ^
  -H "Content-Type: application/json" ^
  -d "{\"action\":\"OPEN\",\"barrier_id\":\"main\",\"duration\":5}" | echo.
echo.

echo [POST 3/3] Testando controle de barreira (CLOSE)...
curl -s -X POST "%API_BASE%/barrier/control.php" ^
  -H "Content-Type: application/json" ^
  -d "{\"action\":\"CLOSE\",\"barrier_id\":\"main\"}" | echo.
echo.

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RESUMO DOS TESTES                                      ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo ✅ ENDPOINTS DISPONÍVEIS PARA ESP32:
echo.
echo 🔍 GET Endpoints:
echo    • %API_BASE%/test.php
echo    • %API_BASE%/status/latest.php
echo    • %API_BASE%/macs-autorizados.php
echo    • %API_BASE%/vehicles/authorize.php?mac=MAC_ADDRESS
echo    • %API_BASE%/telemetry.php
echo    • %API_BASE%/barrier/control.php
echo.
echo 📤 POST Endpoints:
echo    • %API_BASE%/telemetry.php (registrar telemetria)
echo    • %API_BASE%/barrier/control.php (controlar barreira)
echo    • %API_BASE%/macs-autorizados.php (adicionar MAC)
echo.
echo 🎯 PRÓXIMOS PASSOS:
echo    1. Configure o WiFi no firmware ESP32
echo    2. Configure o IP do servidor no firmware
echo    3. Faça upload do firmware
echo    4. Teste conectividade via Serial Monitor
echo    5. Monitore logs no dashboard web
echo.
echo 📖 DOCUMENTAÇÃO:
echo    • Leia: CONFIGURACAO_ESP32.md
echo    • Configure: base/src/main.cpp
echo    • Monitore: http://localhost:8080
echo.

pause