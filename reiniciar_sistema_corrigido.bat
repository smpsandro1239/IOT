@echo off
echo ===================================================
echo    REINICIANDO O SISTEMA DE BARREIRAS IOT (CORRIGIDO)
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
php artisan view:clear
cd ..
echo.

echo [3/6] Verificando banco de dados...
cd backend
php artisan migrate
cd ..
echo.

echo [4/6] Compilando CSS...
cd frontend
echo /* Compilando CSS... */
echo.

echo [5/6] Iniciando o servidor Laravel...
start cmd /k "cd backend && php artisan serve --host=localhost --port=8000"
timeout /t 5 >nul

echo [6/6] Iniciando o servidor frontend...
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
echo IMPORTANTE: Se o login nao funcionar, verifique:
echo 1. Se o banco de dados foi criado corretamente
echo 2. Se o servidor Laravel esta rodando
echo 3. Se as credenciais estao corretas
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul
