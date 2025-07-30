@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🇵🇹 COMMIT SEGURO EM PORTUGUÊS                          ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Sistema de commit seguro com mensagens em português de Portugal...
echo.

REM Verificar se há ficheiros para commit
git diff --cached --name-only >nul 2>&1
if %errorlevel% neq 0 (
    echo ℹ️  Nenhum ficheiro no staging area
    echo.
    echo 💡 SUGESTÕES:
    echo    • git add ficheiro.ext    - Adicionar ficheiro específico
    echo    • git add .               - Adicionar todos os ficheiros
    echo    • git status              - Ver estado atual
    echo.
    pause
    exit /b 0
)

echo ✅ Ficheiros encontrados no staging area
echo.

REM Mostrar ficheiros que serão commitados
echo 📁 FICHEIROS A SEREM COMMITADOS:
git diff --cached --name-status
echo.

REM Verificação de segurança rápida
echo 🔍 Verificando segurança...
call verificar_seguranca_simples.bat >nul 2>&1
set "SECURITY_RESULT=%errorlevel%"

if %SECURITY_RESULT% gtr 1 (
    echo ❌ ERRO: Verificação de segurança falhou!
    echo.
    echo 🚨 PROBLEMAS CRÍTICOS ENCONTRADOS
    echo    Execute: verificar_seguranca_simples.bat
    echo    Corrija os problemas antes de continuar
    echo.
    pause
    exit /b 1
)

echo ✅ Verificação de segurança aprovada
echo.

REM Solicitar tipo de commit
echo 📝 TIPO DE COMMIT:
echo.
echo 1. feat     - Nova funcionalidade
echo 2. fix      - Correção de erro
echo 3. docs     - Documentação
echo 4. style    - Formatação/estilo
echo 5. refactor - Refatorização
echo 6. test     - Testes
echo 7. chore    - Manutenção
echo 8. security - Segurança
echo 9. config   - Configuração
echo 0. outro    - Outro tipo
echo.
set /p commit_type_num="Escolha o tipo (1-9, 0): "

REM Mapear número para tipo
if "%commit_type_num%"=="1" set "commit_type=feat"
if "%commit_type_num%"=="2" set "commit_type=fix"
if "%commit_type_num%"=="3" set "commit_type=docs"
if "%commit_type_num%"=="4" set "commit_type=style"
if "%commit_type_num%"=="5" set "commit_type=refactor"
if "%commit_type_num%"=="6" set "commit_type=test"
if "%commit_type_num%"=="7" set "commit_type=chore"
if "%commit_type_num%"=="8" set "commit_type=security"
if "%commit_type_num%"=="9" set "commit_type=config"
if "%commit_type_num%"=="0" (
    set /p commit_type="Digite o tipo: "
)

if "%commit_type%"=="" (
    echo ❌ ERRO: Tipo de commit é obrigatório
    pause
    exit /b 1
)

echo.
echo 💬 MENSAGEM DE COMMIT:
echo.
echo 📋 EXEMPLOS DE BOAS MENSAGENS:
echo    • "Adicionar sistema de autenticação de utilizadores"
echo    • "Corrigir erro na validação de formulários"
echo    • "Atualizar documentação de instalação"
echo    • "Remover código obsoleto do módulo"
echo.
echo 📝 DICAS:
echo    • Use verbos no infinitivo (adicionar, corrigir, atualizar)
echo    • Seja claro e conciso (máximo 50 caracteres)
echo    • Use português de Portugal
echo.
set /p commit_message="Mensagem: "

if "%commit_message%"=="" (
    echo ❌ ERRO: Mensagem de commit não pode estar vazia
    pause
    exit /b 1
)

REM Verificar se mensagem está em inglês (básico)
echo %commit_message% | findstr /i "\badd\b\|fix\b\|update\b\|remove\b\|delete\b" >nul 2>&1
if %errorlevel% equ 0 (
    echo.
    echo ⚠️  AVISO: A mensagem parece estar em inglês
    echo.
    echo 🇵🇹 SUGESTÕES EM PORTUGUÊS:
    echo    • add → adicionar
    echo    • fix → corrigir
    echo    • update → atualizar
    echo    • remove/delete → remover
    echo.
    echo ❓ Deseja continuar mesmo assim? (s/N)
    set /p continue_english=""
    if /i not "%continue_english%"=="s" (
        echo ℹ️  Commit cancelado. Reescreva a mensagem em português.
        pause
        exit /b 0
    )
)

REM Construir mensagem final
set "final_message=%commit_type%: %commit_message%"

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📋 RESUMO DO COMMIT                                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🏷️  Tipo: %commit_type%
echo 💬 Mensagem: %commit_message%
echo 📝 Commit final: %final_message%
echo.
echo 📁 Ficheiros:
git diff --cached --name-status
echo.

REM Confirmação final
echo ❓ Confirma este commit? (s/N)
set /p final_confirm=""
if /i not "%final_confirm%"=="s" (
    echo ℹ️  Commit cancelado pelo utilizador
    pause
    exit /b 0
)

echo.
echo 🚀 Executando commit...
git commit -m "%final_message%"

if %errorlevel% equ 0 (
    echo.
    echo 🎉 COMMIT REALIZADO COM SUCESSO!
    echo.
    echo ✅ PRÓXIMOS PASSOS:
    echo    • Verificar: git log --oneline -1
    echo    • Push: git push origin main
    echo    • Continuar desenvolvimento
    echo.
    
    REM Mostrar último commit
    echo 📋 ÚLTIMO COMMIT:
    git log --oneline -1
    echo.
    
    REM Perguntar sobre push
    echo ❓ Deseja fazer push agora? (s/N)
    set /p do_push=""
    if /i "%do_push%"=="s" (
        echo.
        echo 📤 Fazendo push...
        git push
        if !errorlevel! equ 0 (
            echo ✅ Push realizado com sucesso!
        ) else (
            echo ⚠️  Erro no push. Execute manualmente: git push
        )
    )
) else (
    echo.
    echo ❌ ERRO: Falha ao executar commit
    echo    Verifique mensagens de erro do Git acima
)

echo.
pause
exit /b %errorlevel%