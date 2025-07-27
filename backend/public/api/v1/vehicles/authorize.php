<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getJsonData($filename) {
    $path = __DIR__ . "/../../../../storage/app/{$filename}.json";
    if (!file_exists($path)) {
        return [];
    }
    return json_decode(file_get_contents($path), true) ?: [];
}

function saveJsonData($filename, $data) {
    $path = __DIR__ . "/../../../../storage/app/{$filename}.json";
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

// Verificar parâmetros obrigatórios
$mac = $_GET['mac'] ?? '';
$lora_id = $_GET['lora_id'] ?? $mac; // Usar MAC como LoRa ID se não fornecido

if (empty($mac)) {
    http_response_code(400);
    echo json_encode([
        'authorized' => false,
        'error' => 'MAC address é obrigatório',
        'timestamp' => date('c')
    ]);
    exit;
}

// Normalizar MAC address (remover separadores)
$mac_normalized = strtoupper(str_replace([':', '-', '.'], '', $mac));

// Buscar MACs autorizados
$macs_autorizados = getJsonData('macs_autorizados');

// Verificar se o MAC está autorizado
$autorizado = false;
$veiculo_nome = '';

foreach ($macs_autorizados as $veiculo) {
    $mac_autorizado = strtoupper(str_replace([':', '-', '.'], '', $veiculo['mac_address']));
    
    if ($mac_autorizado === $mac_normalized) {
        $autorizado = true;
        $veiculo_nome = $veiculo['nome'];
        
        // Atualizar último acesso
        $veiculo['ultimo_acesso'] = date('c');
        break;
    }
}

// Salvar dados atualizados se autorizado
if ($autorizado) {
    // Atualizar último acesso no arquivo
    for ($i = 0; $i < count($macs_autorizados); $i++) {
        $mac_autorizado = strtoupper(str_replace([':', '-', '.'], '', $macs_autorizados[$i]['mac_address']));
        if ($mac_autorizado === $mac_normalized) {
            $macs_autorizados[$i]['ultimo_acesso'] = date('c');
            break;
        }
    }
    saveJsonData('macs_autorizados', $macs_autorizados);
    
    // Registrar log de acesso
    $access_logs = getJsonData('access_logs');
    $access_logs[] = [
        'id' => count($access_logs) + 1,
        'mac_address' => $mac,
        'nome' => $veiculo_nome,
        'timestamp' => date('c'),
        'status' => 'AUTORIZADO',
        'created_at' => date('c')
    ];
    saveJsonData('access_logs', $access_logs);
    
    // Atualizar status do sistema
    $system_status = getJsonData('system_status');
    $system_status['last_detection'] = date('c');
    $system_status['barrier_status'] = 'aberta';
    saveJsonData('system_status', $system_status);
}

// Resposta para ESP32
$response = [
    'authorized' => $autorizado,
    'mac' => $mac,
    'lora_id' => $lora_id,
    'timestamp' => date('c')
];

if ($autorizado) {
    $response['name'] = $veiculo_nome;
    $response['action'] = 'OPEN_BARRIER';
} else {
    $response['reason'] = 'MAC não autorizado';
    $response['action'] = 'DENY_ACCESS';
    
    // Registrar tentativa negada
    $access_logs = getJsonData('access_logs');
    $access_logs[] = [
        'id' => count($access_logs) + 1,
        'mac_address' => $mac,
        'nome' => 'Desconhecido',
        'timestamp' => date('c'),
        'status' => 'NEGADO',
        'created_at' => date('c')
    ];
    saveJsonData('access_logs', $access_logs);
}

echo json_encode($response);
?>