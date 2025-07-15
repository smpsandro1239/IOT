<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telemetria;
use App\Events\TelemetriaUpdated;

class AccessLogController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mac' => 'required|string|size:12',
            'direcao' => 'nullable|string|in:NS,SN',
            'datahora' => 'required|date',
            'status' => 'required|string|in:AUTORIZADO,NEGADO,JSON INVÁLIDO',
        ]);

        $telemetria = Telemetria::create($validated);
        event(new TelemetriaUpdated($telemetria->mac, $telemetria->direcao, $telemetria->datahora, $telemetria->status));

        return response()->json(['message' => 'Telemetria registrada'], 201);
    }

    public function getLatest()
    {
        $latestLog = Telemetria::latest()->first();

        $barrierStatus = [
            'north' => ['state' => 'closed', 'last_updated' => now()],
            'south' => ['state' => 'closed', 'last_updated' => now()],
        ];

        if ($latestLog) {
            if ($latestLog->direcao === 'NS' && $latestLog->status === 'AUTORIZADO') {
                $barrierStatus['north']['state'] = 'open';
                $barrierStatus['north']['last_updated'] = $latestLog->datahora;
            } elseif ($latestLog->direcao === 'SN' && $latestLog->status === 'AUTORIZADO') {
                $barrierStatus['south']['state'] = 'open';
                $barrierStatus['south']['last_updated'] = $latestLog->datahora;
            }
        }

        return response()->json([
            'latest_log' => $latestLog,
            'barrier_status' => $barrierStatus,
            'system_status' => 'operational',
            'server_time' => now(),
        ]);
    }
}
