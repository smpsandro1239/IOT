<?php
// Configurações
$apiBaseUrl = 'http://localhost:8000';

// Adicionar cabeçalhos CORS para todas as respostas
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 horas

// Responder imediatamente às solicitações OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

// Obter o caminho da solicitação
$requestUri = $_SERVER['REQUEST_URI'];

// Se a solicitação for para a API
if (strpos($requestUri, '/api/') === 0) {
  // Construir a URL de destino
  $targetUrl = $apiBaseUrl . $requestUri;

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

    // Retornar erro como JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Erro na comunicação com o servidor: ' . $error]);
    exit;
  }

  curl_close($ch);

  // Definir o código de status HTTP
  http_response_code($httpCode);

  // Definir o tipo de conteúdo
  header('Content-Type: application/json');

  // Verificar se a resposta é um JSON válido
  json_decode($response);
  if (json_last_error() !== JSON_ERROR_NONE) {
    // Se não for um JSON válido, retornar um erro
    http_response_code(500);
    echo json_encode(['error' => 'Resposta inválida do servidor']);
    exit;
  }

  // Enviar a resposta
  echo $response;
  exit;
}

// Se não for uma solicitação para a API, continuar com o fluxo normal
