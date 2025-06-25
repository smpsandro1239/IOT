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

        // Filtro por Status de Autorização
        if ($request->filled('is_authorized_filter') && $request->is_authorized_filter !== 'all') {
            $query->where('is_authorized', (bool)$request->is_authorized_filter);
        }

        $vehicles = $query->paginate(15)->withQueryString(); // Manter filtros na paginação

        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vehicles.create');
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

        $validatedData['is_authorized'] = $request->boolean('is_authorized'); // Correct way to get boolean from request

        Vehicle::create($validatedData);

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo criado com sucesso!');
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
        return view('admin.vehicles.edit', compact('vehicle'));
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

        $validatedData['is_authorized'] = $request->boolean('is_authorized');

        $vehicle->update($validatedData);

        return redirect()->route('admin.vehicles.index')->with('success', 'Veículo atualizado com sucesso!');
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
     * Check if a vehicle is authorized.
     * API endpoint for ESP32.
     */
    public function checkAuthorization(string $lora_id)
    {
        // TODO: Consider logging this request attempt for security/audit purposes if needed, even if vehicle not found.
        $vehicle = \App\Models\Vehicle::where('lora_id', $lora_id)->first();

        if ($vehicle) {
            if ($vehicle->is_authorized) {
                return response()->json(['authorized' => true, 'lora_id' => $lora_id, 'name' => $vehicle->name]);
            } else {
                // Vehicle found but not authorized
                return response()->json(['authorized' => false, 'lora_id' => $lora_id, 'reason' => 'Vehicle not authorized'], 403);
            }
        } else {
            // Vehicle not found in the database
            return response()->json(['authorized' => false, 'lora_id' => $lora_id, 'reason' => 'Vehicle not found'], 404);
        }
    }
}
