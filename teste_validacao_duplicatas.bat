@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE DE VALIDAÇÃO DE MATRÍCULAS                      ║
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
echo ║                        📋 TESTE DE MATRÍCULAS                               ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 INSTRUÇÕES DE TESTE:
echo.
echo 1. 📱 Clique em "MACs Autorizados" na navegação
echo.
echo 2. 🧪 TESTE ESTES FORMATOS (devem ser ACEITOS):
echo    ✅ AA1212     → deve formatar como AA-12-12
echo    ✅ 12AB34     → deve formatar como 12-AB-34  
echo    ✅ 1234AB     → deve formatar como 12-34-AB
echo    ✅ AA-12-12   → deve manter como AA-12-12
echo    ✅ 12-AB-34   → deve manter como 12-AB-34
echo    ✅ 12-34-AB   → deve manter como 12-34-AB
echo.
echo 3. ❌ TESTE ESTES FORMATOS (devem ser REJEITADOS):
echo    ❌ 123456     → só números
echo    ❌ ABCDEF     → só letras  
echo    ❌ A1B2C3     → formato inválido
echo    ❌ 12345      → muito curto
echo    ❌ 1234567    → muito longo
echo.
echo 4. 🔍 TESTE A PESQUISA AVANÇADA:
echo    • Digite parte de um MAC ou matrícula
echo    • Verifique se os resultados aparecem
echo    • Teste a paginação (5 itens por página)
echo.
echo 5. 🧭 TESTE AS DIREÇÕES:
echo    • Inicie uma simulação de veículo
echo    • Verifique se aparecem apenas "Norte → Sul" e "Sul → Norte"
echo    • NÃO deve aparecer "Oeste → Leste"
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema: http://localhost:8080
echo    • API: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar
echo.

echo 📊 RESULTADOS ESPERADOS:
echo    ✅ Matrículas portuguesas aceitas e formatadas
echo    ✅ Pesquisa avançada funcional
echo    ✅ Direções corretas (Norte-Sul/Sul-Norte)
echo    ✅ Feedback de sucesso/erro
echo.

pause