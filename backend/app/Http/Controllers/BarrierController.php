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
        $user = auth()->user();
        // SuperAdmin (Gate::before), CompanyAdmin, SiteManager.
        if ($user->hasRole('company-admin')) {
            $this->authorize('barriers:view-own-company');
        } elseif ($user->hasRole('site-manager')) {
            $this->authorize('barriers:view-assigned-site');
        } else {
            $this->authorize('barriers:view-any'); // SuperAdmin ou outros papéis
        }

        $query = Barrier::with('site.company'); // orderBy será no final

        // Aplicar escopo baseado no papel
        if ($user->isSuperAdmin()) {
            $sites_for_filter = Site::orderBy('name')->get();
            $companies_for_filter = Company::orderBy('name')->get();
        } elseif ($user->hasRole('company-admin') && $user->company_id) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
            $sites_for_filter = Site::where('company_id', $user->company_id)->orderBy('name')->get();
            $companies_for_filter = Company::where('id', $user->company_id)->orderBy('name')->get();
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            // SiteManager vê barreiras de sites da sua company_id.
            // Idealmente, filtraria por sites especificamente atribuídos a ele.
            // $query->whereIn('site_id', $user->getManagedSiteIds()); // Se existisse
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
                // $q->whereIn('id', $user->getManagedSiteIds()); // Se existisse
            });
            // $sites_for_filter = Site::whereIn('id', $user->getManagedSiteIds())->orderBy('name')->get(); // Ideal
            $sites_for_filter = Site::where('company_id', $user->company_id)->orderBy('name')->get(); // Simplificado
            $companies_for_filter = Company::where('id', $user->company_id)->orderBy('name')->get();
        } else {
            $query->whereRaw('1 = 0'); // Query vazia para outros papéis
            $sites_for_filter = collect();
            $companies_for_filter = collect();
        }

        // Aplicar filtros do request
        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('base_station_mac_filter')) {
            $query->where('base_station_mac_address', 'like', '%' . $request->base_station_mac_filter . '%');
        }

        // Filtro de site_id
        if ($request->filled('site_filter')) {
            $site_filter_id = $request->site_filter;
            // Validar se o site_filter_id está dentro do escopo do utilizador (se não for SuperAdmin)
            if (!$user->isSuperAdmin() && $user->company_id) {
                $allowed_site = Site::where('id', $site_filter_id)->where('company_id', $user->company_id)->exists();
                // Para SiteManager, verificar se $site_filter_id é um dos seus sites geridos.
                if ($allowed_site) { // Simplificado: se pertence à empresa do user
                    $query->where('site_id', $site_filter_id);
                } else {
                    // Não aplicar filtro ou logar tentativa, pois a query base já protege.
                    // Para evitar resultados inesperados, pode-se até forçar a query a não retornar nada se o filtro for inválido.
                     $query->whereRaw('1 = 0'); // Filtro inválido para o escopo do user
                }
            } else if ($user->isSuperAdmin()){ // SuperAdmin pode filtrar por qualquer site
                $query->where('site_id', $site_filter_id);
            }
        }

        // Filtro de company_id (indireto)
        if ($request->filled('company_filter')) {
            $company_filter_id = $request->company_filter;
            if ($user->isSuperAdmin()) {
                $query->whereHas('site', function ($q) use ($company_filter_id) {
                    $q->where('company_id', $company_filter_id);
                });
            } elseif ($user->company_id && $company_filter_id == $user->company_id) {
                // A query base já filtra pela empresa do user, este filtro é redundante mas inofensivo se for a mesma empresa.
                // Não é preciso adicionar $query->whereHas de novo se a query base já o faz.
            } elseif ($user->company_id && $company_filter_id != $user->company_id) {
                // Tentativa de filtrar por outra empresa não permitida.
                $query->whereRaw('1 = 0'); // Filtro inválido para o escopo do user
            }
        }

        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $query->orderBy('site_id')->orderBy('name', 'asc');
        $barriers = $query->withCount('accessLogs')->paginate(15)->withQueryString();

        // Determinar currentSite e currentCompany para breadcrumbs/títulos
        $currentSiteFromController = null;
        $currentCompanyFromController = null;
        $siteIdParam = $request->input('site_filter', $request->input('site_id'));
        $companyIdParam = $request->input('company_filter', $request->input('company_id'));

        if ($user->isSuperAdmin()) {
            if ($siteIdParam) {
                $currentSiteFromController = Site::with('company')->find($siteIdParam);
                if ($currentSiteFromController) $currentCompanyFromController = $currentSiteFromController->company;
            } elseif ($companyIdParam) {
                $currentCompanyFromController = Company::find($companyIdParam);
            }
        } else if ($user->company_id) {
            // Para CompanyAdmin/SiteManager, currentCompany é sempre a sua.
            $currentCompanyFromController = $companies_for_filter->first(); // Deve ser a única na coleção
            if ($siteIdParam) {
                // Verificar se o site pertence à empresa do utilizador
                $site_check = Site::where('id', $siteIdParam)->where('company_id', $user->company_id)->first();
                if ($site_check) {
                     // Para SiteManager, verificar se é um dos seus sites geridos
                    $currentSiteFromController = $site_check;
                }
            }
            // Se não houver filtro de site, $currentSiteFromController permanece null, o que é ok.
        }

        return view('admin.barriers.index', compact('barriers', 'sites_for_filter', 'companies_for_filter', 'currentSiteFromController', 'currentCompanyFromController'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        // SuperAdmin (Gate::before), CompanyAdmin, SiteManager.
        if ($user->hasRole('company-admin')) {
            $this->authorize('barriers:create-own-company-site');
        } elseif ($user->hasRole('site-manager')) {
            $this->authorize('barriers:create-assigned-site');
        } else {
            // Para SuperAdmin, Gate::before permite. Se outro papel criar, precisa de uma permissão como 'barriers:create-any'
            // Por agora, vamos assumir que apenas os acima ou super-admin podem criar.
            // Se um super-admin está a criar, ele pode escolher qualquer site.
            // Se um company-admin, sites da sua empresa. Se site-manager, sites atribuídos.
            $this->authorize('barriers:create-own-company-site'); // Ou uma permissão mais genérica se existir
        }

        // Filtrar sites_grouped com base no papel do utilizador
        $companies_query = Company::query()->where('is_active', true);

        if ($user->hasRole('company-admin') && $user->company_id) {
            $companies_query->where('id', $user->company_id);
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            // SiteManager só deve ver sites da sua própria empresa para selecionar
            $companies_query->where('id', $user->company_id);
            // Adicionalmente, poderia filtrar para apenas os sites que ele gere, se essa lógica existir.
            // Por agora, ele pode criar barreiras em qualquer site da sua empresa.
        }
        // SuperAdmin vê todas as empresas.

        $sites_grouped = $companies_query->with(['sites' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->orderBy('name')->get()
        ->mapWithKeys(function ($company) {
            return [$company->name => $company->sites->pluck('name', 'id')];
        })->filter(function ($sitesInCompany) {
            return $sitesInCompany->isNotEmpty();
        });

        return view('admin.barriers.create', compact('sites_grouped'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $site = Site::find($request->site_id);

        if (!$site) {
            abort(404, 'Site not found.');
        }

        if ($user->isSuperAdmin()) {
            // SuperAdmin pode criar em qualquer site (Gate::before já deve ter permitido a intenção)
            // Apenas valida se ele tem uma permissão genérica de criar, se não for coberto pelo before.
            // $this->authorize('barriers:create-any'); // ou similar
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION. Company Admin can only create barriers for their own company sites.');
            }
            $this->authorize('barriers:create-own-company-site');
        } elseif ($user->hasRole('site-manager')) {
            // Assumindo que site-manager tem company_id e só pode gerir sites dessa empresa.
            // A verificação de se $site é um dos "assigned" seria mais granular aqui.
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION. Site Manager can only create barriers for their assigned company sites.');
            }
            $this->authorize('barriers:create-assigned-site');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

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
        $user = auth()->user();
        $site = $barrier->site; // O site ao qual a barreira pertence

        if ($user->isSuperAdmin()) {
            $this->authorize('barriers:update-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            $this->authorize('barriers:update-own-company-site');
        } elseif ($user->hasRole('site-manager')) {
            if ($site->company_id !== $user->company_id) { // Verificação base de empresa
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            // Adicionar lógica para verificar se $site é um dos "assigned" ao site-manager.
            $this->authorize('barriers:update-assigned-site');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        // Filtrar sites_grouped para o dropdown, semelhante ao create()
        $companies_query = Company::query()->where('is_active', true);
        if ($user->hasRole('company-admin') && $user->company_id) {
            $companies_query->where('id', $user->company_id);
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            $companies_query->where('id', $user->company_id);
            // SiteManager não deve poder mudar a barreira para um site que não gere.
            // O dropdown de sites deve ser limitado aos seus sites.
        }

        $sites_grouped = $companies_query->with(['sites' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->orderBy('name')->get()
        ->mapWithKeys(function ($company) {
            return [$company->name => $company->sites->pluck('name', 'id')];
        })->filter(function ($sitesInCompany) {
            return $sitesInCompany->isNotEmpty();
        });

        return view('admin.barriers.edit', compact('barrier', 'sites_grouped'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barrier $barrier)
    {
        $user = auth()->user();
        $requested_site = Site::find($request->site_id); // Site para o qual se quer mover/manter a barreira

        if (!$requested_site) {
            abort(404, 'Target site not found.');
        }

        // Verificar se o utilizador pode desassociar do site antigo E associar ao novo site.
        $original_site = $barrier->site;

        // 1. Pode o user editar esta barreira no seu site original?
        if ($user->isSuperAdmin()) {
            $this->authorize('barriers:update-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($original_site->company_id !== $user->company_id || $requested_site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION. Company Admin can only manage barriers within their own company sites.');
            }
            $this->authorize('barriers:update-own-company-site');
        } elseif ($user->hasRole('site-manager')) {
            // SiteManager só pode gerir barreiras nos seus sites. Não deve poder mover entre sites a menos que ambos sejam seus.
            if ($original_site->company_id !== $user->company_id || $requested_site->company_id !== $user->company_id) {
                 abort(403, 'UNAUTHORIZED ACTION. Site Manager cannot move barriers out of their company.');
            }
            // Adicionar verificação se original_site e requested_site são "assigned"
            if ($original_site->id !== $requested_site->id) { // Se está a tentar mudar de site
                 // Precisa de permissão para "tirar" do original E para "colocar" no novo.
                 // Esta lógica pode ser complexa e ideal para uma Policy.
                 // Por agora, simplificamos: se ele pode editar barreiras nos sites da sua empresa, e ambos os sites são da sua empresa.
            }
            $this->authorize('barriers:update-assigned-site');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }


        $validatedData = $request->validate([
            'site_id' => ['required', 'exists:sites,id', function ($attribute, $value, $fail) use ($user, $barrier) {
                $targetSite = Site::find($value);
                if ($user->hasRole('company-admin') && $targetSite && $targetSite->company_id != $user->company_id) {
                    $fail('You can only assign barriers to sites within your own company.');
                }
                if ($user->hasRole('site-manager')) {
                    // SiteManager só pode atribuir a um site que ele gere.
                    // Esta validação precisa da lista de sites geridos pelo SiteManager.
                    // Por agora, assumimos que ele só pode operar dentro da sua company_id.
                    if ($targetSite && $targetSite->company_id != $user->company_id) {
                        $fail('Site Managers can only assign barriers to sites within their company.');
                    }
                    // Idealmente: if (!$user->managesSite($targetSite)) { $fail(...); }
                }
            }],
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
        $user = auth()->user();
        $site = $barrier->site;

        if ($user->isSuperAdmin()) {
            $this->authorize('barriers:delete-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            $this->authorize('barriers:delete-own-company-site');
        } elseif ($user->hasRole('site-manager')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            // Adicionar verificação se $site é um dos "assigned" ao site-manager.
            $this->authorize('barriers:delete-assigned-site');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

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

    // --- API Methods for Dashboard and ESP32 ---

    /**
     * Handles manual commands from the dashboard.
     * Stores the command to be picked up by the ESP32 base station.
     */
    public function handleCommand(Request $request, Barrier $barrier)
    {
        $validated = $request->validate([
            'command' => ['required', 'string', Rule::in(['open', 'close', 'lock', 'unlock'])],
        ]);

        $command = $validated['command'];

        // Store the command in the database for the ESP32 to retrieve.
        $barrier->pending_command = $command;
        $barrier->command_last_updated_at = now();
        $barrier->save();

        Log::info("Comando '{$command}' para a barreira '{$barrier->name}' (ID: {$barrier->id}) foi armazenado na base de dados.");

        return response()->json([
            'message' => "Comando '{$command}' para a barreira '{$barrier->name}' foi registado.",
            'barrier_id' => $barrier->id,
            'command_sent' => $command,
        ]);
    }

    /**
     * Called by the ESP32 base station to check for a pending command.
     * If a command is found, it's returned and cleared from the database.
     */
    public function getPendingCommand(Request $request, Barrier $barrier)
    {
        $command = $barrier->pending_command;

        if ($command) {
            // Clear the command after retrieving it to prevent re-execution.
            $barrier->pending_command = null;
            $barrier->save();

            Log::info("Comando '{$command}' para a barreira '{$barrier->name}' (ID: {$barrier->id}) foi entregue à Placa Base.");

            return response()->json([
                'command' => $command,
                'timestamp' => $barrier->command_last_updated_at,
            ]);
        }

        // No command pending, return empty response.
        return response()->json(null, 204); // 204 No Content
    }
}
