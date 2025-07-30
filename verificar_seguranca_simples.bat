@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔍 VERIFICAÇÃO RÁPIDA DE SEGURANÇA                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🛡️ Verificando segurança do projeto...
echo.

set "ISSUES_FOUND=0"

echo [1/6] Verificando .gitignore...
if exist ".gitignore" (
    echo ✅ .gitignore existe
) else (
    echo ❌ .gitignore não encontrado!
    set /a ISSUES_FOUND+=1
)

echo.
echo [2/6] Verificando ficheiros sensíveis...
if exist "backend\.env" (
    echo ⚠️  backend\.env existe (OK se não for commitado)
) else (
    echo ℹ️  backend\.env não encontrado
)

if exist "base\src\config.h" (
    echo ⚠️  base\src\config.h existe (OK se não for commitado)
)
if exist "auto\src\config.h" (
    echo ⚠️  auto\src\config.h existe (OK se não for commitado)
)
if exist "direcao\src\config.h" (
    echo ⚠️  direcao\src\config.h existe (OK se não for commitado)
)

echo.
echo [3/6] Verificando ficheiros de exemplo...
if exist "backend\.env.example" (
    echo ✅ backend\.env.example existe
) else (
    echo ❌ backend\.env.example não encontrado!
    set /a ISSUES_FOUND+=1
)

if exist "base\src\config.h.example" (
    echo ✅ base\src\config.h.example existe
) else (
    echo ❌ base\src\config.h.example não encontrado!
    set /a ISSUES_FOUND+=1
)

echo.
echo [4/6] Verificando pastas sensíveis...
if exist ".kiro" (
    echo ⚠️  Pasta .kiro encontrada (será ignorada pelo Git)
)
if exist ".emergent" (
    echo ⚠️  Pasta .emergent encontrada (será ignorada pelo Git)
)

echo.
echo [5/6] Verificando chaves privadas...
if exist "base\private_key.pem" (
    echo ❌ CRÍTICO: Chave privada encontrada!
    set /a ISSUES_FOUND+=1
) else (
    echo ✅ Nenhuma chave privada encontrada
)

echo.
echo [6/6] Verificando status Git...
git status --porcelain >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Git disponível
    git status --porcelain 2>nul | find ".env" >nul
    if !errorlevel! equ 0 (
        echo ❌ CRÍTICO: Ficheiros .env no staging area!
        set /a ISSUES_FOUND+=1
    ) else (
        echo ✅ Nenhum ficheiro .env no staging area
    )
) else (
    echo ℹ️  Git não disponível
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📊 RESULTADO DA VERIFICAÇÃO                              ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

if %ISSUES_FOUND% equ 0 (
    echo 🎉 SEGURANÇA OK - Nenhum problema crítico encontrado!
    echo.
    echo ✅ VERIFICAÇÕES APROVADAS:
    echo    • .gitignore configurado
    echo    • Ficheiros de exemplo existem
    echo    • Nenhuma chave privada exposta
    echo    • Staging area limpo
    echo.
    echo 🚀 PRONTO PARA COMMIT!
) else (
    echo 🚨 PROBLEMAS ENCONTRADOS: %ISSUES_FOUND%
    echo.
    echo ❌ AÇÕES NECESSÁRIAS:
    echo    • Corrigir problemas listados acima
    echo    • Executar novamente esta verificação
    echo    • NÃO fazer commit até resolver
    echo.
    echo 🔧 COMANDOS ÚTEIS:
    echo    • Configurar: configurar_ambiente_seguro.bat
    echo    • Limpar: limpar_credenciais_emergencia.bat
)

echo.
pause
exit /b %ISSUES_FOUND%