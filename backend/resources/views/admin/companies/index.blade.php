<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empresas</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background-color: #007bff; padding: 15px; color: white; text-align: center; }
        .nav { background-color: #343a40; padding: 10px 0; text-align: center; }
        .nav a { color: white; padding: 10px 15px; text-decoration: none; margin: 0 5px; }
        .nav a:hover { background-color: #495057; }
        .container { padding: 20px; max-width: 900px; margin: auto; }
        .card { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 0.9em; }
        th { background-color: #f2f2f2; }
        .actions a, .actions button { margin-right: 5px; text-decoration: none; padding: 5px 8px; border: 1px solid #ccc; background-color: #eee; cursor: pointer; font-size: 0.9em; border-radius:3px; }
        .actions button.delete { color: white; background-color: #dc3545; border-color: #dc3545;}
        .actions a.edit {color: white; background-color: #007bff; border-color: #007bff;}
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: .25rem; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .create-link { margin-bottom: 20px; display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; }
        .filters form { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;}
        .filters label {font-weight: bold;}
        .filters input[type="text"], .filters select, .filters button { padding: 8px; border-radius: 4px; border: 1px solid #ccc;}
        .filters button {background-color: #007bff; color:white; border:none;}
        .filters a {padding: 8px 10px; text-decoration: none; color: #333; background-color: #eee; border: 1px solid #ccc; border-radius:4px;}
    </style>
</head>
<body>
    <div class="header">
        <h1>Painel Administrativo</h1>
    </div>
    <nav class="nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.companies.index') }}">Empresas</a>
        <a href="{{ route('admin.vehicles.index') }}">Veículos</a>
        <a href="{{ route('admin.access-logs.index') }}">Logs de Acesso</a>
        <a href="{{ route('admin.firmwares.index') }}">Firmwares</a>
        <a href="{{ route('admin.api-tokens.index') }}">Tokens de API</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Gerenciar Empresas</h2>

            <div class="filters">
                <form method="GET" action="{{ route('admin.companies.index') }}">
                    <div>
                        <label for="name_filter">Nome:</label>
                        <input type="text" name="name_filter" id="name_filter" value="{{ request('name_filter') }}">
                    </div>
                    <div>
                        <label for="is_active_filter">Status:</label>
                        <select name="is_active_filter" id="is_active_filter">
                            <option value="all" {{ request('is_active_filter', 'all') == 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="1" {{ request('is_active_filter') == '1' ? 'selected' : '' }}>Ativa</option>
                            <option value="0" {{ request('is_active_filter') === '0' ? 'selected' : '' }}>Inativa</option>
                        </select>
                    </div>
                    <button type="submit">Filtrar</button>
                    <a href="{{ route('admin.companies.index') }}">Limpar</a>
                </form>
            </div>

            <a href="{{ route('admin.companies.create') }}" class="create-link">Adicionar Nova Empresa</a>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email Contato</th>
                        <th>Status</th>
                        <th>Sites</th>
                        <th>Criada em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->contact_email ?: '-' }}</td>
                            <td>{{ $company->is_active ? 'Ativa' : 'Inativa' }}</td>
                            <td>{{ $company->sites_count ?? $company->sites()->count() }}</td>
                            <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.sites.index', ['company_id' => $company->id]) }}" class="button-style">Ver Sites</a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="edit">Editar</a>
                                <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta empresa? Todos os sites e barreiras associados também serão excluídos.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhuma empresa encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($companies->hasPages())
                <div>
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
