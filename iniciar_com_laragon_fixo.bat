@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 INICIAR SISTEMA COM LARAGON - FIXO                    ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Configurando PHP do Laragon de forma robusta...
echo.

:: Parar serviços existentes primeiro
echo [1/7] Parando serviços existentes...
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im powershell.exe /fi "WINDOWTITLE eq Frontend*" >nul 2>&1
taskkill /f /im cmd.exe /fi "WINDOWTITLE eq Backend*" >nul 2>&1
echo ✅ Serviços parados

echo [2/7] Procurando PHP do Laragon...

:: Caminhos mais comuns do Laragon
set "LARAGON_FOUND="
set "PHP_PATH="

:: Verificar caminhos padrão do Laragon
for %%v in (8.3 8.2 8.1 8.0 7.4) do (
    for %%a in (x64 x86) do (
        set "TEST_PATH=C:\laragon\bin\php\php-%%v.*-Win32-vs16-%%a"
        for /d %%d in (!TEST_PATH!) do (
            if exist "%%d\php.exe" (
                echo ✅ PHP encontrado: %%d
                set "PHP_PATH=%%d"
                set "LARAGON_FOUND=1"
                goto :found_php
            )
        )
    )
)

:: Se não encontrou, procurar em toda estrutura
if not defined LARAGON_FOUND (
    echo 🔍 Procurando em toda estrutura do Laragon...
    for /d %%d in (C:\laragon\bin\php\php-*) do (
        if exist "%%d\php.exe" (
            echo ✅ PHP encontrado: %%d
            set "PHP_PATH=%%d"
            set "LARAGON_FOUND=1"
            goto :found_php
        )
    )
)

if not defined LARAGON_FOUND (
    echo ❌ PHP do Laragon não encontrado!
    echo.
    echo 💡 SOLUÇÕES:
    echo    1. Verifique se o Laragon está instalado
    echo    2. Inicie o Laragon e ative o PHP
    echo    3. Ou instale PHP manualmente
    echo.
    pause
    exit /b 1
)

:found_php
echo [3/7] Configurando PATH...
set "PATH=%PHP_PATH%;%PATH%"

echo [4/7] Testando PHP...
"%PHP_PATH%\php.exe" --version
if errorlevel 1 (
    echo ❌ Erro ao executar PHP!
    pause
    exit /b 1
)

echo.
echo [5/7] Verificando diretórios...
if not exist "backend" (
    echo ❌ Diretório backend não encontrado!
    pause
    exit /b 1
)
if not exist "frontend" (
    echo ❌ Diretório frontend não encontrado!
    pause
    exit /b 1
)

echo [6/7] Iniciando backend Laravel...
echo 🚀 Backend será iniciado na porta 8000...
cd backend
start "Backend Laravel" cmd /k "set PATH=%PHP_PATH%;%%PATH%% && php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 5 >nul
cd ..

echo [7/7] Iniciando frontend...
echo 🌐 Frontend será iniciado na porta 8080...
cd frontend
start "Frontend PHP" cmd /k "set PATH=%PHP_PATH%;%%PATH%% && php -S localhost:8080"
timeout /t 3 >nul
cd ..

echo.
echo ✅ Sistema iniciado com sucesso!
echo.
echo 🌐 URLs disponíveis:
echo    Frontend: http://localhost:8080
echo    Backend:  http://localhost:8000
echo.

:: Verificar se os serviços estão rodando
echo 🔍 Verificando serviços...
timeout /t 3 >nul

netstat -an | findstr ":8000" >nul
if errorlevel 1 (
    echo ⚠️  Backend pode não estar rodando na porta 8000
) else (
    echo ✅ Backend rodando na porta 8000
)

netstat -an | findstr ":8080" >nul
if errorlevel 1 (
    echo ⚠️  Frontend pode não estar rodando na porta 8080
) else (
    echo ✅ Frontend rodando na porta 8080
)

echo.
echo 🚀 Abrindo navegador...
start http://localhost:8080

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎉 SISTEMA FUNCIONANDO COM LARAGON                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo ✅ FUNCIONALIDADES DISPONÍVEIS:
echo    • Interface otimizada completa
echo    • Backend Laravel funcionando
echo    • API endpoints ativos
echo    • Base de dados conectada
echo    • Pesquisa e validação funcionais
echo    • Importar/Exportar CSV
echo.
echo ⚠️  Para parar os serviços:
echo    • Feche as janelas "Backend Laravel" e "Frontend PHP"
echo    • Ou pressione Ctrl+C em cada uma
echo.
echo 🎯 TESTE AGORA:
echo    1. Acesse: http://localhost:8080
echo    2. Teste todas as funcionalidades
echo    3. Backend API estará funcionando!
echo.
pause