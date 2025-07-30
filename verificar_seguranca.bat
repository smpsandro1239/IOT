@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔍 VERIFICAÇÃO DE SEGURANÇA AVANÇADA                     ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Executando auditoria completa de segurança...
echo.

set "CRITICAL_ISSUES=0"
set "HIGH_ISSUES=0"
set "MEDIUM_ISSUES=0"
set "LOW_ISSUES=0"

echo [1/12] Verificando .gitignore...
if exist ".gitignore" (
    echo ✅ .gitignore existe
    
    REM Verificar se contém padrões críticos
    findstr /c:".env" .gitignore >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ CRÍTICO: .gitignore não protege ficheiros .env
        set /a CRITICAL_ISSUES+=1
    )
    
    findstr /c:"config.h" .gitignore >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ CRÍTICO: .gitignore não protege ficheiros config.h
        set /a CRITICAL_ISSUES+=1
    )
    
    findstr /c:".kiro/" .gitignore >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ ALTO: .gitignore não protege pasta .kiro/
        set /a HIGH_ISSUES+=1
    )
    
    if %CRITICAL_ISSUES% equ 0 (
        if %HIGH_ISSUES% equ 0 (
            echo ✅ .gitignore configurado corretamente
        )
    )
) else (
    echo ❌ CRÍTICO: .gitignore não encontrado!
    set /a CRITICAL_ISSUES+=1
)

echo.
echo [2/12] Verificando ficheiros sensíveis...

REM Verificar .env
if exist "backend\.env" (
    echo ⚠️  backend\.env existe (OK se não for commitado)
    
    REM Verificar se contém credenciais reais
    findstr /i "password.*=" backend\.env | findstr /v "null" | findstr /v "example" >nul 2>&1
    if %errorlevel% equ 0 (
        echo ⚠️  MÉDIO: Possíveis credenciais reais em .env
        set /a MEDIUM_ISSUES+=1
    )
) else (
    echo ℹ️  backend\.env não encontrado (use configurar_ambiente_seguro.bat)
)

REM Verificar config.h files
for %%d in (base auto direcao) do (
    if exist "%%d\src\config.h" (
        echo ⚠️  %%d\src\config.h existe (OK se não for commitado)
        
        REM Verificar credenciais WiFi
        findstr /i "WIFI_SSID.*\"" %%d\src\config.h | findstr /v "SUA_REDE_WIFI" >nul 2>&1
        if %errorlevel% equ 0 (
            echo ⚠️  MÉDIO: Possíveis credenciais WiFi reais em %%d\config.h
            set /a MEDIUM_ISSUES+=1
        )
    )
)

echo.
echo [3/12] Verificando ficheiros de exemplo...
set "MISSING_EXAMPLES=0"

if exist "backend\.env.example" (
    echo ✅ backend\.env.example existe
) else (
    echo ❌ ALTO: backend\.env.example não encontrado!
    set /a HIGH_ISSUES+=1
    set /a MISSING_EXAMPLES+=1
)

for %%d in (base auto direcao) do (
    if exist "%%d\src\config.h.example" (
        echo ✅ %%d\src\config.h.example existe
    ) else (
        echo ❌ ALTO: %%d\src\config.h.example não encontrado!
        set /a HIGH_ISSUES+=1
        set /a MISSING_EXAMPLES+=1
    )
)

if exist ".gitconfig.example" (
    echo ✅ .gitconfig.example existe
) else (
    echo ❌ MÉDIO: .gitconfig.example não encontrado!
    set /a MEDIUM_ISSUES+=1
    set /a MISSING_EXAMPLES+=1
)

echo.
echo [4/12] Verificando pastas sensíveis...
if exist ".kiro" (
    echo ⚠️  Pasta .kiro encontrada (será ignorada pelo Git)
    
    REM Verificar se está oculta
    for /f %%i in ('dir /ah .kiro 2^>nul ^| find /c ".kiro"') do set hidden_kiro=%%i
    if "%hidden_kiro%"=="0" (
        echo ⚠️  BAIXO: Pasta .kiro não está oculta
        set /a LOW_ISSUES+=1
    )
)

if exist ".emergent" (
    echo ⚠️  Pasta .emergent encontrada (será ignorada pelo Git)
)

echo.
echo [5/12] Verificando chaves e certificados...
set "KEYS_FOUND=0"

for %%e in (pem key crt p12 pfx) do (
    dir /b *.%%e 2>nul | findstr . >nul
    if !errorlevel! equ 0 (
        echo ⚠️  Ficheiros .%%e encontrados:
        dir /b *.%%e 2>nul
        echo    Verifique se estão no .gitignore
        set /a KEYS_FOUND+=1
        set /a MEDIUM_ISSUES+=1
    )
)

if %KEYS_FOUND% equ 0 (
    echo ✅ Nenhuma chave exposta encontrada na raiz
)

