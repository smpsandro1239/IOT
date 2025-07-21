@echo off
echo ========================================
echo  SISTEMA RADAR FINAL - TESTE COMPLETO
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
echo  SISTEMA FINAL PRONTO!
echo ========================================
echo.
echo URL: http://localhost:8080
echo.
echo MELHORIAS IMPLEMENTADAS:
echo.
echo [LAYOUT OTIMIZADO]
echo - Configuracao do Sistema movida para baixo das informacoes do veiculo
echo - Interface mais compacta e bem organizada
echo - Controles de configuracao funcionais
echo.
echo [RADAR CIRCULAR]
echo - Varredura rotativa em tempo real
echo - Marcadores de veiculos coloridos
echo - Animacoes de pulso para proximidade
echo - Aneis concentricos de distancia
echo.
echo [INFORMACOES COMPLETAS]
echo - Placa e MAC do veiculo exibidos
echo - Veiculo ativo mostrado nos controles
echo - Ultimo acesso registrado por barreira
echo - Pesquisa por MAC e Matricula
echo.
echo [CONFIGURACOES AVANCADAS]
echo - Configuracoes LoRa (Frequencia, Potencia, SF, BW)
echo - Configuracoes de Seguranca (Tempo, Distancia, Alcance)
echo - Modo de Emergencia
echo - Salvar/Restaurar/Exportar configuracoes
echo.
echo [CONTROLES INTELIGENTES]
echo - Barreiras Oeste-Leste e Leste-Oeste
echo - Abertura automatica baseada na direcao
echo - Informacoes do ultimo acesso
echo - Controles manuais funcionais
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo COMO TESTAR O SISTEMA COMPLETO:
echo.
echo 1. OBSERVE O RADAR:
echo    - Varredura circular continua
echo    - Aneis de distancia visiveis
echo    - Centro da estacao base destacado
echo.
echo 2. CONFIGURE O SISTEMA:
echo    - Ajuste as configuracoes LoRa
echo    - Defina parametros de seguranca
echo    - Teste o modo de emergencia
echo    - Salve as configuracoes
echo.
echo 3. TESTE A SIMULACAO:
echo    - Digite uma placa (ex: ABC-1234)
echo    - Escolha direcao (Oeste→Leste ou Leste→Oeste)
echo    - Inicie a simulacao
echo    - Observe todas as informacoes sendo atualizadas
echo.
echo 4. VERIFIQUE OS CONTROLES:
echo    - Veiculo ativo aparece no centro dos controles
echo    - Ultimo acesso registrado nas barreiras
echo    - Controles manuais funcionais
echo.
echo 5. TESTE AS PESQUISAS:
echo    - Pesquise por MAC ou Matricula
echo    - Use o autocomplete
echo    - Navegue pelos resultados
echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
