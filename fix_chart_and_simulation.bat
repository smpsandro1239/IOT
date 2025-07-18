@echo off
echo ===================================================
echo CORRIGINDO PROBLEMAS DE GRAFICOS E SIMULACAO
echo ===================================================

echo [1/4] Verificando Chart.js...
echo // Verificando se o Chart.js polyfill existe
if not exist frontend\js\chart-polyfill.js (
  echo Criando Chart.js polyfill...
  echo /**> frontend\js\chart-polyfill.js
  echo  * Chart.js Polyfill>> frontend\js\chart-polyfill.js
  echo  * Este script garante que o Chart.js esteja disponivel globalmente>> frontend\js\chart-polyfill.js
  echo  */>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo (function() {>> frontend\js\chart-polyfill.js
  echo   // Verificar se o Chart.js ja esta carregado>> frontend\js\chart-polyfill.js
  echo   if (typeof Chart !== 'undefined'^) {>> frontend\js\chart-polyfill.js
  echo     console.log('Chart.js ja esta disponivel'^);>> frontend\js\chart-polyfill.js
  echo     return;>> frontend\js\chart-polyfill.js
  echo   }>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo   console.log('Carregando Chart.js dinamicamente...'^);>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo   // Criar elemento script>> frontend\js\chart-polyfill.js
  echo   const script = document.createElement('script'^);>> frontend\js\chart-polyfill.js
  echo   script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';>> frontend\js\chart-polyfill.js
  echo   script.async = false; // Importante: carregar de forma sincrona>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo   // Adicionar evento de carregamento>> frontend\js\chart-polyfill.js
  echo   script.onload = function() {>> frontend\js\chart-polyfill.js
  echo     console.log('Chart.js carregado com sucesso'^);>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo     // Disparar evento personalizado para notificar que o Chart.js foi carregado>> frontend\js\chart-polyfill.js
  echo     document.dispatchEvent(new CustomEvent('chartjsloaded'^)^);>> frontend\js\chart-polyfill.js
  echo   };>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo   // Adicionar evento de erro>> frontend\js\chart-polyfill.js
  echo   script.onerror = function() {>> frontend\js\chart-polyfill.js
  echo     console.error('Falha ao carregar Chart.js'^);>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo     // Criar um polyfill minimo para evitar erros>> frontend\js\chart-polyfill.js
  echo     window.Chart = class Chart {>> frontend\js\chart-polyfill.js
  echo       constructor(canvas, config^) {>> frontend\js\chart-polyfill.js
  echo         console.warn('Usando Chart.js polyfill - os graficos nao serao renderizados'^);>> frontend\js\chart-polyfill.js
  echo         this.canvas = canvas;>> frontend\js\chart-polyfill.js
  echo         this.config = config;>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo         // Adicionar mensagem de erro no canvas>> frontend\js\chart-polyfill.js
  echo         if (canvas^) {>> frontend\js\chart-polyfill.js
  echo           const ctx = canvas.getContext('2d'^);>> frontend\js\chart-polyfill.js
  echo           if (ctx^) {>> frontend\js\chart-polyfill.js
  echo             ctx.font = '14px Arial';>> frontend\js\chart-polyfill.js
  echo             ctx.fillStyle = 'red';>> frontend\js\chart-polyfill.js
  echo             ctx.textAlign = 'center';>> frontend\js\chart-polyfill.js
  echo             ctx.fillText('Erro ao carregar Chart.js', canvas.width / 2, canvas.height / 2^);>> frontend\js\chart-polyfill.js
  echo           }>> frontend\js\chart-polyfill.js
  echo         }>> frontend\js\chart-polyfill.js
  echo       }>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo       update() { return this; }>> frontend\js\chart-polyfill.js
  echo       destroy() { return this; }>> frontend\js\chart-polyfill.js
  echo     };>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo     // Disparar evento personalizado para notificar que o polyfill foi criado>> frontend\js\chart-polyfill.js
  echo     document.dispatchEvent(new CustomEvent('chartjspolyfill'^)^);>> frontend\js\chart-polyfill.js
  echo   };>> frontend\js\chart-polyfill.js
  echo.>> frontend\js\chart-polyfill.js
  echo   // Adicionar script ao documento>> frontend\js\chart-polyfill.js
  echo   document.head.appendChild(script^);>> frontend\js\chart-polyfill.js
  echo })();>> frontend\js\chart-polyfill.js
)

echo [2/4] Adicionando Chart.js polyfill ao index.html...
powershell -Command "(Get-Content frontend\index.html) -replace '<script src=\"https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js\"></script>', '<script src=\"https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js\"></script>\n    <script src=\"js/chart-polyfill.js\"></script>' | Set-Content frontend\index.html"

echo [3/4] Corrigindo simulacao...
powershell -Command "(Get-Content frontend\js\simulation.js) -replace 'fetch\(''http://127.0.0.1:8000/api/v1/access-logs''\)', 'fetch(''./api/v1/access-logs'')' | Set-Content frontend\js\simulation.js"

echo [4/4] Corrigindo caminhos no API client...
powershell -Command "(Get-Content frontend\js\api-client.js) -replace 'constructor\(baseUrl = ''/api/v1''\)', 'constructor(baseUrl = ''./api/v1'')' | Set-Content frontend\js\api-client.js"

echo ===================================================
echo CORRECOES APLICADAS COM SUCESSO!
echo ===================================================
echo.
echo Agora reinicie o sistema usando:
echo reiniciar_sistema_corrigido.bat
echo.
pause
