<?php

namespace App\Http\Controllers;

use App\Models\Telemetria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function getMetrics()
    {
        $daily = Telemetria::select(DB::raw('DATE(datahora) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->pluck('total', 'date');

        $weekly = Telemetria::select(DB::raw('YEARWEEK(datahora) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->take(4)
            ->get()
            ->pluck('total', 'week');

        $monthly = Telemetria::select(DB::raw('MONTH(datahora) as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->pluck('total', 'month');

        return response()->json([
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
        ]);
    }
}
