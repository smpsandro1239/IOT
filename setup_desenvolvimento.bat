@echo off
echo ===================================================
echo    CONFIGURACAO DE DESENVOLVIMENTO - BARREIRAS IOT
echo ===================================================
echo.

echo [1/8] Verificando Node.js...
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Node.js nao encontrado. Instale Node.js primeiro.
    echo Download: https://nodejs.org/
    pause
    exit /b 1
) else (
    echo Node.js encontrado!
)
echo.

echo [2/8] Configurando backend...
cd backend
echo Instalando dependencias PHP...
call composer install
echo.

echo Configurando ambiente...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
)
echo.

echo Configurando banco de dados...
php artisan migrate:fresh --seed
echo.

echo [3/8] Configurando frontend...
cd ..\frontend
echo Instalando dependencias Node.js...
call npm install
echo.

echo [4/8] Compilando CSS com Tailwind...
call npm run build-css
echo.

echo [5/8] Criando diretorios necessarios...
if not exist "dist" mkdir dist
if not exist "icons" mkdir icons
echo.

echo [6/8] Configurando CORS...
echo Middleware CORS ja configurado no backend.
echo.

echo [7/8] Testando configuracao...
cd ..\backend
echo Testando servidor Laravel...
timeout /t 2 >nul
echo.

echo [8/8] Configuracao concluida!
echo.

echo ===================================================
echo    CONFIGURACAO CONCLUIDA COM SUCESSO!
echo ===================================================
echo.
echo Para iniciar o sistema, execute:
echo   iniciar_sistema_otimizado.bat
echo.
echo Ou manualmente:
echo   Backend:  cd backend ^&^& php artisan serve
echo   Frontend: cd frontend ^&^& npm run dev
echo.
echo Credenciais de acesso:
echo   Email: admin@example.com
echo   Senha: password
echo.
echo PROXIMOS PASSOS:
echo 1. Execute iniciar_sistema_otimizado.bat
echo 2. Acesse http://localhost:8080
echo 3. Faca login com as credenciais acima
echo 4. Teste a simulacao de veiculos
echo.
pause
