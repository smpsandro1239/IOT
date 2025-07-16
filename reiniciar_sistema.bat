@echo off
echo ===================================================
echo    REINICIALIZACAO DO SISTEMA DE BARREIRAS IOT
echo ===================================================
echo.

echo [1/6] Parando servidores em execucao...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
timeout /t 2 >nul

echo [2/6] Reinstalando Laravel Sanctum...
cd backend
call composer require laravel/sanctum
call php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
cd ..
timeout /t 2 >nul

echo [3/6] Reiniciando o banco de dados...
cd backend
call php artisan migrate:fresh --seed
cd ..
echo.

echo [4/6] Iniciando o servidor Laravel...
start cmd /k "cd backend && php artisan serve"
timeout /t 5 >nul

echo [5/6] Iniciando o servidor WebSocket...
start cmd /k "cd backend && php artisan websockets:serve"
timeout /t 3 >nul

echo [6/6] Iniciando o servidor frontend...
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
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
