@echo off
chcp 65001 >nul
echo ===================================================
echo    INICIALIZAÇÃO COMPLETA - BARREIRAS IOT
echo    Inclui verificações e configuração Git
echo ===================================================
echo.

echo [1/4] Verificando Git e configuração...
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Git não encontrado - continuando sem Git
) else (
    echo ✅ Git encontrado
    
    :: Verificar configuração do Git
    git config --global user.name >nul 2>&1
    if %errorlevel% neq 0 (
        echo.
        echo 🔧 Git não está configurado
        echo.
        set /p "CONFIGURAR_GIT=Deseja configurar o Git agora? (s/n): "
        if /i "%CONFIGURAR_GIT%"=="s" (
            echo.
            set /p "GIT_NAME=Digite seu nome para o Git: "
            set /p "GIT_EMAIL=Digite seu email para o Git: "
            
            git config --global user.name "%GIT_NAME%"
            git config --global user.email "%GIT_EMAIL%"
            
            echo ✅ Git configurado com sucesso
            echo    Nome: %GIT_NAME%
            echo    Email: %GIT_EMAIL%
        )
    ) else (
        echo ✅ Git já configurado
        for /f "delims=" %%i in ('git config --global user.name') do echo    👤 Nome: %%i
        for /f "delims=" %%i in ('git config --global user.email') do echo    📧 Email: %%i
    )
)

echo.
echo [2/4] Verificando pré-requisitos essenciais...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: PHP não encontrado!
    echo    Execute: verificar_requisitos.bat
    pause
    exit /b 1
)

composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Composer não encontrado!
    echo    Execute: verificar_requisitos.bat
    pause
    exit /b 1
)

node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Node.js não encontrado!
    echo    Execute: verificar_requisitos.bat
    pause
    exit /b 1
)

echo ✅ Pré-requisitos OK

echo.
echo [3/4] Parando servidores anteriores...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
ping 127.0.0.1 -n 2 >nul

echo [4/4] Iniciando sistema...
echo.

:: Verificar se backend está configurado
cd backend
if not exist .env (
    echo ❌ Sistema não configurado
    echo    Execute: configurar_novo_computador_v2.bat
    pause
    exit /b 1
)

:: Verificar banco de dados
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Problema com banco de dados
    echo    Execute: criar_banco.bat
    pause
    exit /b 1
)

echo ✅ Backend configurado
echo Iniciando Laravel...
start "Laravel Backend" cmd /k "echo ✅ Backend: http://localhost:8000 && echo 🔐 API: http://localhost:8000/api && php artisan serve"
ping 127.0.0.1 -n 3 >nul

cd ..\frontend

:: Verificar se frontend está configurado
if not exist node_modules (
    echo ❌ Frontend não configurado
    echo    Execute: configurar_novo_computador_v2.bat
    pause
    exit /b 1
)

if not exist css\tailwind-local.css (
    echo Compilando CSS...
    call npm run build:css >nul 2>&1
)

echo ✅ Frontend configurado
echo Iniciando servidor web...
start "Frontend Server" cmd /k "echo ✅ Frontend: http://localhost:8080 && echo 🔐 Login: admin@example.com / password && php -S localhost:8080"
ping 127.0.0.1 -n 2 >nul

cd ..

echo.
echo ===================================================
echo    ✅ SISTEMA INICIADO COM SUCESSO!
echo ===================================================
echo.
echo 🌐 URLs do sistema:
echo    Frontend: http://localhost:8080
echo    Backend API: http://localhost:8000/api
echo.
echo 🔐 Credenciais de acesso:
echo    Email: admin@example.com
echo    Senha: password
echo.
echo 📊 Funcionalidades disponíveis:
echo    - Dashboard em tempo real
echo    - Gerenciamento de MACs autorizados
echo    - Modo simulação (sem hardware)
echo    - Logs de acesso
echo    - Métricas do sistema
echo.
echo 💡 Dicas:
echo    - Use F12 para abrir console do navegador
echo    - Teste o modo simulação primeiro
echo    - Verifique logs em backend/storage/logs/
echo.
echo ⚠️  Aguarde alguns segundos para os servidores iniciarem
echo.
pause