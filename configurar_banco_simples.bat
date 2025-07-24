@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🗄️ CONFIGURAR BANCO DE DADOS                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🗄️ Configurando banco de dados MySQL...
echo.

:: Configurar PHP do Laragon
echo [1/6] Configurando PHP do Laragon...
set "PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64"
if not exist "%PHP_PATH%\php.exe" (
    for /d %%d in (C:\laragon\bin\php\php-*) do (
        if exist "%%d\php.exe" (
            set "PHP_PATH=%%d"
            goto :found_php
        )
    )
    echo ❌ PHP não encontrado!
    pause
    exit /b 1
)

:found_php
set "PATH=%PHP_PATH%;%PATH%"
echo ✅ PHP configurado

echo [2/6] Verificando MySQL...
tasklist | findstr mysqld >nul
if errorlevel 1 (
    echo ❌ MySQL não está rodando!
    echo    Inicie o Laragon e ative o MySQL
    pause
    exit /b 1
) else (
    echo ✅ MySQL está rodando
)

echo [3/6] Criando banco de dados...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS laravel_barrier_control;" 2>nul
if errorlevel 1 (
    echo ⚠️  Erro ao criar banco, tentando continuar...
) else (
    echo ✅ Banco de dados criado/verificado
)

echo [4/6] Verificando conexão com banco...
mysql -u root -e "USE laravel_barrier_control; SELECT 'Conexão OK' as status;" 2>nul
if errorlevel 1 (
    echo ❌ Erro na conexão com banco
    pause
    exit /b 1
) else (
    echo ✅ Conexão com banco OK
)

echo [5/6] Criando tabelas básicas...
cd backend

:: Criar tabela de MACs autorizados
mysql -u root laravel_barrier_control -e "
CREATE TABLE IF NOT EXISTS macs_autorizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    ultimo_acesso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);" 2>nul

:: Criar tabela de logs de acesso
mysql -u root laravel_barrier_control -e "
CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(17) NOT NULL,
    nome VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'autorizado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);" 2>nul

:: Criar tabela de status do sistema
mysql -u root laravel_barrier_control -e "
CREATE TABLE IF NOT EXISTS system_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barrier_status VARCHAR(50) DEFAULT 'fechada',
    last_detection TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);" 2>nul

echo ✅ Tabelas criadas

echo [6/6] Inserindo dados de teste...
mysql -u root laravel_barrier_control -e "
INSERT IGNORE INTO macs_autorizados (mac_address, nome, ultimo_acesso) VALUES 
('AA-BB-CC-DD-EE-FF', 'Veículo Teste 1', NOW()),
('11-22-33-44-55-66', 'Veículo Teste 2', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('77-88-99-AA-BB-CC', 'Veículo Teste 3', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT IGNORE INTO system_status (barrier_status, last_detection) VALUES 
('fechada', NOW());
" 2>nul

echo ✅ Dados de teste inseridos

cd ..

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ BANCO DE DADOS CONFIGURADO                             ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🧪 Testando banco...
mysql -u root laravel_barrier_control -e "SELECT COUNT(*) as total_macs FROM macs_autorizados;" 2>nul
if errorlevel 1 (
    echo ⚠️  Erro ao testar banco
) else (
    echo ✅ Banco funcionando
)

echo.
echo 🚀 Agora execute: iniciar_com_laragon_fixo.bat
echo.
pause