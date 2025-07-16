@echo off
echo ===================================================
echo    SISTEMA DE BARREIRAS IOT - VERSAO OTIMIZADA
echo ===================================================
echo.

echo [1/6] Parando servidores em execucao...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
timeout /t 2 >nul

echo [2/6] Limpando cache do Laravel...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
REM Removido view:clear - projeto API nao usa views Blade
cd ..
echo.

echo [3/6] Verificando dependencias...
cd backend
call composer install --no-dev --optimize-autoloader
cd ..
echo.

echo [4/6] Configurando o ambiente...
cd backend
if not exist .env (
    copy .env.example .env
    php artisan key:generate
)
cd ..
echo.

echo [5/6] Reiniciando o banco de dados...
cd backend
php artisan migrate:fresh --seed
cd ..
echo.

echo [6/6] Iniciando servidores...
echo Iniciando servidor Laravel (Backend)...
start cmd /k "cd backend && php artisan serve"
timeout /t 3 >nul

echo Iniciando servidor Frontend com proxy CORS...
start cmd /k "cd frontend && php -S localhost:8080 router.php"
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
echo MELHORIAS IMPLEMENTADAS:
echo - Removido laravel-websockets abandonado
echo - Corrigido erro view:clear
echo - Proxy CORS integrado
echo - Cache otimizado
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
