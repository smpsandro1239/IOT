@echo off
echo ======================================
echo  SISTEMA RADAR COMPLETO - TESTE FINAL
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
echo  SISTEMA RADAR PRONTO!
echo ========================================
echo.
echo URL: http://localhost:8080
echo.
echo FUNCIONALIDADES IMPLEMENTADAS:
echo.
echo [RADAR CIRCULAR]
echo - Varredura rotativa em tempo real
echo - Aneis concentricos de distancia
echo - Marcadores de veiculos coloridos
echo - Animacoes de pulso para proximidade
echo.
echo [INFORMACOES COMPACTAS]
echo - Layout otimizado apos forca do sinal
echo - Exibicao de Placa e MAC do veiculo
echo - Direcao e distancia em tempo real
echo - Interface mais limpa e funcional
echo.
echo [CONTROLES DE BARREIRA]
echo - Barreiras Oeste-Leste e Leste-Oeste
echo - Ultimo acesso com matricula e MAC
echo - Abertura automatica baseada na direcao
echo - Controles manuais funcionais
echo.
echo [PESQUISA AVANCADA]
echo - Pesquisa por MAC e por Matricula
echo - Metricas por MAC ou Matricula
echo - Resultados paginados
echo - Autocomplete nos campos
echo.
echo [VEICULO ATIVO]
echo - Informacoes do veiculo no centro dos controles
echo - Matricula e MAC do veiculo que abriu a barreira
echo - Status em tempo real
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo COMO TESTAR:
echo.
echo 1. OBSERVE O RADAR:
echo    - Varredura circular continua
echo    - Aneis de distancia visiveis
echo    - Centro da estacao base
echo.
echo 2. TESTE A SIMULACAO:
echo    - Va para "Simulacao de Veiculo"
echo    - Digite uma placa (ex: ABC-1234)
echo    - Escolha direcao (Oeste→Leste ou Leste→Oeste)
echo    - Clique "Iniciar Simulacao"
echo.
echo 3. OBSERVE AS FUNCIONALIDADES:
echo    - Veiculo aparece no radar
echo    - Informacoes atualizadas em tempo real
echo    - Barreira abre automaticamente
echo    - Ultimo acesso registrado
echo    - Logs detalhados
echo.
echo 4. TESTE AS PESQUISAS:
echo    - Use os campos de pesquisa por MAC/Matricula
echo    - Teste o autocomplete
echo    - Verifique as metricas
echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
