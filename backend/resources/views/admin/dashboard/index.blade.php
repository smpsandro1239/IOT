<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background-color: #007bff; padding: 15px; color: white; text-align: center; }
        .nav { background-color: #343a40; padding: 10px 0; text-align: center; }
        .nav a { color: white; padding: 10px 15px; text-decoration: none; margin: 0 5px; }
        .nav a:hover { background-color: #495057; }
        .container { padding: 20px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .metric-card { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .metric-card h3 { margin-top: 0; font-size: 1.2em; color: #007bff; }
        .metric-card p { font-size: 2em; font-weight: bold; margin: 10px 0; }
        .metric-card .details { font-size: 0.9em; color: #6c757d; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Painel Administrativo - Controlo de Barreiras</h1>
    </div>

    <nav class="nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.vehicles.index') }}">Gerenciar Veículos</a>
        <a href="{{ 'admin.access-logs.index' }}">Logs de Acesso</a>
        <a href="{{ route('admin.firmwares.index') }}">Gerenciar Firmwares</a>
        {{-- Adicionar link para logout quando auth estiver implementado --}}
    </nav>

    <div class="container">
        <h2>Métricas do Sistema</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Total de Veículos Registrados</h3>
                <p>{{ $metrics['totalVehicles'] }}</p>
                <span class="details">Autorizados: {{ $metrics['authorizedVehicles'] }}</span><br>
                <span class="details">Não Autorizados: {{ $metrics['unauthorizedVehicles'] }}</span>
            </div>

            <div class="metric-card">
                <h3>Logs de Acesso Totais</h3>
                <p>{{ $metrics['totalAccessLogs'] }}</p>
                <span class="details">Acessos Bem-sucedidos: {{ $metrics['successfulAccesses'] }}</span><br>
                <span class="details">Acessos Falhados/Negados: {{ $metrics['failedAccesses'] }}</span>
            </div>

            <div class="metric-card">
                <h3>Acessos por Direção</h3>
                <p>N-S: {{ $metrics['northSouthAccesses'] }}</p>
                <span class="details">S-N: {{ $metrics['southNorthAccesses'] }}</span><br>
                <span class="details">Indefinida: {{ $metrics['undefinedDirectionAccesses'] }}</span><br>
                <span class="details">Conflito: {{ $metrics['conflictDirectionAccesses'] }}</span>
            </div>

            {{--
            @if (isset($metrics['accessLogsLast7Days']))
            <div class="metric-card" style="grid-column: span 2;"> // Exemplo de card maior
                <h3>Logs de Acesso (Últimos 7 dias)</h3>
                <pre>{{ print_r($metrics['accessLogsLast7Days']->toArray(), true) }}</pre>
                // Idealmente, isto seria um gráfico
            </div>
            @endif
            --}}
        </div>
    </div>

</body>
</html>
