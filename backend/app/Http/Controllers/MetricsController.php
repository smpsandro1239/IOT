<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telemetria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Get general metrics for the dashboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetrics()
    {
        // Daily metrics (last 7 days)
        $dailyMetrics = $this->getDailyMetrics();

        // Weekly metrics (last 12 weeks)
        $weeklyMetrics = $this->getWeeklyMetrics();

        // Monthly metrics (last 12 months)
        $monthlyMetrics = $this->getMonthlyMetrics();

        return response()->json([
            'daily' => $dailyMetrics,
            'weekly' => $weeklyMetrics,
            'monthly' => $monthlyMetrics
        ]);
    }

    /**
     * Get metrics for a specific MAC
     *
     * @param string $mac
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMacMetrics($mac)
    {
        // Daily metrics (last 7 days)
        $dailyMetrics = $this->getDailyMetrics($mac);

        // Weekly metrics (last 12 weeks)
        $weeklyMetrics = $this->getWeeklyMetrics($mac);

        // Monthly metrics (last 12 months)
        $monthlyMetrics = $this->getMonthlyMetrics($mac);

        return response()->json([
            'mac' => $mac,
            'daily' => $dailyMetrics,
            'weekly' => $weeklyMetrics,
            'monthly' => $monthlyMetrics
        ]);
    }

    /**
     * Get daily metrics
     *
     * @param string|null $mac
     * @return array
     */
    private function getDailyMetrics($mac = null)
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $query = Telemetria::select(
            DB::raw('DATE(datahora) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('datahora', '>=', $startDate)
            ->where('datahora', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date');

        if ($mac) {
            $query->where('mac', $mac);
        }

        $results = $query->get();

        // Create array of all dates in range
        $dates = [];
        $counts = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dates[] = $current->format('d/m');

            // Find count for this date
            $found = false;
            foreach ($results as $result) {
                if ($result->date == $dateStr) {
                    $counts[] = $result->count;
                    $found = true;
                    break;
                }
            }

            // If no data for this date, add 0
            if (!$found) {
                $counts[] = 0;
            }

            $current->addDay();
        }

        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }

    /**
     * Get weekly metrics
     *
     * @param string|null $mac
     * @return array
     */
    private function getWeeklyMetrics($mac = null)
    {
        $startDate = Carbon::now()->subWeeks(11)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $query = Telemetria::select(
            DB::raw('YEARWEEK(datahora, 1) as yearweek'),
            DB::raw('COUNT(*) as count')
        )
            ->where('datahora', '>=', $startDate)
            ->where('datahora', '<=', $endDate)
            ->groupBy('yearweek')
            ->orderBy('yearweek');

        if ($mac) {
            $query->where('mac', $mac);
        }

        $results = $query->get();

        // Create array of all weeks in range
        $weeks = [];
        $counts = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $yearWeek = $current->format('YW');
            $weeks[] = 'S' . $current->weekOfYear . ' ' . $current->format('M');

            // Find count for this week
            $found = false;
            foreach ($results as $result) {
                if ($result->yearweek == $yearWeek) {
                    $counts[] = $result->count;
                    $found = true;
                    break;
                }
            }

            // If no data for this week, add 0
            if (!$found) {
                $counts[] = 0;
            }

            $current->addWeek();
        }

        return [
            'labels' => $weeks,
            'data' => $counts
        ];
    }

    /**
     * Get monthly metrics
     *
     * @param string|null $mac
     * @return array
     */
    private function getMonthlyMetrics($mac = null)
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $query = Telemetria::select(
            DB::raw('DATE_FORMAT(datahora, "%Y-%m") as yearmonth'),
            DB::raw('COUNT(*) as count')
        )
            ->where('datahora', '>=', $startDate)
            ->where('datahora', '<=', $endDate)
            ->groupBy('yearmonth')
            ->orderBy('yearmonth');

        if ($mac) {
            $query->where('mac', $mac);
        }

        $results = $query->get();

        // Create array of all months in range
        $months = [];
        $counts = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $yearMonth = $current->format('Y-m');
            $months[] = $current->format('M Y');

            // Find count for this month
            $found = false;
            foreach ($results as $result) {
                if ($result->yearmonth == $yearMonth) {
                    $counts[] = $result->count;
                    $found = true;
                    break;
                }
            }

            // If no data for this month, add 0
            if (!$found) {
                $counts[] = 0;
            }

            $current->addMonth();
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }
}
