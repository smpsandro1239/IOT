<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessLog;

class AccessLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AccessLog::with('vehicle') // Eager load vehicle relationship
                          ->orderBy('timestamp_event', 'desc');

        // Filtro por ID do Veículo (lora_id)
        if ($request->filled('vehicle_lora_id')) {
            $query->where('vehicle_lora_id', 'like', '%' . $request->vehicle_lora_id . '%');
        }

        // Filtro por Data (timestamp_event)
        if ($request->filled('date_from')) {
            $query->whereDate('timestamp_event', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('timestamp_event', '<=', $request->date_to);
        }

        // Filtro por Direção
        if ($request->filled('direction_detected') && $request->direction_detected !== 'all') {
            $query->where('direction_detected', $request->direction_detected);
        }

        // Filtro por Status de Autorização
        if ($request->filled('authorization_status') && $request->authorization_status !== 'all') {
            $query->where('authorization_status', (bool)$request->authorization_status);
        }

        $logs = $query->paginate(25)->withQueryString(); // withQueryString para manter os filtros na paginação

        return view('admin.access-logs.index', compact('logs'));
    }

    /**
     * Get the latest system status for the dashboard.
     */
    public function getLatest(Request $request)
    {
        $latestLog = AccessLog::with('vehicle')->orderBy('timestamp_event', 'desc')->first();

        // Placeholder for barrier status. In a real scenario, this would come
        // from a cache, a separate table, or a direct device communication proxy.
        // For now, we'll simulate it based on the last log.
        $barrierStatus = [
            'north' => ['state' => 'closed', 'last_updated' => now()],
            'south' => ['state' => 'closed', 'last_updated' => now()],
        ];

        if ($latestLog) {
            if ($latestLog->direction_detected === 'north_south' && $latestLog->authorization_status) {
                $barrierStatus['north']['state'] = 'open';
                $barrierStatus['north']['last_updated'] = $latestLog->timestamp_event;
            } elseif ($latestLog->direction_detected === 'south_north' && $latestLog->authorization_status) {
                $barrierStatus['south']['state'] = 'open';
                $barrierStatus['south']['last_updated'] = $latestLog->timestamp_event;
            }
        }

        return response()->json([
            'latest_log' => $latestLog,
            'barrier_status' => $barrierStatus,
            'system_status' => 'operational', // Could be dynamic based on other factors
            'server_time' => now(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     * API endpoint for ESP32.
     */
    public function store(Request $request)
    {
        // TODO: Add more robust error handling for validation failures, e.g., logging
        $validatedData = $request->validate([
            'vehicle_lora_id' => 'required|string|max:16',
            'timestamp_event' => 'required|date_format:Y-m-d H:i:s',
            'direction_detected' => 'required|string|in:north_south,south_north,undefined,conflito',
            'base_station_id' => 'required|string|max:17', // MAC da placa base, formato XX:XX:XX:XX:XX:XX
            'sensor_reports' => 'nullable|json',
            'authorization_status' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $barrier = \App\Models\Barrier::where('base_station_mac_address', $validatedData['base_station_id'])->first();

        if (!$barrier) {
            \Illuminate\Support\Facades\Log::warning("AccessLog: Tentativa de log para estação base desconhecida. Base MAC: {$validatedData['base_station_id']}, Veículo: {$validatedData['vehicle_lora_id']}");
            // Decidir se retorna erro ou cria o log sem barrier_id (se a coluna for nullable).
            // Pela migration, barrier_id é constrained, então não pode ser null. Retornar erro.
            return response()->json(['message' => 'Base station (barrier) not found for the provided MAC address.'], 404);
        }

        // Adicionar barrier_id aos dados para criação do log
        $dataToCreate = $validatedData;
        $dataToCreate['barrier_id'] = $barrier->id;

        try {
            $log = AccessLog::create($dataToCreate);
            return response()->json(['message' => 'Access log created successfully', 'log_id' => $log->id], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // A validação do Laravel já retorna uma resposta JSON formatada em caso de falha na API.
            // Mas se quisermos logar explicitamente:
            \Illuminate\Support\Facades\Log::warning('Falha na validação ao criar log de acesso: ' . $e->getMessage() . ' Data: ' . json_encode($request->all()));
            throw $e; // Relança para o Laravel lidar com a resposta JSON.
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Falha ao criar log de acesso: ' . $e->getMessage() . ' Data: ' . json_encode($validatedData));
            return response()->json(['message' => 'Falha ao criar log de acesso no servidor.', 'error' => $e->getMessage()], 500);
        }
    }
}
