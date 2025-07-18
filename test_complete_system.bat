@echo off
echo ========================================
echo  TESTE COMPLETO DO SISTEMA RADAR
echo ========================================
echo.

echo Verificando arquivos essenciais...

if exist "frontend\index.html" (
    echo [OK] index.html encontrado
) else (
    echo [ERRO] index.html nao encontrado
    pause
    exit
)

if exist "frontend\js\radar-simulation.js" (
    echo [OK] radar-simulation.js encontrado
) else (
    echo [ERRO] radar-simulation.js nao encontrado
    pause
    exit
)

if exist "frontend\js\api-client.js" (
    echo [OK] api-client.js encontrado
) else (
    echo [ERRO] api-client.js nao encontrado
    pause
    exit
)

echo.
echo Todos os arquivos essenciais estao presentes!
echo.

echo Iniciando servidor de teste...
start /B php -S localhost:8080 -t frontend

echo.
echo Aguardando inicializacao...
timeout /t 3 /nobreak > nul

echo.
echo ========================================
echo  SISTEMA PRONTO PARA TESTE!
echo ========================================
echo.
echo URL: http://localhost:8080
echo.
echo FUNCIONALIDADES PARA TESTAR:
echo.
echo 1. RADAR VISUAL:
echo    - Varredura circular em tempo real
echo    - Aneis de distancia
echo    - Centro da estacao base
echo.
echo 2. SIMULACAO DE VEICULO:
echo    - Selecionar placa e direcao
echo    - Iniciar simulacao
echo    - Observar movimento no radar
echo.
echo 3. CONTROLE DE BARREIRAS:
echo    - Abertura automatica
echo    - Controles manuais
echo    - Indicadores visuais
echo.
echo 4. LOGS DO SISTEMA:
echo    - Eventos em tempo real
echo    - Historico de acoes
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
