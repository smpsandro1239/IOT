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
    $path = __DIR__ . "/../../../storage/app/{$filename}.json";
    if (!file_exists($path)) {
        return [];
    }
    return json_decode(file_get_contents($path), true) ?: [];
}

function saveJsonData($filename, $data) {
    $path = __DIR__ . "/../../../storage/app/{$filename}.json";
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados de telemetria
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados JSON inválidos']);
        exit;
    }
    
    // Validar campos obrigatórios
    $required_fields = ['mac_address', 'sensor_id', 'rssi'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo obrigatório: {$field}"]);
            exit;
        }
    }
    
    // Criar registro de telemetria
    $telemetry_data = [
        'id' => time() . rand(1000, 9999),
        'mac_address' => $input['mac_address'],
        'sensor_id' => $input['sensor_id'],
        'rssi' => $input['rssi'],
        'snr' => $input['snr'] ?? null,
        'direction' => $input['direction'] ?? null,
        'distance' => $input['distance'] ?? null,
        'timestamp' => $input['timestamp'] ?? date('c'),
        'created_at' => date('c')
    ];
    
    // Salvar telemetria
    $telemetry_logs = getJsonData('telemetry_logs');
    $telemetry_logs[] = $telemetry_data;
    
    // Manter apenas os últimos 1000 registros
    if (count($telemetry_logs) > 1000) {
        $telemetry_logs = array_slice($telemetry_logs, -1000);
    }
    
    saveJsonData('telemetry_logs', $telemetry_logs);
    
    // Atualizar status do sistema
    $system_status = getJsonData('system_status');
    $system_status['last_detection'] = date('c');
    $system_status['last_sensor'] = $input['sensor_id'];
    $system_status['last_rssi'] = $input['rssi'];
    saveJsonData('system_status', $system_status);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Telemetria registrada',
        'id' => $telemetry_data['id'],
        'timestamp' => date('c')
    ]);
    
} else {
    // GET - Listar telemetria recente
    $telemetry_logs = getJsonData('telemetry_logs');
    
    // Pegar apenas os últimos 50 registros
    $recent_logs = array_slice($telemetry_logs, -50);
    
    echo json_encode([
        'status' => 'success',
        'data' => $recent_logs,
        'total' => count($telemetry_logs),
        'timestamp' => date('c')
    ]);
}
?>