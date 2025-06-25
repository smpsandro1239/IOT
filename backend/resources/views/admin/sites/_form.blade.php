@csrf
<div>
    <label for="company_id">Empresa:</label><br>
    <select id="company_id" name="company_id" required>
        <option value="">Selecione uma Empresa</option>
        @foreach($companies as $id => $name)
            <option value="{{ $id }}" {{ old('company_id', $site->company_id ?? '') == $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
    @error('company_id')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <label for="name">Nome do Site:</label><br>
    <input type="text" id="name" name="name" value="{{ old('name', $site->name ?? '') }}" required maxlength="255">
    @error('name')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <label for="address">Endereço (Opcional):</label><br>
    <input type="text" id="address" name="address" value="{{ old('address', $site->address ?? '') }}" maxlength="255">
    @error('address')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', isset($site) ? $site->is_active : true) ? 'checked' : '' }}>
    <label for="is_active" style="display: inline;">Ativo</label>
    @error('is_active')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.sites.index') }}">Cancelar</a>
</div>