echo.
echo [6/12] Verificando logs e dados sensíveis...
if exist "backend\storage\logs" (
    echo ⚠️  Pasta de logs existe (será ignorada pelo Git)
    
    REM Verificar tamanho dos logs
    for /f %%i in ('dir /s backend\storage\logs 2^>nul ^| find "File(s)"') do (
        echo ℹ️  Logs encontrados - verifique regularmente
    )
)

REM Verificar ficheiros de backup
for %%e in (backup bak dump sql) do (
    dir /b *.%%e 2>nul | findstr . >nul
    if !errorlevel! equ 0 (
        echo ⚠️  MÉDIO: Ficheiros de backup encontrados (*.%%e)
        set /a MEDIUM_ISSUES+=1
    )
)

echo.
echo [7/12] Verificando credenciais em código fonte...
echo 🔍 Analisando código fonte por credenciais expostas...

set "CODE_ISSUES=0"

REM Procurar padrões suspeitos em ficheiros de código
for %%e in (cpp js php py) do (
    for /r %%f in (*.%%e) do (
        REM Verificar se não é ficheiro de exemplo
        echo %%f | findstr /i "example" >nul
        if !errorlevel! neq 0 (
            REM Procurar credenciais
            findstr /i "password.*=.*[^example]" "%%f" 2>nul | findstr /v "PASSWORD" | findstr /v "example" >nul
            if !errorlevel! equ 0 (
                echo ⚠️  ALTO: Possível credencial em %%f
                set /a HIGH_ISSUES+=1
                set /a CODE_ISSUES+=1
            )
        )
    )
)

if %CODE_ISSUES% equ 0 (
    echo ✅ Nenhuma credencial óbvia encontrada em código
)

echo.
echo [8/12] Verificando status Git...
git status --porcelain >nul 2>&1
if %errorlevel% equ 0 (
    REM Git está disponível
    git status --porcelain 2>nul | findstr "\.env\|config\.h\|\.pem\|\.key" >nul
    if !errorlevel! equ 0 (
        echo ❌ CRÍTICO: Ficheiros sensíveis no staging area!
        echo    Ficheiros encontrados:
        git status --porcelain 2>nul | findstr "\.env\|config\.h\|\.pem\|\.key"
        set /a CRITICAL_ISSUES+=1
    ) else (
        echo ✅ Nenhum ficheiro sensível no staging area
    )
    
    REM Verificar branch atual
    for /f "tokens=*" %%i in ('git branch --show-current 2^>nul') do set current_branch=%%i
    if defined current_branch (
        echo ℹ️  Branch atual: %current_branch%
    )
) else (
    echo ℹ️  Git não disponível ou não é um repositório Git
)

echo.
echo [9/12] Verificando configurações de desenvolvimento...

REM Verificar se PHP está disponível
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ PHP disponível
    
    REM Verificar Laravel
    if exist "backend\artisan" (
        echo ✅ Laravel detectado
    )
) else (
    echo ⚠️  BAIXO: PHP não encontrado no PATH
    set /a LOW_ISSUES+=1
)

REM Verificar se Node.js está disponível (se necessário)
node --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Node.js disponível
) else (
    echo ℹ️  Node.js não encontrado (opcional)
)

echo.
echo [10/12] Verificando estrutura do projeto...
set "STRUCTURE_OK=1"

for %%d in (backend frontend base auto direcao) do (
    if not exist "%%d" (
        echo ⚠️  MÉDIO: Pasta %%d não encontrada
        set /a MEDIUM_ISSUES+=1
        set "STRUCTURE_OK=0"
    )
)

if "%STRUCTURE_OK%"=="1" (
    echo ✅ Estrutura básica do projeto OK
)

echo.
echo [11/12] Verificando permissões e acessos...

REM Verificar se consegue escrever na pasta atual
echo test > test_write.tmp 2>nul
if exist test_write.tmp (
    del test_write.tmp >nul 2>&1
    echo ✅ Permissões de escrita OK
) else (
    echo ⚠️  MÉDIO: Problemas de permissão de escrita
    set /a MEDIUM_ISSUES+=1
)

echo.
echo [12/12] Verificando integridade dos templates...
set "TEMPLATE_ISSUES=0"

REM Verificar se templates contêm placeholders
if exist "backend\.env.example" (
    findstr /c:"GERE_UMA_CHAVE_AQUI" backend\.env.example >nul
    if !errorlevel! neq 0 (
        echo ⚠️  BAIXO: Template .env.example pode estar desatualizado
        set /a LOW_ISSUES+=1
        set /a TEMPLATE_ISSUES+=1
    )
)

