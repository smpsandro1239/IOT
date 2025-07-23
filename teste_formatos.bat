@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE DE FORMATOS E DUPLICATAS                        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🚀 Iniciando sistema para teste...
echo.

echo [1/3] Iniciando backend...
cd backend
start "Backend API" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 >nul
cd ..

echo [2/3] Iniciando frontend...
cd frontend
start "Frontend Server" cmd /k "php -S localhost:8080"
timeout /t 2 >nul
cd ..

echo [3/3] Aguardando serviços iniciarem...
timeout /t 3 >nul

echo ✅ Sistema iniciado!
echo.
echo 🌐 Abrindo navegador...
start http://localhost:8080

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📋 TESTE DE VALIDAÇÃO DE DUPLICATAS                      ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 CENÁRIOS DE TESTE:
echo.
echo 1. 📱 Clique em "MACs Autorizados" na navegação
echo.
echo 2. 🧪 TESTE DE FORMATOS EQUIVALENTES (devem detectar duplicata):
echo.
echo    ✅ CENÁRIO 1 - Adicione primeiro:
echo       MAC: 24A160123456
echo       Matrícula: AB1234
echo.
echo    ⚠️  CENÁRIO 2 - Tente adicionar depois (deve detectar duplicata):
echo       MAC: 24:A1:60:12:34:56  (mesmo MAC, formato diferente)
echo       Matrícula: AB-12-34      (mesma matrícula, formato diferente)
echo.
echo    📋 RESULTADO ESPERADO:
echo       • Sistema deve detectar que são iguais APÓS formatação
echo       • Deve mostrar modal de duplicata
echo       • Deve mostrar dados existentes vs novos dados
echo.
echo 3. 🔄 TESTE DE SUBSTITUIÇÃO:
echo.
echo    ✅ CENÁRIO 3 - Adicione primeiro:
echo       MAC: 1122334455AA
echo       Matrícula: CD5678
echo.
echo    🔄 CENÁRIO 4 - Tente adicionar (deve permitir substituir):
echo       MAC: 11:22:33:44:55:AA  (mesmo MAC formatado)
echo       Matrícula: CD-56-78      (mesma matrícula formatada)
echo.
echo    📋 RESULTADO ESPERADO:
echo       • Modal de duplicata aparece
echo       • Opção "Substituir" disponível
echo       • Dados são atualizados após confirmação
echo.
echo 4. ✅ TESTE DE FORMATOS VÁLIDOS (devem ser aceitos):
echo.
echo    FORMATO ANTIGO:
echo    • AA1234 → AA-12-34
echo    • BC5678 → BC-56-78
echo.
echo    FORMATO INTERMÉDIO:
echo    • 12AB34 → 12-AB-34
echo    • 56CD78 → 56-CD-78
echo.
echo    FORMATO ATUAL:
echo    • 1234AB → 12-34-AB
echo    • 5678CD → 56-78-CD
echo.
echo 5. ❌ TESTE DE FORMATOS INVÁLIDOS (devem ser rejeitados):
echo.
echo    • 123456 (só números)
echo    • ABCDEF (só letras)
echo    • A1B2C3 (formato inválido)
echo    • 12345  (muito curto)
echo    • 1234567 (muito longo)
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema: http://localhost:8080
echo    • API: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar
echo.

echo 📊 FUNCIONALIDADES TESTADAS:
echo    ✅ Validação de formatos portugueses
echo    ✅ Detecção de duplicatas APÓS formatação
echo    ✅ Modal de confirmação de duplicatas
echo    ✅ Comparação dados existentes vs novos
echo    ✅ Opção de substituir dados existentes
echo    ✅ Feedback de sucesso/erro
echo    ✅ Pesquisa avançada (5 itens por página)
echo    ✅ Direções corretas (Norte-Sul/Sul-Norte)
echo.

pause