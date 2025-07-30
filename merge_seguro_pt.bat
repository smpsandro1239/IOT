@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔀 MERGE SEGURO EM PORTUGUÊS                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Sistema de merge seguro com verificações de segurança...
echo.

REM Verificar se Git está disponível
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Git não está disponível
    pause
    exit /b 1
)

REM Verificar se estamos num repositório Git
git status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERRO: Não é um repositório Git
    pause
    exit /b 1
)

REM Obter branch atual
for /f "tokens=*" %%i in ('git branch --show-current 2^>nul') do set current_branch=%%i
if "%current_branch%"=="" (
    echo ❌ ERRO: Não foi possível determinar a branch atual
    pause
    exit /b 1
)

echo ✅ Branch atual: %current_branch%
echo.

REM Verificar se há alterações não commitadas
git diff --quiet
if %errorlevel% neq 0 (
    echo ⚠️  AVISO: Há alterações não commitadas
    echo.
    echo 📋 ALTERAÇÕES PENDENTES:
    git status --porcelain
    echo.
    echo ❓ Deseja continuar? As alterações não commitadas podem causar conflitos. (s/N)
    set /p continue_uncommitted=""
    if /i not "%continue_uncommitted%"=="s" (
        echo ℹ️  Merge cancelado. Faça commit das alterações primeiro.
        pause
        exit /b 0
    )
)

REM Listar branches disponíveis
echo 📋 BRANCHES DISPONÍVEIS:
git branch -a | findstr /v "HEAD"
echo.

REM Solicitar branch de origem
echo 🔀 MERGE DE BRANCH:
echo.
set /p source_branch="Digite o nome da branch a fazer merge: "

if "%source_branch%"=="" (
    echo ❌ ERRO: Nome da branch é obrigatório
    pause
    exit /b 1
)

REM Verificar se branch existe
git show-ref --verify --quiet refs/heads/%source_branch%
if %errorlevel% neq 0 (
    REM Tentar branch remota
    git show-ref --verify --quiet refs/remotes/origin/%source_branch%
    if !errorlevel! neq 0 (
        echo ❌ ERRO: Branch '%source_branch%' não encontrada
        pause
        exit /b 1
    ) else (
        echo ℹ️  Branch remota encontrada: origin/%source_branch%
        set "source_branch=origin/%source_branch%"
    )
)

echo ✅ Branch encontrada: %source_branch%
echo.

REM Verificar se é merge para branch principal
if /i "%current_branch%"=="main" (
    echo ⚠️  AVISO: Está a fazer merge para a branch MAIN
    echo    Certifique-se de que as alterações foram testadas
    set "is_main_merge=1"
) else if /i "%current_branch%"=="master" (
    echo ⚠️  AVISO: Está a fazer merge para a branch MASTER
    echo    Certifique-se de que as alterações foram testadas
    set "is_main_merge=1"
) else (
    set "is_main_merge=0"
)

echo.
echo 🔍 Verificando segurança antes do merge...

REM Verificação de segurança
call verificar_seguranca_simples.bat >nul 2>&1
set "SECURITY_RESULT=%errorlevel%"

if %SECURITY_RESULT% gtr 1 (
    echo ❌ CRÍTICO: Verificação de segurança falhou!
    echo.
    echo 🚨 PROBLEMAS CRÍTICOS ENCONTRADOS
    echo    Execute: verificar_seguranca_simples.bat
    echo    Corrija os problemas antes do merge
    echo.
    pause
    exit /b 1
)

echo ✅ Verificação de segurança aprovada
echo.

REM Verificar diferenças entre branches
echo 📊 DIFERENÇAS ENTRE BRANCHES:
git log --oneline %current_branch%..%source_branch% | head -10
echo.

REM Verificar se há conflitos potenciais
echo 🔍 Verificando conflitos potenciais...
git merge-tree $(git merge-base %current_branch% %source_branch%) %current_branch% %source_branch% >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  AVISO: Podem existir conflitos no merge
) else (
    echo ✅ Nenhum conflito óbvio detectado
)

echo.

REM Solicitar tipo de merge
echo 🔀 TIPO DE MERGE:
echo.
echo 1. Merge normal (cria commit de merge)
echo 2. Fast-forward (se possível)
echo 3. Squash (combina commits numa só)
echo.
set /p merge_type="Escolha o tipo (1-3): "

if "%merge_type%"=="1" set "merge_options="
if "%merge_type%"=="2" set "merge_options=--ff-only"
if "%merge_type%"=="3" set "merge_options=--squash"

if "%merge_options%"=="" if not "%merge_type%"=="1" (
    echo ❌ ERRO: Opção inválida
    pause
    exit /b 1
)

