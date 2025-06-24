@csrf
<div>
    <label for="version">Versão:</label><br>
    <input type="text" id="version" name="version" value="{{ old('version', $firmware->version ?? '') }}" required maxlength="50" {{ isset($firmware) ? 'readonly' : '' }}>
    @error('version')
        <div style="color: red;">{{ $message }}</div>
    @enderror
    @if(isset($firmware))
        <small>A versão não pode ser alterada após a criação.</small>
    @endif
</div>
<br>
<div>
    <label for="description">Descrição (Opcional):</label><br>
    <textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description', $firmware->description ?? '') }}</textarea>
    @error('description')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>

@if(!isset($firmware)) // Mostrar campo de upload de arquivo apenas na criação
<div>
    <label for="firmware_file">Arquivo Firmware (.bin):</label><br>
    <input type="file" id="firmware_file" name="firmware_file" required accept=".bin,application/octet-stream,application/x-binary,application/macbinary">
    @error('firmware_file')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
@else
<div>
    <p><strong>Arquivo:</strong> {{ $firmware->filename }} (Tamanho: {{ number_format($firmware->size) }} bytes)</p>
    <small>Para alterar o arquivo de firmware, por favor, exclua este registro e faça um novo upload.</small>
</div>
<br>
@endif

<div>
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.firmwares.index') }}">Cancelar</a>
</div>
