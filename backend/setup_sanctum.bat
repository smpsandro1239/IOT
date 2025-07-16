@echo off
echo ===================================================
echo    CONFIGURACAO DO LARAVEL SANCTUM
echo ===================================================
echo.

echo [1/4] Instalando Laravel Sanctum...
call composer require laravel/sanctum
echo.

echo [2/4] Publicando arquivos de configuracao...
call php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
echo.

echo [3/4] Configurando o middleware...
echo Certifique-se de que o middleware Sanctum esteja configurado em app/Http/Kernel.php
echo.

echo [4/4] Executando migracoes...
call php artisan migrate:fresh --seed
echo.

echo ===================================================
echo    LARAVEL SANCTUM CONFIGURADO COM SUCESSO!
echo ===================================================
echo.
pause
