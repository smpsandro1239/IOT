<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Company::query();

        if ($user->isSuperAdmin()) {
            // Super Admin pode ver todas as empresas
            $this->authorize('companies:view-any');
            // Nenhuma restrição adicional na query
        } elseif ($user->hasRole('company-admin') && $user->company_id) {
            // Company Admin só pode ver a sua própria empresa
            $this->authorize('companies:view-own');
            $query->where('id', $user->company_id);
        } else {
            // Outros papéis (ex: Site Manager) não devem listar empresas desta forma.
            // A autorização deve apanhar isto. Se chegar aqui, é um erro de lógica de permissão.
            // Ou, se um SiteManager *pudesse* ver a empresa-mãe, seria uma lógica diferente.
            // Por agora, negamos acesso se não for SuperAdmin ou CompanyAdmin com company_id.
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
            // Alternativamente, se SiteManager tivesse 'companies:view-own' (para ver a sua empresa mãe)
            // if ($user->hasRole('site-manager') && $user->company_id) {
            //     $this->authorize('companies:view-own');
            //     $query->where('id', $user->company_id);
            // } else {
            //     abort(403, 'THIS ACTION IS UNAUTHORIZED.');
            // }
        }

        $query->orderBy('name', 'asc');

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $companies = $query->withCount('sites')->paginate(15)->withQueryString(); // Adiciona contagem de sites
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('companies:create');
        return view('admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('companies:create');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'contact_email' => 'nullable|email|max:255|unique:companies,contact_email',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        Company::create($validatedData);
        return redirect()->route('admin.companies.index')->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        // Se um company-admin só pode editar a sua, e tem a perm 'companies:update-own'
        // A Policy ou uma verificação explícita da propriedade seria ideal aqui.
        // Gate::authorize('update', $company) com uma Policy seria o mais idiomático.
        // Por agora, verificamos se ele tem a permissão e se a empresa é a dele (lógica a ser adicionada ou assumida).
        // Para o super-admin, o Gate::before já resolve.
        // Para company-admin, ele deve ter 'companies:update-own'.
        // A lógica de que $company é DELE será reforçada no passo de "Adaptar Queries".
        $this->authorize('companies:update-own', $company); // Este Gate precisa ser definido para aceitar $company ou usar Policy
                                                       // Se o Gate dinâmico não lidar com o objeto, usar apenas a string:
                                                       // $this->authorize('companies:update-own');
                                                       // E a lógica de propriedade virá da query que busca a $company.
                                                       // Por agora, vamos usar a string e a lógica de propriedade será no passo 4.
                                                       // Se um company admin tentar editar /admin/companies/OTHER_ID/edit, ele não deve nem carregar a $company.
                                                       // Se carregar, este authorize deve falhar se não for dele.
                                                       // Vamos usar a permissão geral e a Policy/lógica de query tratará da propriedade.

        // Para cobrir o caso de super-admin (update-any) e company-admin (update-own)
        // de forma mais simples com Gates dinâmicos, podemos fazer:
        if (auth()->user()->hasRole('company-admin')) {
            $this->authorize('companies:update-own', $company); // Este authorize precisa que o Gate 'companies:update-own'
                                                              // seja capaz de verificar a propriedade $user->company_id == $company->id
                                                              // Ou, mais simples, que o company_admin só consiga carregar a sua $company (filtragem de query)
                                                              // e então só precise de $this->authorize('companies:update-own');
        } else {
            $this->authorize('companies:update-any'); // Para super-admin (coberto pelo Gate::before) ou outros papéis com esta perm.
        }
        // Simplificando: o Gate::before trata o super-admin.
        // Para o company-admin, ele terá a perm 'companies:update-own'.
        // A verificação de que $company é a sua será crucial (passo 4).
        // Se ele tiver a perm e for a sua company, deve passar.
        // $this->authorize('update', $company); // Seria o ideal com uma CompanyPolicy

        // Tentativa mais direta com o que temos:
        // Se o utilizador é company-admin, ele precisa da permissão 'companies:update-own' E $company deve ser a sua.
        // Se for super-admin, o Gate::before já deu true.
        // O Gate dinâmico para 'companies:update-own' não verifica a propriedade.
        // Portanto, a autorização aqui tem que ser mais esperta ou usar uma Policy.
        // Vamos assumir que o carregamento da $company já é restrito para company-admin (Passo 4)
        // e aqui apenas verificamos a permissão de "intenção".

        // Abordagem mais segura e simples por agora:
        // O super-admin (com todas as perms) passa no Gate::before.
        // O company-admin precisa de 'companies:update-own'. A query no index/edit deve garantir que ele só edita a sua.
        // Se ele tentar um URL para editar outra company, o Route Model Binding pode falhar (404) ou
        // este authorize falharia se soubesse da propriedade.
        // Por agora, confiamos que o Gate::before e a permissão 'companies:update-own' são suficientes,
        // e a lógica de propriedade será garantida pela query que popula $company para o company-admin.
        $this->authorize('companies:update-own', $company); // Passar $company para o Gate.
                                                       // O Gate 'companies:update-own' deve ser capaz de lidar com isto.
                                                       // Se não, criar um Gate explícito ou Policy.
                                                       // Vamos REVER o AuthServiceProvider para garantir que isto funcione.

        // O AuthServiceProvider cria Gate::define($permission->name, fn...) que não usa o segundo argumento (modelo).
        // Portanto, `$this->authorize('companies:update-own', $company)` vai chamar o Gate para 'companies:update-own'
        // mas o Gate em si não usará $company. A autorização só verificará se o user TEM a string de permissão.
        // A lógica de propriedade (user->company_id == $company->id) DEVE ser feita ANTES ou DEPOIS, ou numa Policy.

        // Solução temporária mais explícita no controlador:
        if (auth()->user()->hasRole('company-admin')) {
            if (auth()->user()->company_id !== $company->id) {
                abort(403, 'THIS ACTION IS UNAUTHORIZED.'); // Ou redirect com erro
            }
            $this->authorize('companies:update-own'); // Ele tem a permissão e a empresa é dele
        } else {
            // Para super-admin, o Gate::before já tratou.
            // Se outro papel tivesse 'companies:update-any', seria aqui.
            $this->authorize('companies:update-any'); // Isto será coberto pelo Gate::before para super-admin.
                                                    // Se não houver 'companies:update-any', mas apenas 'companies:update-own'
                                                    // então um company-admin não passaria aqui se não for dele.
                                                    // Esta lógica está a ficar complexa aqui, Policies são melhores.
        }
        // A linha acima é complexa. Vamos simplificar e assumir que o carregamento da $company
        // para um company-admin já é seguro (só carrega a sua).
        // Então só precisamos de verificar a permissão.
        // E o Gate::before para super-admin trata do acesso total.

        // A melhor abordagem com o sistema de Gates atual:
        // Super-admin: Passa por Gate::before.
        // Company-admin: Precisa da permissão 'companies:update-own'. A query que lhe dá $company deve ser segura.
        // Se ele tentar editar outra $company via URL, o Route Model Binding deve falhar (se filtrado) ou esta auth falha.
        // Para que 'companies:update-own' funcione para company-admin e não para outros (exceto super-admin),
        // o Gate::define('companies:update-own', fn...) já faz isso.
        // A verificação de propriedade (user->company_id == $company->id) é o que falta aqui.
        // `$this->authorize('update', $company);` usando uma Policy seria o ideal.

        // Vamos usar a permissão 'companies:update-any' para super-admin e
        // 'companies:update-own' para company-admin. A lógica de propriedade
        // será verificada explicitamente por agora, até termos Policies.
        if ($company->id === auth()->user()->company_id || auth()->user()->isSuperAdmin() ) {
            // Se for a empresa do company_admin OU se for super_admin
             if (auth()->user()->isSuperAdmin()){
                $this->authorize('companies:update-any');
             } else {
                $this->authorize('companies:update-own');
             }
        } else {
             abort(403, 'UNAUTHORIZED ACTION.');
        }


        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Lógica similar ao edit()
        if ($company->id === auth()->user()->company_id || auth()->user()->isSuperAdmin() ) {
             if (auth()->user()->isSuperAdmin()){
                $this->authorize('companies:update-any');
             } else {
                $this->authorize('companies:update-own');
             }
        } else {
             abort(403, 'UNAUTHORIZED ACTION.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'contact_email' => 'nullable|email|max:255|unique:companies,contact_email,' . $company->id,
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        $company->update($validatedData);
        return redirect()->route('admin.companies.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // Apenas super-admin pode excluir empresas por agora, conforme RBAC_DESIGN.md
        $this->authorize('companies:delete-any');

        try {
            // onDelete('cascade') na migration de 'sites' deve cuidar dos sites e barreiras associadas.
            $company->delete();
            return redirect()->route('admin.companies.index')->with('success', 'Empresa excluída com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erro de Query ao excluir empresa ID {$company->id}: " . $e->getMessage());
            return redirect()->route('admin.companies.index')->with('error', 'Erro ao excluir empresa. Verifique se há entidades associadas (sites, etc.) se o onDelete cascade não estiver configurado corretamente ou se houver outras restrições.');
        } catch (\Exception $e) {
            Log::error("Erro geral ao excluir empresa ID {$company->id}: " . $e->getMessage());
            return redirect()->route('admin.companies.index')->with('error', 'Erro ao excluir empresa.');
        }
    }
}
