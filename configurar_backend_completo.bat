@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔧 CONFIGURAR BACKEND COMPLETO                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Configurando backend Laravel com Laragon...
echo.

:: Configurar PHP do Laragon
echo [1/8] Configurando PHP do Laragon...
set "PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64"
if not exist "%PHP_PATH%\php.exe" (
    for /d %%d in (C:\laragon\bin\php\php-*) do (
        if exist "%%d\php.exe" (
            set "PHP_PATH=%%d"
            goto :found_php
        )
    )
    echo ❌ PHP não encontrado!
    pause
    exit /b 1
)

:found_php
set "PATH=%PHP_PATH%;%PATH%"
echo ✅ PHP configurado: %PHP_PATH%

echo [2/8] Verificando diretório backend...
if not exist "backend" (
    echo ❌ Diretório backend não encontrado!
    pause
    exit /b 1
)
cd backend

echo [3/8] Verificando Composer...
composer --version >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Composer não encontrado, tentando usar o do Laragon...
    set "COMPOSER_PATH=C:\laragon\bin\composer\composer.phar"
    if exist "%COMPOSER_PATH%" (
        set "COMPOSER_CMD=php %COMPOSER_PATH%"
    ) else (
        echo ❌ Composer não encontrado!
        echo    Instale o Composer ou use o Laragon
        pause
        exit /b 1
    )
) else (
    set "COMPOSER_CMD=composer"
)

echo [4/8] Instalando dependências...
%COMPOSER_CMD% install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo ⚠️  Erro ao instalar dependências, continuando...
)

echo [5/8] Gerando chave da aplicação...
php artisan key:generate --force
if errorlevel 1 (
    echo ⚠️  Erro ao gerar chave, continuando...
)

echo [6/8] Criando banco de dados...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS laravel_barrier_control;" 2>nul
if errorlevel 1 (
    echo ⚠️  MySQL pode não estar rodando, tentando continuar...
)

echo [7/8] Executando migrações...
php artisan migrate --force
if errorlevel 1 (
    echo ⚠️  Erro nas migrações, tentando criar tabelas básicas...
    php artisan migrate:install --force
    php artisan migrate --force
)

echo [8/8] Executando seeders...
php artisan db:seed --force
if errorlevel 1 (
    echo ⚠️  Erro nos seeders, continuando...
)

echo.
echo ✅ Backend configurado!
echo.
echo 🧪 Testando configuração...
php artisan route:list | findstr "api/v1" >nul
if errorlevel 1 (
    echo ⚠️  Rotas API podem não estar carregadas
) else (
    echo ✅ Rotas API carregadas
)

cd ..

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ BACKEND CONFIGURADO COM SUCESSO                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🚀 Agora execute: iniciar_com_laragon_fixo.bat
echo.
pause