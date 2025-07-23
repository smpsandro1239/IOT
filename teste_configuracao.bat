@echo off
chcp 65001 >nul
echo ===================================================
echo    TESTE DE CONFIGURAÇÃO - BARREIRAS IOT
echo ===================================================
echo.

set "TESTES_OK=1"

echo 🧪 Executando testes de configuração...
echo.

echo [1/8] Testando PHP e Laravel...
cd backend
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP não encontrado
    set "TESTES_OK=0"
) else (
    echo ✅ PHP OK
)

if exist .env (
    echo ✅ Arquivo .env existe
) else (
    echo ❌ Arquivo .env não encontrado
    set "TESTES_OK=0"
)

echo [2/8] Testando dependências PHP...
if exist vendor (
    echo ✅ Dependências PHP instaladas
) else (
    echo ❌ Dependências PHP não instaladas
    set "TESTES_OK=0"
)

echo [3/8] Testando conexão com banco...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Problema com banco de dados
    echo    Execute: criar_banco.bat
    set "TESTES_OK=0"
) else (
    echo ✅ Banco de dados OK
)

echo [4/8] Testando rotas da API...
php artisan route:list | findstr api >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Problema com rotas da API
    set "TESTES_OK=0"
) else (
    echo ✅ Rotas da API OK
)

echo [5/8] Testando frontend...
cd ..\frontend
if exist package.json (
    echo ✅ package.json existe
) else (
    echo ❌ package.json não encontrado
    set "TESTES_OK=0"
)

echo [6/8] Testando dependências Node.js...
if exist node_modules (
    echo ✅ Dependências Node.js instaladas
) else (
    echo ❌ Dependências Node.js não instaladas
    set "TESTES_OK=0"
)

echo [7/8] Testando CSS compilado...
if exist css\tailwind-local.css (
    echo ✅ CSS compilado existe
) else (
    echo ❌ CSS não compilado
    set "TESTES_OK=0"
)

echo [8/8] Testando arquivos principais...
if exist index.html (
    echo ✅ index.html existe
) else (
    echo ❌ index.html não encontrado
    set "TESTES_OK=0"
)

cd ..

echo.
echo ===================================================

if "%TESTES_OK%"=="1" (
    echo ✅ TODOS OS TESTES PASSARAM!
    echo.
    echo 🚀 Sistema pronto para uso:
    echo    Execute: iniciar_sistema_otimizado.bat
    echo.
    echo 🌐 URLs que estarão disponíveis:
    echo    Frontend: http://localhost:8080
    echo    Backend: http://localhost:8000/api
    echo.
    echo 🔐 Credenciais:
    echo    Email: admin@example.com
    echo    Senha: password
) else (
    echo ❌ ALGUNS TESTES FALHARAM
    echo.
    echo 🔧 Para corrigir problemas:
    echo    1. Execute: verificar_requisitos.bat
    echo    2. Execute: configurar_novo_computador.bat
    echo    3. Execute: clear_cache.bat
    echo    4. Execute este teste novamente
)

echo.
echo ===================================================
echo.

if "%TESTES_OK%"=="1" (
    echo 💡 DICA: Para desenvolvimento contínuo use:
    echo    - iniciar_sistema_otimizado.bat (iniciar)
    echo    - reiniciar_sistema.bat (reiniciar)
    echo    - clear_cache.bat (limpar cache)
    echo.
    
    set /p "INICIAR=Deseja iniciar o sistema agora? (s/n): "
    if /i "%INICIAR%"=="s" (
        echo.
        echo Iniciando sistema...
        call iniciar_sistema_otimizado.bat
    )
)

pause