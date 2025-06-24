<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo - {{ $vehicle->name ?: $vehicle->lora_id }}</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="checkbox"] { margin-bottom: 10px; }
        .error { color: red; font-size: 0.9em; }
        button, a { padding: 10px 15px; text-decoration: none; border: 1px solid #ccc; background-color: #eee; cursor: pointer; }
        button[type="submit"] { background-color: #007bff; color: white; }
        a { color: #333; }
    </style>
</head>
<body>
    <h1>Editar Veículo: {{ $vehicle->name ?: $vehicle->lora_id }}</h1>

    <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST">
        @method('PUT')
        @include('admin.vehicles._form', ['submitButtonText' => 'Atualizar Veículo', 'vehicle' => $vehicle])
    </form>
</body>
</html>
