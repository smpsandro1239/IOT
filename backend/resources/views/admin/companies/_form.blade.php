@csrf
<div>
    <label for="name">Nome da Empresa:</label><br>
    <input type="text" id="name" name="name" value="{{ old('name', $company->name ?? '') }}" required maxlength="255">
    @error('name')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <label for="contact_email">Email de Contato (Opcional):</label><br>
    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $company->contact_email ?? '') }}" maxlength="255">
    @error('contact_email')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <input type="hidden" name="is_active" value="0"> {{-- Envia 0 se o checkbox não for marcado --}}
    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', isset($company) ? $company->is_active : true) ? 'checked' : '' }}>
    <label for="is_active" style="display: inline;">Ativa</label>
    @error('is_active')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.companies.index') }}">Cancelar</a>
</div>
