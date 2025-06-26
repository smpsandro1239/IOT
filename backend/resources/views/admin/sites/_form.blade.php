@csrf

@php
    $currentUser = Auth::user();
    $isSuperAdmin = $currentUser->isSuperAdmin();
    // Se for Company Admin, a empresa é a dele e não pode ser alterada.
    // $companies é assumido como uma coleção de objetos Company ou um array id => nome.
    // Se $companies for uma coleção, o primeiro item será usado para o CompanyAdmin.
    $companyAdminCompany = null;
    if (!$isSuperAdmin && $currentUser->hasRole('company-admin')) {
        if ($companies instanceof Illuminate\Support\Collection && $companies->isNotEmpty()) {
            $companyAdminCompany = $companies->first(); // Assumindo que o controller passou apenas a empresa do CompanyAdmin
        } elseif (is_array($companies) && !empty($companies)) {
             // Se for um array [id => nome], precisamos encontrar o ID da empresa do admin.
             // Esta parte pode precisar de ajuste dependendo de como $companies é passado para CA.
             // Idealmente, o controller passa apenas a empresa do CompanyAdmin.
             $companyAdminCompany = (object) ['id' => $currentUser->company_id, 'name' => $companies[$currentUser->company_id] ?? 'Current Company'];
        }
    }
@endphp

@if($isSuperAdmin)
<div>
    <label for="company_id">Empresa:</label><br>
    <select id="company_id" name="company_id" required>
        <option value="">Selecione uma Empresa</option>
        {{-- Iterar sobre $companies, que para SuperAdmin deve ser todas as empresas --}}
        @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ old('company_id', $site->company_id ?? '') == $company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
        @endforeach
    </select>
    @error('company_id')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
@elseif($companyAdminCompany)
<div>
    <label>Empresa:</label><br>
    <p><strong>{{ $companyAdminCompany->name }}</strong></p>
    <input type="hidden" name="company_id" value="{{ $companyAdminCompany->id }}">
</div>
@else
    {{-- Caso para SiteManager editando seu próprio site, ou erro --}}
    @if(isset($site) && $site->company)
    <div>
        <label>Empresa:</label><br>
        <p><strong>{{ $site->company->name }}</strong></p>
        {{-- SiteManager não deve poder mudar a empresa do site --}}
        <input type="hidden" name="company_id" value="{{ $site->company_id }}">
    </div>
    @else
    <div style="color: red;">Erro: A empresa não pôde ser determinada. Contacte o suporte.</div>
    @endif
@endif
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
