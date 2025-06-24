<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Firmware - v{{ $firmware->version }}</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea { margin-bottom: 10px; width: 300px; padding: 8px; border: 1px solid #ccc; }
        textarea { height: 80px; }
        .error { color: red; font-size: 0.9em; }
        button, a { padding: 10px 15px; text-decoration: none; border: 1px solid #ccc; background-color: #eee; cursor: pointer; }
        button[type="submit"] { background-color: #007bff; color: white; }
        a { color: #333; }
    </style>
</head>
<body>
    <h1>Editar Firmware: v{{ $firmware->version }}</h1>

    <form action="{{ route('admin.firmwares.update', $firmware) }}" method="POST">
        @method('PUT')
        @include('admin.firmwares._form', ['submitButtonText' => 'Atualizar Detalhes', 'firmware' => $firmware])
    </form>
</body>
</html>
