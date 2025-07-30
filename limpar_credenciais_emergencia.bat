@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚨 LIMPEZA DE EMERGÊNCIA - CREDENCIAIS                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo ⚠️  AVISO: Este script remove TODAS as credenciais e dados sensíveis!
echo.
echo 🔥 SITUAÇÕES DE USO:
echo    • Credenciais foram commitadas acidentalmente
echo    • Suspeita de comprometimento de segurança
echo    • Preparação para partilha pública do código
echo    • Reset completo de configurações
echo.

echo ❓ Tem certeza que deseja continuar? (s/N)
set /p confirm_emergency=""
if /i not "%confirm_emergency%"=="s" (
    echo ℹ️  Operação cancelada pelo utilizador
    pause
    exit /b 0
)

echo.
echo 🚨 ÚLTIMA CONFIRMAÇÃO: Esta ação é IRREVERSÍVEL!
echo    Todos os ficheiros de configuração serão removidos.
echo    Deseja realmente continuar? (CONFIRMAR/N)
set /p final_confirm=""
if /i not "%final_confirm%"=="CONFIRMAR" (
    echo ℹ️  Operação cancelada - confirmação não recebida
    pause
    exit /b 0
)

echo.
echo 🔥 Iniciando limpeza de emergência...
echo.

set "FILES_REMOVED=0"
set "ERRORS_FOUND=0"

echo [1/10] Criando backup de segurança...
set "BACKUP_DIR=backup_emergencia_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%"
set "BACKUP_DIR=%BACKUP_DIR: =0%"

mkdir "%BACKUP_DIR%" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Pasta de backup criada: %BACKUP_DIR%
    
    REM Backup de ficheiros importantes antes de remover
    if exist "backend\.env" (
        copy "backend\.env" "%BACKUP_DIR%\.env.backup" >nul 2>&1
        echo ✅ Backup de .env criado
    )
    
    for %%d in (base auto direcao) do (
        if exist "%%d\src\config.h" (
            copy "%%d\src\config.h" "%BACKUP_DIR%\config_%%d.h.backup" >nul 2>&1
            echo ✅ Backup de config.h (%%d) criado
        )
    )
    
    if exist ".gitconfig" (
        copy ".gitconfig" "%BACKUP_DIR%\.gitconfig.backup" >nul 2>&1
        echo ✅ Backup de .gitconfig criado
    )
) else (
    echo ⚠️  Aviso: Não foi possível criar backup
    set /a ERRORS_FOUND+=1
)

echo.
echo [2/10] Removendo ficheiros .env...
if exist "backend\.env" (
    del "backend\.env" >nul 2>&1
    if %errorlevel% equ 0 (
        echo ✅ backend\.env removido
        set /a FILES_REMOVED+=1
    ) else (
        echo ❌ Erro ao remover backend\.env
        set /a ERRORS_FOUND+=1
    )
) else (
    echo ℹ️  backend\.env não encontrado
)

REM Remover outros ficheiros .env se existirem
for %%f in (.env .env.local .env.production .env.staging) do (
    if exist "%%f" (
        del "%%f" >nul 2>&1
        if !errorlevel! equ 0 (
            echo ✅ %%f removido
            set /a FILES_REMOVED+=1
        )
    )
)

echo.
echo [3/10] Removendo ficheiros config.h...
for %%d in (base auto direcao) do (
    if exist "%%d\src\config.h" (
        del "%%d\src\config.h" >nul 2>&1
        if !errorlevel! equ 0 (
            echo ✅ %%d\src\config.h removido
            set /a FILES_REMOVED+=1
        ) else (
            echo ❌ Erro ao remover %%d\src\config.h
            set /a ERRORS_FOUND+=1
        )
    ) else (
        echo ℹ️  %%d\src\config.h não encontrado
    )
)

echo.
echo [4/10] Removendo ficheiros de chaves e certificados...
set "KEYS_REMOVED=0"
for %%e in (pem key crt p12 pfx cert) do (
    for %%f in (*.%%e) do (
        if exist "%%f" (
            del "%%f" >nul 2>&1
            if !errorlevel! equ 0 (
                echo ✅ %%f removido
                set /a FILES_REMOVED+=1
                set /a KEYS_REMOVED+=1
            )
        )
    )
)

if %KEYS_REMOVED% equ 0 (
    echo ℹ️  Nenhuma chave encontrada na raiz
)

echo.
echo [5/10] Removendo .gitconfig...
if exist ".gitconfig" (
    del ".gitconfig" >nul 2>&1
    if %errorlevel% equ 0 (
        echo ✅ .gitconfig removido
        set /a FILES_REMOVED+=1
    ) else (
        echo ❌ Erro ao remover .gitconfig
        set /a ERRORS_FOUND+=1
    )
) else (
    echo ℹ️  .gitconfig não encontrado
)

echo.
echo [6/10] Limpando logs e dados temporários...
if exist "backend\storage\logs" (
    for /r "backend\storage\logs" %%f in (*.*) do (
        del "%%f" >nul 2>&1
    )
    echo ✅ Logs do Laravel limpos
)

REM Limpar sessões
if exist "backend\storage\framework\sessions" (
    for /r "backend\storage\framework\sessions" %%f in (*.*) do (
        del "%%f" >nul 2>&1
    )
    echo ✅ Sessões do Laravel limpas
)

REM Limpar cache
if exist "backend\storage\framework\cache" (
    for /r "backend\storage\framework\cache" %%f in (*.*) do (
        del "%%f" >nul 2>&1
    )
    echo ✅ Cache do Laravel limpo
)