if %TEMPLATE_ISSUES% equ 0 (
    echo ✅ Templates verificados
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RELATÓRIO FINAL DE SEGURANÇA                          ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

set /a TOTAL_ISSUES=%CRITICAL_ISSUES%+%HIGH_ISSUES%+%MEDIUM_ISSUES%+%LOW_ISSUES%

echo 📈 ESTATÍSTICAS DA AUDITORIA:
echo    🔍 Verificações realizadas: 12
echo    🚨 Problemas críticos: %CRITICAL_ISSUES%
echo    ⚠️  Problemas altos: %HIGH_ISSUES%
echo    ℹ️  Problemas médios: %MEDIUM_ISSUES%
echo    💡 Problemas baixos: %LOW_ISSUES%
echo    📊 Total de problemas: %TOTAL_ISSUES%
echo.

if %CRITICAL_ISSUES% gtr 0 (
    echo 🚨 STATUS: CRÍTICO - NÃO FAZER COMMIT
    echo.
    echo ❌ PROBLEMAS CRÍTICOS ENCONTRADOS:
    echo    • Ficheiros sensíveis podem estar expostos
    echo    • .gitignore pode estar mal configurado
    echo    • Credenciais podem estar no staging area
    echo.
    echo 🔧 AÇÕES IMEDIATAS NECESSÁRIAS:
    echo    1. Corrija todos os problemas críticos listados
    echo    2. Execute novamente esta verificação
    echo    3. NÃO faça commit até resolver tudo
    echo.
    set "EXIT_CODE=2"
) else if %HIGH_ISSUES% gtr 0 (
    echo ⚠️  STATUS: ALTO RISCO - CUIDADO AO FAZER COMMIT
    echo.
    echo ⚠️  PROBLEMAS DE ALTO RISCO ENCONTRADOS:
    echo    • Ficheiros de exemplo podem estar em falta
    echo    • Configurações podem estar expostas
    echo.
    echo 🔧 AÇÕES RECOMENDADAS:
    echo    1. Corrija problemas de alto risco
    echo    2. Verifique configurações antes do commit
    echo    3. Execute configurar_ambiente_seguro.bat se necessário
    echo.
    set "EXIT_CODE=1"
) else if %MEDIUM_ISSUES% gtr 0 (
    echo ℹ️  STATUS: RISCO MÉDIO - PODE FAZER COMMIT COM CUIDADO
    echo.
    echo ℹ️  PROBLEMAS DE RISCO MÉDIO ENCONTRADOS:
    echo    • Algumas configurações podem precisar de atenção
    echo    • Ficheiros de backup podem estar presentes
    echo.
    echo 🔧 AÇÕES SUGERIDAS:
    echo    1. Revise problemas médios quando possível
    echo    2. Faça limpeza regular de ficheiros temporários
    echo.
    set "EXIT_CODE=0"
) else if %LOW_ISSUES% gtr 0 (
    echo 💡 STATUS: BAIXO RISCO - SEGURO PARA COMMIT
    echo.
    echo 💡 PROBLEMAS MENORES ENCONTRADOS:
    echo    • Pequenos ajustes podem melhorar a configuração
    echo.
    echo 🔧 AÇÕES OPCIONAIS:
    echo    1. Considere corrigir problemas menores
    echo    2. Mantenha boas práticas de desenvolvimento
    echo.
    set "EXIT_CODE=0"
) else (
    echo 🎉 STATUS: EXCELENTE - TOTALMENTE SEGURO PARA COMMIT
    echo.
    echo ✅ VERIFICAÇÕES APROVADAS:
    echo    • .gitignore configurado corretamente
    echo    • Ficheiros de exemplo existem
    echo    • Nenhuma credencial exposta
    echo    • Staging area limpo
    echo    • Estrutura do projeto OK
    echo    • Configurações seguras
    echo.
    set "EXIT_CODE=0"
)

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔧 COMANDOS ÚTEIS                                         ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🛠️  COMANDOS DE SEGURANÇA:
echo    • Configurar ambiente: configurar_ambiente_seguro.bat
echo    • Verificar novamente: verificar_seguranca.bat
echo    • Limpar credenciais: limpar_credenciais.bat (se existir)
echo.
echo 🔍 COMANDOS GIT ÚTEIS:
echo    • Verificar staging: git status
echo    • Ver diferenças: git diff --cached
echo    • Remover do staging: git reset HEAD ficheiro
echo    • Verificar .gitignore: git check-ignore ficheiro
echo.
echo 📚 DOCUMENTAÇÃO:
echo    • Guia de segurança: SEGURANCA.md
echo    • Configuração: README.md
echo    • Auditoria inicial: AUDITORIA_SEGURANCA_INICIAL.md
echo.

if %CRITICAL_ISSUES% gtr 0 (
    echo ⚠️  LEMBRE-SE: NÃO FAÇA COMMIT COM PROBLEMAS CRÍTICOS!
    echo.
)

pause
exit /b %EXIT_CODE%