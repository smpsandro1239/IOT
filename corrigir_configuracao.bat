@echo off
chcp 65001 >nul
echo ===================================================
echo    CORREÇÃO DE CONFIGURAÇÃO - BARREIRAS IOT
echo ===================================================
echo.

echo 🔧 Corrigindo problemas identificados...
echo.

echo [1/6] Criando arquivo .env no backend...
cd backend
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo ✅ Arquivo .env criado
    ) else (
        echo ❌ ERRO: .env.example não encontrado
        pause
        exit /b 1
    )
) else (
    echo ✅ Arquivo .env já existe
)

echo.
echo [2/6] Instalando dependências PHP...
if not exist vendor (
    echo Instalando dependências do Composer...
    call composer install
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao instalar dependências PHP
        echo Tentando com --no-dev...
        call composer install --no-dev
        if %errorlevel% neq 0 (
            echo ❌ ERRO: Falha crítica
            pause
            exit /b 1
        )
    )
    echo ✅ Dependências PHP instaladas
) else (
    echo ✅ Dependências PHP já instaladas
)

echo.
echo [3/6] Gerando chave da aplicação...
php artisan key:generate
echo ✅ Chave gerada

echo.
echo [4/6] Instalando dependências Node.js...
cd ..\frontend
if not exist node_modules (
    echo Instalando dependências do NPM...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao instalar dependências Node.js
        echo Limpando cache do NPM...
        npm cache clean --force
        call npm install
        if %errorlevel% neq 0 (
            echo ❌ ERRO: Falha crítica
            pause
            exit /b 1
        )
    )
    echo ✅ Dependências Node.js instaladas
) else (
    echo ✅ Dependências Node.js já instaladas
)

echo.
echo [5/6] Compilando CSS...
if not exist css\tailwind-local.css (
    echo Compilando CSS com Tailwind...
    call npm run build:css
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao compilar CSS
        pause
        exit /b 1
    )
    echo ✅ CSS compilado
) else (
    echo ✅ CSS já compilado
)

echo.
echo [6/6] Configurando banco de dados...
cd ..\backend

echo.
echo ⚠️  CONFIGURAÇÃO DO BANCO DE DADOS
echo.
echo Suas configurações atuais:
type .env | findstr "DB_"
echo.
echo Para Laragon, use estas configurações no .env:
echo   DB_HOST=127.0.0.1
echo   DB_PORT=3306
echo   DB_DATABASE=laravel_barrier_control
echo   DB_USERNAME=root
echo   DB_PASSWORD=
echo.
echo IMPORTANTE: 
echo 1. Certifique-se de que o Laragon está rodando (Start All)
echo 2. Crie o banco de dados se necessário
echo.

set /p "CONFIGURADO=Banco de dados está configurado e rodando? (s/n): "
if /i "%CONFIGURADO%"=="s" (
    echo Testando conexão...
    php artisan migrate:status >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ Não foi possível conectar ao banco
        echo Criando banco de dados...
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS laravel_barrier_control;" 2>nul
        echo Executando migrações...
        php artisan migrate:fresh --seed
        if %errorlevel% neq 0 (
            echo ❌ ERRO: Falha nas migrações
            echo Execute manualmente: php artisan migrate:fresh --seed
        ) else (
            echo ✅ Banco configurado com sucesso
        )
    ) else (
        echo Executando migrações...
        php artisan migrate:fresh --seed
        echo ✅ Banco configurado
    )
) else (
    echo ⚠️  Configure o banco de dados e execute novamente
)

cd ..

echo.
echo ===================================================
echo    ✅ CORREÇÃO FINALIZADA!
echo ===================================================
echo.
echo 🧪 Para testar a configuração:
echo    teste_configuracao.bat
echo.
echo 🚀 Para iniciar o sistema:
echo    iniciar_sistema_otimizado.bat
echo.
pause