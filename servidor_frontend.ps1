# Servidor HTTP simples para servir arquivos estáticos
Write-Host "🚀 Iniciando servidor HTTP simples..." -ForegroundColor Green
Write-Host "📁 Servindo arquivos do diretório: frontend" -ForegroundColor Cyan
Write-Host "🔗 URL: http://localhost:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  LIMITAÇÕES:" -ForegroundColor Yellow
Write-Host "   • Apenas arquivos estáticos (HTML, CSS, JS)" -ForegroundColor Gray
Write-Host "   • Sem backend PHP (API não funcionará)" -ForegroundColor Gray
Write-Host "   • Funcionalidades limitadas" -ForegroundColor Gray
Write-Host ""
Write-Host "💡 Para funcionalidade completa:" -ForegroundColor Blue
Write-Host "   • Instale PHP 8.1+ ou Node.js" -ForegroundColor Gray
Write-Host ""
Write-Host "🚀 Abrindo navegador..." -ForegroundColor Green
Start-Process "http://localhost:8080"
Write-Host ""
Write-Host "⏹️  Pressione Ctrl+C para parar o servidor" -ForegroundColor Red
Write-Host ""

# Criar servidor HTTP simples
$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add('http://localhost:8080/')
$listener.Start()

Write-Host "✅ Servidor iniciado com sucesso!" -ForegroundColor Green
Write-Host ""

try {
    while ($listener.IsListening) {
        $context = $listener.GetContext()
        $request = $context.Request
        $response = $context.Response
        
        $path = $request.Url.LocalPath
        if ($path -eq '/') { $path = '/index.html' }
        
        $filePath = Join-Path 'frontend' $path.TrimStart('/')
        
        Write-Host "📄 Solicitação: $path" -ForegroundColor Gray
        
        if (Test-Path $filePath) {
            $content = Get-Content $filePath -Raw -Encoding UTF8
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($content)
            
            # Definir Content-Type baseado na extensão
            $extension = [System.IO.Path]::GetExtension($filePath).ToLower()
            switch ($extension) {
                '.html' { $response.ContentType = 'text/html; charset=utf-8' }
                '.css'  { $response.ContentType = 'text/css; charset=utf-8' }
                '.js'   { $response.ContentType = 'application/javascript; charset=utf-8' }
                '.json' { $response.ContentType = 'application/json; charset=utf-8' }
                default { $response.ContentType = 'text/plain; charset=utf-8' }
            }
            
            $response.StatusCode = 200
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            
            Write-Host "✅ Arquivo servido: $filePath" -ForegroundColor Green
        } else {
            $response.StatusCode = 404
            $errorContent = @"
<!DOCTYPE html>
<html>
<head>
    <title>404 - Arquivo não encontrado</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { color: #666; }
        .back { margin-top: 20px; }
        a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404 - Arquivo não encontrado</h1>
    <p>O arquivo solicitado não foi encontrado: <strong>$path</strong></p>
    <div class="back">
        <a href="/">← Voltar ao início</a>
    </div>
</body>
</html>
"@
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($errorContent)
            $response.ContentType = 'text/html; charset=utf-8'
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            
            Write-Host "❌ Arquivo não encontrado: $filePath" -ForegroundColor Red
        }
        
        $response.OutputStream.Close()
    }
} catch {
    Write-Host "❌ Erro no servidor: $($_.Exception.Message)" -ForegroundColor Red
} finally {
    $listener.Stop()
    Write-Host "🛑 Servidor parado." -ForegroundColor Yellow
}