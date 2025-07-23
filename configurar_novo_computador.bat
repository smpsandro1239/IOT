@echo off
chcp 65001 >nul
echo ===================================================
echo    CONFIGURAÇÃO COMPLETA - NOVO COMPUTADOR
echo    Sistema de Controle de Barreiras IoT
echo ===================================================
echo.

echo [PASSO 1/10] Verificando pré-requisitos...
echo.

:: Verificar PHP
echo Verificando PHP...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: PHP não encontrado!
    echo    Instale PHP 8.1+ ou use Laragon/XAMPP
    echo    Download Laragon: https://laragon.org/download/
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

:: Verificar MySQL
echo Verificando MySQL...
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  AVISO: MySQL não encontrado no PATH
    echo    Certifique-se de que MySQL/MariaDB está instalado
    echo    Ou use Laragon que já inclui MySQL
) else (
    echo ✅ MySQL encontrado!
)

echo.
echo [PASSO 2/10] Configurando Backend Laravel...
cd backend

:: Instalar dependências PHP
echo Instalando dependências PHP...
call composer install
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao instalar dependências PHP
    pause
    exit /b 1
)

:: Configurar arquivo .env
echo Configurando arquivo .env...
if not exist .env (
    copy .env.example .env
    echo ✅ Arquivo .env criado
) else (
    echo ✅ Arquivo .env já existe
)

:: Gerar chave da aplicação
echo Gerando chave da aplicação...
php artisan key:generate
echo.

echo [PASSO 3/10] Configurando Frontend...
cd ..\frontend

:: Instalar dependências Node.js
echo Instalando dependências Node.js...
call npm install
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao instalar dependências Node.js
    pause
    exit /b 1
)

:: Compilar CSS
echo Compilando CSS com Tailwind...
call npm run build:css
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao compilar CSS
    pause
    exit /b 1
)

echo.
echo [PASSO 4/10] Configurando Banco de Dados...
cd ..\backend

echo.
echo ⚠️  CONFIGURAÇÃO DO BANCO DE DADOS
echo.
echo Por favor, configure as credenciais do banco de dados:
echo 1. Abra o arquivo backend\.env
echo 2. Configure as seguintes variáveis:
echo    DB_HOST=127.0.0.1
echo    DB_PORT=3306
echo    DB_DATABASE=laravel_barrier_control
echo    DB_USERNAME=root
echo    DB_PASSWORD=sua_senha_aqui
echo.
echo Pressione qualquer tecla após configurar o .env...
pause >nul

:: Testar conexão com banco
echo Testando conexão com banco de dados...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Não foi possível conectar ao banco de dados
    echo    Verifique as credenciais no arquivo .env
    echo    Certifique-se de que o MySQL está rodando
    pause
    exit /b 1
) else (
    echo ✅ Conexão com banco de dados OK
)

echo.
echo [PASSO 5/10] Executando migrações...
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo ❌ ERRO: Falha ao executar migrações
    pause
    exit /b 1
) else (
    echo ✅ Banco de dados configurado com sucesso
)

echo.
echo [PASSO 6/10] Limpando cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
echo ✅ Cache limpo

echo.
echo [PASSO 7/10] Configurando permissões...
if not exist storage\logs mkdir storage\logs
if not exist bootstrap\cache mkdir bootstrap\cache
echo ✅ Diretórios criados

echo.
echo [PASSO 8/10] Testando configuração...
echo Testando rotas da API...
php artisan route:list | findstr api >nul
if %errorlevel% neq 0 (
    echo ❌ ERRO: Problema com as rotas da API
) else (
    echo ✅ Rotas da API OK
)

echo.
echo [PASSO 9/10] Criando atalhos...
cd ..

:: Criar script de inicialização rápida
echo @echo off > iniciar_rapido.bat
echo echo Iniciando Sistema de Barreiras IoT... >> iniciar_rapido.bat
echo start cmd /k "cd backend && php artisan serve" >> iniciar_rapido.bat
echo timeout /t 3 ^>nul >> iniciar_rapido.bat
echo start cmd /k "cd frontend && php -S localhost:8080" >> iniciar_rapido.bat
echo echo. >> iniciar_rapido.bat
echo echo Sistema iniciado! >> iniciar_rapido.bat
echo echo Frontend: http://localhost:8080 >> iniciar_rapido.bat
echo echo Backend: http://localhost:8000 >> iniciar_rapido.bat
echo pause >> iniciar_rapido.bat

echo ✅ Script de inicialização rápida criado

echo.
echo [PASSO 10/10] Configuração finalizada!
echo.
echo ===================================================
echo    ✅ CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!
echo ===================================================
echo.
echo 📋 INFORMAÇÕES IMPORTANTES:
echo.
echo 🔐 Credenciais de acesso:
echo    Email: admin@example.com
echo    Senha: password
echo.
echo 🌐 URLs do sistema:
echo    Frontend: http://localhost:8080
echo    Backend API: http://localhost:8000/api
echo.
echo 🚀 Para iniciar o sistema:
echo    1. Execute: iniciar_sistema_otimizado.bat
echo    2. Ou use: iniciar_rapido.bat (mais rápido)
echo.
echo 📁 Arquivos importantes:
echo    - backend\.env (configurações do banco)
echo    - frontend\package.json (dependências frontend)
echo    - backend\composer.json (dependências backend)
echo.
echo 🔧 Comandos úteis:
echo    - Reiniciar: reiniciar_sistema.bat
echo    - Limpar cache: clear_cache.bat
echo    - Reset DB: cd backend && php artisan migrate:fresh --seed
echo.
echo ⚠️  PRÓXIMOS PASSOS:
echo 1. Execute iniciar_sistema_otimizado.bat
echo 2. Acesse http://localhost:8080
echo 3. Faça login com as credenciais acima
echo 4. Teste o modo simulação
echo.
echo Pressione qualquer tecla para continuar...
pause >nul