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
if (!$data || !isset($data['email']) || !isset($data['password'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados inválidos']);
  exit;
}

// Para desenvolvimento, vamos simular uma resposta de login bem-sucedida
// Isso evita problemas com o backend e CORS
if ($data['email'] === 'admin@example.com' && $data['password'] === 'password') {
  // Resposta simulada
  http_response_code(200);
  echo json_encode([
    'message' => 'Login successful',
    'user' => [
      'id' => 1,
      'name' => 'Administrador',
      'email' => 'admin@example.com'
    ],
    'token' => 'dev_token_' . bin2hex(random_bytes(20))
  ]);
  exit;
}

// Se as credenciais não corresponderem às de desenvolvimento, retornar erro
http_response_code(401);
echo json_encode(['message' => 'Invalid credentials']);
exit;
