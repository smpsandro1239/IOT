<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $macs = getJsonData('macs_autorizados');
        echo json_encode([
            'status' => 'success',
            'data' => $macs
        ]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['mac_address']) || !isset($input['nome'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'MAC address e nome são obrigatórios'
            ]);
            exit;
        }
        
        $macs = getJsonData('macs_autorizados');
        
        // Verificar se já existe
        foreach ($macs as $mac) {
            if ($mac['mac_address'] === $input['mac_address']) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'MAC address já existe'
                ]);
                exit;
            }
        }
        
        // Adicionar novo MAC
        $newMac = [
            'id' => count($macs) + 1,
            'mac_address' => $input['mac_address'],
            'nome' => $input['nome'],
            'ultimo_acesso' => null,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ];
        
        $macs[] = $newMac;
        saveJsonData('macs_autorizados', $macs);
        
        echo json_encode([
            'status' => 'success',
            'data' => $newMac
        ]);
        break;
        
    case 'DELETE':
        // Para DELETE, o MAC vem na URL como parâmetro
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        $macAddress = trim($pathInfo, '/');
        
        if (empty($macAddress)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'MAC address é obrigatório'
            ]);
            exit;
        }
        
        $macs = getJsonData('macs_autorizados');
        
        $macs = array_filter($macs, function($mac) use ($macAddress) {
            return $mac['mac_address'] !== $macAddress;
        });
        
        saveJsonData('macs_autorizados', array_values($macs));
        
        echo json_encode([
            'status' => 'success',
            'message' => 'MAC removido com sucesso'
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Método não permitido'
        ]);
        break;
}
?>