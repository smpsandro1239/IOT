<?php
// Configurações
$apiBaseUrl = 'http://localhost:8000';

// Obter o caminho da solicitação
$requestUri = $_SERVER['REQUEST_URI'];

// Se a solicitação for para o proxy.php, ignorar
if (strpos($requestUri, '/proxy.php') !== false) {
  header('HTTP/1.1 400 Bad Request');
  echo 'Erro: Solicitação inválida';
  exit;
}

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
  $headers = [];
  foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
      $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
      if ($header !== 'Host') {
        $headers[] = "$header: $value";
      }
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
  $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  curl_close($ch);

  // Definir o código de status HTTP
  http_response_code($httpCode);

  // Definir o tipo de conteúdo
  if ($contentType) {
    header("Content-Type: $contentType");
  } else {
    header("Content-Type: application/json");
  }

  // Adicionar cabeçalhos CORS
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
  header('Access-Control-Allow-Credentials: true');

  // Enviar a resposta
  echo $response;
  exit;
}

// Se não for uma solicitação para a API, continuar com o fluxo normal
