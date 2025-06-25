<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa - {{ $company->name }}</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f6f9; }
        .header { background-color: #007bff; padding: 15px; color: white; text-align: center; }
        .nav { background-color: #343a40; padding: 10px 0; text-align: center; }
        .nav a { color: white; padding: 10px 15px; text-decoration: none; margin: 0 5px; }
        .nav a:hover { background-color: #495057; }
        .container { padding: 20px; max-width: 700px; margin: auto; background-color: white; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="checkbox"] { margin-bottom: 10px; }
        input[type="text"], input[type="email"] { width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;}
        input[type="checkbox"] { margin-right: 5px; }
        .error { color: red; font-size: 0.9em; }
        button, a.button-style { padding: 10px 15px; text-decoration: none; border: 1px solid #ccc; background-color: #eee; cursor: pointer; border-radius: 4px; margin-top:10px; display: inline-block;}
        button[type="submit"] { background-color: #007bff; color: white; border:none; }
        a.button-style { color: #333; margin-left: 10px;}
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
        <h2>Editar Empresa: {{ $company->name }}</h2>
        <form action="{{ route('admin.companies.update', $company) }}" method="POST">
            @method('PUT')
            @include('admin.companies._form', ['submitButtonText' => 'Atualizar Empresa', 'company' => $company])
        </form>
    </div>
</body>
</html>
