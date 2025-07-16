<?php
// Arquivo de roteamento para o servidor PHP embutido

// Se a solicitação for para a API, usar o proxy
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
  include 'proxy.php';
  exit;
}

// Se o arquivo solicitado existir, servi-lo diretamente
$filePath = __DIR__ . $_SERVER['REQUEST_URI'];
if (is_file($filePath)) {
  // Determinar o tipo MIME com base na extensão do arquivo
  $extension = pathinfo($filePath, PATHINFO_EXTENSION);
  switch ($extension) {
    case 'html':
      header('Content-Type: text/html');
      break;
    case 'css':
      header('Content-Type: text/css');
      break;
    case 'js':
      header('Content-Type: application/javascript');
      break;
    case 'json':
      header('Content-Type: application/json');
      break;
    case 'png':
      header('Content-Type: image/png');
      break;
    case 'jpg':
    case 'jpeg':
      header('Content-Type: image/jpeg');
      break;
    case 'gif':
      header('Content-Type: image/gif');
      break;
    case 'svg':
      header('Content-Type: image/svg+xml');
      break;
  }
  readfile($filePath);
  exit;
}

// Se for uma solicitação para um diretório, procurar por index.html
if (is_dir($filePath)) {
  $indexFile = rtrim($filePath, '/') . '/index.html';
  if (is_file($indexFile)) {
    header('Content-Type: text/html');
    readfile($indexFile);
    exit;
  }
}

// Se for uma solicitação para a raiz, servir index.html
if ($_SERVER['REQUEST_URI'] === '/') {
  header('Content-Type: text/html');
  readfile(__DIR__ . '/index.html');
  exit;
}

// Se for uma solicitação para login, servir login.html
if ($_SERVER['REQUEST_URI'] === '/login') {
  header('Content-Type: text/html');
  readfile(__DIR__ . '/login.html');
  exit;
}

// Se chegou aqui, o arquivo não foi encontrado
header('HTTP/1.1 404 Not Found');
echo '404 - Arquivo não encontrado';
