@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 SERVIDOR SIMPLES - SEM DEPENDÊNCIAS                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Iniciando servidor HTTP simples...
echo.

echo ⚠️  AVISO: Este servidor serve apenas arquivos estáticos
echo    Para funcionalidade completa, instale PHP 8.1+ ou Node.js
echo.

echo [1/2] Verificando se o PowerShell pode servir arquivos...

powershell -Command "& {
    Write-Host '🌐 Iniciando servidor HTTP na porta 8080...'
    Write-Host '📁 Servindo arquivos do diretório: frontend'
    Write-Host '🔗 URL: http://localhost:8080'
    Write-Host ''
    Write-Host '⚠️  LIMITAÇÕES:'
    Write-Host '   • Apenas arquivos estáticos (HTML, CSS, JS)'
    Write-Host '   • Sem backend PHP (API não funcionará)'
    Write-Host '   • Funcionalidades limitadas'
    Write-Host ''
    Write-Host '💡 Para funcionalidade completa:'
    Write-Host '   • Instale PHP 8.1+ ou Node.js'
    Write-Host '   • Execute: iniciar_sistema_otimizado.bat'
    Write-Host ''
    Write-Host '🚀 Abrindo navegador...'
    Start-Process 'http://localhost:8080'
    Write-Host ''
    Write-Host '📂 Servindo arquivos de: frontend/'
    Write-Host '⏹️  Pressione Ctrl+C para parar o servidor'
    Write-Host ''
    
    # Criar servidor HTTP simples
    $listener = New-Object System.Net.HttpListener
    $listener.Prefixes.Add('http://localhost:8080/')
    $listener.Start()
    
    Write-Host '✅ Servidor iniciado com sucesso!'
    Write-Host ''
    
    while ($listener.IsListening) {
        $context = $listener.GetContext()
        $request = $context.Request
        $response = $context.Response
        
        $path = $request.Url.LocalPath
        if ($path -eq '/') { $path = '/index.html' }
        
        $filePath = Join-Path 'frontend' $path.TrimStart('/')
        
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
            
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
        } else {
            $response.StatusCode = 404
            $errorContent = '<h1>404 - Arquivo não encontrado</h1><p>Arquivo: ' + $path + '</p>'
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($errorContent)
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
        }
        
        $response.OutputStream.Close()
    }
}"

echo.
echo [2/2] Servidor parado.
echo.
pause