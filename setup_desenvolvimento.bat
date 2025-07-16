@echo off
echo ===================================================
echo    CONFIGURACAO DO AMBIENTE DE DESENVOLVIMENTO
echo ===================================================
echo.

echo [1/8] Verificando Node.js...
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Node.js nao encontrado. Instale o Node.js primeiro.
    echo Download: https://nodejs.org/
    pause
    exit /b 1
) else (
    echo Node.js encontrado!
)
echo.

echo [2/8] Configurando frontend...
cd frontend
call npm install
echo.

echo [3/8] Compilando CSS do Tailwind...
call npx tailwindcss -i src/input.css -o dist/output.css --minify
echo.

echo [4/8] Configurando backend...
cd ..\backend
call composer install --optimize-autoloader
echo.

echo [5/8] Configurando ambiente Laravel...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
)
echo.

echo [6/8] Configurando banco de dados...
php artisan migrate:fresh --seed
echo.

echo [7/8] Limpando cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
echo.

echo [8/8] Testando configuracao...
php artisan --version
cd ..\frontend
node --version
echo.

echo ===================================================
echo    AMBIENTE CONFIGURADO COM SUCESSO!
echo ===================================================
echo.
echo Para iniciar o sistema, execute:
echo iniciar_sistema_otimizado.bat
echo.
echo Ou manualmente:
echo 1. Backend: cd backend ^&^& php artisan serve
echo 2. Frontend: cd frontend ^&^& php -S localhost:8080 router.php
echo.
pause
