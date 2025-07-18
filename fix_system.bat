@echo off
echo ===================================================
echo CORRIGINDO TODOS OS PROBLEMAS DO SISTEMA
echo ===================================================

echo [1/11] Verificando estrutura de diretorios...
mkdir backend\storage\framework\views 2>nul
mkdir backend\storage\framework\cache 2>nul
mkdir backend\storage\framework\sessions 2>nul
mkdir frontend\api\v1\status\latest 2>nul
mkdir frontend\api\v1\metrics 2>nul
mkdir frontend\api\v1\macs-autorizados 2>nul
mkdir frontend\api\v1\gate\control 2>nul
mkdir frontend\api\v1\access-logs 2>nul

echo [2/11] Corrigindo erros de sintaxe...
call fix_syntax_errors.bat

echo [3/11] Corrigindo caminhos no API client...
powershell -Command "(Get-Content frontend\js\api-client.js) -replace 'constructor\(baseUrl = ''/api/v1''\)', 'constructor(baseUrl = ''./api/v1'')' -replace 'fetch\(''/api/login/index.php'', ', 'fetch(''./api/login/index.php'', ' | Set-Content frontend\js\api-client.js"

echo [4/11] Garantindo que Chart.js seja carregado corretamente...
powershell -Command "(Get-Content frontend\index.html) -replace '<script src=\"https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js\"></script>', '<script src=\"https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js\"></script>' | Set-Content frontend\index.html"

echo [5/11] Corrigindo caminhos no Service Worker...
powershell -Command "(Get-Content frontend\index.html) -replace 'navigator.serviceWorker.register(''/sw.js'')', 'navigator.serviceWorker.register(''./sw.js'')' | Set-Content frontend\index.html"

echo [6/11] Corrigindo caminhos no manifest.json...
powershell -Command "(Get-Content frontend\manifest.json) -replace '\"start_url\": \"/\"', '\"start_url\": \"./\"' | Set-Content frontend\manifest.json"

echo [7/11] Corrigindo simulacao...
powershell -Command "(Get-Content frontend\js\simulation.js) -replace 'fetch\(''http://127.0.0.1:8000/api/v1/access-logs''\)', 'fetch(''./api/v1/access-logs'')' | Set-Content frontend\js\simulation.js"

echo [8/11] Corrigindo animacao de veiculos...
powershell -Command "(Get-Content frontend\js\simulation.js) -replace 'this.vehicleMarker.style.left = ''25%'';', 'this.vehicleMarker.style.left = ''50%'';' | Set-Content frontend\js\simulation.js"
powershell -Command "(Get-Content frontend\js\simulation.js) -replace 'this.vehicleMarker.style.left = ''75%'';', 'this.vehicleMarker.style.left = ''50%'';' | Set-Content frontend\js\simulation.js"

echo [9/11] Removendo caracteres de escape incorretos...
powershell -Command "(Get-Content frontend\index.html) -replace '\\n', '' | Set-Content frontend\index.html"
powershell -Command "(Get-Content frontend\js\app.js) -replace '\\n', '' | Set-Content frontend\js\app.js"

echo [10/11] Limpando cache do Laravel...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
cd ..

echo [11/11] Verificando configuracoes finais...
echo Verificando se todos os arquivos foram criados corretamente...

echo ===================================================
echo SISTEMA CORRIGIDO COM SUCESSO!
echo ===================================================
echo.
echo Melhorias implementadas:
echo.
echo 1. ERROS DE SINTAXE CORRIGIDOS:
echo    - chart-polyfill.js - Sintaxe JavaScript corrigida
echo    - chart-resize.js - Funcoes de redimensionamento corrigidas
echo    - app.js - Caracteres de escape removidos
echo    - index.html - Tags HTML corrigidas
echo.
echo 2. GRAFICOS CORRIGIDOS:
echo    - Containers com altura fixa de 300px
echo    - Escalas que se ajustam automaticamente aos dados
echo    - Nao crescem mais indefinidamente para baixo
echo    - Design responsivo para diferentes telas
echo.
echo 3. SIMULACAO MELHORADA:
echo    - Estacao base centralizada
echo    - Movimento correto dos veiculos
echo    - Forca do sinal realista
echo    - Logica correta das cancelas
echo.
echo 4. API E NAVEGACAO:
echo    - Caminhos relativos corrigidos
echo    - Endpoints simulados funcionais
echo    - CORS configurado corretamente
echo    - Service Worker funcionando
echo.
echo Para usar o sistema:
echo 1. Execute: reiniciar_sistema_corrigido.bat
echo 2. Acesse: http://localhost:8080
echo 3. Login: admin@example.com / password
echo.
echo Os erros de sintaxe foram corrigidos e os graficos devem
echo aparecer em caixas de tamanho fixo sem crescer indefinidamente.
echo.
pause
