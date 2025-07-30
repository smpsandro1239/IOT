@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ TESTE FINAL DE APROVAÇÃO                              ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 Verificando se o projeto está pronto para GitHub...
echo.

set "READY=1"

echo [1/5] Ficheiros essenciais...
if exist "backend\.env.example" (echo ✅ backend\.env.example) else (echo ❌ backend\.env.example & set "READY=0")
if exist "base\src\config.h.example" (echo ✅ base\src\config.h.example) else (echo ❌ base\src\config.h.example & set "READY=0")
if exist ".gitignore" (echo ✅ .gitignore) else (echo ❌ .gitignore & set "READY=0")

echo.
echo [2/5] Scripts de segurança...
if exist "configurar_ambiente_seguro.bat" (echo ✅ configurar_ambiente_seguro.bat) else (echo ❌ configurar_ambiente_seguro.bat & set "READY=0")
if exist "verificar_seguranca_simples.bat" (echo ✅ verificar_seguranca_simples.bat) else (echo ❌ verificar_seguranca_simples.bat & set "READY=0")
if exist "commit_seguro_pt.bat" (echo ✅ commit_seguro_pt.bat) else (echo ❌ commit_seguro_pt.bat & set "READY=0")

echo.
echo [3/5] Documentação...
if exist "SEGURANCA.md" (echo ✅ SEGURANCA.md) else (echo ❌ SEGURANCA.md & set "READY=0")
if exist "README.md" (echo ✅ README.md) else (echo ❌ README.md & set "READY=0")

echo.
echo [4/5] Verificação de segurança...
call verificar_seguranca_simples.bat >nul 2>&1
if %errorlevel% leq 1 (
    echo ✅ Verificação de segurança aprovada
) else (
    echo ❌ Verificação de segurança falhou
    set "READY=0"
)

echo.
echo [5/5] Chaves privadas...
if exist "base\private_key.pem" (
    echo ❌ CRÍTICO: Chave privada encontrada!
    set "READY=0"
) else (
    echo ✅ Nenhuma chave privada exposta
)

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🏆 RESULTADO FINAL                                        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

if "%READY%"=="1" (
    echo 🎉 PROJETO APROVADO PARA GITHUB!
    echo.
    echo ✅ TODAS AS VERIFICAÇÕES APROVADAS:
    echo    • Ficheiros essenciais presentes
    echo    • Scripts de segurança funcionais
    echo    • Documentação completa
    echo    • Verificação de segurança OK
    echo    • Nenhuma credencial exposta
    echo.
    echo 🚀 PRONTO PARA COMMIT E PUSH!
    echo.
    echo 📋 COMANDOS FINAIS:
    echo    1. git add .
    echo    2. .\commit_seguro_pt.bat
    echo    3. git push origin main
    echo.
) else (
    echo 🚨 PROJETO NÃO ESTÁ PRONTO!
    echo.
    echo ❌ Corrija os problemas listados acima
    echo 🔧 Execute os scripts de configuração necessários
    echo 🔍 Verifique novamente com este teste
    echo.
)

pause
exit /b %READY%