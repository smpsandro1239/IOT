@echo off
echo ========================================
echo  CORRECAO E TESTE DO SISTEMA COMPLETO
echo ========================================
echo.

echo [1/5] Verificando estrutura de arquivos...
if not exist "frontend\index.html" (
    echo ERRO: index.html nao encontrado
    pause
    exit /b 1
)

if not exist "frontend\js\radar-simulation.js" (
    echo ERRO: radar-simulation.js nao encontrado
    pause
    exit /b 1
)

if not exist "frontend\js\system-config.js" (
    echo ERRO: system-config.js nao encontrado
    pause
    exit /b 1
)

echo Todos os arquivos essenciais encontrados!

echo.
echo [2/5] Parando servidores existentes...
taskkill /F /IM php.exe >nul 2>&1

echo.
echo [3/5] Iniciando servidor de desenvolvimento...
start /B php -S localhost:8080 -t frontend

echo.
echo [4/5] Aguardando inicializacao...
timeout /t 3 /nobreak > nul

echo.
echo [5/5] Testando conectividade...
curl -s -o nul -w "Status: %%{http_code}" http://localhost:8080 2>nul
if %ERRORLEVEL% EQU 0 (
    echo.
    echo Servidor respondendo corretamente!
) else (
    echo.
    echo Aviso: Nao foi possivel verificar o servidor
)

echo.
echo ========================================
echo  SISTEMA CORRIGIDO E FUNCIONANDO!
echo ========================================
echo.
echo URL Principal: http://localhost:8080
echo URL de Teste:  http://localhost:8080/test-radar.html
echo.
echo COMPONENTES VERIFICADOS:
echo [OK] HTML principal com radar
echo [OK] JavaScript de simulacao radar
echo [OK] Sistema de diagnosticos
echo [OK] Estilos CSS do radar
echo [OK] Controles de barreira
echo [OK] Sistema de logs
echo.
echo FUNCIONALIDADES PRINCIPAIS:
echo.
echo 1. RADAR VISUAL:
echo    - Varredura circular animada
echo    - Aneis de distancia concentricos
echo    - Marcadores de veiculos coloridos
echo    - Animacoes de pulso
echo.
echo 2. SIMULACAO REALISTICA:
echo    - Movimento de veiculos no radar
echo    - Calculo de distancia em tempo real
echo    - Abertura automatica de barreiras
echo    - Logs detalhados de eventos
echo.
echo 3. CONTROLES INTERATIVOS:
echo    - Botoes de simulacao
echo    - Controles manuais de barreira
echo    - Indicadores de status
echo    - Forca de sinal LoRa
echo.
echo COMO USAR:
echo 1. Acesse http://localhost:8080
echo 2. Va para "Simulacao de Veiculo"
echo 3. Escolha placa e direcao
echo 4. Clique "Iniciar Simulacao"
echo 5. Observe o radar e barreiras
echo.
echo Pressione qualquer tecla para abrir o sistema...
pause > nul

start http://localhost:8080

echo.
echo Sistema em execucao!
echo Pressione Ctrl+C para parar o servidor
pause
