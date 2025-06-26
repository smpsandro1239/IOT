<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Company; // Para listar empresas no formulário de criação/edição
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Para regras de validação mais complexas

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // SuperAdmin pode ver todos, CompanyAdmin os da sua empresa, SiteManager os seus designados.
        // A query será adaptada no passo 4. Aqui, verificamos a intenção de ver.
        // Gate::before trata SuperAdmin. CompanyAdmin e SiteManager precisam da permissão específica.
        if (auth()->user()->hasRole('company-admin')) {
            $this->authorize('sites:view-own-company');
        } elseif (auth()->user()->hasRole('site-manager')) {
            $this->authorize('sites:view-assigned');
        } else {
            $this->authorize('sites:view-any'); // Para SuperAdmin (coberto por Gate::before) ou outros papéis
        }

        $user = auth()->user();
        $query = Site::with('company'); // orderBy será adicionado depois

        if ($user->isSuperAdmin()) {
            // Nenhuma restrição de query para SuperAdmin
            $companies_for_filter = Company::orderBy('name')->get();
        } elseif ($user->hasRole('company-admin') && $user->company_id) {
            $query->where('company_id', $user->company_id);
            $companies_for_filter = Company::where('id', $user->company_id)->orderBy('name')->get();
        } elseif ($user->hasRole('site-manager') && $user->company_id) {
            // SiteManager vê sites da sua company_id.
            // Idealmente, filtraria por sites especificamente atribuídos a ele.
            // $query->whereIn('id', $user->getManagedSiteIds()); // Se existisse tal método
            $query->where('company_id', $user->company_id);
            $companies_for_filter = Company::where('id', $user->company_id)->orderBy('name')->get();
        } else {
            // Se não for nenhum dos acima, não deve ver nada ou a autorização já barrou.
            // Para segurança, retornamos uma query vazia se chegar aqui por engano.
            $query->whereRaw('1 = 0'); // Condição sempre falsa
            $companies_for_filter = collect();
        }

        // Aplicar filtros do request
        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }

        // Se o utilizador NÃO é SuperAdmin, o company_filter deve ser ignorado ou validado
        // para ser igual ao company_id do utilizador, pois a query já está no escopo.
        // Se for SuperAdmin, o filtro de empresa é permitido.
        if ($request->filled('company_filter')) {
            if ($user->isSuperAdmin()) {
                $query->where('company_id', $request->company_filter);
            } elseif (($user->hasRole('company-admin') || $user->hasRole('site-manager')) && $user->company_id) {
                // Se o filtro da empresa não for o do próprio utilizador, é uma tentativa inválida.
                // A query principal já está no escopo, então este filtro é mais para UI.
                // Se o company_filter for diferente do user->company_id, pode ser um erro ou tentativa de acesso indevido.
                // No entanto, a query base $query->where('company_id', $user->company_id) já protege.
                // Apenas precisamos garantir que $companies_for_filter está correto.
                // O $request->company_filter pode ser usado para o breadcrumb se for válido.
                if ($request->company_filter != $user->company_id) {
                    // Logar tentativa ou simplesmente ignorar o filtro, pois a query base já protege.
                    // Para a listagem de sites, a query base é o que importa.
                }
            }
        }

        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $query->orderBy('company_id')->orderBy('name', 'asc'); // Adicionar ordenação aqui
        $sites = $query->withCount('barriers')->paginate(15)->withQueryString();

        $currentCompanyFromController = null;
        // Usamos company_filter porque é o que o filtro da página usa.
        // O link de 'companies.index' usa 'company_id', mas o request()->filled('company_id') no blade
        // deve ser atualizado para request()->filled('company_filter') ou unificar o nome do parâmetro.
        // Por agora, vamos assumir que o filtro da página 'company_filter' é o que queremos para o título/breadcrumb.
        // Ou melhor, vamos verificar os dois, dando prioridade ao filtro explícito.
        $companyIdForBreadcrumb = $request->input('company_filter', $request->input('company_id'));

        if ($user->isSuperAdmin()) {
            if ($companyIdForBreadcrumb) {
                $currentCompanyFromController = Company::find($companyIdForBreadcrumb);
            }
        } else if ($user->company_id) {
            // Para CompanyAdmin/SiteManager, currentCompany é sempre a sua.
            // O company_filter no request pode ser usado se for igual ao user->company_id, caso contrário, é a empresa do user.
            if ($companyIdForBreadcrumb && $companyIdForBreadcrumb == $user->company_id) {
                 $currentCompanyFromController = Company::find($user->company_id);
            } else {
                 $currentCompanyFromController = Company::find($user->company_id); // Default para a empresa do user
            }
             // Se $companies_for_filter só tem uma empresa, podemos usá-la.
            if ($companies_for_filter->count() === 1) {
                $currentCompanyFromController = $companies_for_filter->first();
            }

        }


        return view('admin.sites.index', compact('sites', 'companies_for_filter', 'currentCompanyFromController'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Apenas CompanyAdmin (para sua empresa) ou SuperAdmin podem criar sites.
        // O Gate::before trata SuperAdmin. CompanyAdmin precisa de 'sites:create-own-company'.
        // A lógica de qual company_id usar no form será tratada na view ou aqui.
        $this->authorize('sites:create-own-company'); // CompanyAdmin
                                                    // SuperAdmin passará devido ao Gate::before

        $companies = Company::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        // Para CompanyAdmin, idealmente filtraríamos $companies para mostrar apenas a sua.
        // Esta lógica será adicionada no Passo 4 (Adaptar Queries/Controladores).
        if (auth()->user()->hasRole('company-admin') && auth()->user()->company_id) {
            $companies = Company::where('id', auth()->user()->company_id)
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id');
        }

        return view('admin.sites.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Autorização: quem pode criar um site.
        // SuperAdmin pode (Gate::before).
        // CompanyAdmin pode criar para a sua própria empresa ('sites:create-own-company').
        $this->authorize('sites:create-own-company');

        // Validação adicional: Se for CompanyAdmin, garantir que o company_id é o seu.
        if (auth()->user()->hasRole('company-admin')) {
            if ($request->company_id != auth()->user()->company_id) {
                abort(403, 'UNAUTHORIZED ACTION. Company Admin can only create sites for their own company.');
            }
        }
        // Para SuperAdmin, company_id pode ser qualquer um válido.

        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sites')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
            ],
            'address' => 'nullable|string|max:255',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        Site::create($validatedData);
        return redirect()->route('admin.sites.index')->with('success', 'Site criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $this->authorize('sites:update-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            $this->authorize('sites:update-own-company');
        } elseif ($user->hasRole('site-manager')) {
            // Para SiteManager, precisamos verificar se este $site está entre os seus sites "assigned".
            // Esta lógica de "assigned sites" não está totalmente definida ainda (requer site_user pivot ou similar).
            // Assumindo por agora que SiteManager tem acesso a todos os sites da company_id do seu User.
            // E a permissão 'sites:update-assigned' implica que ele pode atualizar *algum* site que lhe é atribuído.
            // A verificação de propriedade específica ($user->can('update', $site) com Policy) é melhor.
            // Por agora, se ele for um site-manager da mesma empresa do site:
            if ($site->company_id !== $user->company_id) { // Assumindo que site-manager tem company_id
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            $this->authorize('sites:update-assigned');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $companies = Company::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        if ($user->hasRole('company-admin') && $user->company_id) {
            $companies = Company::where('id', $user->company_id)
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id');
        }
        // Para SiteManager, ele não deve poder mudar a empresa do site. O campo company_id deve ser read-only.

        return view('admin.sites.edit', compact('site', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $this->authorize('sites:update-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            // CompanyAdmin não deve mudar o company_id de um site para outra empresa.
            // Se o company_id estiver no request, deve ser o mesmo que o user->company_id.
            if ($request->input('company_id') && $request->input('company_id') != $user->company_id) {
                 abort(403, 'Company Admin cannot change the site to a different company.');
            }
            $this->authorize('sites:update-own-company');
        } elseif ($user->hasRole('site-manager')) {
            if ($site->company_id !== $user->company_id) { // Assumindo site-manager tem company_id e só gere sites dessa company
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            // SiteManager não deve poder mudar o company_id.
            if ($request->input('company_id') && $request->input('company_id') != $site->company_id) {
                 abort(403, 'Site Manager cannot change the company of the site.');
            }
            $this->authorize('sites:update-assigned');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $validatedData = $request->validate([
            // Se o user não for SuperAdmin, o company_id não deve ser alterável ou deve ser validado contra a sua própria company.
            'company_id' => ['required', 'exists:companies,id', function ($attribute, $value, $fail) use ($user, $site) {
                if ($user->hasRole('company-admin') && $value != $user->company_id) {
                    $fail('You can only assign sites to your own company.');
                }
                if ($user->hasRole('site-manager') && $value != $site->company_id) {
                    $fail('Site Managers cannot change the company of a site.');
                }
            }],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sites')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($site->id),
            ],
            'address' => 'nullable|string|max:255',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        $site->update($validatedData);
        return redirect()->route('admin.sites.index')->with('success', 'Site atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $this->authorize('sites:delete-any');
        } elseif ($user->hasRole('company-admin')) {
            if ($site->company_id !== $user->company_id) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            $this->authorize('sites:delete-own-company');
        } elseif ($user->hasRole('site-manager')) {
            if ($site->company_id !== $user->company_id) { // Verificação básica
                abort(403, 'UNAUTHORIZED ACTION.');
            }
            // Adicionar verificação se este site específico está atribuído ao site manager, se a lógica for mais granular.
            $this->authorize('sites:delete-assigned');
        } else {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            // onDelete('cascade') na migration de 'barriers' deve cuidar das barreiras associadas.
            $site->delete();
            return redirect()->route('admin.sites.index')->with('success', 'Site excluído com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erro de Query ao excluir site ID {$site->id}: " . $e->getMessage());
            return redirect()->route('admin.sites.index')->with('error', 'Erro ao excluir site. Verifique se há entidades associadas (barreiras, etc.) se o onDelete cascade não estiver configurado corretamente ou se houver outras restrições.');
        } catch (\Exception $e) {
            Log::error("Erro geral ao excluir site ID {$site->id}: " . $e->getMessage());
            return redirect()->route('admin.sites.index')->with('error', 'Erro ao excluir site.');
        }
    }
}
