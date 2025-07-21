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
echo Sistema iniciado com sucesso!
echo.
echo Pressione qualquer tecla para abrir o navegador...
pause > nul

start http://localhost:8080

echo.
echo Sistema em execucao!
echo Para parar, feche esta janela ou pressione Ctrl+C
pause
