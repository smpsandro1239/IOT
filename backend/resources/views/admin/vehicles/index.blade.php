<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Veículos</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a, .actions button { margin-right: 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; background-color: #eee; cursor: pointer; }
        .actions button { color: red; }
        .alert-success { padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 20px; }
        .create-link { margin-bottom: 20px; display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Gerenciar Veículos</h1>

    <div class="filters" style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9;">
        <form method="GET" action="{{ route('admin.vehicles.index') }}">
            <label for="lora_id_filter">Filtrar por ID LoRa:</label>
            <input type="text" name="lora_id_filter" id="lora_id_filter" value="{{ request('lora_id_filter') }}" style="margin-right: 10px; padding: 8px;">

            <label for="name_filter">Filtrar por Nome:</label>
            <input type="text" name="name_filter" id="name_filter" value="{{ request('name_filter') }}" style="margin-right: 10px; padding: 8px;">

            {{-- Filtro de Autorizado Removido --}}
            {{-- <label for="is_authorized_filter">Autorizado:</label>
            <select name="is_authorized_filter" id="is_authorized_filter" style="margin-right: 10px; padding: 8px;">
                <option value="all" {{ request('is_authorized_filter') == 'all' ? 'selected' : '' }}>Todos</option>
                <option value="1" {{ request('is_authorized_filter') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('is_authorized_filter') == '0' && request('is_authorized_filter') !== null ? 'selected' : '' }}>Não</option>
            </select> --}}

            <button type="submit" style="padding: 8px 15px;">Filtrar</button>
            <a href="{{ route('admin.vehicles.index') }}" style="padding: 8px 15px; text-decoration: none; color: #333; background-color: #eee; border: 1px solid #ccc;">Limpar</a>
        </form>
    </div>

    <a href="{{ route('admin.vehicles.create') }}" class="create-link">Adicionar Novo Veículo</a>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID LoRa</th>
                <th>Nome</th>
                {{-- Coluna Autorizado? Removida --}}
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->lora_id }}</td>
                    <td>{{ $vehicle->name ?: '-' }}</td>
                    {{-- <td>{{ $vehicle->is_authorized ? 'Sim' : 'Não' }}</td> --}}
                    <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                    <td class="actions">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}">Editar Permissões</a>
                        <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este veículo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum veículo encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    @if ($vehicles->hasPages())
        <div>
            {{ $vehicles->links() }}
        </div>
    @endif

</body>
</html>
