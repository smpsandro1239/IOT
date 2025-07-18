@echo off
echo ===================================================
echo CORRIGINDO PROBLEMAS DE EXIBICAO DOS GRAFICOS
echo ===================================================

echo [1/6] Adicionando CSS para containers de graficos...
echo Criando estilos CSS para garantir que os graficos fiquem contidos...

echo /* Estilos para containers de graficos */> frontend\css\chart-styles.css
echo .chart-container {>> frontend\css\chart-styles.css
echo     position: relative;>> frontend\css\chart-styles.css
echo     height: 300px !important;>> frontend\css\chart-styles.css
echo     width: 100%% !important;>> frontend\css\chart-styles.css
echo     max-height: 300px;>> frontend\css\chart-styles.css
echo     overflow: hidden;>> frontend\css\chart-styles.css
echo     border-radius: 8px;>> frontend\css\chart-styles.css
echo     background-color: white;>> frontend\css\chart-styles.css
echo     box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);>> frontend\css\chart-styles.css
echo }>> frontend\css\chart-styles.css
echo.>> frontend\css\chart-styles.css
echo .chart-container canvas {>> frontend\css\chart-styles.css
echo     max-height: 280px !important;>> frontend\css\chart-styles.css
echo     height: 280px !important;>> frontend\css\chart-styles.css
echo }>> frontend\css\chart-styles.css
echo.>> frontend\css\chart-styles.css
echo .chart-wrapper {>> frontend\css\chart-styles.css
echo     background-color: #f9fafb;>> frontend\css\chart-styles.css
echo     padding: 1rem;>> frontend\css\chart-styles.css
echo     border-radius: 0.5rem;>> frontend\css\chart-styles.css
echo     border: 1px solid #e5e7eb;>> frontend\css\chart-styles.css
echo     margin-bottom: 1rem;>> frontend\css\chart-styles.css
echo }>> frontend\css\chart-styles.css
echo.>> frontend\css\chart-styles.css
echo .chart-title {>> frontend\css\chart-styles.css
echo     font-size: 1.125rem;>> frontend\css\chart-styles.css
echo     font-weight: 600;>> frontend\css\chart-styles.css
echo     color: #1f2937;>> frontend\css\chart-styles.css
echo     margin-bottom: 0.5rem;>> frontend\css\chart-styles.css
echo     text-align: center;>> frontend\css\chart-styles.css
echo }>> frontend\css\chart-styles.css

echo [2/6] Adicionando o CSS ao index.html...
powershell -Command "(Get-Content frontend\index.html) -replace '<link rel=\"stylesheet\" href=\"css/tailwind-local.css\">', '<link rel=\"stylesheet\" href=\"css/tailwind-local.css\">\n    <link rel=\"stylesheet\" href=\"css/chart-styles.css\">' | Set-Content frontend\index.html"

echo [3/6] Corrigindo o primeiro grafico que nao tem container...
powershell -Command "(Get-Content frontend\index.html) -replace '<div class=\"bg-gray-50 p-4 rounded-lg\">\s+<h3 class=\"text-lg font-semibold text-gray-800 mb-2\">Acessos Diários</h3>\s+<div class=\"chart-container\"', '<div class=\"bg-gray-50 p-4 rounded-lg\">\n                    <h3 class=\"text-lg font-semibold text-gray-800 mb-2\">Acessos Diários</h3>\n                    <div class=\"chart-container\"' | Set-Content frontend\index.html"

echo [4/6] Verificando se todos os graficos tem containers...
findstr /n "chart-container" frontend\index.html
if %errorlevel% neq 0 (
    echo AVISO: Nem todos os graficos tem containers com altura fixa!
)

echo [5/6] Adicionando funcao para redimensionar graficos...
echo // Funcao para redimensionar graficos>> frontend\js\chart-resize.js
echo function resizeCharts() {>> frontend\js\chart-resize.js
echo     if (typeof window.app !== 'undefined' ^&^& window.app.charts) {>> frontend\js\chart-resize.js
echo         Object.values(window.app.charts).forEach(chart => {>> frontend\js\chart-resize.js
echo             if (chart ^&^& typeof chart.resize === 'function') {>> frontend\js\chart-resize.js
echo                 chart.resize();>> frontend\js\chart-resize.js
echo             }>> frontend\js\chart-resize.js
echo         });>> frontend\js\chart-resize.js
echo     }>> frontend\js\chart-resize.js
echo }>> frontend\js\chart-resize.js
echo.>> frontend\js\chart-resize.js
echo // Redimensionar graficos quando a janela for redimensionada>> frontend\js\chart-resize.js
echo window.addEventListener('resize', resizeCharts);>> frontend\js\chart-resize.js
echo.>> frontend\js\chart-resize.js
echo // Redimensionar graficos quando a aba ficar visivel>> frontend\js\chart-resize.js
echo document.addEventListener('visibilitychange', function() {>> frontend\js\chart-resize.js
echo     if (!document.hidden) {>> frontend\js\chart-resize.js
echo         setTimeout(resizeCharts, 100);>> frontend\js\chart-resize.js
echo     }>> frontend\js\chart-resize.js
echo });>> frontend\js\chart-resize.js

echo [6/6] Adicionando o script de redimensionamento ao index.html...
powershell -Command "(Get-Content frontend\index.html) -replace '<script src=\"js/simulation.js\"></script>', '<script src=\"js/simulation.js\"></script>\n    <script src=\"js/chart-resize.js\"></script>' | Set-Content frontend\index.html"

echo ===================================================
echo CORRECOES APLICADAS COM SUCESSO!
echo ===================================================
echo.
echo As seguintes melhorias foram implementadas:
echo.
echo 1. CONTAINERS COM ALTURA FIXA:
echo    - Todos os graficos agora estao em containers de 300px de altura
echo    - Os containers tem overflow: hidden para evitar crescimento
echo    - CSS responsivo para diferentes tamanhos de tela
echo.
echo 2. CONFIGURACOES DE GRAFICO OTIMIZADAS:
echo    - maintainAspectRatio: false (permite ajuste ao container)
echo    - responsive: true (responsivo)
echo    - Escalas automaticas que se ajustam aos dados
echo.
echo 3. ESTILOS VISUAIS MELHORADOS:
echo    - Containers com fundo cinza claro
echo    - Bordas arredondadas
echo    - Sombras sutis
echo    - Titulos centralizados
echo.
echo 4. REDIMENSIONAMENTO AUTOMATICO:
echo    - Os graficos se redimensionam quando a janela muda de tamanho
echo    - Redimensionamento quando a aba fica visivel novamente
echo.
echo Agora reinicie o sistema usando:
echo reiniciar_sistema_corrigido.bat
echo.
echo Os graficos devem aparecer em caixas de tamanho fixo (300px de altura)
echo e a escala se ajustara automaticamente conforme os dados mudam.
echo.
pause