echo.
echo [7/10] Removendo ficheiros de backup e dumps...
set "BACKUP_FILES_REMOVED=0"
for %%e in (backup bak dump sql) do (
    for %%f in (*.%%e) do (
        if exist "%%f" (
            del "%%f" >nul 2>&1
            if !errorlevel! equ 0 (
                echo ✅ %%f removido
                set /a FILES_REMOVED+=1
                set /a BACKUP_FILES_REMOVED+=1
            )
        )
    )
)

if %BACKUP_FILES_REMOVED% equ 0 (
    echo ℹ️  Nenhum ficheiro de backup encontrado
)

echo.
echo [8/10] Limpando staging area do Git...
git status --porcelain >nul 2>&1
if %errorlevel% equ 0 (
    REM Git está disponível
    git reset HEAD . >nul 2>&1
    if !errorlevel! equ 0 (
        echo ✅ Staging area limpo
    ) else (
        echo ℹ️  Staging area já estava limpo
    )
) else (
    echo ℹ️  Git não disponível
)

echo.
echo [9/10] Removendo ficheiros temporários...
set "TEMP_FILES_REMOVED=0"
for %%e in (tmp temp swp swo) do (
    for %%f in (*.%%e) do (
        if exist "%%f" (
            del "%%f" >nul 2>&1
            if !errorlevel! equ 0 (
                set /a TEMP_FILES_REMOVED+=1
            )
        )
    )
)

REM Remover ficheiros com padrões suspeitos
for %%p in (*secret* *password* *credential* *token*) do (
    if exist "%%p" (
        echo %%p | findstr /i "example\|template\|sample" >nul
        if !errorlevel! neq 0 (
            del "%%p" >nul 2>&1
            if !errorlevel! equ 0 (
                echo ✅ %%p removido (padrão suspeito)
                set /a FILES_REMOVED+=1
            )
        )
    )
)

echo ✅ %TEMP_FILES_REMOVED% ficheiros temporários removidos

echo.
echo [10/10] Verificação final de segurança...

REM Verificar se ainda existem ficheiros sensíveis
set "REMAINING_ISSUES=0"

if exist "backend\.env" (
    echo ⚠️  backend\.env ainda existe
    set /a REMAINING_ISSUES+=1
)

for %%d in (base auto direcao) do (
    if exist "%%d\src\config.h" (
        echo ⚠️  %%d\src\config.h ainda existe
        set /a REMAINING_ISSUES+=1
    )
)

if %REMAINING_ISSUES% equ 0 (
    echo ✅ Verificação final aprovada
) else (
    echo ⚠️  %REMAINING_ISSUES% ficheiro(s) sensível(eis) ainda presente(s)
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RELATÓRIO DE LIMPEZA DE EMERGÊNCIA                    ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📈 ESTATÍSTICAS DA LIMPEZA:
echo    🗑️  Ficheiros removidos: %FILES_REMOVED%
echo    ❌ Erros encontrados: %ERRORS_FOUND%
echo    ⚠️  Problemas restantes: %REMAINING_ISSUES%
echo    💾 Backup criado em: %BACKUP_DIR%
echo.

if %ERRORS_FOUND% equ 0 (
    if %REMAINING_ISSUES% equ 0 (
        echo 🎉 LIMPEZA CONCLUÍDA COM SUCESSO!
        echo.
        echo ✅ SISTEMA LIMPO E SEGURO:
        echo    • Todas as credenciais removidas
        echo    • Ficheiros sensíveis eliminados
        echo    • Logs e cache limpos
        echo    • Staging area limpo
        echo    • Backup de segurança criado
    ) else (
        echo ⚠️  LIMPEZA PARCIALMENTE CONCLUÍDA
        echo.
        echo ⚠️  ALGUNS FICHEIROS AINDA PRESENTES:
        echo    • Verifique manualmente os ficheiros listados
        echo    • Pode ser necessário intervenção manual
    )
) else (
    echo ❌ LIMPEZA COM ERROS
    echo.
    echo 🚨 ERROS DURANTE A LIMPEZA:
    echo    • %ERRORS_FOUND% erro(s) encontrado(s)
    echo    • Alguns ficheiros podem não ter sido removidos
    echo    • Verifique permissões e tente novamente
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎯 PRÓXIMOS PASSOS APÓS LIMPEZA                          ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 RECONFIGURAR AMBIENTE:
echo    1. Execute: configurar_ambiente_seguro.bat
echo    2. Configure novas credenciais seguras
echo    3. Teste o sistema completamente
echo.
echo 🔍 VERIFICAR SEGURANÇA:
echo    1. Execute: verificar_seguranca.bat
echo    2. Confirme que não há problemas críticos
echo    3. Verifique .gitignore está correto
echo.
echo 📝 DOCUMENTAR INCIDENTE:
echo    1. Documente o motivo da limpeza
echo    2. Revise práticas de segurança
echo    3. Atualize procedimentos se necessário
echo.
echo 💾 RECUPERAR DADOS (SE NECESSÁRIO):
echo    1. Backup disponível em: %BACKUP_DIR%
echo    2. Recupere apenas dados não sensíveis
echo    3. NÃO restaure credenciais comprometidas
echo.

if %REMAINING_ISSUES% gtr 0 (
    echo ⚠️  ATENÇÃO: LIMPEZA MANUAL NECESSÁRIA
    echo    Alguns ficheiros sensíveis ainda estão presentes.
    echo    Remova-os manualmente antes de continuar.
    echo.
)

echo 🛡️ LEMBRE-SE:
echo    • Gere novas credenciais seguras
echo    • Não reutilize credenciais antigas
echo    • Monitore logs por atividade suspeita
echo    • Documente o incidente adequadamente
echo.

pause
exit /b %ERRORS_FOUND%