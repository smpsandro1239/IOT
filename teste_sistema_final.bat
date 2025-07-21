@echo off
echo ========================================
echo  SISTEMA RADAR FINAL - VERSAO COMPLETA
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
echo  SISTEMA PRONTO PARA USO!
echo ========================================
echo.
echo URL: http://localhost:8080
echo.
echo MELHORIAS IMPLEMENTADAS:
echo.
echo [1] ADICAO DE MACS COM PERSISTENCIA
echo - MACs adicionados sao salvos no localStorage
echo - Disponibilidade imediata para consulta
echo - Formatacao automatica do MAC
echo.
echo [2] PESQUISA POR MATRICULA NAS METRICAS
echo - Busca por correspondencias exatas e parciais
echo - Atualizacao automatica do campo MAC
echo - Uso do MAC para atualizacao dos graficos
echo.
echo [3] CONFIGURACOES APLICADAS IMEDIATAMENTE
echo - Configuracoes salvas automaticamente
echo - Aplicacao imediata das configuracoes
echo - Persistencia entre sessoes
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo COMO TESTAR:
echo.
echo 1. ADICIONAR NOVO MAC:
echo    - Va para "Adicionar MAC Autorizado"
echo    - Digite um MAC e uma placa
echo    - Clique em "Adicionar MAC"
echo    - Verifique que o MAC aparece na lista de MACs autorizados
echo.
echo 2. PESQUISAR POR MATRICULA:
echo    - Va para "Metricas por MAC"
echo    - Digite uma matricula no campo "Selecionar por Matricula"
echo    - Observe que o campo MAC e preenchido automaticamente
echo    - Verifique que os graficos sao atualizados
echo.
echo 3. CONFIGURAR O SISTEMA:
echo    - Ajuste as configuracoes LoRa e de Seguranca
echo    - Observe que as configuracoes sao aplicadas imediatamente
echo    - Feche e reabra o navegador para verificar a persistencia
echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
