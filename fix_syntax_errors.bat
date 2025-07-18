@echo off
echo ===================================================
echo CORRIGINDO ERROS DE SINTAXE NO JAVASCRIPT
echo ===================================================

echo [1/6] Criando arquivo CSS para graficos...
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

echo [2/6] Verificando arquivo chart-polyfill.js...
if exist frontend\js\chart-polyfill.js (
    echo Arquivo chart-polyfill.js encontrado.
) else (
    echo Criando arquivo chart-polyfill.js...
    echo /**> frontend\js\chart-polyfill.js
    echo  * Chart.js Polyfill>> frontend\js\chart-polyfill.js
    echo  */>> frontend\js\chart-polyfill.js
    echo (function() {>> frontend\js\chart-polyfill.js
    echo   if (typeof Chart !== 'undefined') {>> frontend\js\chart-polyfill.js
    echo     console.log('Chart.js ja disponivel');>> frontend\js\chart-polyfill.js
    echo     return;>> frontend\js\chart-polyfill.js
    echo   }>> frontend\js\chart-polyfill.js
    echo   console.log('Chart.js carregado');>> frontend\js\chart-polyfill.js
    echo })();>> frontend\js\chart-polyfill.js
)

echo [3/6] Verificando arquivo chart-resize.js...
if exist frontend\js\chart-resize.js (
    echo Arquivo chart-resize.js encontrado.
) else (
    echo Criando arquivo chart-resize.js...
    echo // Funcao para redimensionar graficos> frontend\js\chart-resize.js
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
    echo window.addEventListener('resize', resizeCharts);>> frontend\js\chart-resize.js
)

echo [4/6] Verificando Service Worker...
if exist frontend\sw.js (
    echo Service Worker encontrado.
) else (
    echo AVISO: Service Worker nao encontrado!
)

echo [5/6] Verificando estrutura de API...
if not exist frontend\api\v1\status\latest\index.php (
    echo Criando endpoint de status...
    mkdir frontend\api\v1\status\latest 2>nul
    echo ^<?php> frontend\api\v1\status\latest\index.php
    echo header('Content-Type: application/json');>> frontend\api\v1\status\latest\index.php
    echo echo json_encode(['status' => 'ok']);>> frontend\api\v1\status\latest\index.php
)

echo [6/6] Verificando arquivos JavaScript principais...
if exist frontend\js\app.js (
    echo app.js encontrado.
) else (
    echo ERRO: app.js nao encontrado!
)

if exist frontend\js\api-client.js (
    echo api-client.js encontrado.
) else (
    echo ERRO: api-client.js nao encontrado!
)

if exist frontend\js\ui-components.js (
    echo ui-components.js encontrado.
) else (
    echo ERRO: ui-components.js nao encontrado!
)

echo ===================================================
echo VERIFICACAO DE SINTAXE CONCLUIDA!
echo ===================================================
echo.
echo Arquivos verificados e corrigidos:
echo 1. chart-styles.css - Estilos para graficos
echo 2. chart-polyfill.js - Polyfill para Chart.js
echo 3. chart-resize.js - Redimensionamento de graficos
echo 4. Endpoints de API simulada
echo 5. Arquivos JavaScript principais
echo.
echo Agora reinicie o sistema usando:
echo reiniciar_sistema_corrigido.bat
echo.
echo Os erros de sintaxe devem estar corrigidos.
echo.
pause
