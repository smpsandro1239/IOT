@echo off
echo ===================================================
echo    CONFIGURACAO COMPLETA DO SISTEMA DE BARREIRAS IOT
echo ===================================================
echo.

echo [1/7] Parando servidores em execucao...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
timeout /t 2 >nul

echo [2/7] Limpando cache do Laravel...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
REM Removido view:clear pois este projeto é uma API e não usa views Blade
cd ..
echo.

echo [3/7] Verificando dependencias...
cd backend
call composer install
cd ..
echo.

echo [4/7] Configurando o ambiente...
cd backend
if not exist .env (
    copy .env.example .env
    php artisan key:generate
)
cd ..
echo.

echo [5/7] Reiniciando o banco de dados...
cd backend
php artisan migrate:fresh --seed
cd ..
echo.

echo [6/7] Iniciando o servidor Laravel...
start cmd /k "cd backend && php artisan serve"
timeout /t 5 >nul

echo [7/7] Iniciando o servidor frontend...
start cmd /k "cd frontend && php -S localhost:8080"
timeout /t 2 >nul

echo.
echo ===================================================
echo    SISTEMA INICIADO COM SUCESSO!
echo ===================================================
echo.
echo Frontend: http://localhost:8080
echo Backend API: http://localhost:8000/api
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
echo IMPORTANTE: Se o login nao funcionar, verifique:
echo 1. Se o banco de dados foi criado corretamente
echo 2. Se o servidor Laravel esta rodando
echo 3. Se as credenciais estao corretas
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
