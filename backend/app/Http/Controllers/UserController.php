<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth; // Para obter o utilizador autenticado

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = User::with('roles', 'company'); // Eager load roles and company

        if ($user->isSuperAdmin()) {
            $this->authorize('users:view-any');
            // SuperAdmin vê todos os utilizadores
        } elseif ($user->hasRole('company-admin') && $user->company_id) {
            $this->authorize('users:view-own-company');
            // CompanyAdmin vê utilizadores da sua própria empresa
            $query->where('company_id', $user->company_id);
        } else {
            // Outros papéis não devem aceder a esta listagem geral
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        // Filtros (opcional, pode ser adicionado depois se necessário)
        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('email_filter')) {
            $query->where('email', 'like', '%' . $request->email_filter . '%');
        }
        if ($request->filled('role_filter')) {
            $role_filter_id = $request->role_filter;
            $query->whereHas('roles', function ($q) use ($role_filter_id) {
                $q->where('id', $role_filter_id);
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles_for_filter = Role::orderBy('name')->get(); // Para o dropdown de filtro de papéis

        return view('admin.users.index', compact('users', 'roles_for_filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $editable_roles = [];
        $companies = collect();

        if ($user->isSuperAdmin()) {
            $this->authorize('users:create-company-admin'); // Ou uma permissão mais genérica como 'users:create'
            // SuperAdmin pode criar CompanyAdmins (e outros SuperAdmins se houver perm para isso)
            // e atribuir-lhes qualquer papel e qualquer empresa.
            $editable_roles = Role::orderBy('name')->get();
            $companies = Company::orderBy('name')->get();
        } elseif ($user->hasRole('company-admin')) {
            $this->authorize('users:create-site-manager-own-company');
            // CompanyAdmin só pode criar SiteManagers para a sua própria empresa.
            $editable_roles = Role::where('name', 'site-manager')->get(); // Apenas o papel 'site-manager'
            $companies = Company::where('id', $user->company_id)->get(); // Apenas a sua empresa
        } else {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        return view('admin.users.create', compact('editable_roles', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $creator = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $target_role_names = Role::whereIn('id', $validatedData['role_ids'])->pluck('name');
        $new_user_company_id = $validatedData['company_id'] ?? null;

        if ($creator->isSuperAdmin()) {
            // SuperAdmin pode criar CompanyAdmins (e outros) e atribuir empresa.
            // Precisa de permissão para criar o tipo de utilizador/papel que está a tentar criar.
            // Ex: Se está a atribuir 'company-admin', precisa de 'users:create-company-admin'.
            // Esta lógica pode ser refinada com Gate::inspect ou verificações mais granulares.
            // Por agora, uma permissão genérica de criar para SuperAdmin é assumida.
            $this->authorize('users:create-company-admin'); // Exemplo, pode precisar de mais.

            if ($target_role_names->contains('company-admin') && !$new_user_company_id) {
                 return back()->withInput()->withErrors(['company_id' => 'A Company ID is required for Company Admins.']);
            }

        } elseif ($creator->hasRole('company-admin')) {
            $this->authorize('users:create-site-manager-own-company');
            // CompanyAdmin só pode criar SiteManagers.
            if (!$target_role_names->contains('site-manager') || $target_role_names->count() > 1) {
                abort(403, 'Company Admins can only create Site Managers.');
            }
            // E o SiteManager deve ser associado à empresa do CompanyAdmin.
            if ($new_user_company_id != $creator->company_id) {
                 // Forçar company_id ou dar erro se não for a do criador
                 $new_user_company_id = $creator->company_id;
                 // return back()->withInput()->withErrors(['company_id' => 'Site Managers must be assigned to your company.']);
            }
        } else {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }


        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'company_id' => $new_user_company_id,
            'email_verified_at' => now(), // Opcional: auto-verificar ou enviar email de verificação
        ]);

        $user->roles()->sync($validatedData['role_ids']);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $managedUser) // Renomeado $user para $managedUser para evitar conflito com Auth::user()
    {
        $editor = Auth::user();
        $editable_roles = collect();
        $companies = collect();

        // Lógica de autorização para editar $managedUser
        if ($editor->isSuperAdmin()) {
            $this->authorize('users:update-any');
            $editable_roles = Role::orderBy('name')->get();
            $companies = Company::orderBy('name')->get();
        } elseif ($editor->hasRole('company-admin') && $editor->company_id === $managedUser->company_id) {
            // CompanyAdmin pode editar utilizadores da sua própria empresa.
            // Só pode atribuir/alterar para o papel 'site-manager' (ou remover se for site-manager).
            $this->authorize('users:update-own-company-user', $managedUser);
            if (!$managedUser->hasRole('site-manager') && !$managedUser->roles->isEmpty()){ // Se o user já tem um papel que não é site-manager
                 abort(403, 'Company Admins can only manage Site Managers.');
            }
            $editable_roles = Role::where('name', 'site-manager')->get(); // Só pode gerir o papel de site-manager
            $companies = Company::where('id', $editor->company_id)->get(); // Só a sua empresa
        } elseif ($editor->id === $managedUser->id) {
            // Utilizador a editar o seu próprio perfil (lógica diferente, talvez rota /profile)
            $this->authorize('users:update-own', $managedUser);
            // Geralmente não se permite mudar o próprio papel ou empresa.
            $editable_roles = $managedUser->roles; // Mostra os papéis atuais, mas não permite edição por esta via.
            if($managedUser->company_id) $companies = Company::where('id', $managedUser->company_id)->get();

        } else {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        return view('admin.users.edit', compact('managedUser', 'editable_roles', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $managedUser) // Renomeado $user para $managedUser
    {
        $editor = Auth::user();

        // Validação base (nome e email)
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $managedUser->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password é opcional na atualização
            'role_ids' => ['sometimes', 'required', 'array'], // 'sometimes' para que não seja obrigatório se não se estiver a mudar papéis
            'role_ids.*' => ['exists:roles,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $target_role_ids = $request->input('role_ids', $managedUser->roles->pluck('id')->toArray());
        $target_role_names = Role::whereIn('id', $target_role_ids)->pluck('name');
        $new_company_id = $request->input('company_id', $managedUser->company_id);


        // Lógica de Autorização e Validação de Papel/Empresa
        if ($editor->isSuperAdmin()) {
            $this->authorize('users:update-any');
            if ($target_role_names->contains('company-admin') && !$new_company_id) {
                 return back()->withInput()->withErrors(['company_id' => 'A Company ID is required for Company Admins.']);
            }
            // SuperAdmin pode mudar tudo.
        } elseif ($editor->hasRole('company-admin') && $editor->company_id === $managedUser->company_id) {
            $this->authorize('users:update-own-company-user', $managedUser);
            // CompanyAdmin só pode gerir 'site-manager'
            if (!$target_role_names->contains('site-manager') || $target_role_names->filter(fn($name) => $name !== 'site-manager')->isNotEmpty()) {
                 if (!$target_role_names->isEmpty() || ($target_role_names->isEmpty() && $request->has('role_ids'))) { // Permite desatribuir o papel de site-manager
                    abort(403, 'Company Admins can only assign/unassign the Site Manager role.');
                 }
            }
            // CompanyAdmin não pode mudar a empresa do utilizador
            if ($new_company_id != $editor->company_id) {
                abort(403, 'Company Admins cannot change the company of a user.');
            }
            $new_company_id = $editor->company_id; // Garantir
        } elseif ($editor->id === $managedUser->id) {
            $this->authorize('users:update-own', $managedUser);
            // Utilizador não pode mudar o seu próprio papel ou empresa através deste form.
            // Se 'role_ids' ou 'company_id' vierem no request, devem ser ignorados ou validados para não mudar.
            if ($request->has('role_ids') && array_diff($target_role_ids, $managedUser->roles->pluck('id')->toArray())) {
                 abort(403, 'You cannot change your own roles.');
            }
            if ($request->has('company_id') && $new_company_id != $managedUser->company_id) {
                 abort(403, 'You cannot change your own company assignment.');
            }
            $target_role_ids = $managedUser->roles->pluck('id')->toArray(); // Manter os papéis atuais
            $new_company_id = $managedUser->company_id; // Manter a empresa atual
        } else {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        // Atualizar dados do utilizador
        $managedUser->name = $validatedData['name'];
        $managedUser->email = $validatedData['email'];
        if (!empty($validatedData['password'])) {
            $managedUser->password = Hash::make($validatedData['password']);
        }
        $managedUser->company_id = $new_company_id; // Atualizar company_id
        $managedUser->save();

        // Sincronizar papéis (se permitido pela lógica de autorização acima)
        if ($editor->isSuperAdmin() || ($editor->hasRole('company-admin') && $editor->company_id === $managedUser->company_id)) {
             if ($request->has('role_ids')) { // Só sincronizar se 'role_ids' foi enviado
                $managedUser->roles()->sync($target_role_ids);
             }
        }


        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $managedUser) // Renomeado $user para $managedUser
    {
        $editor = Auth::user();

        if ($managedUser->id === $editor->id) {
            abort(403, 'You cannot delete yourself.');
        }

        if ($editor->isSuperAdmin()) {
            $this->authorize('users:delete-any');
            // SuperAdmin pode excluir qualquer um (exceto ele mesmo, talvez)
        } elseif ($editor->hasRole('company-admin') && $editor->company_id === $managedUser->company_id) {
            // CompanyAdmin só pode excluir SiteManagers da sua empresa.
            if (!$managedUser->hasRole('site-manager')) {
                abort(403, 'Company Admins can only delete Site Managers from their company.');
            }
            $this->authorize('users:delete-own-company-user', $managedUser);
        } else {
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }

        // Adicional: Prevenir exclusão do último SuperAdmin? (Lógica mais complexa)

        $managedUser->roles()->detach(); // Remover associações de papéis antes de excluir
        $managedUser->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
