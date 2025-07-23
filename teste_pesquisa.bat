@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🧪 TESTE COMPLETO DO SISTEMA                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🚀 Iniciando sistema para teste completo...
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
echo ║                    📋 TESTE DE ÚLTIMO ACESSO                                ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 CENÁRIOS DE TESTE PARA ÚLTIMO ACESSO:
echo.
echo 1. 📱 TESTE DE SIMULAÇÃO E ÚLTIMO ACESSO:
echo.
echo    ✅ PASSO 1 - Adicionar veículo:
echo       • Clique em "MACs Autorizados"
echo       • Adicione: MAC: 24A160123456, Matrícula: AB1234
echo       • Verifique se foi adicionado com sucesso
echo.
echo    🎮 PASSO 2 - Executar simulação:
echo       • Volte ao painel principal
echo       • Na seção "Simulação de Veículo":
echo         - Matrícula: AB1234 (ou AB-12-34)
echo         - Direção: Norte → Sul
echo       • Clique "Iniciar Simulação"
echo       • Aguarde a simulação completar
echo.
echo    📊 PASSO 3 - Verificar último acesso:
echo       • Na seção "Controlo de Barreiras"
echo       • Barreira "Norte → Sul" deve mostrar:
echo         - Matrícula: AB-12-34
echo         - MAC: 24:A1:60:12:34:56
echo         - Hora atual do acesso
echo.
echo    🔍 PASSO 4 - Verificar na base de dados:
echo       • Vá para "MACs Autorizados"
echo       • Procure o veículo AB-12-34
echo       • Último acesso deve estar atualizado
echo.
echo 2. 🧪 TESTE DE FORMATOS E DUPLICATAS:
echo.
echo    ✅ CENÁRIO A - Teste de duplicata:
echo       • Tente adicionar: MAC: 24:A1:60:12:34:56, Matrícula: AB-12-34
echo       • Deve detectar duplicata e mostrar modal
echo       • Teste opções "Cancelar" e "Substituir"
echo.
echo    ✅ CENÁRIO B - Teste de formatos:
echo       • Adicione: MAC: 1122334455AA, Matrícula: CD5678
echo       • Deve formatar como: 11:22:33:44:55:AA, CD-56-78
echo.
echo 3. 🔍 TESTE DE PESQUISA AVANÇADA:
echo.
echo    • Digite "AB" no campo de matrícula
echo    • Digite "24:A1" no campo de MAC
echo    • Verifique se encontra os veículos
echo    • Teste paginação (5 itens por página)
echo.
echo 4. 🧭 TESTE DE DIREÇÕES:
echo.
echo    • Execute simulação com direção "Sul → Norte"
echo    • Verifique se barreira "Sul → Norte" é atualizada
echo    • Confirme que não aparece "Oeste → Leste"
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema: http://localhost:8080
echo    • API: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar
echo.

echo 📊 FUNCIONALIDADES A VERIFICAR:
echo    ✅ Validação de matrículas portuguesas
echo    ✅ Detecção de duplicatas após formatação
echo    ✅ Atualização de último acesso após simulação
echo    ✅ Sincronização entre simulação e base de dados
echo    ✅ Pesquisa avançada (5 itens por página)
echo    ✅ Direções corretas (Norte-Sul/Sul-Norte)
echo    ✅ Modal de confirmação de duplicatas
echo    ✅ Feedback de sucesso/erro
echo.

echo 🎯 RESULTADOS ESPERADOS:
echo    ✅ Último acesso atualizado automaticamente
echo    ✅ Informação sincronizada em tempo real
echo    ✅ Base de dados atualizada após simulação
echo    ✅ Interface mostra hora correta do acesso
echo.

pause