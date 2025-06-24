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
                <th>Autorizado?</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->lora_id }}</td>
                    <td>{{ $vehicle->name ?: '-' }}</td>
                    <td>{{ $vehicle->is_authorized ? 'Sim' : 'Não' }}</td>
                    <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                    <td class="actions">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}">Editar</a>
                        <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este veículo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum veículo encontrado.</td>
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
