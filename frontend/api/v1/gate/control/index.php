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

// Verificar se é uma solicitação POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

// Obter o corpo da solicitação
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Verificar se os dados são válidos
if (!$data || !isset($data['gate']) || !isset($data['action'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados inválidos']);
  exit;
}

// Simular controle de barreira
$gate = $data['gate'];
$action = $data['action'];

// Retornar resposta de sucesso
echo json_encode([
  'message' => 'Barreira controlada com sucesso',
  'data' => [
    'gate' => $gate,
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s')
  ]
]);
