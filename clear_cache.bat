@echo off
chcp 65001 >nul
echo ===================================================
echo    LIMPEZA COMPLETA DE CACHE - BARREIRAS IOT
echo ===================================================
echo.

echo [1/7] Parando servidores...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
timeout /t 2 >nul
echo ✅ Servidores parados

echo [2/7] Limpando cache do Laravel...
cd backend
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
php artisan optimize:clear >nul 2>&1
echo ✅ Cache Laravel limpo

echo [3/7] Limpando autoload do Composer...
composer dump-autoload >nul 2>&1
echo ✅ Autoload recarregado

echo [4/7] Limpando logs antigos...
if exist storage\logs\*.log (
    del /q storage\logs\*.log >nul 2>&1
    echo ✅ Logs limpos
) else (
    echo ✅ Nenhum log para limpar
)

echo [5/7] Limpando cache do Node.js...
cd ..\frontend
if exist node_modules (
    echo Removendo node_modules...
    rmdir /s /q node_modules >nul 2>&1
    echo ✅ node_modules removido
) else (
    echo ✅ node_modules já limpo
)

if exist package-lock.json (
    del package-lock.json >nul 2>&1
    echo ✅ package-lock.json removido
)

echo [6/7] Reinstalando dependências...
call npm install >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao reinstalar dependências Node.js
) else (
    echo ✅ Dependências Node.js reinstaladas
)

echo [7/7] Recompilando CSS...
call npm run build:css >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao compilar CSS
) else (
    echo ✅ CSS recompilado
)

cd ..

echo.
echo ===================================================
echo    ✅ LIMPEZA COMPLETA FINALIZADA!
echo ===================================================
echo.
echo 🧹 O que foi limpo:
echo    - Cache do Laravel (config, routes, views)
echo    - Autoload do Composer
echo    - Logs antigos
echo    - node_modules e package-lock.json
echo    - CSS recompilado
echo.
echo 🚀 Para reiniciar o sistema:
echo    Execute: iniciar_sistema_otimizado.bat
echo.
echo 💡 Se ainda houver problemas:
echo    Execute: configurar_novo_computador.bat
echo.
pause