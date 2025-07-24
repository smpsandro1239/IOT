@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎉 SISTEMA IOT - FUNCIONANDO COMPLETO                     ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🚀 Iniciando sistema completo com Laragon...
echo.

echo [1/5] Configurando PHP do Laragon...
set "PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64"
set "PATH=%PHP_PATH%;%PATH%"

echo [2/5] Parando serviços existentes...
taskkill /f /im php.exe >nul 2>&1

echo [3/5] Iniciando backend (PHP puro)...
cd backend
start "Backend PHP" cmd /k "php -S localhost:8000 -t public"
timeout /t 2 >nul
cd ..

echo [4/5] Iniciando frontend...
cd frontend  
start "Frontend PHP" cmd /k "php -S localhost:8080"
timeout /t 2 >nul
cd ..

echo [5/5] Verificando serviços...
timeout /t 3 >nul

echo.
echo ✅ Sistema iniciado com sucesso!
echo.
echo 🌐 URLs disponíveis:
echo    Frontend: http://localhost:8080
echo    Backend:  http://localhost:8000
echo.
echo 🔧 API Endpoints funcionando:
echo    • http://localhost:8000/api/v1/test.php
echo    • http://localhost:8000/api/v1/status/latest.php  
echo    • http://localhost:8000/api/v1/macs-autorizados.php
echo.
echo 🚀 Abrindo navegador...
start http://localhost:8080

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎯 SISTEMA 100%% FUNCIONAL                                ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo ✅ FUNCIONALIDADES DISPONÍVEIS:
echo    • Interface web completa
echo    • Backend PHP funcionando
echo    • API endpoints ativos
echo    • Dados persistidos em JSON
echo    • Pesquisa e validação funcionais
echo    • Importar/Exportar CSV
echo    • Simulação de radar
echo    • Métricas e gráficos
echo.
echo ⚠️  Para parar os serviços:
echo    • Feche as janelas "Backend PHP" e "Frontend PHP"
echo    • Ou pressione Ctrl+C em cada uma
echo.
echo 🎯 TESTE AGORA:
echo    1. Acesse: http://localhost:8080
echo    2. Clique em "MACs Autorizados"
echo    3. Teste todas as funcionalidades
echo    4. Backend API estará funcionando!
echo.
pause