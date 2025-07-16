@echo off
echo ===================================================
echo    INICIANDO SISTEMA DE BARREIRAS IOT
echo ===================================================
echo.

echo [1/4] Verificando se o banco de dados foi inicializado...
cd backend
php artisan migrate:status > nul 2>&1
if %errorlevel% neq 0 (
    echo O banco de dados precisa ser inicializado.
    echo Executando migrações e seeders...
    php artisan migrate:fresh --seed
) else (
    echo Banco de dados já inicializado.
)
cd ..
echo.

echo [2/4] Iniciando o servidor Laravel...
start cmd /k "cd backend && php artisan serve"
timeout /t 3 >nul

echo [3/4] Iniciando o servidor WebSocket...
start cmd /k "cd backend && php artisan websockets:serve"
timeout /t 2 >nul

echo [4/4] Iniciando o servidor frontend...
start cmd /k "cd frontend && php -S localhost:8080"
timeout /t 2 >nul

echo.
echo ===================================================
echo    SISTEMA INICIADO COM SUCESSO!
echo ===================================================
echo.
echo Frontend: http://localhost:8080
echo Backend API: http://localhost:8000/api/v1
echo WebSocket: ws://localhost:6001
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
echo IMPORTANTE: Se você tiver problemas de login, execute:
echo cd backend ^&^& php artisan migrate:fresh --seed
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
