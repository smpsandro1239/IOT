@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 TESTE DO SISTEMA FINAL CORRIGIDO                      ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo [1/6] Iniciando backend...
cd backend
start "Backend API" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 >nul
cd ..

echo [2/6] Iniciando frontend...
cd frontend
start "Frontend Server" cmd /k "php -S localhost:8080"
timeout /t 3 >nul
cd ..

echo [3/6] Aguardando serviços iniciarem...
timeout /t 5 >nul

echo [4/6] Abrindo sistema no navegador...
start http://localhost:8080

echo [5/6] Testando funcionalidades corrigidas...
echo.
echo ✅ CORREÇÕES IMPLEMENTADAS:
echo    • Validação de matrículas portuguesas (AA-12-12, 12-AB-34, 12-34-AB)
echo    • Pesquisa avançada conectada à base de dados
echo    • Direções corrigidas (apenas Norte-Sul e Sul-Norte)
echo    • Dados de exemplo com formatos corretos
echo    • Feedback de operações (sucesso/erro)
echo    • Paginação de 5 itens na pesquisa avançada
echo.

echo [6/6] COMO TESTAR:
echo.
echo 📋 VALIDAÇÃO DE MATRÍCULAS:
echo    1. Vá para "MACs Autorizados"
echo    2. Teste adicionar: AA1212, 12AB34, 1234AB
echo    3. Teste com hífens: AA-12-12, 12-AB-34, 12-34-AB
echo    4. Todos devem ser aceitos e formatados
echo.
echo 🔍 PESQUISA AVANÇADA:
echo    1. Digite parte de um MAC ou matrícula
echo    2. Verifique se os resultados aparecem
echo    3. Teste a paginação (5 itens por página)
echo.
echo 🧭 DIREÇÕES:
echo    1. Inicie uma simulação
echo    2. Verifique se aparecem apenas "Norte → Sul" e "Sul → Norte"
echo    3. Não deve aparecer "Oeste → Leste"
echo.
echo 💾 FEEDBACK:
echo    1. Adicione um veículo novo - deve mostrar sucesso
echo    2. Tente adicionar duplicado - deve mostrar modal
echo    3. Verifique mensagens de confirmação
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema rodando em: http://localhost:8080
echo    • API rodando em: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar os serviços
echo.

pause