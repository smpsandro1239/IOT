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
        // Para o formulário de criação, não temos um $vehicle ainda, então $vehiclePermissions estará vazio.
        // Mas precisamos passar as listas de companhias, sites e barreiras.
        $companies = \App\Models\Company::orderBy('name')->get();
        $sites = \App\Models\Site::with('company')->orderBy('company_id')->orderBy('name')->get();
        $barriers = \App\Models\Barrier::with('site.company')->orderBy('site_id')->orderBy('name')->get();

        // Passamos um array vazio para vehiclePermissions para que o form não falhe
        $vehiclePermissions = ['companies' => [], 'sites' => [], 'barriers' => []];

        return view('admin.vehicles.create', compact('companies', 'sites', 'barriers', 'vehiclePermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'lora_id' => 'required|string|max:16|unique:vehicles,lora_id',
            'name' => 'nullable|string|max:255',
            // 'is_authorized' é tratado abaixo
        ]);

        // $validatedData['is_authorized'] = $request->boolean('is_authorized'); // Removido

        $vehicle = Vehicle::create($validatedData);

        // Gerenciar Permissões (similar ao update)
        $permissionsInput = $request->input('permissions', []);

        if (!empty($permissionsInput['companies'])) {
            foreach ($permissionsInput['companies'] as $companyId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Company::class,
                    'permissible_id' => $companyId,
                ]);
            }
        }

        if (!empty($permissionsInput['sites'])) {
            foreach ($permissionsInput['sites'] as $siteId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Site::class,
                    'permissible_id' => $siteId,
                ]);
            }
        }

        if (!empty($permissionsInput['barriers'])) {
            foreach ($permissionsInput['barriers'] as $barrierId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Barrier::class,
                    'permissible_id' => $barrierId,
                ]);
            }
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo criado e permissões sincronizadas com sucesso!');
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
        $vehicle->load('permissions'); // Carrega as permissões existentes do veículo

        $companies = \App\Models\Company::orderBy('name')->get();
        $sites = \App\Models\Site::with('company')->orderBy('company_id')->orderBy('name')->get();
        $barriers = \App\Models\Barrier::with('site.company')->orderBy('site_id')->orderBy('name')->get();

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
        $validatedData = $request->validate([
            'lora_id' => 'required|string|max:16|unique:vehicles,lora_id,' . $vehicle->id,
            'name' => 'nullable|string|max:255',
            // 'is_authorized' é tratado abaixo
        ]);

        // $validatedData['is_authorized'] = $request->boolean('is_authorized'); // Removido - não usamos mais is_authorized diretamente no veículo
        $vehicle->update($validatedData);

        // Gerenciar Permissões
        // 1. Remover todas as permissões existentes para este veículo
        $vehicle->permissions()->delete();

        // 2. Adicionar novas permissões com base no request
        $permissionsInput = $request->input('permissions', []);

        if (!empty($permissionsInput['companies'])) {
            foreach ($permissionsInput['companies'] as $companyId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Company::class,
                    'permissible_id' => $companyId,
                    // 'expires_at' => null, // Opcional: Adicionar lógica para data de expiração se necessário
                ]);
            }
        }

        if (!empty($permissionsInput['sites'])) {
            foreach ($permissionsInput['sites'] as $siteId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Site::class,
                    'permissible_id' => $siteId,
                ]);
            }
        }

        if (!empty($permissionsInput['barriers'])) {
            foreach ($permissionsInput['barriers'] as $barrierId) {
                $vehicle->permissions()->create([
                    'permissible_type' => \App\Models\Barrier::class,
                    'permissible_id' => $barrierId,
                ]);
            }
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo atualizado e permissões sincronizadas com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle) // Using Route Model Binding
    {
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
