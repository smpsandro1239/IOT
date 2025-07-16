<?php

// Configurações do proxy
$apiBaseUrl = 'http://localhost:8000/api';

// Obter o caminho da solicitação
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/api', '', $path);

// Construir a URL de destino
$targetUrl = $apiBaseUrl . $path;

// Obter o método da solicitação
$method = $_SERVER['REQUEST_METHOD'];

// Obter os cabeçalhos da solicitação
$headers = [];
foreach ($_SERVER as $key => $value) {
  if (strpos($key, 'HTTP_') === 0) {
    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
    $headers[$header] = $value;
  }
}

// Obter o corpo da solicitação
$body = file_get_contents('php://input');

// Inicializar cURL
$ch = curl_init($targetUrl);

// Configurar opções do cURL
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Adicionar cabeçalhos
$curlHeaders = [];
foreach ($headers as $key => $value) {
  if ($key !== 'Host' && $key !== 'Content-Length') {
    $curlHeaders[] = "$key: $value";
  }
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

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
header("Content-Type: $contentType");

// Enviar a resposta
echo $response;
