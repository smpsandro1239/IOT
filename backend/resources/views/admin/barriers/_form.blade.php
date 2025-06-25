@csrf
<div>
    <label for="site_id">Site (Empresa):</label><br>
    <select id="site_id" name="site_id" required>
        <option value="">Selecione um Site</option>
        @foreach($sites_grouped as $companyName => $sitesInCompany)
            <optgroup label="{{ $companyName }}">
                @foreach($sitesInCompany as $siteId => $siteName)
                    <option value="{{ $siteId }}" {{ old('site_id', $barrier->site_id ?? '') == $siteId ? 'selected' : '' }}>
                        {{ $siteName }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    @error('site_id')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <label for="name">Nome da Barreira/Ponto de Controle:</label><br>
    <input type="text" id="name" name="name" value="{{ old('name', $barrier->name ?? '') }}" required maxlength="255">
    @error('name')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <label for="base_station_mac_address">MAC Address da Placa Base (ESP32):</label><br>
    <input type="text" id="base_station_mac_address" name="base_station_mac_address" value="{{ old('base_station_mac_address', $barrier->base_station_mac_address ?? '') }}" placeholder="XX:XX:XX:XX:XX:XX" maxlength="17">
    @error('base_station_mac_address')
        <div style="color: red;">{{ $message }}</div>
    @enderror
    <small>Deixe em branco se ainda não configurado. Deve ser único globalmente.</small>
</div>
<br>
<div>
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', isset($barrier) ? $barrier->is_active : true) ? 'checked' : '' }}>
    <label for="is_active" style="display: inline;">Ativa</label>
    @error('is_active')
        <div style="color: red;">{{ $message }}</div>
    @enderror
</div>
<br>
<div>
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.barriers.index') }}">Cancelar</a>
</div>
