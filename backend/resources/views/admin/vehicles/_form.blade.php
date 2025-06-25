@csrf
<div>
    <label for="lora_id">ID LoRa:</label>
    <input type="text" name="lora_id" id="lora_id" value="{{ old('lora_id', $vehicle->lora_id ?? '') }}" required>
    @error('lora_id')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div>
    <label for="name">Nome (Opcional):</label>
    <input type="text" name="name" id="name" value="{{ old('name', $vehicle->name ?? '') }}">
    @error('name')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<hr>
<h3>Permissões de Acesso</h3>

{{-- Permissões para Empresas --}}
@if(isset($companies) && $companies->count())
<div>
    <h4>Empresas</h4>
    @foreach($companies as $company)
        <label>
            <input type="checkbox" name="permissions[companies][]" value="{{ $company->id }}"
                   {{ (isset($vehiclePermissions['companies']) && in_array($company->id, $vehiclePermissions['companies'])) ? 'checked' : '' }}>
            {{ $company->name }}
        </label>
    @endforeach
</div>
@endif

{{-- Permissões para Sites --}}
@if(isset($sites) && $sites->count())
<div style="margin-top: 15px;">
    <h4>Sites (Locais)</h4>
    @foreach($sites as $site)
        <label>
            <input type="checkbox" name="permissions[sites][]" value="{{ $site->id }}"
                   {{ (isset($vehiclePermissions['sites']) && in_array($site->id, $vehiclePermissions['sites'])) ? 'checked' : '' }}>
            {{ $site->name }} (Empresa: {{ $site->company->name ?? 'N/A' }})
        </label>
    @endforeach
</div>
@endif

{{-- Permissões para Barreiras --}}
@if(isset($barriers) && $barriers->count())
<div style="margin-top: 15px;">
    <h4>Barreiras (Pontos de Controle)</h4>
    @foreach($barriers as $barrier)
        <label>
            <input type="checkbox" name="permissions[barriers][]" value="{{ $barrier->id }}"
                   {{ (isset($vehiclePermissions['barriers']) && in_array($barrier->id, $vehiclePermissions['barriers'])) ? 'checked' : '' }}>
            {{ $barrier->name }} (Site: {{ $barrier->site->name ?? 'N/A' }}, Empresa: {{ $barrier->site->company->name ?? 'N/A' }})
        </label>
    @endforeach
</div>
@endif


<div style="margin-top: 20px;">
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.vehicles.index') }}">Cancelar</a>
</div>
