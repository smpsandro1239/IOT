@echo off
chcp 65001 >nul
echo ===================================================
echo    INICIALIZAÇÃO RÁPIDA - BARREIRAS IOT
echo ===================================================
echo.

echo 🚀 Iniciando Sistema de Barreiras IoT...
echo.

echo [1/3] Parando servidores anteriores...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
ping 127.0.0.1 -n 2 >nul

echo [2/3] Iniciando Backend Laravel...
cd backend
start "Laravel Backend" cmd /k "echo ✅ Backend: http://localhost:8000 && php artisan serve"
ping 127.0.0.1 -n 3 >nul

echo [3/3] Iniciando Frontend...
cd ..\frontend
start "Frontend Server" cmd /k "echo ✅ Frontend: http://localhost:8080 && php -S localhost:8080"
ping 127.0.0.1 -n 2 >nul

cd ..

echo.
echo ===================================================
echo    ✅ SISTEMA INICIADO!
echo ===================================================
echo.
echo 🌐 Acesse: http://localhost:8080
echo 🔐 Login: admin@example.com / password
echo.
echo ⚠️  Aguarde alguns segundos para os servidores iniciarem
echo.
pause