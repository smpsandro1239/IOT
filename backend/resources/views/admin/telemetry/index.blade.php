<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Telemetria de Barreiras</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background-color: #007bff; padding: 15px; color: white; text-align: center; }
        .nav { background-color: #343a40; padding: 10px 0; text-align: center; }
        .nav a { color: white; padding: 10px 15px; text-decoration: none; margin: 0 5px; }
        .nav a:hover { background-color: #495057; }
        .container { padding: 20px; max-width: 1200px; margin: auto; }
        .card { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.85em; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        pre { background-color: #eee; padding: 10px; border-radius: 4px; white-space: pre-wrap; word-break: break-all; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: .25rem; }
        .alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
        .refresh-link { margin-bottom: 20px; display: inline-block; padding: 10px 15px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Painel Administrativo</h1>
    </div>
    <nav class="nav">
        {{-- Adapte este menu conforme o layout principal da sua aplicação --}}
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.companies.index') }}">Empresas</a>
        <a href="{{ route('admin.sites.index') }}">Sites</a>
        <a href="{{ route('admin.barriers.index') }}">Barreiras</a>
        <a href="{{ route('admin.vehicles.index') }}">Veículos</a>
        <a href="{{ route('admin.telemetry.logs') }}">Logs de Telemetria</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Log de Telemetria de Barreiras</h2>
            <p>Exibindo as últimas entradas do log (mais recentes primeiro).</p>

            <a href="{{ route('admin.telemetry.logs') }}" class="refresh-link">Atualizar</a>

            @if (empty($logEntries))
                <div class="alert alert-info">
                    Nenhuma entrada de log de telemetria encontrada ou o ficheiro de log está vazio.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Timestamp (Servidor)</th>
                            <th style="width: 10%;">ID Barreira</th>
                            <th style="width: 15%;">Nome Barreira</th>
                            <th style="width: 15%;">MAC Barreira</th>
                            <th style="width: 45%;">Dados de Telemetria</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logEntries as $entry)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($entry['server_timestamp'])->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $entry['barrier_id'] ?? 'N/A' }}</td>
                                <td>{{ $entry['barrier_name'] ?? 'N/A' }}</td>
                                <td>{{ $entry['barrier_mac'] ?? 'N/A' }}</td>
                                <td>
                                    <pre>{{ json_encode($entry['telemetry'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
