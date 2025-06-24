<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Firmwares</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a, .actions button { margin-right: 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; background-color: #eee; cursor: pointer; font-size: 0.9em; }
        .actions button.delete { color: red; }
        .actions button.set-active { color: green; }
        .alert-success { padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 20px; }
        .alert-error { padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 20px; }
        .create-link { margin-bottom: 20px; display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; }
        .is-active { font-weight: bold; color: green; }
    </style>
</head>
<body>
    <h1>Gerenciar Firmwares</h1>

    <a href="{{ route('admin.firmwares.create') }}" class="create-link">Upload Novo Firmware</a>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Versão</th>
                <th>Descrição</th>
                <th>Tamanho (bytes)</th>
                <th>Data Upload</th>
                <th>Ativo?</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($firmwares as $firmware)
                <tr>
                    <td>{{ $firmware->version }}</td>
                    <td>{{ $firmware->description ?: '-' }}</td>
                    <td>{{ number_format($firmware->size) }}</td>
                    <td>{{ $firmware->created_at->format('d/m/Y H:i') }}</td>
                    <td class="{{ $firmware->is_active ? 'is-active' : '' }}">{{ $firmware->is_active ? 'Sim' : 'Não' }}</td>
                    <td class="actions">
                        @if (!$firmware->is_active)
                            <form action="{{ route('admin.firmwares.set-active', $firmware) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="set-active">Marcar como Ativo</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.firmwares.edit', $firmware) }}">Editar</a>
                        <form action="{{ route('admin.firmwares.destroy', $firmware) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este firmware? O arquivo físico também será removido.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhum firmware encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($firmwares->hasPages())
        <div>
            {{ $firmwares->links() }}
        </div>
    @endif

</body>
</html>
