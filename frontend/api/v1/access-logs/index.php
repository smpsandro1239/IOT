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

// Processar a solicitação com base no método HTTP
switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    // Simular lista de logs de acesso
    $logs = [];
    for ($i = 1; $i <= 10; $i++) {
      $logs[] = [
        'id' => $i,
        'mac' => sprintf('%012X', rand(0, 0xFFFFFFFFFFFF)),
        'placa' => 'ABC' . rand(1000, 9999),
        'direcao' => rand(0, 1) ? 'NS' : 'SN',
        'status' => rand(0, 10) > 2 ? 'AUTORIZADO' : 'NEGADO',
        'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400 * 7))
      ];
    }

    echo json_encode([
      'data' => $logs,
      'meta' => [
        'current_page' => 1,
        'from' => 1,
        'last_page' => 1,
        'path' => '/api/v1/access-logs',
        'per_page' => 10,
        'to' => 10,
        'total' => 10
      ]
    ]);
    break;

  case 'POST':
    // Obter dados do corpo da solicitação
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    // Verificar se os dados são válidos
    if (!$data) {
      http_response_code(400);
      echo json_encode(['error' => 'Dados inválidos']);
      exit;
    }

    // Simular adição de log de acesso
    echo json_encode([
      'message' => 'Log de acesso registrado com sucesso',
      'data' => array_merge($data, [
        'id' => rand(100, 999),
        'created_at' => date('Y-m-d H:i:s')
      ])
    ]);
    break;

  default:
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    break;
}
