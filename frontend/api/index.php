<?php
// Configurações
$apiBaseUrl = 'http://localhost:8000';

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

// Obter o caminho da solicitação
$requestUri = $_SERVER['REQUEST_URI'];
$apiPath = str_replace('/api', '', $requestUri);

// Se o caminho for vazio, retornar informações sobre a API
if ($apiPath === '' || $apiPath === '/') {
  echo json_encode([
    'name' => 'API do Sistema de Controle de Barreiras IoT',
    'version' => '1.0.0',
    'status' => 'online'
  ]);
  exit;
}

// Se for uma solicitação para login, redirecionar para o endpoint específico
if ($apiPath === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  include __DIR__ . '/login/index.php';
  exit;
}

// Para outras solicitações, encaminhar para o backend
$targetUrl = $apiBaseUrl . '/api' . $apiPath;

// Obter o método da solicitação
$method = $_SERVER['REQUEST_METHOD'];

// Obter o corpo da solicitação
$body = file_get_contents('php://input');

// Inicializar cURL
$ch = curl_init($targetUrl);

// Configurar opções do cURL
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Adicionar cabeçalhos
$headers = [
  'Content-Type: application/json',
  'Accept: application/json'
];

// Adicionar token de autorização se disponível
$authHeader = null;
foreach ($_SERVER as $key => $value) {
  if ($key === 'HTTP_AUTHORIZATION') {
    $authHeader = $value;
    $headers[] = "Authorization: $value";
  }
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Adicionar corpo da solicitação para métodos POST, PUT, etc.
if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
  curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

// Executar a solicitação
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Verificar se houve erro no cURL
if (curl_errno($ch)) {
  $error = curl_error($ch);
  curl_close($ch);

  http_response_code(500);
  echo json_encode(['error' => 'Erro na comunicação com o servidor: ' . $error]);
  exit;
}

curl_close($ch);

// Definir o código de status HTTP
http_response_code($httpCode);

// Verificar se a resposta é um JSON válido
if ($response) {
  $jsonTest = json_decode($response);
  if (json_last_error() !== JSON_ERROR_NONE) {
    // Se não for um JSON válido, retornar um erro
    http_response_code(500);
    echo json_encode(['error' => 'Resposta inválida do servidor']);
    exit;
  }
}

// Enviar a resposta
echo $response;
