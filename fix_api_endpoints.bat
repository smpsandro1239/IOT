@echo off
echo ===================================================
echo CORRIGINDO ENDPOINTS DE API DO SISTEMA
echo ===================================================

echo [1/5] Verificando estrutura de diretórios da API...
mkdir frontend\api\v1\status\latest 2>nul
mkdir frontend\api\v1\metrics 2>nul
mkdir frontend\api\v1\macs-autorizados 2>nul
mkdir frontend\api\v1\gate\control 2>nul
mkdir frontend\api\v1\access-logs 2>nul

echo [2/5] Corrigindo caminhos no API client...
powershell -Command "(Get-Content frontend\js\api-client.js) -replace 'constructor\(baseUrl = ''/api/v1''\)', 'constructor(baseUrl = ''./api/v1'')' -replace 'fetch\(''/api/login/index.php'', ', 'fetch(''./api/login/index.php'', ' | Set-Content frontend\js\api-client.js"

echo [3/5] Corrigindo Chart.js no index.html...
powershell -Command "(Get-Content frontend\index.html) -replace '<script src=\"js/ui-components.js\"></script>', '<script src=\"js/ui-components.js\"></script>\n    <script>\n        // Garantir que o Chart.js está disponível globalmente\n        if (typeof Chart === \"undefined\") {\n            document.write(''<script src=\"https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js\"><\/script>'');\n        }\n    </script>' | Set-Content frontend\index.html"

echo [4/5] Corrigindo caminhos no Service Worker...
powershell -Command "(Get-Content frontend\index.html) -replace 'navigator.serviceWorker.register(''/sw.js'')', 'navigator.serviceWorker.register(''./sw.js'')' | Set-Content frontend\index.html"
powershell -Command "(Get-Content frontend\sw.js) -replace 'const urlsToCache = \[\n  ''/'',\n  ''/index.html'',\n  ''/login.html'',\n  ''/manifest.json'',\n  ''/css/', 'const urlsToCache = [\n  ''./'',\n  ''./index.html'',\n  ''./login.html'',\n  ''./manifest.json'',\n  ''./css/' | Set-Content frontend\sw.js"

echo [5/5] Corrigindo caminhos no manifest.json...
powershell -Command "(Get-Content frontend\manifest.json) -replace '\"start_url\": \"/\"', '\"start_url\": \"./\"' | Set-Content frontend\manifest.json"

echo ===================================================
echo ENDPOINTS DE API CORRIGIDOS COM SUCESSO!
echo ===================================================
echo.
echo Agora reinicie o sistema usando:
echo reiniciar_sistema_corrigido.bat
echo.
pause
