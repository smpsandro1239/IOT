<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Todos os papéis (SuperAdmin, CompanyAdmin, SiteManager) podem ver a lista de veículos
        // para poderem atribuir-lhes permissões.
        // A permissão 'vehicles:view-any' será atribuída a estes papéis.
        $this->authorize('vehicles:view-any');

        $query = Vehicle::orderBy('name', 'asc');

        // Filtro por ID LoRa
        if ($request->filled('lora_id_filter')) {
            $query->where('lora_id', 'like', '%' . $request->lora_id_filter . '%');
        }

        // Filtro por Nome
        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }

        // Filtro por Status de Autorização (REMOVIDO - is_authorized não é mais usado diretamente)
        // if ($request->filled('is_authorized_filter') && $request->is_authorized_filter !== 'all') {
        //     $query->where('is_authorized', (bool)$request->is_authorized_filter);
        // }

        $vehicles = $query->withCount(['companyPermissions', 'sitePermissions', 'barrierPermissions'])
                           ->paginate(15)
                           ->withQueryString(); // Manter filtros na paginação

        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Apenas SuperAdmin pode criar novos registos de veículos.
        $this->authorize('vehicles:create-any');

        $user = auth()->user();

        // Listas de entidades para atribuir permissões.
        // SuperAdmin vê tudo. CompanyAdmin/SiteManager vêem um subconjunto.
        $companies_query = \App\Models\Company::query()->orderBy('name');
        $sites_query = \App\Models\Site::query()->with('company')->orderBy('company_id')->orderBy('name');
        $barriers_query = \App\Models\Barrier::query()->with('site.company')->orderBy('site_id')->orderBy('name');

        if ($user->hasRole('company-admin') && $user->company_id) {
            $company_id = $user->company_id;
            $companies_query->where('id', $company_id);
            $sites_query->where('company_id', $company_id);
            $barriers_query->whereHas('site', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            });
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            $company_id = $user->company_id;
            // SiteManager só pode atribuir permissões a sites/barreiras da sua empresa,
            // e idealmente apenas aos seus sites "atribuídos".
            // Esta filtragem de "atribuídos" precisaria de uma tabela site_user ou similar.
            // Por agora, limitamos à sua empresa.
            $companies_query->where('id', $company_id); // Só a sua empresa
            $sites_query->where('company_id', $company_id); // Sites da sua empresa
            // TODO: Filtrar $sites_query para apenas os sites que o SiteManager gere.
            $barriers_query->whereHas('site', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
                // TODO: Filtrar $barriers_query para apenas barreiras de sites que o SiteManager gere.
            });
        }
        // SuperAdmin não tem restrições de query.

        $companies = $companies_query->get();
        $sites = $sites_query->get();
        $barriers = $barriers_query->get();

        $vehiclePermissions = ['companies' => [], 'sites' => [], 'barriers' => []];

        return view('admin.vehicles.create', compact('companies', 'sites', 'barriers', 'vehiclePermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Autorizar criação do REGISTO do veículo
        $this->authorize('vehicles:create-any'); // Só SuperAdmin pode criar registos de veículos

        $validatedData = $request->validate([
            'lora_id' => 'required|string|max:16|unique:vehicles,lora_id',
            'name' => 'nullable|string|max:255',
        ]);

        $vehicle = Vehicle::create($validatedData);

        // 2. Autorizar ATRIBUIÇÃO de permissões de acesso
        $user = auth()->user();
        $permissionsInput = $request->input('permissions', []);

        if ($user->isSuperAdmin()) {
            $this->authorize('vehicle-permissions:assign-to-any-company');
        } elseif ($user->hasRole('company-admin')) {
            $this->authorize('vehicle-permissions:assign-to-own-company');
        } elseif ($user->hasRole('site-manager')) {
            $this->authorize('vehicle-permissions:assign-to-assigned-site');
        } else {
            abort(403, 'UNAUTHORIZED: Cannot assign vehicle permissions.');
        }

        // Filtrar e validar $permissionsInput com base no papel do utilizador
        $this->syncVehiclePermissions($vehicle, $permissionsInput, $user);

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo criado e permissões sincronizadas com sucesso!');
    }

    /**
     * Helper function to sync vehicle permissions based on user role.
     */
    private function syncVehiclePermissions(Vehicle $vehicle, array $permissionsInput, \App\Models\User $user)
    {
        $vehicle->permissions()->delete(); // Remove old permissions first

        // Company Permissions
        if (!empty($permissionsInput['companies'])) {
            foreach ($permissionsInput['companies'] as $companyId) {
                if ($user->isSuperAdmin() || ($user->hasRole('company-admin') && $user->company_id == $companyId)) {
                    $vehicle->permissions()->create([
                        'permissible_type' => \App\Models\Company::class,
                        'permissible_id' => $companyId,
                    ]);
                } else if ($user->hasRole('site-manager') && $user->company_id == $companyId) {
                    // Site manager pode implicitamente ter acesso à empresa se gere sites dessa empresa,
                    // mas geralmente não atribui permissão a nível de empresa diretamente.
                    // Permitir se a empresa for a do SiteManager, para consistência se ele puder ver essa opção.
                     $vehicle->permissions()->create([
                        'permissible_type' => \App\Models\Company::class,
                        'permissible_id' => $companyId,
                    ]);
                } else {
                     \Illuminate\Support\Facades\Log::warning("User {$user->id} attempted to assign unauthorized company permission {$companyId} to vehicle {$vehicle->id}");
                }
            }
        }

        // Site Permissions
        if (!empty($permissionsInput['sites'])) {
            $allowedSiteIds = [];
            if ($user->isSuperAdmin()) {
                $allowedSiteIds = \App\Models\Site::whereIn('id', $permissionsInput['sites'])->pluck('id')->toArray();
            } elseif ($user->hasRole('company-admin') && $user->company_id) {
                $allowedSiteIds = \App\Models\Site::where('company_id', $user->company_id)
                                       ->whereIn('id', $permissionsInput['sites'])
                                       ->pluck('id')->toArray();
            } elseif ($user->hasRole('site-manager') && $user->company_id) {
                // SiteManager: apenas sites da sua empresa (e idealmente, apenas os que ele gere)
                $allowedSiteIds = \App\Models\Site::where('company_id', $user->company_id)
                                       // TODO: ->whereIn('id', $user->managedSiteIds()) // Se managedSiteIds() existir
                                       ->whereIn('id', $permissionsInput['sites'])
                                       ->pluck('id')->toArray();
            }
            foreach ($allowedSiteIds as $siteId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Site::class,
                    'permissible_id' => $siteId,
                ]);
            }
            // Log non-allowed attempts
            $disallowedSiteIds = array_diff($permissionsInput['sites'], $allowedSiteIds);
            if (!empty($disallowedSiteIds)) {
                \Illuminate\Support\Facades\Log::warning("User {$user->id} attempted to assign unauthorized site permissions " . implode(',', $disallowedSiteIds) . " to vehicle {$vehicle->id}");
            }
        }

        // Barrier Permissions
        if (!empty($permissionsInput['barriers'])) {
            $allowedBarrierIds = [];
            if ($user->isSuperAdmin()) {
                $allowedBarrierIds = \App\Models\Barrier::whereIn('id', $permissionsInput['barriers'])->pluck('id')->toArray();
            } elseif ($user->hasRole('company-admin') && $user->company_id) {
                $company_id = $user->company_id;
                $allowedBarrierIds = \App\Models\Barrier::whereHas('site', function ($q) use ($company_id) {
                                           $q->where('company_id', $company_id);
                                       })
                                       ->whereIn('id', $permissionsInput['barriers'])
                                       ->pluck('id')->toArray();
            } elseif ($user->hasRole('site-manager') && $user->company_id) {
                $company_id = $user->company_id;
                // SiteManager: apenas barreiras de sites da sua empresa (e idealmente, apenas dos sites que ele gere)
                $allowedBarrierIds = \App\Models\Barrier::whereHas('site', function ($q) use ($company_id) {
                                           $q->where('company_id', $company_id);
                                           // TODO: ->whereIn('id', $user->managedSiteIds()) // Se managedSiteIds() existir
                                       })
                                       ->whereIn('id', $permissionsInput['barriers'])
                                       ->pluck('id')->toArray();
            }
            foreach ($allowedBarrierIds as $barrierId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Barrier::class,
                    'permissible_id' => $barrierId,
                ]);
            }
            // Log non-allowed attempts
            $disallowedBarrierIds = array_diff($permissionsInput['barriers'], $allowedBarrierIds);
            if (!empty($disallowedBarrierIds)) {
                 \Illuminate\Support\Facades\Log::warning("User {$user->id} attempted to assign unauthorized barrier permissions " . implode(',', $disallowedBarrierIds) . " to vehicle {$vehicle->id}");
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle) // Using Route Model Binding
    {
        // Se tiver um show.blade.php:
        // return view('admin.vehicles.show', compact('vehicle'));
        return redirect()->route('admin.vehicles.edit', $vehicle); // Ou simplesmente redireciona para edição
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle) // Using Route Model Binding
    {
        // Apenas SuperAdmin pode editar os DETALHES do veículo (lora_id, name).
        // CompanyAdmin/SiteManager podem editar as PERMISSÕES DE ACESSO do veículo.
        // A view _form.blade.php terá que distinguir campos editáveis vs. atribuição de permissões.

        // Autorizar a intenção de "gerir" permissões (para popular o form corretamente)
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $this->authorize('vehicle-permissions:assign-to-any-company');
        } elseif ($user->hasRole('company-admin')) {
            $this->authorize('vehicle-permissions:assign-to-own-company');
        } elseif ($user->hasRole('site-manager')) {
            $this->authorize('vehicle-permissions:assign-to-assigned-site');
        } else {
            // Se o utilizador não tiver nenhuma destas, não deve poder nem ver a página de edição
            // (a menos que haja uma permissão genérica de 'vehicles:view-details' ou similar)
            abort(403, 'UNAUTHORIZED: Cannot manage vehicle permissions.');
        }
        // A autorização para editar os campos do veículo em si (lora_id, name) será verificada
        // com 'vehicles:update-any', e a view pode desabilitar esses campos se o user não tiver essa perm.

        $vehicle->load('permissions');

        // Filtrar listas de Companies, Sites, Barriers para o formulário de permissões
        $companies_query = \App\Models\Company::query()->orderBy('name');
        $sites_query = \App\Models\Site::query()->with('company')->orderBy('company_id')->orderBy('name');
        $barriers_query = \App\Models\Barrier::query()->with('site.company')->orderBy('site_id')->orderBy('name');

        if ($user->hasRole('company-admin') && $user->company_id) {
            $company_id = $user->company_id;
            $companies_query->where('id', $company_id);
            $sites_query->where('company_id', $company_id);
            $barriers_query->whereHas('site', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            });
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            $company_id = $user->company_id;
            $companies_query->where('id', $company_id);
            $sites_query->where('company_id', $user->company_id);
            // TODO: Filtrar $sites_query para apenas os sites que o SiteManager gere.
            $barriers_query->whereHas('site', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
                // TODO: Filtrar $barriers_query para apenas barreiras de sites que o SiteManager gere.
            });
        }

        $companies = $companies_query->get();
        $sites = $sites_query->get();
        $barriers = $barriers_query->get();

        // Para pré-selecionar nos formulários, criamos arrays de IDs das permissões existentes
        $vehiclePermissions = [
            'companies' => $vehicle->permissions->where('permissible_type', \App\Models\Company::class)->pluck('permissible_id')->toArray(),
            'sites' => $vehicle->permissions->where('permissible_type', \App\Models\Site::class)->pluck('permissible_id')->toArray(),
            'barriers' => $vehicle->permissions->where('permissible_type', \App\Models\Barrier::class)->pluck('permissible_id')->toArray(),
        ];

        return view('admin.vehicles.edit', compact('vehicle', 'companies', 'sites', 'barriers', 'vehiclePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle) // Using Route Model Binding
    {
        $user = auth()->user();

        // 1. Autorizar atualização dos DETALHES do veículo (lora_id, name)
        // Apenas SuperAdmin pode fazer isto.
        // Se o request contiver 'lora_id' ou 'name', verificar a permissão.
        if ($request->has('lora_id') || $request->has('name')) {
            $this->authorize('vehicles:update-any');
            $validatedVehicleData = $request->validate([
                'lora_id' => 'required|string|max:16|unique:vehicles,lora_id,' . $vehicle->id,
                'name' => 'nullable|string|max:255',
            ]);
            $vehicle->update($validatedVehicleData);
        }

        // 2. Autorizar ATRIBUIÇÃO de permissões de acesso
        // Se o request contiver 'permissions'
        if ($request->has('permissions')) {
            if ($user->isSuperAdmin()) {
                $this->authorize('vehicle-permissions:assign-to-any-company');
            } elseif ($user->hasRole('company-admin')) {
                $this->authorize('vehicle-permissions:assign-to-own-company');
            } elseif ($user->hasRole('site-manager')) {
                $this->authorize('vehicle-permissions:assign-to-assigned-site');
            } else {
                abort(403, 'UNAUTHORIZED: Cannot assign vehicle permissions.');
            }
            $permissionsInput = $request->input('permissions', []);
            $this->syncVehiclePermissions($vehicle, $permissionsInput, $user);
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo atualizado e permissões sincronizadas com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle) // Using Route Model Binding
    {
        // Apenas SuperAdmin pode excluir registos de veículos.
        $this->authorize('vehicles:delete-any');

        try {
            $vehicle->delete();
            return redirect()->route('admin.vehicles.index')->with('success', 'Veículo excluído com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Captura especificamente exceções de query (como violações de FK)
            \Illuminate\Support\Facades\Log::error("Erro de Query ao excluir veículo ID {$vehicle->id}: " . $e->getMessage());
            return redirect()->route('admin.vehicles.index')->with('error', 'Erro ao excluir veículo. Verifique se está sendo usado em logs de acesso.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro geral ao excluir veículo ID {$vehicle->id}: " . $e->getMessage());
            return redirect()->route('admin.vehicles.index')->with('error', 'Erro ao excluir veículo.');
        }
    }

    /**
     * Check if a vehicle is authorized for a specific barrier.
     * API endpoint for ESP32.
     * Query Params: lora_id, base_station_mac
     */
    public function checkAuthorization(Request $request)
    {
        $validatedData = $request->validate([
            'lora_id' => 'required|string|max:16',
            'base_station_mac' => 'required|string|max:17', // XX:XX:XX:XX:XX:XX
        ]);

        $loraId = $validatedData['lora_id'];
        $baseStationMac = $validatedData['base_station_mac'];

        $vehicle = Vehicle::where('lora_id', $loraId)->first();
        if (!$vehicle) {
            \Illuminate\Support\Facades\Log::warning("Tentativa de autorização para veículo não encontrado. LoRa ID: {$loraId}, Base MAC: {$baseStationMac}");
            return response()->json(['authorized' => false, 'lora_id' => $loraId, 'reason' => 'Vehicle not found'], 404);
        }

        $barrier = \App\Models\Barrier::with('site.company') // Eager load site and company
                                      ->where('base_station_mac_address', $baseStationMac)
                                      ->first();
        if (!$barrier) {
            \Illuminate\Support\Facades\Log::warning("Tentativa de autorização para estação base/barreira não encontrada. LoRa ID: {$loraId}, Base MAC: {$baseStationMac}");
            return response()->json(['authorized' => false, 'lora_id' => $loraId, 'reason' => 'Barrier/Base station not found'], 404);
        }

        if (!$barrier->is_active || !$barrier->site->is_active || !$barrier->site->company->is_active) {
            \Illuminate\Support\Facades\Log::info("Tentativa de acesso a barreira/site/empresa inativa. Veículo: {$loraId}, Barreira: {$barrier->name} (ID: {$barrier->id}), Base MAC: {$baseStationMac}");
            return response()->json(['authorized' => false, 'lora_id' => $loraId, 'reason' => 'Access point inactive'], 403);
        }

        // Hierarquia de verificação de permissão:
        // 1. Permissão específica para a Barreira
        // 2. Permissão para o Site da Barreira
        // 3. Permissão para a Company do Site

        $isAuthorized = false;
        $reason = "No specific permission found";

        // 1. Checar permissão na Barreira
        if ($vehicle->hasPermissionFor($barrier)) {
            $isAuthorized = true;
            $reason = "Authorized for barrier: {$barrier->name}";
        }
        // 2. Se não, checar permissão no Site
        else if ($vehicle->hasPermissionFor($barrier->site)) {
            $isAuthorized = true;
            $reason = "Authorized for site: {$barrier->site->name}";
        }
        // 3. Se não, checar permissão na Empresa
        else if ($vehicle->hasPermissionFor($barrier->site->company)) {
            $isAuthorized = true;
            $reason = "Authorized for company: {$barrier->site->company->name}";
        }

        if ($isAuthorized) {
            \Illuminate\Support\Facades\Log::info("Veículo autorizado. LoRa ID: {$loraId}, Nome Veículo: {$vehicle->name}, Barreira: {$barrier->name} (ID {$barrier->id}), Razão: {$reason}");
            return response()->json(['authorized' => true, 'lora_id' => $loraId, 'name' => $vehicle->name, 'barrier_name' => $barrier->name]);
        } else {
            \Illuminate\Support\Facades\Log::info("Veículo NÃO autorizado. LoRa ID: {$loraId}, Nome Veículo: {$vehicle->name}, Barreira: {$barrier->name} (ID {$barrier->id}), Base MAC: {$baseStationMac}, Razão: {$reason}");
            return response()->json(['authorized' => false, 'lora_id' => $loraId, 'reason' => $reason], 403);
        }
    }
}
