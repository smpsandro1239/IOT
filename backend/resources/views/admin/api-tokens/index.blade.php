<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Tokens de API</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background-color: #007bff; padding: 15px; color: white; text-align: center; }
        .nav { background-color: #343a40; padding: 10px 0; text-align: center; }
        .nav a { color: white; padding: 10px 15px; text-decoration: none; margin: 0 5px; }
        .nav a:hover { background-color: #495057; }
        .container { padding: 20px; max-width: 900px; margin: auto; }
        .card { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2, .card h3 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 0.9em; }
        th { background-color: #f2f2f2; }
        .actions button { margin-right: 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; background-color: #dc3545; color:white; cursor: pointer; font-size: 0.9em; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: .25rem; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
        .alert-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        label, input, button { display: block; margin-bottom: 8px; }
        input[type="text"] { width: calc(100% - 18px); padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button[type="submit"] { background-color: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        .token-display { background-color: #e9ecef; padding: 10px; border: 1px dashed #ced4da; word-break: break-all; margin-top: 10px; }
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
        <a href="{{ route('admin.api-tokens.index') }}">Tokens de API</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Gerenciar Tokens de API</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('plainTextToken'))
                <div class="alert alert-info">
                    <strong>Token Criado: {{ session('tokenName') }}</strong><br>
                    Copie este token. Ele não será exibido novamente:<br>
                    <div class="token-display">{{ session('plainTextToken') }}</div>
                </div>
            @endif

            <div class="card" style="margin-top: 20px;">
                <h3>Criar Novo Token de API</h3>
                <form action="{{ route('admin.api-tokens.store') }}" method="POST">
                    @csrf
                    <div>
                        <label for="token_name">Nome do Token (para sua referência):</label>
                        <input type="text" id="token_name" name="token_name" value="{{ old('token_name') }}" required>
                        @error('token_name') <div style="color:red; font-size:0.8em;">{{ $message }}</div> @enderror
                    </div>
                    {{-- Para habilidades no futuro:
                    <div>
                        <label>Habilidades (opcional):</label>
                        <input type="checkbox" name="abilities[]" value="api:access"> Acesso Geral API <br>
                        <input type="checkbox" name="abilities[]" value="device:update-firmware"> Atualizar Firmware Dispositivo
                    </div>
                    --}}
                    <button type="submit">Criar Token</button>
                </form>
            </div>

            <div class="card" style="margin-top: 30px;">
                <h3>Tokens Existentes</h3>
                @if ($tokens->isNotEmpty())
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Habilidades</th>
                                <th>Último Uso</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tokens as $token)
                                <tr>
                                    <td>{{ $token->name }}</td>
                                    <td>{{ $token->abilities ? implode(', ', $token->abilities) : 'N/A' }}</td>
                                    <td>{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Nunca' }}</td>
                                    <td>{{ $token->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="actions">
                                        <form action="{{ route('admin.api-tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja revogar este token?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Revogar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Nenhum token de API foi criado ainda.</p>
                @endif
            </div>
        </div>
    </div>

</body>
</html>
