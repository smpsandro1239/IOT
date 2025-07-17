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

// Verificar se é uma solicitação GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

// Gerar dados simulados para gráficos
function generateRandomData($count)
{
  $data = [];
  for ($i = 0; $i < $count; $i++) {
    $data[] = rand(1, 20);
  }
  return $data;
}

// Gerar labels para dias
function generateDailyLabels()
{
  $labels = [];
  $today = time();
  for ($i = 0; $i < 24; $i++) {
    $labels[] = date('H:00', $today - (23 - $i) * 3600);
  }
  return $labels;
}

// Gerar labels para semanas
function generateWeeklyLabels()
{
  $labels = [];
  $today = time();
  for ($i = 0; $i < 7; $i++) {
    $labels[] = date('D', $today - (6 - $i) * 86400);
  }
  return $labels;
}

// Gerar labels para meses
function generateMonthlyLabels()
{
  $labels = [];
  $today = time();
  for ($i = 0; $i < 12; $i++) {
    $labels[] = date('M', strtotime("-" . (11 - $i) . " months", $today));
  }
  return $labels;
}

// Verificar se é uma solicitação para métricas específicas de MAC
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $path);
$mac = isset($pathParts[4]) ? $pathParts[4] : null;

if ($mac) {
  // Métricas específicas de MAC
  echo json_encode([
    'daily' => [
      'labels' => generateDailyLabels(),
      'data' => generateRandomData(24)
    ],
    'weekly' => [
      'labels' => generateWeeklyLabels(),
      'data' => generateRandomData(7)
    ],
    'monthly' => [
      'labels' => generateMonthlyLabels(),
      'data' => generateRandomData(12)
    ]
  ]);
} else {
  // Métricas gerais
  echo json_encode([
    'daily' => [
      'labels' => generateDailyLabels(),
      'data' => generateRandomData(24)
    ],
    'weekly' => [
      'labels' => generateWeeklyLabels(),
      'data' => generateRandomData(7)
    ],
    'monthly' => [
      'labels' => generateMonthlyLabels(),
      'data' => generateRandomData(12)
    ]
  ]);
}
