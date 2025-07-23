@echo off
chcp 65001 >nul
echo ===================================================
echo    INICIALIZAÇÃO OTIMIZADA - BARREIRAS IOT
echo ===================================================
echo.

echo [1/9] Parando servidores em execução...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
ping 127.0.0.1 -n 3 >nul
echo ✅ Servidores parados

echo [2/9] Verificando pré-requisitos...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: PHP não encontrado!
    echo    Execute: verificar_requisitos.bat
    pause
    exit /b 1
)
echo ✅ PHP OK

echo [3/9] Limpando cache do Laravel...
cd backend
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
echo ✅ Cache limpo

echo [4/9] Verificando dependências...
if not exist vendor (
    echo Instalando dependências PHP...
    call composer install
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao instalar dependências PHP
        pause
        exit /b 1
    )
)
echo ✅ Dependências PHP OK

echo [5/9] Configurando ambiente...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
    echo ⚠️  Configure o banco de dados no arquivo .env
    echo    Pressione qualquer tecla após configurar...
    pause >nul
)
echo ✅ Ambiente configurado

echo [6/9] Testando banco de dados...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Problema com banco de dados
    echo    Verifique as credenciais no .env
    echo    Execute: configurar_novo_computador.bat
    pause
    exit /b 1
)
echo ✅ Banco de dados OK

echo [7/9] Preparando frontend...
cd ..\frontend
if not exist node_modules (
    echo Instalando dependências Node.js...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao instalar dependências Node.js
        pause
        exit /b 1
    )
)

if not exist css\tailwind-local.css (
    echo Compilando CSS...
    call npm run build:css
    if %errorlevel% neq 0 (
        echo ❌ ERRO: Falha ao compilar CSS
        pause
        exit /b 1
    )
)
echo ✅ Frontend preparado

echo [8/9] Iniciando servidores...
cd ..\backend
echo Iniciando Laravel (Backend)...
start "Laravel Backend" cmd /k "echo Backend rodando em http://localhost:8000 && php artisan serve"
ping 127.0.0.1 -n 4 >nul

cd ..\frontend
echo Iniciando Frontend...
start "Frontend Server" cmd /k "echo Frontend rodando em http://localhost:8080 && php -S localhost:8080"
ping 127.0.0.1 -n 3 >nul

echo [9/9] Verificando serviços...
ping 127.0.0.1 -n 4 >nul
curl -s http://localhost:8000/api/v1/status/latest >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Backend pode não estar respondendo ainda
) else (
    echo ✅ Backend respondendo
)

echo.
echo ===================================================
echo    ✅ SISTEMA INICIADO COM SUCESSO!
echo ===================================================
echo.
echo 🌐 URLs do sistema:
echo    Frontend: http://localhost:8080
echo    Backend API: http://localhost:8000/api
echo.
echo 🔐 Credenciais de acesso:
echo    Email: admin@example.com
echo    Senha: password
echo.
echo 🚀 PRÓXIMOS PASSOS:
echo 1. Acesse http://localhost:8080
echo 2. Faça login com as credenciais acima
echo 3. Teste o modo simulação
echo.
echo ⚠️  Se houver problemas:
echo 1. Verifique se ambos os servidores estão rodando
echo 2. Execute: verificar_requisitos.bat
echo 3. Execute: configurar_novo_computador.bat
echo.
echo Pressione qualquer tecla para fechar esta janela...
pause >nul