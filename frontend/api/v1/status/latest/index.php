<?php
// Adicionar cabeçalhos CORS para todas as respostas
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Responder imediatamente às solicitações OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

// Verificar se é uma solicitação GET ou HEAD
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

// Simular status do sistema
echo json_encode([
  'system_status' => 'operational',
  'barrier_status' => [
    'north' => [
      'state' => 'closed',
      'last_updated' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
    ],
    'south' => [
      'state' => 'closed',
      'last_updated' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
    ]
  ],
  'latest_log' => [
    'mac' => 'AABBCCDDEEFF',
    'placa' => 'ABC1234',
    'direcao' => 'NS',
    'timestamp' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
  ]
]);
