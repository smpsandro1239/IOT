<?php

namespace App\Http\Controllers;

use App\Models\Telemetria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
use Illuminate\Support\Facades\Cache;

    public function getMetrics()
    {
        $daily = Cache::remember('metrics_daily', 60 * 60, function () {
            return Telemetria::select(DB::raw('DATE(datahora) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take(7)
                ->get()
                ->pluck('total', 'date');
        });

        $weekly = Cache::remember('metrics_weekly', 60 * 60, function () {
            return Telemetria::select(DB::raw('YEARWEEK(datahora) as week'), DB::raw('count(*) as total'))
                ->groupBy('week')
                ->orderBy('week', 'desc')
                ->take(4)
                ->get()
                ->pluck('total', 'week');
        });

        $monthly = Cache::remember('metrics_monthly', 60 * 60, function () {
            return Telemetria::select(DB::raw('MONTH(datahora) as month'), DB::raw('count(*) as total'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get()
                ->pluck('total', 'month');
        });

        return response()->json([
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
        ]);
    }

    public function getMacMetrics($mac)
    {
        $daily = Telemetria::where('mac', $mac)
            ->select(DB::raw('DATE(datahora) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->pluck('total', 'date');

        $weekly = Telemetria::where('mac', $mac)
            ->select(DB::raw('YEARWEEK(datahora) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->take(4)
            ->get()
            ->pluck('total', 'week');

        $monthly = Telemetria::where('mac', $mac)
            ->select(DB::raw('MONTH(datahora) as month'), DB::raw('count(*) as total'))
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
