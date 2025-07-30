@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔒 PREPARAÇÃO SEGURA PARA COMMIT                         ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Verificando segurança antes do commit...
echo.

set "CRITICAL_ISSUES=0"
set "WARNINGS=0"
set "COMMIT_SAFE=1"

echo [1/8] Verificando staging area...
git status --porcelain >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Git não disponível ou não é um repositório Git
    set "COMMIT_SAFE=0"
    goto :end_check
)

REM Verificar se há ficheiros no staging area
git diff --cached --name-only >nul 2>&1
if %errorlevel% neq 0 (
    echo ℹ️  Nenhum ficheiro no staging area
    echo.
    echo 💡 SUGESTÃO: Adicione ficheiros com 'git add' antes de fazer commit
    echo.
    pause
    exit /b 0
)

echo ✅ Ficheiros encontrados no staging area

echo.
echo [2/8] Verificando ficheiros sensíveis no staging...
set "SENSITIVE_FILES=0"

REM Verificar ficheiros .env
git diff --cached --name-only | findstr "\.env$" >nul 2>&1
if %errorlevel% equ 0 (
    echo ❌ CRÍTICO: Ficheiros .env no staging area!
    git diff --cached --name-only | findstr "\.env$"
    set /a CRITICAL_ISSUES+=1
    set /a SENSITIVE_FILES+=1
)

REM Verificar ficheiros config.h
git diff --cached --name-only | findstr "config\.h$" >nul 2>&1
if %errorlevel% equ 0 (
    echo ❌ CRÍTICO: Ficheiros config.h no staging area!
    git diff --cached --name-only | findstr "config\.h$"
    set /a CRITICAL_ISSUES+=1
    set /a SENSITIVE_FILES+=1
)

REM Verificar chaves privadas
git diff --cached --name-only | findstr "\.pem$\|\.key$" >nul 2>&1
if %errorlevel% equ 0 (
    echo ❌ CRÍTICO: Chaves privadas no staging area!
    git diff --cached --name-only | findstr "\.pem$\|\.key$"
    set /a CRITICAL_ISSUES+=1
    set /a SENSITIVE_FILES+=1
)

REM Verificar .gitconfig
git diff --cached --name-only | findstr "\.gitconfig$" >nul 2>&1
if %errorlevel% equ 0 (
    echo ❌ CRÍTICO: Ficheiro .gitconfig no staging area!
    set /a CRITICAL_ISSUES+=1
    set /a SENSITIVE_FILES+=1
)

if %SENSITIVE_FILES% equ 0 (
    echo ✅ Nenhum ficheiro sensível no staging area
)

echo.
echo [3/8] Verificando conteúdo dos ficheiros...
echo 🔍 Analisando conteúdo por credenciais expostas...

set "CONTENT_ISSUES=0"

REM Verificar cada ficheiro no staging por credenciais
for /f "delims=" %%f in ('git diff --cached --name-only') do (
    REM Verificar se é ficheiro de código
    echo %%f | findstr "\.cpp$\|\.js$\|\.php$\|\.py$\|\.h$" >nul 2>&1
    if !errorlevel! equ 0 (
        REM Verificar credenciais no ficheiro
        git show :%%f | findstr /i "password.*=" >nul 2>&1
        if !errorlevel! equ 0 (
            echo ⚠️  AVISO: Possível credencial em %%f
            set /a WARNINGS+=1
            set /a CONTENT_ISSUES+=1
        )
    )
)

if %CONTENT_ISSUES% equ 0 (
    echo ✅ Nenhuma credencial óbvia encontrada no conteúdo
)

echo.
echo [4/8] Verificando .gitignore...
if exist ".gitignore" (
    echo ✅ .gitignore existe
    
    REM Verificar se .gitignore protege ficheiros críticos
    findstr /c:".env" .gitignore >nul 2>&1
    if !errorlevel! neq 0 (
        echo ❌ CRÍTICO: .gitignore não protege ficheiros .env
        set /a CRITICAL_ISSUES+=1
    )
    
    findstr /c:"config.h" .gitignore >nul 2>&1
    if !errorlevel! neq 0 (
        echo ❌ CRÍTICO: .gitignore não protege ficheiros config.h
        set /a CRITICAL_ISSUES+=1
    )
) else (
    echo ❌ CRÍTICO: .gitignore não encontrado!
    set /a CRITICAL_ISSUES+=1
)

echo.
echo [5/8] Verificando mensagem de commit...
echo.
echo 📝 MENSAGEM DE COMMIT:
echo    Por favor, insira a mensagem de commit em português de Portugal:
echo.
set /p commit_message="💬 Mensagem: "

if "%commit_message%"=="" (
    echo ❌ ERRO: Mensagem de commit não pode estar vazia
    set "COMMIT_SAFE=0"
    goto :end_check
)

REM Verificar se mensagem está em português (básico)
echo %commit_message% | findstr /i "fix\|add\|update\|remove\|delete" >nul 2>&1
if %errorlevel% equ 0 (
    echo ⚠️  AVISO: Mensagem parece estar em inglês
    echo    Considere usar português: corrigir, adicionar, atualizar, remover
    set /a WARNINGS+=1
)

echo ✅ Mensagem de commit recebida

