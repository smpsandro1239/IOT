@echo off
chcp 65001 >nul
echo ===================================================
echo    VERIFICAÇÃO DE PRÉ-REQUISITOS
echo    Sistema de Controle de Barreiras IoT
echo ===================================================
echo.

set "TODOS_OK=1"

echo 🔍 Verificando pré-requisitos necessários...
echo.

:: Verificar PHP
echo [1/6] PHP 8.1+
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP não encontrado
    echo    📥 Instale PHP 8.1+ ou use Laragon/XAMPP
    echo    🔗 Laragon: https://laragon.org/download/
    set "TODOS_OK=0"
) else (
    for /f "tokens=2" %%i in ('php --version ^| findstr /C:"PHP"') do (
        echo ✅ PHP %%i encontrado
        goto php_ok
    )
    :php_ok
)

:: Verificar Composer
echo [2/6] Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer não encontrado
    echo    📥 Instale Composer
    echo    🔗 https://getcomposer.org/download/
    set "TODOS_OK=0"
) else (
    for /f "tokens=3" %%i in ('composer --version ^| findstr /C:"Composer"') do (
        echo ✅ Composer %%i encontrado
        goto composer_ok
    )
    :composer_ok
)

:: Verificar Node.js
echo [3/6] Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js não encontrado
    echo    📥 Instale Node.js LTS
    echo    🔗 https://nodejs.org/
    set "TODOS_OK=0"
) else (
    for /f %%i in ('node --version') do (
        echo ✅ Node.js %%i encontrado
    )
)

:: Verificar NPM
echo [4/6] NPM
npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ NPM não encontrado
    echo    📥 NPM vem com Node.js
    set "TODOS_OK=0"
) else (
    for /f %%i in ('npm --version') do (
        echo ✅ NPM %%i encontrado
    )
)

:: Verificar MySQL
echo [5/6] MySQL/MariaDB
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  MySQL não encontrado no PATH
    echo    📥 Instale MySQL/MariaDB ou use Laragon
    echo    🔗 MySQL: https://dev.mysql.com/downloads/
    echo    🔗 Laragon (recomendado): https://laragon.org/
    echo    ℹ️  Laragon inclui MySQL, PHP, Composer
) else (
    for /f "tokens=3" %%i in ('mysql --version ^| findstr /C:"mysql"') do (
        echo ✅ MySQL %%i encontrado
        goto mysql_ok
    )
    :mysql_ok
)

:: Verificar Git
echo [6/6] Git
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Git não encontrado
    echo    📥 Instale Git para controle de versão
    echo    🔗 https://git-scm.com/download/win
) else (
    for /f "tokens=3" %%i in ('git --version') do (
        echo ✅ Git %%i encontrado
    )
)

echo.
echo ===================================================

if "%TODOS_OK%"=="1" (
    echo ✅ TODOS OS PRÉ-REQUISITOS ATENDIDOS!
    echo.
    echo 🚀 Você pode prosseguir com a configuração:
    echo    Execute: configurar_novo_computador.bat
) else (
    echo ❌ ALGUNS PRÉ-REQUISITOS ESTÃO FALTANDO
    echo.
    echo 💡 RECOMENDAÇÃO RÁPIDA:
    echo    Instale Laragon - inclui PHP, MySQL, Composer
    echo    🔗 https://laragon.org/download/
    echo.
    echo    Depois instale Node.js separadamente
    echo    🔗 https://nodejs.org/
    echo.
    echo ⚠️  Execute este script novamente após instalar
)

echo.
echo ===================================================
echo.
echo 📋 RESUMO DO PROJETO:
echo    - Backend: Laravel 10 (PHP 8.1+)
echo    - Frontend: HTML/JS/Tailwind CSS
echo    - Banco: MySQL/MariaDB
echo    - Firmware: ESP32 (Arduino IDE)
echo.
echo 🔧 ESTRUTURA:
echo    backend/     - API Laravel
echo    frontend/    - Interface web
echo    base/        - Firmware ESP32 base
echo    auto/        - Firmware detecção automática
echo    direcao/     - Firmware detecção direção
echo.
pause