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

// Simular dados de MACs autorizados
$macs = [
  [
    'id' => 1,
    'mac' => 'AABBCCDDEEFF',
    'placa' => 'ABC1234',
    'data_adicao' => '2025-07-15 10:00:00'
  ],
  [
    'id' => 2,
    'mac' => '112233445566',
    'placa' => 'DEF5678',
    'data_adicao' => '2025-07-16 11:30:00'
  ],
  [
    'id' => 3,
    'mac' => '778899AABBCC',
    'placa' => 'GHI9012',
    'data_adicao' => '2025-07-17 09:15:00'
  ]
];

// Processar a solicitação com base no método HTTP
switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    // Obter parâmetros de consulta
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Filtrar MACs com base na pesquisa
    if (!empty($search)) {
      $filteredMacs = array_filter($macs, function ($mac) use ($search) {
        return stripos($mac['mac'], $search) !== false || stripos($mac['placa'], $search) !== false;
      });
    } else {
      $filteredMacs = $macs;
    }

    // Simular paginação
    $perPage = 10;
    $total = count($filteredMacs);
    $lastPage = ceil($total / $perPage);
    $from = ($page - 1) * $perPage + 1;
    $to = min($from + $perPage - 1, $total);

    // Retornar resposta paginada
    echo json_encode([
      'data' => $filteredMacs,
      'meta' => [
        'current_page' => $page,
        'from' => $from,
        'last_page' => $lastPage,
        'path' => '/api/v1/macs-autorizados',
        'per_page' => $perPage,
        'to' => $to,
        'total' => $total
      ]
    ]);
    break;

  case 'POST':
    // Obter dados do corpo da solicitação
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    // Verificar se os dados são válidos
    if (!$data || !isset($data['mac']) || !isset($data['placa'])) {
      http_response_code(400);
      echo json_encode(['error' => 'Dados inválidos']);
      exit;
    }

    // Simular adição de MAC
    $newMac = [
      'id' => count($macs) + 1,
      'mac' => $data['mac'],
      'placa' => $data['placa'],
      'data_adicao' => date('Y-m-d H:i:s')
    ];

    // Retornar resposta de sucesso
    echo json_encode([
      'message' => 'MAC adicionado com sucesso',
      'data' => $newMac
    ]);
    break;

  case 'DELETE':
    // Obter MAC da URL
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', $path);
    $mac = end($pathParts);

    // Simular exclusão de MAC
    echo json_encode([
      'message' => "MAC {$mac} excluído com sucesso"
    ]);
    break;

  default:
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    break;
}