REM Solicitar mensagem de merge (se necessário)
if "%merge_type%"=="1" (
    echo.
    echo 💬 MENSAGEM DE MERGE:
    echo.
    echo 📝 EXEMPLOS:
    echo    • "Integrar funcionalidade de autenticação"
    echo    • "Corrigir conflitos na branch de desenvolvimento"
    echo    • "Adicionar melhorias de performance"
    echo.
    set /p merge_message="Mensagem (opcional): "
    
    if not "%merge_message%"=="" (
        set "merge_options=--message \"%merge_message%\""
    )
)

if "%merge_type%"=="3" (
    echo.
    echo 💬 MENSAGEM PARA SQUASH:
    echo.
    set /p squash_message="Mensagem do commit squash: "
    
    if "%squash_message%"=="" (
        echo ❌ ERRO: Mensagem é obrigatória para squash
        pause
        exit /b 1
    )
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📋 RESUMO DO MERGE                                        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🎯 Branch destino: %current_branch%
echo 🔀 Branch origem: %source_branch%
echo 🏷️  Tipo: %merge_type%
if not "%merge_message%"=="" echo 💬 Mensagem: %merge_message%
if not "%squash_message%"=="" echo 💬 Mensagem squash: %squash_message%
echo.

REM Confirmação final
if "%is_main_merge%"=="1" (
    echo ⚠️  ATENÇÃO: MERGE PARA BRANCH PRINCIPAL
    echo.
    echo 🔒 CONFIRMAÇÃO EXTRA NECESSÁRIA
    echo    Digite "CONFIRMAR" para proceder:
    set /p extra_confirm=""
    if /i not "%extra_confirm%"=="CONFIRMAR" (
        echo ℹ️  Merge cancelado - confirmação não recebida
        pause
        exit /b 0
    )
) else (
    echo ❓ Confirma este merge? (s/N)
    set /p final_confirm=""
    if /i not "%final_confirm%"=="s" (
        echo ℹ️  Merge cancelado pelo utilizador
        pause
        exit /b 0
    )
)

echo.
echo 🚀 Executando merge...

REM Executar merge baseado no tipo
if "%merge_type%"=="1" (
    if "%merge_message%"=="" (
        git merge %source_branch%
    ) else (
        git merge %source_branch% -m "%merge_message%"
    )
) else if "%merge_type%"=="2" (
    git merge --ff-only %source_branch%
) else if "%merge_type%"=="3" (
    git merge --squash %source_branch%
    if !errorlevel! equ 0 (
        echo.
        echo 📝 Fazendo commit do squash...
        git commit -m "%squash_message%"
    )
)

set "MERGE_RESULT=%errorlevel%"

echo.
if %MERGE_RESULT% equ 0 (
    echo 🎉 MERGE REALIZADO COM SUCESSO!
    echo.
    echo ✅ PRÓXIMOS PASSOS:
    echo    • Verificar: git log --oneline -5
    echo    • Testar alterações
    echo    • Push: git push origin %current_branch%
    echo.
    
    REM Mostrar últimos commits
    echo 📋 ÚLTIMOS COMMITS:
    git log --oneline -5
    echo.
    
    REM Verificação de segurança pós-merge
    echo 🔍 Verificação de segurança pós-merge...
    call verificar_seguranca_simples.bat >nul 2>&1
    if !errorlevel! gtr 1 (
        echo ⚠️  AVISO: Problemas de segurança detectados após merge
        echo    Execute: verificar_seguranca_simples.bat
    ) else (
        echo ✅ Verificação pós-merge aprovada
    )
    
    echo.
    REM Perguntar sobre push
    echo ❓ Deseja fazer push agora? (s/N)
    set /p do_push=""
    if /i "%do_push%"=="s" (
        echo.
        echo 📤 Fazendo push...
        git push origin %current_branch%
        if !errorlevel! equ 0 (
            echo ✅ Push realizado com sucesso!
        ) else (
            echo ⚠️  Erro no push. Execute manualmente: git push
        )
    )
    
) else (
    echo ❌ ERRO: Falha no merge
    echo.
    echo 🔧 POSSÍVEIS SOLUÇÕES:
    echo    • Resolver conflitos manualmente
    echo    • git status (ver estado atual)
    echo    • git merge --abort (cancelar merge)
    echo.
    echo 📋 COMANDOS ÚTEIS:
    echo    • git diff (ver conflitos)
    echo    • git add ficheiro (marcar como resolvido)
    echo    • git commit (finalizar merge manual)
)

echo.
pause
exit /b %MERGE_RESULT%