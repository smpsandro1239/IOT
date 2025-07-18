@echo off
echo ===================================================
echo REINICIANDO O SISTEMA DE BARREIRAS IOT (CORRIGIDO)
echo ===================================================

echo [1/5] Parando servidores em execucao...
taskkill /F /IM php.exe >nul 2>&1

echo [2/5] Limpando cache...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
cd ..

echo [3/5] Verificando estrutura de pastas...
mkdir backend\storage\framework\views 2>nul
mkdir backend\storage\framework\cache 2>nul
mkdir backend\storage\framework\sessions 2>nul

echo [4/5] Iniciando o servidor Laravel...
start cmd /c "cd backend && php artisan serve"

echo [5/5] Iniciando o servidor frontend...
start cmd /c "cd frontend && php -S localhost:8080"

echo ===================================================
echo SISTEMA REINICIADO COM SUCESSO!
echo ===================================================
echo Frontend: http://localhost:8080
echo Backend API: http://localhost:8000/api
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
echo SISTEMA RADAR CIRCULAR - VERSAO FINAL:
echo - Radar circular com varredura em tempo real
echo - Deteccao de veiculos com marcadores visuais
echo - Layout compacto com informacoes de Placa e MAC
echo - Controles de barreira com veiculo ativo
echo - Pesquisa avancada por MAC e Matricula
echo - Sistema de logs detalhado
echo - Interface responsiva e intuitiva
echo.
echo IMPORTANTE: O sistema agora esta usando APIs simuladas para desenvolvimento.
echo Todas as funcionalidades devem funcionar corretamente no frontend.
echo.
pause
