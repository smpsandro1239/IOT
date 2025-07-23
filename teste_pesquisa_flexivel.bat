@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🔍 TESTE DE PESQUISA FLEXÍVEL                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🚀 Iniciando sistema para teste de pesquisa flexível...
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
echo ║                    🧪 TESTE DE PESQUISA FLEXÍVEL                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 CENÁRIOS DE TESTE PARA PESQUISA FLEXÍVEL:
echo.
echo 1. 📱 PREPARAÇÃO - Adicionar veículos de teste:
echo.
echo    ✅ Adicione os seguintes veículos:
echo       • MAC: 24A160123456, Matrícula: AB1234
echo       • MAC: 1122334455AA, Matrícula: CD5678
echo       • MAC: BBCCDDEE1122, Matrícula: EF9012
echo.
echo    📋 Resultado esperado:
echo       • Formatados como: 24:A1:60:12:34:56 / AB-12-34
echo       • Formatados como: 11:22:33:44:55:AA / CD-56-78
echo       • Formatados como: BB:CC:DD:EE:11:22 / EF-90-12
echo.
echo 2. 🔍 TESTE DE PESQUISA POR MATRÍCULA:
echo.
echo    ✅ CENÁRIO A - Pesquisa com hífens:
echo       • Digite "AB-12-34" no campo de matrícula
echo       • Deve encontrar o veículo AB-12-34
echo.
echo    ✅ CENÁRIO B - Pesquisa sem hífens:
echo       • Digite "AB1234" no campo de matrícula
echo       • Deve encontrar o MESMO veículo AB-12-34
echo.
echo    ✅ CENÁRIO C - Pesquisa parcial com hífens:
echo       • Digite "CD-56" no campo de matrícula
echo       • Deve encontrar o veículo CD-56-78
echo.
echo    ✅ CENÁRIO D - Pesquisa parcial sem hífens:
echo       • Digite "CD56" no campo de matrícula
echo       • Deve encontrar o MESMO veículo CD-56-78
echo.
echo 3. 🔍 TESTE DE PESQUISA POR MAC:
echo.
echo    ✅ CENÁRIO E - Pesquisa com dois pontos:
echo       • Digite "24:A1:60" no campo de MAC
echo       • Deve encontrar o veículo 24:A1:60:12:34:56
echo.
echo    ✅ CENÁRIO F - Pesquisa sem dois pontos:
echo       • Digite "24A160" no campo de MAC
echo       • Deve encontrar o MESMO veículo 24:A1:60:12:34:56
echo.
echo    ✅ CENÁRIO G - Pesquisa completa sem separadores:
echo       • Digite "1122334455AA" no campo de MAC
echo       • Deve encontrar o veículo 11:22:33:44:55:AA
echo.
echo    ✅ CENÁRIO H - Pesquisa completa com separadores:
echo       • Digite "11:22:33:44:55:AA" no campo de MAC
echo       • Deve encontrar o MESMO veículo 11:22:33:44:55:AA
echo.
echo 4. 🔍 TESTE DE PESQUISA AVANÇADA (5 itens por página):
echo.
echo    ✅ CENÁRIO I - Pesquisa avançada por matrícula:
echo       • Vá para seção "Pesquisa Avançada"
echo       • Digite "EF90" no campo de matrícula
echo       • Deve encontrar o veículo EF-90-12
echo.
echo    ✅ CENÁRIO J - Pesquisa avançada por MAC:
echo       • Digite "BBCCDD" no campo de MAC
echo       • Deve encontrar o veículo BB:CC:DD:EE:11:22
echo.
echo 5. 🔄 TESTE DE PESQUISA COMBINADA:
echo.
echo    ✅ CENÁRIO K - Pesquisa por MAC e matrícula:
echo       • Digite "24A1" no campo de MAC
echo       • Digite "AB12" no campo de matrícula
echo       • Deve encontrar apenas o veículo que corresponde a ambos
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema: http://localhost:8080
echo    • API: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar
echo.

echo 📊 FUNCIONALIDADES A VERIFICAR:
echo    ✅ Pesquisa por matrícula COM hífens (AA-12-34)
echo    ✅ Pesquisa por matrícula SEM hífens (AA1234)
echo    ✅ Pesquisa por MAC COM dois pontos (24:A1:60:12:34:56)
echo    ✅ Pesquisa por MAC SEM dois pontos (24A160123456)
echo    ✅ Pesquisa parcial em ambos os formatos
echo    ✅ Pesquisa avançada com paginação (5 itens)
echo    ✅ Pesquisa combinada (MAC + matrícula)
echo    ✅ Resultados em tempo real (debounce)
echo.

echo 🎯 RESULTADOS ESPERADOS:
echo    ✅ Encontra veículos independente do formato de entrada
echo    ✅ "AB1234" encontra veículo armazenado como "AB-12-34"
echo    ✅ "24A160123456" encontra veículo armazenado como "24:A1:60:12:34:56"
echo    ✅ Pesquisa parcial funciona em ambos os formatos
echo    ✅ Pesquisa avançada com mesma flexibilidade
echo    ✅ Paginação de 5 itens na pesquisa avançada
echo.

echo 💡 DICA DE TESTE:
echo    • Teste primeiro com formato formatado (com separadores)
echo    • Depois teste com formato sem separadores
echo    • Ambos devem encontrar o mesmo resultado
echo    • Verifique se a pesquisa é instantânea (debounce)
echo.

pause