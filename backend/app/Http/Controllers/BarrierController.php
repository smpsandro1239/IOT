<?php

namespace App\Http\Controllers;

use App\Models\Barrier;
use App\Models\Site; // Para listar sites no formulário e filtros
use App\Models\Company; // Para filtro hierárquico
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Barrier::with('site.company')->orderBy('site_id')->orderBy('name', 'asc');

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('base_station_mac_filter')) {
            $query->where('base_station_mac_address', 'like', '%' . $request->base_station_mac_filter . '%');
        }
        if ($request->filled('site_filter')) {
            $query->where('site_id', $request->site_filter);
        }
        // Filtro por empresa (indireto, através do site)
        if ($request->filled('company_filter')) {
            $companyId = $request->company_filter;
            $query->whereHas('site', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $barriers = $query->withCount('accessLogs')->paginate(15)->withQueryString(); // Adiciona contagem de logs
        $sites_for_filter = Site::orderBy('name')->get(); // TODO: Considerar filtrar sites pela empresa selecionada, se houver
        $companies_for_filter = Company::orderBy('name')->get();

        $currentSiteFromController = null;
        $currentCompanyFromController = null;

        // Prioriza o filtro de site, pois ele contém a empresa
        $siteIdParam = $request->input('site_filter', $request->input('site_id'));
        if ($siteIdParam) {
            $currentSiteFromController = Site::with('company')->find($siteIdParam);
            if ($currentSiteFromController) {
                $currentCompanyFromController = $currentSiteFromController->company;
            }
        } else {
            // Se não há filtro de site, verifica se há filtro de empresa
            $companyIdParam = $request->input('company_filter', $request->input('company_id'));
            if ($companyIdParam) {
                $currentCompanyFromController = Company::find($companyIdParam);
            }
        }

        return view('admin.barriers.index', compact('barriers', 'sites_for_filter', 'companies_for_filter', 'currentSiteFromController', 'currentCompanyFromController'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites_grouped = Company::with(['sites' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->where('is_active', true)->orderBy('name')->get()
        ->mapWithKeys(function ($company) {
            return [$company->name => $company->sites->pluck('name', 'id')];
        })->filter(function ($sitesInCompany) { // Renomeado para clareza
            return $sitesInCompany->isNotEmpty();
        });

        return view('admin.barriers.create', compact('sites_grouped'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('barriers')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                }),
            ],
            'base_station_mac_address' => 'nullable|string|max:17|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|unique:barriers,base_station_mac_address',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        Barrier::create($validatedData);
        return redirect()->route('admin.barriers.index')->with('success', 'Barreira criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barrier $barrier)
    {
        $sites_grouped = Company::with(['sites' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->where('is_active', true)->orderBy('name')->get()
        ->mapWithKeys(function ($company) {
            return [$company->name => $company->sites->pluck('name', 'id')];
        })->filter(function ($sitesInCompany) { // Renomeado para clareza
            return $sitesInCompany->isNotEmpty();
        });
        return view('admin.barriers.edit', compact('barrier', 'sites_grouped'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barrier $barrier)
    {
        $validatedData = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('barriers')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                })->ignore($barrier->id),
            ],
            'base_station_mac_address' => 'nullable|string|max:17|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/|unique:barriers,base_station_mac_address,' . $barrier->id,
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        $barrier->update($validatedData);
        return redirect()->route('admin.barriers.index')->with('success', 'Barreira atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barrier $barrier)
    {
        try {
            $barrier->delete();
            return redirect()->route('admin.barriers.index')->with('success', 'Barreira excluída com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erro de Query ao excluir barreira ID {$barrier->id}: " . $e->getMessage());
            // onDelete('cascade') não está definido para access_logs referenciando barriers, então pode falhar aqui.
            return redirect()->route('admin.barriers.index')->with('error', 'Erro ao excluir barreira. Verifique se há logs de acesso associados.');
        } catch (\Exception $e) {
            Log::error("Erro geral ao excluir barreira ID {$barrier->id}: " . $e->getMessage());
            return redirect()->route('admin.barriers.index')->with('error', 'Erro ao excluir barreira.');
        }
    }
}
