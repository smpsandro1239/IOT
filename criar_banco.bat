@echo off
chcp 65001 >nul
echo ===================================================
echo    CRIAÇÃO DO BANCO DE DADOS - BARREIRAS IOT
echo ===================================================
echo.

echo 🗄️  Criando banco de dados MySQL...
echo.

echo Tentando criar banco de dados...
mysql -u root -p1234567890aa -e "CREATE DATABASE IF NOT EXISTS laravel_barrier_control;" 2>nul
if %errorlevel% neq 0 (
    echo ❌ ERRO: Não foi possível conectar ao MySQL
    echo.
    echo Possíveis soluções:
    echo 1. Certifique-se de que o MySQL está rodando
    echo 2. No Laragon: clique em "Start All"
    echo 3. Verifique se o MySQL está no PATH
    echo.
    echo Comando manual (no MySQL):
    echo CREATE DATABASE laravel_barrier_control;
    echo.
    pause
    exit /b 1
) else (
    echo ✅ Banco de dados 'laravel_barrier_control' criado/verificado
)

echo.
echo Testando conexão com Laravel...
cd backend
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Laravel não consegue conectar ao banco
    echo Verifique as configurações no arquivo .env:
    echo.
    type .env | findstr "DB_"
    echo.
    pause
    exit /b 1
) else (
    echo ✅ Laravel conectado ao banco com sucesso
)

echo.
echo Executando migrações...
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao executar migrações
    echo Tentando sem seed...
    php artisan migrate:fresh
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha crítica nas migrações
        pause
        exit /b 1
    ) else (
        echo ✅ Migrações executadas (sem dados de teste)
    )
) else (
    echo ✅ Banco configurado com dados de teste
)

cd ..

echo.
echo ===================================================
echo    ✅ BANCO DE DADOS CONFIGURADO!
echo ===================================================
echo.
echo 🔐 Credenciais criadas:
echo    Email: admin@example.com
echo    Senha: password
echo.
echo 🧪 Para testar: teste_configuracao.bat
echo 🚀 Para iniciar: iniciar_sistema_otimizado.bat
echo.
pause