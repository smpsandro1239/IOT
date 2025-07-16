<?php

// Configurações do proxy
$apiBaseUrl = 'http://localhost:8000/api/login';

// Obter o corpo da solicitação
$body = file_get_contents('php://input');

// Inicializar cURL
$ch = curl_init($apiBaseUrl);

// Configurar opções do cURL
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Accept: application/json'
]);

// Executar a solicitação
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Definir o código de status HTTP
http_response_code($httpCode);

// Definir o tipo de conteúdo
header('Content-Type: application/json');

// Enviar a resposta
echo $response;
