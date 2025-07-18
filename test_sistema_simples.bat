@echo off
echo ========================================
echo  TESTE DO SISTEMA HORIZONTAL SIMPLES
echo ========================================
echo.

echo Parando servidores existentes...
taskkill /F /IM php.exe >nul 2>&1

echo.
echo Iniciando servidor frontend...
start /B php -S localhost:8080 -t frontend

echo.
echo Aguardando inicializacao...
timeout /t 3 /nobreak > nul

echo.
echo ========================================
echo  SISTEMA PRONTO!
echo ========================================
echo.
echo URL: http://localhost:8080
echo.
echo FUNCIONALIDADES DISPONIVEIS:
echo.
echo 1. ANIMACAO HORIZONTAL:
echo    - Veiculo move-se da esquerda para direita
echo    - Estrada horizontal com marcacoes
echo    - Barreiras nas laterais (Oeste/Leste)
echo.
echo 2. SIMULACAO DE VEICULO:
echo    - Selecionar placa do veiculo
echo    - Escolher direcao (Oeste→Leste / Leste→Oeste)
echo    - Iniciar simulacao
echo.
echo 3. CONTROLE DE BARREIRAS:
echo    - Barreiras Oeste e Leste
echo    - Abertura automatica baseada na direcao
echo    - Controles manuais
echo.
echo 4. SISTEMA DE LOGS:
echo    - Registro de eventos
echo    - Monitoramento em tempo real
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
