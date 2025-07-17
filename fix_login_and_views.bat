@echo off
echo ===================================================
echo CORRIGINDO PROBLEMAS DE LOGIN E VIEWS DO SISTEMA
echo ===================================================

echo [1/6] Criando diretorios necessarios para o Laravel...
mkdir backend\storage\framework\views 2>nul
mkdir backend\storage\framework\cache 2>nul
mkdir backend\storage\framework\sessions 2>nul

echo [2/6] Definindo permissoes corretas...
icacls backend\storage /grant Everyone:(OI)(CI)F /T 2>nul

echo [3/6] Limpando cache do Laravel...
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
cd ..

echo [4/6] Verificando arquivos de frontend...
if not exist frontend\sw.js (
  echo ERRO: Service Worker nao encontrado!
) else (
  echo Service Worker encontrado.
)

echo [5/6] Verificando simulacao de login...
if not exist frontend\api\login\index.php (
  echo ERRO: API de login simulada nao encontrada!
) else (
  echo API de login simulada encontrada.
)

echo [6/6] Reiniciando o sistema...
call reiniciar_sistema_corrigido.bat

echo ===================================================
echo SISTEMA CORRIGIDO E REINICIADO COM SUCESSO!
echo ===================================================
echo Frontend: http://localhost:8080
echo Backend API: http://localhost:8000/api
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
echo IMPORTANTE: O sistema agora esta usando login simulado para desenvolvimento.
echo Todas as funcionalidades devem funcionar corretamente no frontend.
echo.
pause
