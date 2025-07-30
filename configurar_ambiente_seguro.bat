@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔒 CONFIGURAÇÃO SEGURA DE AMBIENTE                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Configurando ambiente de desenvolvimento seguro...
echo.

set "ERRORS_FOUND=0"
set "WARNINGS_FOUND=0"

echo [1/8] Verificando ficheiros de exemplo...
if not exist "backend\.env.example" (
    echo ❌ Ficheiro backend\.env.example não encontrado!
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
if not exist "base\src\config.h.example" (
    echo ❌ Ficheiro base\src\config.h.example não encontrado!
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiros de exemplo encontrados

echo.
echo [2/8] Configurando backend Laravel...

REM Verificar se .env já existe
if exist "backend\.env" (
    echo ⚠️  Ficheiro .env já existe. Deseja sobrescrever? (s/N)
    set /p overwrite_env=""
    if /i not "%overwrite_env%"=="s" (
        echo ℹ️  Mantendo ficheiro .env existente
        goto :skip_env
    )
)

REM Copiar ficheiro de exemplo
copy "backend\.env.example" "backend\.env" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erro ao copiar ficheiro .env
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiro .env criado com sucesso

:skip_env

echo.
echo [3/8] Gerando chave da aplicação Laravel...
cd backend
php artisan key:generate --force >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Chave Laravel gerada com sucesso
) else (
    echo ⚠️  Aviso: Erro ao gerar chave Laravel
    echo    Verifique se PHP está instalado e acessível
    echo    Execute manualmente: cd backend && php artisan key:generate
    set /a WARNINGS_FOUND+=1
)
cd ..

echo.
echo [4/8] Configurando ESP32 - Estação Base...

REM Verificar se config.h já existe
if exist "base\src\config.h" (
    echo ⚠️  Ficheiro config.h já existe. Deseja sobrescrever? (s/N)
    set /p overwrite_base=""
    if /i not "%overwrite_base%"=="s" (
        echo ℹ️  Mantendo ficheiro config.h existente
        goto :skip_base
    )
)

REM Copiar ficheiro de exemplo
copy "base\src\config.h.example" "base\src\config.h" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erro ao copiar ficheiro config.h para base
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiro config.h criado para estação base

:skip_base

echo.
echo [5/8] Configurando ESP32 - Sensor Automático...

REM Verificar se config.h já existe
if exist "auto\src\config.h" (
    echo ⚠️  Ficheiro config.h já existe. Deseja sobrescrever? (s/N)
    set /p overwrite_auto=""
    if /i not "%overwrite_auto%"=="s" (
        echo ℹ️  Mantendo ficheiro config.h existente
        goto :skip_auto
    )
)

REM Copiar ficheiro de exemplo
copy "auto\src\config.h.example" "auto\src\config.h" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erro ao copiar ficheiro config.h para auto
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiro config.h criado para sensor automático

:skip_auto

echo.
echo [6/8] Configurando ESP32 - Sensor de Direção...

REM Verificar se config.h já existe
if exist "direcao\src\config.h" (
    echo ⚠️  Ficheiro config.h já existe. Deseja sobrescrever? (s/N)
    set /p overwrite_dir=""
    if /i not "%overwrite_dir%"=="s" (
        echo ℹ️  Mantendo ficheiro config.h existente
        goto :skip_dir
    )
)

REM Copiar ficheiro de exemplo
copy "direcao\src\config.h.example" "direcao\src\config.h" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erro ao copiar ficheiro config.h para direcao
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiro config.h criado para sensor de direção

:skip_dir

echo.
echo [7/8] Configurando Git...

REM Verificar se .gitconfig já existe
if exist ".gitconfig" (
    echo ⚠️  Ficheiro .gitconfig já existe. Deseja sobrescrever? (s/N)
    set /p overwrite_git=""
    if /i not "%overwrite_git%"=="s" (
        echo ℹ️  Mantendo ficheiro .gitconfig existente
        goto :skip_git
    )
)

REM Copiar ficheiro de exemplo
copy ".gitconfig.example" ".gitconfig" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erro ao copiar ficheiro .gitconfig
    set /a ERRORS_FOUND+=1
    goto :error_exit
)
echo ✅ Ficheiro .gitconfig criado

:skip_git

echo.
echo [8/8] Configurando permissões e segurança...

REM Ocultar pastas sensíveis
attrib +H .kiro >nul 2>&1
attrib +H .emergent >nul 2>&1

REM Verificar se .gitignore existe
if not exist ".gitignore" (
    echo ⚠️  Ficheiro .gitignore não encontrado!
    echo    Este ficheiro é crítico para segurança
    set /a WARNINGS_FOUND+=1
) else (
    echo ✅ Ficheiro .gitignore verificado
)

