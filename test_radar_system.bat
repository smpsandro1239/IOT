@echo off
echo ========================================
echo  Sistema Radar IoT - Teste Completo
echo ========================================
echo.

echo Iniciando servidor frontend...
start /B php -S localhost:8080 -t frontend

echo.
echo Aguardando servidor inicializar...
timeout /t 3 /nobreak > nul

echo.
echo ========================================
echo  Sistema Radar iniciado com sucesso!
echo ========================================
echo.
echo Frontend: http://localhost:8080
echo.
echo NOVAS FUNCIONALIDADES IMPLEMENTADAS:
echo.
echo [VISUALIZACAO RADAR]
echo - Radar circular com varredura em tempo real
echo - Aneis concentricos para indicacao de distancia
echo - Marcadores visuais para veiculos detectados
echo - Animacao de pulso para veiculos proximos
echo.
echo [DETECCAO DE VEICULOS]
echo - Deteccao Norte-Sul e Sul-Norte
echo - Indicadores de forca de sinal LoRa
echo - Marcadores coloridos (Verde=Autorizado, Vermelho=Nao autorizado)
echo - Setas direcionais para indicar movimento
echo.
echo [CONTROLE DE BARREIRAS]
echo - Controles visuais para cada barreira
echo - Abertura automatica baseada na direcao do veiculo
echo - Bloqueio da barreira oposta durante passagem
echo - Indicadores de status em tempo real
echo.
echo [SIMULACAO AVANCADA]
echo - Movimento realista do veiculo no radar
echo - Calculo de distancia e forca do sinal
echo - Logs detalhados do sistema
echo - Integracao completa com controles
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo COMO TESTAR:
echo 1. Acesse a secao "Simulacao de Veiculo"
echo 2. Escolha uma placa e direcao
echo 3. Clique em "Iniciar Simulacao"
echo 4. Observe o radar e as barreiras
echo.
echo Para parar o servidor, feche esta janela ou pressione Ctrl+C
pause
