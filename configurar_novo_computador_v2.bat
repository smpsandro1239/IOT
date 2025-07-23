@echo off
chcp 65001 >nul
echo ===================================================
echo    CONFIGURAÇÃO COMPLETA - NOVO COMPUTADOR V2
echo    Sistema de Controle de Barreiras IoT
echo ===================================================
echo.

echo [PASSO 1/12] Verificando pré-requisitos...
echo.

:: Verificar PHP
echo Verificando PHP...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: PHP não encontrado!
    echo    Instale PHP 8.1+ ou use Laragon/XAMPP
    pause
    exit /b 1
) else (
    echo ✅ PHP encontrado!
)

:: Verificar Composer
echo Verificando Composer...
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Composer não encontrado!
    echo    Instale Composer: https://getcomposer.org/download/
    pause
    exit /b 1
) else (
    echo ✅ Composer encontrado!
)

:: Verificar Node.js
echo Verificando Node.js...
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Node.js não encontrado!
    echo    Instale Node.js: https://nodejs.org/
    pause
    exit /b 1
) else (
    echo ✅ Node.js encontrado!
)

echo.
echo [PASSO 2/12] Configurando Backend Laravel...
cd backend

echo Instalando dependências PHP...
call composer install
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao instalar dependências PHP
    echo Tentando novamente...
    call composer install --no-dev --optimize-autoloader
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha crítica ao instalar dependências PHP
        pause
        exit /b 1
    )
)
echo ✅ Dependências PHP instaladas

echo.
echo [PASSO 3/12] Criando arquivo .env...
if exist .env (
    echo ✅ Arquivo .env já existe
) else (
    copy .env.example .env
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao copiar .env.example
        pause
        exit /b 1
    )
    echo ✅ Arquivo .env criado
)

echo.
echo [PASSO 4/12] Gerando chave da aplicação...
php artisan key:generate
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao gerar chave da aplicação
    pause
    exit /b 1
)
echo ✅ Chave da aplicação gerada

echo.
echo [PASSO 5/12] Configurando banco de dados...
echo.
echo ⚠️  CONFIGURAÇÃO DO BANCO DE DADOS
echo.
echo Suas configurações atuais no .env:
findstr "DB_" .env
echo.
echo Se precisar alterar, edite o arquivo backend\.env
echo Configurações recomendadas para Laragon:
echo   DB_HOST=127.0.0.1
echo   DB_PORT=3306
echo   DB_DATABASE=laravel_barrier_control
echo   DB_USERNAME=root
echo   DB_PASSWORD=
echo.
set /p "CONTINUAR=Pressione ENTER para continuar ou Ctrl+C para parar e editar .env..."

echo.
echo [PASSO 6/12] Testando conexão com banco...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Não foi possível conectar ao banco de dados
    echo.
    echo Possíveis soluções:
    echo 1. Certifique-se de que o MySQL está rodando (Laragon: Start All)
    echo 2. Verifique as credenciais no arquivo .env
    echo 3. Crie o banco de dados manualmente se necessário
    echo.
    echo Comando para criar banco (MySQL):
    echo CREATE DATABASE laravel_barrier_control;
    echo.
    set /p "TENTAR=Deseja tentar novamente? (s/n): "
    if /i "%TENTAR%"=="s" (
        php artisan migrate:status >nul 2>&1
        if %errorlevel% neq 0 (
            echo ❌ Ainda não foi possível conectar
            pause
            exit /b 1
        )
    ) else (
        pause
        exit /b 1
    )
)
echo ✅ Conexão com banco de dados OK

echo.
echo [PASSO 7/12] Executando migrações...
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao executar migrações
    echo Tentando sem seed...
    php artisan migrate:fresh
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha crítica nas migrações
        pause
        exit /b 1
    )
    echo ⚠️  Migrações OK, mas seed falhou
) else (
    echo ✅ Banco de dados configurado com dados de teste
)

echo.
echo [PASSO 8/12] Configurando Frontend...
cd ..\frontend

echo Verificando package.json...
if not exist package.json (
    echo ❌ ERRO: package.json não encontrado
    pause
    exit /b 1
)
echo ✅ package.json encontrado

echo Instalando dependências Node.js...
call npm install
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao instalar dependências Node.js
    echo Tentando limpar cache...
    npm cache clean --force
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha crítica ao instalar dependências Node.js
        pause
        exit /b 1
    )
)
echo ✅ Dependências Node.js instaladas

echo.
echo [PASSO 9/12] Compilando CSS...
call npm run build:css
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao compilar CSS
    echo Verificando se Tailwind está instalado...
    npm list tailwindcss
    pause
    exit /b 1
)
echo ✅ CSS compilado com sucesso

echo.
echo [PASSO 10/12] Limpando cache...
cd ..\backend
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
echo ✅ Cache limpo

echo.
echo [PASSO 11/12] Criando diretórios necessários...
if not exist storage\logs mkdir storage\logs
if not exist bootstrap\cache mkdir bootstrap\cache
cd ..\frontend
if not exist dist mkdir dist
echo ✅ Diretórios criados

echo.
echo [PASSO 12/12] Teste final da configuração...
cd ..\backend
echo Testando rotas...
php artisan route:list | findstr api >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Problema com as rotas da API
) else (
    echo ✅ Rotas da API OK
)

cd ..

echo.
echo ===================================================
echo    ✅ CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!
echo ===================================================
echo.
echo 📋 RESUMO DA CONFIGURAÇÃO:
echo    ✅ Backend Laravel configurado
echo    ✅ Banco de dados migrado
echo    ✅ Frontend preparado
echo    ✅ CSS compilado
echo    ✅ Cache limpo
echo.
echo 🔐 Credenciais de acesso:
echo    Email: admin@example.com
echo    Senha: password
echo.
echo 🌐 URLs do sistema:
echo    Frontend: http://localhost:8080
echo    Backend API: http://localhost:8000/api
echo.
echo 🚀 PRÓXIMOS PASSOS:
echo 1. Execute: teste_configuracao.bat (para verificar)
echo 2. Execute: iniciar_sistema_otimizado.bat (para iniciar)
echo 3. Acesse http://localhost:8080
echo 4. Faça login com as credenciais acima
echo.
echo Pressione qualquer tecla para continuar...
pause >nul