@echo off
echo ========================================
echo  LIMPEZA DE CACHE DO SISTEMA RADAR
echo ========================================
echo.

echo [1/4] Parando servidores existentes...
taskkill /F /IM php.exe >nul 2>&1

echo [2/4] Criando arquivo de cache-buster...
echo // Cache buster - %DATE% %TIME% > frontend/js/cache-buster.js
echo window.CACHE_VERSION = "%DATE%-%TIME%"; >> frontend/js/cache-buster.js
echo console.log("Cache atualizado em: %DATE% %TIME%"); >> frontend/js/cache-buster.js

echo [3/4] Atualizando referências de cache no HTML...
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/chart-polyfill.js\"></script>', '<script src=\"js/chart-polyfill.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/app.js\"></script>', '<script src=\"js/app.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/radar-simulation.js\"></script>', '<script src=\"js/radar-simulation.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/search-functionality.js\"></script>', '<script src=\"js/search-functionality.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/system-configuration.js\"></script>', '<script src=\"js/system-configuration.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/chart-resize.js\"></script>', '<script src=\"js/chart-resize.js?v=%RANDOM%\"></script>' | Set-Content frontend/index.html"

echo [4/4] Adicionando script de cache-buster...
powershell -Command "(Get-Content frontend/index.html) -replace '<script src=\"js/chart-polyfill.js', '<script src=\"js/cache-buster.js?v=%RANDOM%\"></script>\n    <script src=\"js/chart-polyfill.js' | Set-Content frontend/index.html"

echo.
echo ========================================
echo  CACHE LIMPO COM SUCESSO!
echo ========================================
echo.
echo Agora você pode iniciar o sistema com:
echo   teste_sistema_final.bat
echo.
echo O sistema carregará sempre a versão mais recente dos arquivos.
echo.
echo Pressione qualquer tecla para continuar...
pause > nul
