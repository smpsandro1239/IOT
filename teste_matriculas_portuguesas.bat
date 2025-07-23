@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🇵🇹 TESTE DE MATRÍCULAS PORTUGUESAS                      ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 📋 FORMATOS SUPORTADOS DE MATRÍCULAS PORTUGUESAS:
echo.
echo ✅ FORMATO ANTIGO (até 1992):
echo    • AA-12-12  (com hífens)
echo    • AA1212    (sem hífens)
echo    • Exemplo: AB-12-34, CD5678
echo.
echo ✅ FORMATO INTERMÉDIO (1992-2005):
echo    • 12-AB-34  (com hífens)
echo    • 12AB34    (sem hífens)
echo    • Exemplo: 12-CD-56, 78EF90
echo.
echo ✅ FORMATO ATUAL (desde 2005):
echo    • 12-34-AB  (com hífens)
echo    • 1234AB    (sem hífens)
echo    • Exemplo: 12-34-CD, 5678EF
echo.

echo 🔧 VALIDAÇÃO IMPLEMENTADA:
echo    • Aceita qualquer combinação de 6 caracteres
echo    • Pelo menos 2 letras e 2 números
echo    • Formatação automática com hífens (XX-XX-XX)
echo    • Conversão para maiúsculas
echo.

echo 🧪 TESTES PARA EXECUTAR:
echo.
echo 1. Abra http://localhost:8080
echo 2. Vá para "MACs Autorizados"
echo 3. Teste os seguintes formatos:
echo.
echo    FORMATO ANTIGO:
echo    • AB1234  → deve aceitar e formatar como AB-12-34
echo    • CD-56-78 → deve aceitar e formatar como CD-56-78
echo.
echo    FORMATO INTERMÉDIO:
echo    • 12AB34  → deve aceitar e formatar como 12-AB-34
echo    • 56-CD-78 → deve aceitar e formatar como 56-CD-78
echo.
echo    FORMATO ATUAL:
echo    • 1234AB  → deve aceitar e formatar como 12-34-AB
echo    • 56-78-CD → deve aceitar e formatar como 56-78-CD
echo.

echo ❌ FORMATOS QUE DEVEM SER REJEITADOS:
echo    • 123456  (só números)
echo    • ABCDEF  (só letras)
echo    • A1B2C3  (menos de 2 números consecutivos)
echo    • 12345   (menos de 6 caracteres)
echo    • 1234567 (mais de 6 caracteres)
echo.

echo 🚀 Para iniciar o teste, execute:
echo    teste_sistema_final_corrigido.bat
echo.

pause