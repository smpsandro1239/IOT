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

// Retornar informações sobre a API
echo json_encode([
  'name' => 'Sistema de Controle de Barreiras IoT - API',
  'version' => '1.0.0',
  'description' => 'API para o Sistema de Controle de Barreiras IoT',
  'endpoints' => [
    '/api/login' => 'Autenticação de usuários',
    '/api/v1/status/latest' => 'Status mais recente do sistema',
    '/api/v1/macs-autorizados' => 'Gerenciamento de MACs autorizados',
    '/api/v1/metrics' => 'Métricas de acesso',
    '/api/v1/gate/control' => 'Controle de barreiras',
    '/api/v1/access-logs' => 'Logs de acesso'
  ],
  'documentation' => 'Para mais informações, consulte a documentação',
  'status' => 'online',
  'timestamp' => date('Y-m-d H:i:s')
]);
