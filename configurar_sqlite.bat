@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🗄️ CONFIGURAR SQLITE - SIMPLES                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🗄️ Configurando SQLite (mais simples que MySQL)...
echo.

:: Configurar PHP do Laragon
echo [1/5] Configurando PHP do Laragon...
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

echo [2/5] Criando diretório database...
cd backend
if not exist "database" mkdir database
echo ✅ Diretório database criado

echo [3/5] Criando arquivo SQLite...
echo. > database\database.sqlite
echo ✅ Arquivo SQLite criado

echo [4/5] Criando tabelas...
php -r "
\$pdo = new PDO('sqlite:database/database.sqlite');
\$pdo->exec('
CREATE TABLE IF NOT EXISTS macs_autorizados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mac_address TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    ultimo_acesso DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS access_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mac_address TEXT NOT NULL,
    nome TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TEXT DEFAULT \"autorizado\",
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS system_status (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    barrier_status TEXT DEFAULT \"fechada\",
    last_detection DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
');
echo 'Tabelas criadas com sucesso!';
"
echo ✅ Tabelas criadas

echo [5/5] Inserindo dados de teste...
php -r "
\$pdo = new PDO('sqlite:database/database.sqlite');
\$pdo->exec('
INSERT OR IGNORE INTO macs_autorizados (mac_address, nome, ultimo_acesso) VALUES 
(\"AA-BB-CC-DD-EE-FF\", \"Veículo Teste 1\", datetime(\"now\")),
(\"11-22-33-44-55-66\", \"Veículo Teste 2\", datetime(\"now\", \"-1 hour\")),
(\"77-88-99-AA-BB-CC\", \"Veículo Teste 3\", datetime(\"now\", \"-2 day\"));

INSERT OR IGNORE INTO system_status (barrier_status, last_detection) VALUES 
(\"fechada\", datetime(\"now\"));
');
echo 'Dados de teste inseridos!';
"
echo ✅ Dados de teste inseridos

cd ..

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    ✅ SQLITE CONFIGURADO COM SUCESSO                        ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo 🧪 Testando banco...
cd backend
php -r "
\$pdo = new PDO('sqlite:database/database.sqlite');
\$result = \$pdo->query('SELECT COUNT(*) as total FROM macs_autorizados');
\$row = \$result->fetch();
echo 'Total de MACs: ' . \$row['total'] . PHP_EOL;
"
cd ..

echo.
echo 🚀 Agora execute: iniciar_com_laragon_fixo.bat
echo.
pause