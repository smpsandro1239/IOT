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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Controlar barreira
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados JSON inválidos']);
        exit;
    }
    
    // Validar campos
    $action = $input['action'] ?? '';
    $barrier_id = $input['barrier_id'] ?? 'main';
    $duration = $input['duration'] ?? 5; // segundos
    
    if (!in_array($action, ['OPEN', 'CLOSE', 'TOGGLE'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida. Use: OPEN, CLOSE, TOGGLE']);
        exit;
    }
    
    // Atualizar status da barreira
    $system_status = getJsonData('system_status');
    
    $current_status = $system_status['barrier_status'] ?? 'fechada';
    
    switch ($action) {
        case 'OPEN':
            $new_status = 'aberta';
            break;
        case 'CLOSE':
            $new_status = 'fechada';
            break;
        case 'TOGGLE':
            $new_status = ($current_status === 'aberta') ? 'fechada' : 'aberta';
            break;
    }
    
    $system_status['barrier_status'] = $new_status;
    $system_status['last_action'] = $action;
    $system_status['last_action_time'] = date('c');
    $system_status['barrier_id'] = $barrier_id;
    
    // Se abrir, programar fechamento automático
    if ($new_status === 'aberta' && $duration > 0) {
        $system_status['auto_close_time'] = date('c', time() + $duration);
    }
    
    saveJsonData('system_status', $system_status);
    
    // Registrar ação
    $barrier_logs = getJsonData('barrier_logs');
    $barrier_logs[] = [
        'id' => time() . rand(1000, 9999),
        'barrier_id' => $barrier_id,
        'action' => $action,
        'previous_status' => $current_status,
        'new_status' => $new_status,
        'duration' => $duration,
        'timestamp' => date('c'),
        'created_at' => date('c')
    ];
    
    // Manter apenas os últimos 500 registros
    if (count($barrier_logs) > 500) {
        $barrier_logs = array_slice($barrier_logs, -500);
    }
    
    saveJsonData('barrier_logs', $barrier_logs);
    
    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'barrier_id' => $barrier_id,
        'previous_status' => $current_status,
        'new_status' => $new_status,
        'duration' => $duration,
        'timestamp' => date('c')
    ]);
    
} else {
    // GET - Status atual da barreira
    $system_status = getJsonData('system_status');
    
    $barrier_status = $system_status['barrier_status'] ?? 'fechada';
    $last_action = $system_status['last_action'] ?? null;
    $last_action_time = $system_status['last_action_time'] ?? null;
    $auto_close_time = $system_status['auto_close_time'] ?? null;
    
    // Verificar se deve fechar automaticamente
    $should_auto_close = false;
    if ($auto_close_time && $barrier_status === 'aberta') {
        if (strtotime($auto_close_time) <= time()) {
            $should_auto_close = true;
            $barrier_status = 'fechada';
            $system_status['barrier_status'] = 'fechada';
            $system_status['last_action'] = 'AUTO_CLOSE';
            $system_status['last_action_time'] = date('c');
            unset($system_status['auto_close_time']);
            saveJsonData('system_status', $system_status);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'barrier_status' => $barrier_status,
        'last_action' => $last_action,
        'last_action_time' => $last_action_time,
        'auto_close_time' => $auto_close_time,
        'auto_closed' => $should_auto_close,
        'timestamp' => date('c')
    ]);
}
?>