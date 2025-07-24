<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Ler dados dos arquivos JSON
function getJsonData($filename) {
    $path = __DIR__ . "/../../../../storage/app/{$filename}.json";
    if (!file_exists($path)) {
        return [];
    }
    return json_decode(file_get_contents($path), true) ?: [];
}

$systemStatus = getJsonData('system_status');
$macsAutorizados = getJsonData('macs_autorizados');
$accessLogs = getJsonData('access_logs');

echo json_encode([
    'status' => 'success',
    'data' => [
        'barrier_status' => $systemStatus['barrier_status'] ?? 'fechada',
        'last_detection' => $systemStatus['last_detection'] ?? null,
        'total_authorized' => count($macsAutorizados),
        'recent_access' => array_slice(array_reverse($accessLogs), 0, 5),
        'timestamp' => date('c')
    ]
]);
?>