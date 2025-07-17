@echo off
echo ===================================================
echo    REINICIANDO O SISTEMA DE BARREIRAS IOT (CORRIGIDO)
echo ===================================================
echo.

echo [1/5] Parando servidores em execucao...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
timeout /t 2 >nul

echo [2/5] Limpando cache...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
cd ..
echo.

echo [3/5] Verificando estrutura de pastas...
if not exist frontend\api\v1\metrics mkdir frontend\api\v1\metrics
if not exist frontend\api\v1\status\latest mkdir frontend\api\v1\status\latest
if not exist frontend\api\v1\gate\control mkdir frontend\api\v1\gate\control
if not exist frontend\api\v1\access-logs mkdir frontend\api\v1\access-logs
if not exist frontend\api\v1\macs-autorizados mkdir frontend\api\v1\macs-autorizados
echo.

echo [4/5] Iniciando o servidor Laravel...
start cmd /k "cd backend && php artisan serve --host=localhost --port=8000"
timeout /t 3 >nul

echo [5/5] Iniciando o servidor frontend...
start cmd /k "cd frontend && php -S localhost:8080 -t . router.php"
timeout /t 2 >nul

echo.
echo ===================================================
echo    SISTEMA REINICIADO COM SUCESSO!
echo ===================================================
echo.
echo Frontend: http://localhost:8080
echo Backend API: http://localhost:8000/api
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
echo IMPORTANTE: O sistema agora está usando APIs simuladas para desenvolvimento.
echo Todas as funcionalidades devem funcionar corretamente no frontend.
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
