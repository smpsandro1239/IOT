@echo off
echo Reiniciando o banco de dados...
php artisan migrate:fresh --seed
echo Banco de dados reiniciado com sucesso!
echo.
echo Credenciais de acesso:
echo Email: admin@example.com
echo Senha: password
echo.
pause
