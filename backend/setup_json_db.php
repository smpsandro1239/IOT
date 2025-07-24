<?php

echo "🗄️ Configurando sistema com JSON (sem banco de dados)...\n";

try {
    // Criar diretório storage se não existir
    if (!is_dir('storage/app')) {
        mkdir('storage/app', 0755, true);
        echo "✅ Diretório storage criado\n";
    }

    // Dados iniciais
    $macsAutorizados = [
        [
            'id' => 1,
            'mac_address' => 'AA-BB-CC-DD-EE-FF',
            'nome' => 'Veículo Teste 1',
            'ultimo_acesso' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'mac_address' => '11-22-33-44-55-66',
            'nome' => 'Veículo Teste 2',
            'ultimo_acesso' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'mac_address' => '77-88-99-AA-BB-CC',
            'nome' => 'Veículo Teste 3',
            'ultimo_acesso' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    $accessLogs = [
        [
            'id' => 1,
            'mac_address' => 'AA-BB-CC-DD-EE-FF',
            'nome' => 'Veículo Teste 1',
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'autorizado',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    $systemStatus = [
        'id' => 1,
        'barrier_status' => 'fechada',
        'last_detection' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Salvar arquivos JSON
    file_put_contents('storage/app/macs_autorizados.json', json_encode($macsAutorizados, JSON_PRETTY_PRINT));
    file_put_contents('storage/app/access_logs.json', json_encode($accessLogs, JSON_PRETTY_PRINT));
    file_put_contents('storage/app/system_status.json', json_encode($systemStatus, JSON_PRETTY_PRINT));

    echo "✅ Arquivos JSON criados\n";
    echo "✅ Dados de teste inseridos\n";
    echo "✅ Total de MACs: " . count($macsAutorizados) . "\n";

    echo "\n🎉 Sistema JSON configurado com sucesso!\n";
    echo "📁 Arquivos criados em storage/app/\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}