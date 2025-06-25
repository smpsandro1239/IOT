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
{{-- Div para carregar Sites dinamicamente --}}
<div style="margin-top: 15px;">
    <h4>Sites (Locais)</h4>
    <div id="sites-permission-list">
        {{-- Sites selecionados inicialmente (modo de edição) serão populados aqui pelo JS ou mantidos se já renderizados --}}
        {{-- Ou podemos deixar o PHP renderizar os selecionados e o JS adiciona/remove os outros --}}
        @if(isset($vehicle) && !empty($vehiclePermissions['sites']))
            @php
                // Carregar os modelos de Site que estão de facto selecionados para este veículo
                // Isto é importante para que, mesmo que não pertençam às empresas inicialmente selecionadas (se o user desmarcar uma empresa),
                // eles ainda apareçam como selecionados até serem explicitamente desmarcados.
                // No entanto, a lógica do JS irá RECARREGAR os sites com base nas empresas selecionadas.
                // Uma abordagem mais simples é deixar o JS lidar com a população inicial dos sites e barreiras
                // com base nas empresas/sites selecionados.
                // Por agora, vamos deixar o JS popular tudo dinamicamente.
            @endphp
        @endif
        <p>Selecione uma ou mais empresas para ver os sites disponíveis.</p>
    </div>
</div>

{{-- Div para carregar Barreiras dinamicamente --}}
<div style="margin-top: 15px;">
    <h4>Barreiras (Pontos de Controle)</h4>
    <div id="barriers-permission-list">
        <p>Selecione um ou mais sites para ver as barreiras disponíveis.</p>
    </div>
</div>


<div style="margin-top: 20px;">
    <button type="submit">{{ $submitButtonText ?? 'Salvar' }}</button>
    <a href="{{ route('admin.vehicles.index') }}">Cancelar</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companyCheckboxes = document.querySelectorAll('input[name="permissions[companies][]"]');
    const sitesPermissionList = document.getElementById('sites-permission-list');
    const barriersPermissionList = document.getElementById('barriers-permission-list');

    // Permissões que vieram do controller (para o modo de edição)
    const existingPermissions = {
        sites: @json($vehiclePermissions['sites'] ?? []),
        barriers: @json($vehiclePermissions['barriers'] ?? [])
    };

    async function fetchSitesForCompanies(companyIds) {
        if (!companyIds || companyIds.length === 0) {
            renderSites([]);
            renderBarriers([]); // Limpa barreiras se nenhuma empresa estiver selecionada
            return;
        }
        try {
            const response = await fetch(`{{ url('api/v1/companies') }}/${companyIds.join(',')}/sites`, {
                headers: {
                    'Accept': 'application/json',
                    // Adicionar X-CSRF-TOKEN se necessário para APIs com estado e middleware web, mas para Sanctum API GET geralmente não é.
                    // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            if (!response.ok) {
                console.error('Erro ao buscar sites:', response.statusText);
                renderSites([]); return;
            }
            const sites = await response.json();
            renderSites(sites);
        } catch (error) {
            console.error('Erro na requisição de sites:', error);
            renderSites([]);
        }
    }

    async function fetchBarriersForSites(siteIds) {
        if (!siteIds || siteIds.length === 0) {
            renderBarriers([]);
            return;
        }
        try {
            const response = await fetch(`{{ url('api/v1/sites') }}/${siteIds.join(',')}/barriers`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                console.error('Erro ao buscar barreiras:', response.statusText);
                renderBarriers([]); return;
            }
            const barriers = await response.json();
            renderBarriers(barriers);
        } catch (error) {
            console.error('Erro na requisição de barreiras:', error);
            renderBarriers([]);
        }
    }

    function renderSites(sites) {
        sitesPermissionList.innerHTML = ''; // Limpa a lista
        if (sites.length === 0) {
            sitesPermissionList.innerHTML = '<p>Nenhum site encontrado para as empresas selecionadas ou nenhuma empresa selecionada.</p>';
            return;
        }
        sites.forEach(site => {
            const isChecked = existingPermissions.sites.includes(site.id);
            // Se um site estava selecionado mas a empresa dele foi desmarcada, ele não aparecerá mais aqui,
            // o que é o comportamento esperado (a permissão será removida no backend ao salvar).
            const label = document.createElement('label');
            label.style.display = 'block'; // Para melhor formatação
            label.innerHTML = `
                <input type="checkbox" name="permissions[sites][]" value="${site.id}" ${isChecked ? 'checked' : ''} data-company-id="${site.company_id}">
                ${site.name} (Empresa ID: ${site.company_id})
            `; // TODO: Melhorar para mostrar nome da empresa se disponível no JSON
            sitesPermissionList.appendChild(label);
        });
        // Adiciona listeners às novas checkboxes de sites
        addSiteCheckboxListeners();
        // Após renderizar os sites, verifica se algum está selecionado e carrega as barreiras
        updateBarriersBasedOnSelectedSites();
    }

    function renderBarriers(barriers) {
        barriersPermissionList.innerHTML = ''; // Limpa a lista
        if (barriers.length === 0) {
            barriersPermissionList.innerHTML = '<p>Nenhuma barreira encontrada para os sites selecionados ou nenhum site selecionado.</p>';
            return;
        }
        barriers.forEach(barrier => {
            const isChecked = existingPermissions.barriers.includes(barrier.id);
            const label = document.createElement('label');
            label.style.display = 'block';
            label.innerHTML = `
                <input type="checkbox" name="permissions[barriers][]" value="${barrier.id}" ${isChecked ? 'checked' : ''} data-site-id="${barrier.site_id}">
                ${barrier.name} (Site ID: ${barrier.site_id})
            `; // TODO: Melhorar para mostrar nome do site se disponível no JSON
            barriersPermissionList.appendChild(label);
        });
    }

    function getSelectedCompanyIds() {
        return Array.from(companyCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
    }

    function getSelectedSiteIds() {
        const currentSiteCheckboxes = sitesPermissionList.querySelectorAll('input[name="permissions[sites][]"]');
        return Array.from(currentSiteCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
    }

    function updateSitesBasedOnSelectedCompanies() {
        const selectedCompanyIds = getSelectedCompanyIds();
        fetchSitesForCompanies(selectedCompanyIds);
    }

    function updateBarriersBasedOnSelectedSites() {
        const selectedSiteIds = getSelectedSiteIds();
        fetchBarriersForSites(selectedSiteIds);
    }

    companyCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSitesBasedOnSelectedCompanies);
    });

    function addSiteCheckboxListeners() {
        const currentSiteCheckboxes = sitesPermissionList.querySelectorAll('input[name="permissions[sites][]"]');
        currentSiteCheckboxes.forEach(checkbox => {
            // Remove listener antigo para evitar duplicação se esta função for chamada múltiplas vezes
            checkbox.removeEventListener('change', updateBarriersBasedOnSelectedSites);
            checkbox.addEventListener('change', updateBarriersBasedOnSelectedSites);
        });
    }

    // Carga Inicial (Modo de Edição)
    // Se houver empresas selecionadas na carga, busca os sites.
    // A função renderSites então chamará updateBarriersBasedOnSelectedSites se sites forem selecionados.
    if (getSelectedCompanyIds().length > 0) {
        updateSitesBasedOnSelectedCompanies();
    } else {
        // Garante que as listas estejam vazias/com placeholder se nenhuma empresa estiver selecionada inicialmente.
        renderSites([]);
        renderBarriers([]);
    }
});
</script>
@endpush
