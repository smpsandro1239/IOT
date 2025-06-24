<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\AccessLog;
use Illuminate\Support\Facades\DB; // Para queries mais complexas se necessário

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalVehicles = Vehicle::count();
        $authorizedVehicles = Vehicle::where('is_authorized', true)->count();
        $unauthorizedVehicles = $totalVehicles - $authorizedVehicles;

        $totalAccessLogs = AccessLog::count();

        // Contagem de acessos autorizados/negados dos logs
        $accessesByAuthorization = AccessLog::select('authorization_status', DB::raw('count(*) as total'))
                                            ->groupBy('authorization_status')
                                            ->pluck('total', 'authorization_status');

        $successfulAccesses = $accessesByAuthorization[1] ?? 0; // authorization_status = true (1)
        $failedAccesses = $accessesByAuthorization[0] ?? 0;     // authorization_status = false (0)

        // Contagem de acessos por direção
        $accessesByDirection = AccessLog::select('direction_detected', DB::raw('count(*) as total'))
                                        ->groupBy('direction_detected')
                                        ->pluck('total', 'direction_detected');

        $northSouthAccesses = $accessesByDirection['north_south'] ?? 0;
        $southNorthAccesses = $accessesByDirection['south_north'] ?? 0;
        $undefinedDirectionAccesses = $accessesByDirection['undefined'] ?? 0;
        $conflictDirectionAccesses = $accessesByDirection['conflito'] ?? 0;


        // Para gráficos futuros, poderíamos preparar dados de logs por período (ex: últimos 7 dias)
        // $accessLogsLast7Days = AccessLog::select(DB::raw('DATE(timestamp_event) as date'), DB::raw('count(*) as total'))
        //                                 ->where('timestamp_event', '>=', now()->subDays(7))
        //                                 ->groupBy('date')
        //                                 ->orderBy('date', 'asc')
        //                                 ->pluck('total', 'date');

        $metrics = [
            'totalVehicles' => $totalVehicles,
            'authorizedVehicles' => $authorizedVehicles,
            'unauthorizedVehicles' => $unauthorizedVehicles,
            'totalAccessLogs' => $totalAccessLogs,
            'successfulAccesses' => $successfulAccesses,
            'failedAccesses' => $failedAccesses,
            'northSouthAccesses' => $northSouthAccesses,
            'southNorthAccesses' => $southNorthAccesses,
            'undefinedDirectionAccesses' => $undefinedDirectionAccesses,
            'conflictDirectionAccesses' => $conflictDirectionAccesses,
            // 'accessLogsLast7Days' => $accessLogsLast7Days,
        ];

        return view('admin.dashboard.index', compact('metrics'));
    }
}
