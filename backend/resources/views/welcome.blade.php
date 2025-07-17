<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API do Sistema de Controle de Barreiras IoT</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2563eb;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        h2 {
            color: #1d4ed8;
            margin-top: 30px;
        }
        .card {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        code {
            background-color: #e5e7eb;
            padding: 2px 5px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
        }
        .endpoint {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .method {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .get { background-color: #10b981; }
        .post { background-color: #3b82f6; }
        .put { background-color: #f59e0b; }
        .delete { background-color: #ef4444; }
    </style>
</head>
<body>
    <h1>API do Sistema de Controle de Barreiras IoT</h1>

    <div class="card">
        <p>Bem-vindo à API do Sistema de Controle de Barreiras IoT. Esta API fornece endpoints para gerenciar o controle de barreiras, autenticação de usuários, e monitoramento de telemetria.</p>
        <p>Para acessar a interface do usuário, visite: <a href="http://localhost:8080">http://localhost:8080</a></p>
    </div>

    <h2>Endpoints Disponíveis</h2>

    <div class="endpoint">
        <span class="method post">POST</span> <code>/api/login</code>
        <p>Autentica um usuário e retorna um token de acesso.</p>
    </div>

    <div class="endpoint">
        <span class="method get">GET</span> <code>/api/v1/status/latest</code>
        <p>Retorna o status mais recente do sistema.</p>
    </div>

    <div class="endpoint">
        <span class="method get">GET</span> <code>/api/v1/macs-autorizados</code>
        <p>Lista todos os MACs autorizados.</p>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span> <code>/api/v1/macs-autorizados</code>
        <p>Adiciona um novo MAC autorizado.</p>
    </div>

    <div class="endpoint">
        <span class="method delete">DELETE</span> <code>/api/v1/macs-autorizados/{mac}</code>
        <p>Remove um MAC autorizado.</p>
    </div>

    <div class="endpoint">
        <span class="method get">GET</span> <code>/api/v1/metrics</code>
        <p>Retorna métricas de acesso do sistema.</p>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span> <code>/api/v1/gate/control</code>
        <p>Controla o estado de uma barreira.</p>
    </div>

    <h2>WebSockets</h2>
    <div class="card">
        <p>Esta API também suporta WebSockets para atualizações em tempo real.</p>
        <p>Canal: <code>telemetria</code> - Evento: <code>TelemetriaUpdated</code></p>
        <p>Canal: <code>gate-control</code> - Evento: <code>GateControl</code></p>
    </div>

    <h2>Documentação</h2>
    <div class="card">
        <p>Para mais informações sobre como usar esta API, consulte a documentação completa ou entre em contato com a equipe de desenvolvimento.</p>
    </div>

    <footer style="margin-top: 50px; text-align: center; color: #6b7280; font-size: 0.9em;">
        <p>Sistema de Controle de Barreiras IoT &copy; 2025</p>
    </footer>
</body>
</html>
