<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Site;
use App\Models\Barrier;
use Illuminate\Support\Arr;

class DataController extends Controller
{
    public function getSitesForCompanies(Request $request, $company_ids_str)
    {
        $company_ids = explode(',', $company_ids_str);
        $company_ids = array_filter(array_map('intval', $company_ids)); // Sanitizar

        if (empty($company_ids)) {
            return response()->json([]);
        }

        $sites = Site::whereIn('company_id', $company_ids)
                     ->where('is_active', true) // Considerar apenas ativos
                     ->orderBy('name')
                     ->get(['id', 'name', 'company_id']); // Incluir company_id para possível agrupamento no frontend

        return response()->json($sites);
    }

    public function getBarriersForSites(Request $request, $site_ids_str)
    {
        $site_ids = explode(',', $site_ids_str);
        $site_ids = array_filter(array_map('intval', $site_ids)); // Sanitizar

        if (empty($site_ids)) {
            return response()->json([]);
        }

        $barriers = Barrier::whereIn('site_id', $site_ids)
                           ->where('is_active', true) // Considerar apenas ativos
                           ->orderBy('name')
                           ->get(['id', 'name', 'site_id']); // Incluir site_id para possível agrupamento

        return response()->json($barriers);
    }
}
