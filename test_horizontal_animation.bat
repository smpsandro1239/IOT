@echo off
echo ========================================
echo  Testando Animacao Horizontal Corrigida
echo ========================================
echo.

echo Iniciando servidor frontend...
start /B php -S localhost:8080 -t frontend

echo.
echo Aguardando servidor inicializar...
timeout /t 3 /nobreak > nul

echo.
echo ========================================
echo  Sistema iniciado com sucesso!
echo ========================================
echo.
echo Frontend: http://localhost:8080
echo.
echo Alteracoes implementadas:
echo - Visualizacao radar circular implementada
echo - Animacao de varredura radar em tempo real
echo - Deteccao de veiculos com marcadores visuais
echo - Indicadores de forca de sinal LoRa
echo - Sistema de aneis concentricos para distancia
echo - Animacao de pulso para veiculos proximos
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo Para parar o servidor, feche esta janela ou pressione Ctrl+C
pause