echo ✅ Configurações de segurança aplicadas

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RESULTADO DA CONFIGURAÇÃO                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

REM Verificar configuração final
echo 🔍 Verificando configuração final...
echo.

if exist "backend\.env" (
    echo ✅ Backend: Ficheiro .env configurado
) else (
    echo ❌ Backend: Ficheiro .env não encontrado
    set /a ERRORS_FOUND+=1
)

if exist "base\src\config.h" (
    echo ✅ ESP32 Base: Ficheiro config.h configurado
) else (
    echo ❌ ESP32 Base: Ficheiro config.h não encontrado
    set /a ERRORS_FOUND+=1
)

if exist "auto\src\config.h" (
    echo ✅ ESP32 Auto: Ficheiro config.h configurado
) else (
    echo ❌ ESP32 Auto: Ficheiro config.h não encontrado
    set /a ERRORS_FOUND+=1
)

if exist "direcao\src\config.h" (
    echo ✅ ESP32 Direção: Ficheiro config.h configurado
) else (
    echo ❌ ESP32 Direção: Ficheiro config.h não encontrado
    set /a ERRORS_FOUND+=1
)

if exist ".gitconfig" (
    echo ✅ Git: Ficheiro .gitconfig configurado
) else (
    echo ❌ Git: Ficheiro .gitconfig não encontrado
    set /a ERRORS_FOUND+=1
)

echo.

if %ERRORS_FOUND% equ 0 (
    if %WARNINGS_FOUND% equ 0 (
        echo 🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!
        echo.
        echo ✅ TODOS OS COMPONENTES CONFIGURADOS:
        echo    • Backend Laravel com .env seguro
        echo    • ESP32 Base com config.h
        echo    • ESP32 Auto com config.h  
        echo    • ESP32 Direção com config.h
        echo    • Git com .gitconfig
        echo    • Segurança aplicada
    ) else (
        echo ⚠️  CONFIGURAÇÃO CONCLUÍDA COM AVISOS
        echo.
        echo ✅ COMPONENTES PRINCIPAIS CONFIGURADOS
        echo ⚠️  %WARNINGS_FOUND% aviso(s) encontrado(s)
        echo    Verifique as mensagens acima
    )
) else (
    echo ❌ CONFIGURAÇÃO FALHOU
    echo.
    echo 🚨 %ERRORS_FOUND% erro(s) encontrado(s)
    echo    Corrija os problemas e execute novamente
    goto :error_exit
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎯 PRÓXIMOS PASSOS OBRIGATÓRIOS                          ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📝 CONFIGURAR CREDENCIAIS AGORA:
echo.
echo 1️⃣ BACKEND (backend\.env):
echo    • Abra o ficheiro backend\.env
echo    • Configure a base de dados se necessário
echo    • Verifique APP_URL e outras configurações
echo.
echo 2️⃣ ESP32 BASE (base\src\config.h):
echo    • Abra o ficheiro base\src\config.h
echo    • Altere WIFI_SSID para "SUA_REDE_WIFI"
echo    • Altere WIFI_PASSWORD para "SUA_SENHA_WIFI"
echo    • Altere API_HOST para o IP do seu servidor
echo.
echo 3️⃣ ESP32 AUTO (auto\src\config.h):
echo    • Abra o ficheiro auto\src\config.h
echo    • Configure WiFi (se necessário)
echo    • Ajuste configurações de detecção
echo.
echo 4️⃣ ESP32 DIREÇÃO (direcao\src\config.h):
echo    • Abra o ficheiro direcao\src\config.h
echo    • Configure WiFi (se necessário)
echo    • Ajuste configurações AoA
echo.
echo 5️⃣ GIT (.gitconfig):
echo    • Abra o ficheiro .gitconfig
echo    • Altere "name" para o seu nome
echo    • Altere "email" para o seu email
echo    • Configure URL do repositório
echo.
echo 🔧 TESTAR CONFIGURAÇÃO:
echo    • Execute: verificar_seguranca.bat
echo    • Execute: iniciar_sistema_otimizado.bat
echo    • Teste conectividade ESP32
echo.
echo ⚠️  IMPORTANTE - SEGURANÇA:
echo    • NUNCA commite ficheiros .env ou config.h
echo    • Use apenas ficheiros .example no Git
echo    • Mantenha credenciais seguras e privadas
echo    • Execute verificar_seguranca.bat antes de commits
echo.

goto :success_exit

:error_exit
echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ❌ CONFIGURAÇÃO FALHOU                                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🚨 Erros encontrados durante a configuração.
echo 🔧 Corrija os problemas listados acima e execute novamente.
echo 📞 Se precisar de ajuda, consulte a documentação SEGURANCA.md
echo.
pause
exit /b 1

:success_exit
echo 🎯 CONFIGURAÇÃO PRONTA PARA USO!
echo.
pause
exit /b 0