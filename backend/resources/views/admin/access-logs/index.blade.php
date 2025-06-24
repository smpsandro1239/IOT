<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Acesso</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .filters form { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; }
        .filters label { margin-right: 10px; }
        .filters input[type="text"], .filters input[type="date"], .filters select, .filters button { padding: 8px; margin-right: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Logs de Acesso</h1>

    <div class="filters">
        <form method="GET" action="{{ route('admin.access-logs.index') }}">
            <label for="vehicle_lora_id">ID Veículo (LoRa):</label>
            <input type="text" name="vehicle_lora_id" id="vehicle_lora_id" value="{{ request('vehicle_lora_id') }}">

            <label for="date_from">De:</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">

            <label for="date_to">Até:</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">

            <label for="direction_detected">Direção:</label>
            <select name="direction_detected" id="direction_detected">
                <option value="all" {{ request('direction_detected') == 'all' ? 'selected' : '' }}>Todas</option>
                <option value="north_south" {{ request('direction_detected') == 'north_south' ? 'selected' : '' }}>Norte-Sul</option>
                <option value="south_north" {{ request('direction_detected') == 'south_north' ? 'selected' : '' }}>Sul-Norte</option>
                <option value="conflito" {{ request('direction_detected') == 'conflito' ? 'selected' : '' }}>Conflito</option>
                <option value="undefined" {{ request('direction_detected') == 'undefined' ? 'selected' : '' }}>Indefinida</option>
            </select>

            <label for="authorization_status">Status Autorização:</label>
            <select name="authorization_status" id="authorization_status">
                <option value="all" {{ request('authorization_status') == 'all' ? 'selected' : '' }}>Todos</option>
                <option value="1" {{ request('authorization_status') == '1' ? 'selected' : '' }}>Autorizado</option>
                <option value="0" {{ request('authorization_status') == '0' ? 'selected' : '' }}>Não Autorizado</option>
            </select>

            <button type="submit">Filtrar</button>
            <a href="{{ route('admin.access-logs.index') }}">Limpar Filtros</a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Timestamp Evento</th>
                <th>ID Veículo (LoRa)</th>
                <th>Nome Veículo</th>
                <th>Direção</th>
                <th>Estação Base</th>
                <th>Autorizado?</th>
                <th>Detalhes Sensores</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->timestamp_event->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->vehicle_lora_id }}</td>
                    <td>{{ $log->vehicle->name ?? '-' }}</td>
                    <td>{{ $log->direction_detected }}</td>
                    <td>{{ $log->base_station_id }}</td>
                    <td>{{ $log->authorization_status ? 'Sim' : 'Não' }}</td>
                    <td>
                        @if($log->sensor_reports && is_array($log->sensor_reports))
                            @foreach($log->sensor_reports as $report)
                                Sensor: {{ $report['sensor_id'] ?? 'N/A' }}<br>
                                RSSI: {{ $report['rssi'] ?? 'N/A' }}<br>
                                Hora Sensor: {{ $report['timestamp_sensor'] ?? 'N/A' }}
                                <hr>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $log->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Nenhum log de acesso encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($logs->hasPages())
        <div>
            {{ $logs->links() }}
        </div>
    @endif

</body>
</html>