echo.
echo [6/8] Verificando branch atual...
for /f "tokens=*" %%i in ('git branch --show-current 2^>nul') do set current_branch=%%i
if defined current_branch (
    echo ✅ Branch atual: %current_branch%
    
    REM Avisar se está na main/master
    if /i "%current_branch%"=="main" (
        echo ⚠️  AVISO: Está a fazer commit diretamente na branch main
        set /a WARNINGS+=1
    )
    if /i "%current_branch%"=="master" (
        echo ⚠️  AVISO: Está a fazer commit diretamente na branch master
        set /a WARNINGS+=1
    )
) else (
    echo ⚠️  AVISO: Não foi possível determinar a branch atual
    set /a WARNINGS+=1
)

echo.
echo [7/8] Verificando ficheiros de exemplo...
set "MISSING_EXAMPLES=0"

REM Verificar se ficheiros .example existem para ficheiros sensíveis
git diff --cached --name-only | findstr "\.env\.example$\|config\.h\.example$" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Ficheiros .example encontrados no commit
) else (
    REM Verificar se há ficheiros sensíveis sem .example
    if exist "backend\.env" (
        if not exist "backend\.env.example" (
            echo ⚠️  AVISO: .env existe mas .env.example não foi commitado
            set /a WARNINGS+=1
            set /a MISSING_EXAMPLES+=1
        )
    )
)

if %MISSING_EXAMPLES% equ 0 (
    echo ✅ Ficheiros de exemplo verificados
)

echo.
echo [8/8] Verificação final de segurança...

REM Executar verificação rápida
call verificar_seguranca_simples.bat >nul 2>&1
set "SECURITY_CHECK_RESULT=%errorlevel%"

if %SECURITY_CHECK_RESULT% gtr 1 (
    echo ❌ CRÍTICO: Verificação de segurança falhou
    set /a CRITICAL_ISSUES+=1
) else (
    echo ✅ Verificação de segurança aprovada
)

:end_check

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RELATÓRIO DE SEGURANÇA PRÉ-COMMIT                     ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📈 ESTATÍSTICAS:
echo    🚨 Problemas críticos: %CRITICAL_ISSUES%
echo    ⚠️  Avisos: %WARNINGS%
echo    📝 Mensagem: "%commit_message%"
echo    🌿 Branch: %current_branch%
echo.

if %CRITICAL_ISSUES% gtr 0 (
    echo 🚨 STATUS: COMMIT BLOQUEADO - PROBLEMAS CRÍTICOS
    echo.
    echo ❌ PROBLEMAS CRÍTICOS ENCONTRADOS:
    echo    • Ficheiros sensíveis no staging area
    echo    • .gitignore mal configurado
    echo    • Verificação de segurança falhou
    echo.
    echo 🔧 AÇÕES OBRIGATÓRIAS:
    echo    1. Remover ficheiros sensíveis: git reset HEAD ficheiro
    echo    2. Corrigir .gitignore se necessário
    echo    3. Executar: verificar_seguranca_simples.bat
    echo    4. Tentar novamente após correções
    echo.
    set "COMMIT_SAFE=0"
) else if %WARNINGS% gtr 0 (
    echo ⚠️  STATUS: COMMIT COM AVISOS - PROCEDER COM CUIDADO
    echo.
    echo ⚠️  AVISOS ENCONTRADOS:
    echo    • Mensagem pode estar em inglês
    echo    • Commit direto na branch principal
    echo    • Ficheiros .example podem estar em falta
    echo.
    echo ❓ Deseja continuar mesmo assim? (s/N)
    set /p continue_with_warnings=""
    if /i not "%continue_with_warnings%"=="s" (
        echo ℹ️  Commit cancelado pelo utilizador
        set "COMMIT_SAFE=0"
    )
) else (
    echo 🎉 STATUS: COMMIT APROVADO - SEGURO PARA PROCEDER
    echo.
    echo ✅ TODAS AS VERIFICAÇÕES APROVADAS:
    echo    • Nenhum ficheiro sensível no staging
    echo    • .gitignore configurado corretamente
    echo    • Mensagem de commit adequada
    echo    • Verificação de segurança OK
)

echo.
if "%COMMIT_SAFE%"=="1" (
    echo ╔══════════════════════════════════════════════════════════════════════════════╗
    echo ║                    🚀 EXECUTAR COMMIT                                        ║
    echo ╚══════════════════════════════════════════════════════════════════════════════╝
    echo.
    
    echo 🚀 Executando commit seguro...
    git commit -m "%commit_message%"
    
    if %errorlevel% equ 0 (
        echo.
        echo 🎉 COMMIT REALIZADO COM SUCESSO!
        echo.
        echo ✅ PRÓXIMOS PASSOS:
        echo    • Verificar: git log --oneline -1
        echo    • Push: git push origin %current_branch%
        echo    • Continuar desenvolvimento
    ) else (
        echo.
        echo ❌ ERRO: Falha ao executar commit
        echo    Verifique mensagens de erro do Git
    )
) else (
    echo ╔══════════════════════════════════════════════════════════════════════════════╗
    echo ║                    ❌ COMMIT BLOQUEADO                                       ║
    echo ╚══════════════════════════════════════════════════════════════════════════════╝
    echo.
    
    echo 🚫 COMMIT NÃO REALIZADO
    echo.
    echo 🔧 COMANDOS ÚTEIS:
    echo    • Ver staging: git status
    echo    • Remover do staging: git reset HEAD ficheiro
    echo    • Ver diferenças: git diff --cached
    echo    • Verificar segurança: verificar_seguranca_simples.bat
    echo.
    echo ⚠️  CORRIJA OS PROBLEMAS E TENTE NOVAMENTE
)

echo.
pause
exit /b %CRITICAL_ISSUES%