<?php

echo "🗄️ Configurando SQLite...\n";

try {
    // Criar diretório database se não existir
    if (!is_dir('database')) {
        mkdir('database', 0755, true);
        echo "✅ Diretório database criado\n";
    }

    // Criar conexão SQLite
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão SQLite estabelecida\n";

    // Criar tabelas
    $pdo->exec('
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
            status TEXT DEFAULT "autorizado",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS system_status (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            barrier_status TEXT DEFAULT "fechada",
            last_detection DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ');
    
    echo "✅ Tabelas criadas\n";

    // Inserir dados de teste
    $pdo->exec('
        INSERT OR IGNORE INTO macs_autorizados (mac_address, nome, ultimo_acesso) VALUES 
        ("AA-BB-CC-DD-EE-FF", "Veículo Teste 1", datetime("now")),
        ("11-22-33-44-55-66", "Veículo Teste 2", datetime("now", "-1 hour")),
        ("77-88-99-AA-BB-CC", "Veículo Teste 3", datetime("now", "-2 day"));

        INSERT OR IGNORE INTO system_status (barrier_status, last_detection) VALUES 
        ("fechada", datetime("now"));
    ');
    
    echo "✅ Dados de teste inseridos\n";

    // Testar
    $result = $pdo->query('SELECT COUNT(*) as total FROM macs_autorizados');
    $row = $result->fetch();
    echo "✅ Total de MACs: " . $row['total'] . "\n";

    echo "\n🎉 SQLite configurado com sucesso!\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}