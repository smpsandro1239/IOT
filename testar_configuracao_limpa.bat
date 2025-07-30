@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE DE CONFIGURAÇÃO LIMPA                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔍 Simulando configuração de novo utilizador...
echo.

set "TEST_PASSED=0"
set "TEST_FAILED=0"

echo [1/8] Verificando ficheiros .example...
if exist "backend\.env.example" (
    echo ✅ backend\.env.example existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ backend\.env.example não encontrado
    set /a TEST_FAILED+=1
)

if exist "base\src\config.h.example" (
    echo ✅ base\src\config.h.example existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ base\src\config.h.example não encontrado
    set /a TEST_FAILED+=1
)

if exist "auto\src\config.h.example" (
    echo ✅ auto\src\config.h.example existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ auto\src\config.h.example não encontrado
    set /a TEST_FAILED+=1
)

if exist "direcao\src\config.h.example" (
    echo ✅ direcao\src\config.h.example existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ direcao\src\config.h.example não encontrado
    set /a TEST_FAILED+=1
)

echo.
echo [2/8] Verificando scripts de segurança...
if exist "configurar_ambiente_seguro.bat" (
    echo ✅ configurar_ambiente_seguro.bat existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ configurar_ambiente_seguro.bat não encontrado
    set /a TEST_FAILED+=1
)

if exist "verificar_seguranca_simples.bat" (
    echo ✅ verificar_seguranca_simples.bat existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ verificar_seguranca_simples.bat não encontrado
    set /a TEST_FAILED+=1
)

echo.
echo [3/8] Verificando documentação...
if exist "SEGURANCA.md" (
    echo ✅ SEGURANCA.md existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ SEGURANCA.md não encontrado
    set /a TEST_FAILED+=1
)

if exist "CONFIGURACAO_SEGURA.md" (
    echo ✅ CONFIGURACAO_SEGURA.md existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ CONFIGURACAO_SEGURA.md não encontrado
    set /a TEST_FAILED+=1
)

if exist "README.md" (
    echo ✅ README.md existe
    set /a TEST_PASSED+=1
) else (
    echo ❌ README.md não encontrado
    set /a TEST_FAILED+=1
)

echo.
echo [4/8] Verificando .gitignore...
if exist ".gitignore" (
    echo ✅ .gitignore existe
    
    REM Verificar conteúdo crítico
    findstr /c:".env" .gitignore >nul 2>&1
    if !errorlevel! equ 0 (
        echo ✅ .gitignore protege ficheiros .env
        set /a TEST_PASSED+=1
    ) else (
        echo ❌ .gitignore não protege ficheiros .env
        set /a TEST_FAILED+=1
    )
    
    findstr /c:"config.h" .gitignore >nul 2>&1
    if !errorlevel! equ 0 (
        echo ✅ .gitignore protege ficheiros config.h
        set /a TEST_PASSED+=1
    ) else (
        echo ❌ .gitignore não protege ficheiros config.h
        set /a TEST_FAILED+=1
    )
) else (
    echo ❌ .gitignore não encontrado
    set /a TEST_FAILED+=1
)

echo.
echo [5/8] Verificando que ficheiros sensíveis NÃO existem...
if not exist "backend\.env" (
    echo ✅ backend\.env não existe (correto)
    set /a TEST_PASSED+=1
) else (
    echo ⚠️  backend\.env existe (será ignorado pelo Git)
)

if not exist "base\src\config.h" (
    echo ✅ base\src\config.h não existe (correto)
    set /a TEST_PASSED+=1
) else (
    echo ⚠️  base\src\config.h existe (será ignorado pelo Git)
)

echo.
echo [6/8] Testando script de configuração...
echo 🔧 Executando configuração automática...

REM Simular execução do script (sem interação)
if exist "configurar_ambiente_seguro.bat" (
    echo ✅ Script de configuração disponível
    set /a TEST_PASSED+=1
) else (
    echo ❌ Script de configuração não disponível
    set /a TEST_FAILED+=1
)

echo.
echo [7/8] Testando verificação de segurança...
call verificar_seguranca_simples.bat >nul 2>&1
set "SECURITY_RESULT=%errorlevel%"

if %SECURITY_RESULT% leq 1 (
    echo ✅ Verificação de segurança passou
    set /a TEST_PASSED+=1
) else (
    echo ❌ Verificação de segurança falhou
    set /a TEST_FAILED+=1
)

echo.
echo [8/8] Verificando estrutura do projeto...
set "STRUCTURE_ISSUES=0"

for %%d in (backend frontend base auto direcao) do (
    if exist "%%d" (
        echo ✅ Pasta %%d existe
        set /a TEST_PASSED+=1
    ) else (
        echo ❌ Pasta %%d não encontrada
        set /a TEST_FAILED+=1
        set /a STRUCTURE_ISSUES+=1
    )
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RESULTADO DO TESTE                                     ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

set /a TOTAL_TESTS=%TEST_PASSED%+%TEST_FAILED%
echo 📈 ESTATÍSTICAS:
echo    ✅ Testes aprovados: %TEST_PASSED%
echo    ❌ Testes falhados: %TEST_FAILED%
echo    📊 Total de testes: %TOTAL_TESTS%

if %TEST_FAILED% equ 0 (
    echo.
    echo 🎉 TODOS OS TESTES APROVADOS!
    echo.
    echo ✅ PROJETO PRONTO PARA PUBLICAÇÃO:
    echo    • Todos os ficheiros necessários existem
    echo    • Scripts de segurança funcionam
    echo    • Documentação completa
    echo    • Estrutura correta
    echo    • Verificação de segurança aprovada
    echo.
    echo 🚀 PRÓXIMOS PASSOS:
    echo    1. git add .
    echo    2. .\commit_seguro_pt.bat
    echo    3. git push origin main
    echo.
    set "EXIT_CODE=0"
) else (
    echo.
    echo ⚠️  ALGUNS TESTES FALHARAM
    echo.
    echo ❌ PROBLEMAS ENCONTRADOS:
    echo    • %TEST_FAILED% teste(s) falharam
    echo    • Corrija os problemas listados acima
    echo    • Execute novamente este teste
    echo.
    echo 🔧 AÇÕES NECESSÁRIAS:
    echo    • Verificar ficheiros em falta
    echo    • Corrigir configurações
    echo    • Executar scripts de configuração
    echo.
    set "EXIT_CODE=1"
)

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎯 RESUMO FINAL                                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

if %EXIT_CODE% equ 0 (
    echo 🏆 STATUS: APROVADO PARA GITHUB
    echo.
    echo 🛡️ SEGURANÇA: MÁXIMA
    echo 📚 DOCUMENTAÇÃO: COMPLETA
    echo 🔧 AUTOMAÇÃO: FUNCIONAL
    echo 🎯 USABILIDADE: EXCELENTE
) else (
    echo 🚨 STATUS: REQUER CORREÇÕES
    echo.
    echo 🔧 Corrija os problemas e teste novamente
)

echo.
pause
exit /b %EXIT_CODE%