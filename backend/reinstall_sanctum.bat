@echo off
echo Reinstalando Laravel Sanctum...
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate:fresh --seed
echo Sanctum reinstalado com sucesso!
pause
