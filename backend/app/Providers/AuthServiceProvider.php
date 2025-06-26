<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Permission;
use App\Models\Company;
use App\Models\Site;
use App\Models\Barrier;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
            return null;
        });

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                Permission::all()->each(function (Permission $permission) {
                    Gate::define($permission->name, function (User $user) use ($permission) {
                        return $user->hasPermissionTo($permission->name);
                    });
                });
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AuthServiceProvider: Could not register dynamic gates: ' . $e->getMessage());
        }

        // Gate para 'create' genérico, pode ser mais específico se necessário
        Gate::define('create', function(User $user, string $modelClass, $related_model_id = null) {
            if ($modelClass === Site::class) { // Criar Site
                if ($user->isSuperAdmin() && $user->hasPermissionTo('sites:create-any')) {
                    return true;
                }
                // $related_model_id aqui seria company_id
                if ($user->hasRole('company-admin') && $user->company_id == $related_model_id && $user->hasPermissionTo('sites:create-own-company')) {
                    return true;
                }
                return false;
            }
            if ($model instanceof Vehicle) {
                // SuperAdmin pode editar detalhes do veículo E atribuir quaisquer permissões
                if ($user->isSuperAdmin() && ($user->hasPermissionTo('vehicles:update-any') || $user->hasPermissionTo('vehicle-permissions:assign-to-any-company'))) {
                    return true;
                }
                // CompanyAdmin pode atribuir permissões para sua empresa
                if ($user->hasRole('company-admin') && $user->hasPermissionTo('vehicle-permissions:assign-to-own-company')) {
                    return true;
                }
                // SiteManager pode atribuir permissões para seus sites/barreiras
                if ($user->hasRole('site-manager') && $user->hasPermissionTo('vehicle-permissions:assign-to-assigned-site')) {
                    return true;
                }
                return false;
            }
            if ($modelClass === Barrier::class) { // Criar Barrier
                // $related_model_id aqui seria site_id
                $site = Site::find($related_model_id);
                if (!$site) return false;

                if ($user->isSuperAdmin() && $user->hasPermissionTo('barriers:create-any')) {
                    return true;
                }
                if ($user->hasRole('company-admin') && $site->company_id === $user->company_id && $user->hasPermissionTo('barriers:create-own-company-site')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $site->company_id === $user->company_id && $user->hasPermissionTo('barriers:create-assigned-site')) {
                    // Simplificado: SiteManager pode criar barreira em qualquer site da sua empresa.
                    return true;
                }
                return false;
            }
            // Adicionar outras lógicas de 'create' aqui se necessário (ex: Vehicle)
            if ($modelClass === Vehicle::class) { // Criar Vehicle (registo)
                // Assumindo que apenas SuperAdmin pode criar o registo de um veículo
                return $user->isSuperAdmin() && $user->hasPermissionTo('vehicles:create-any');
            }
            return null;
        });

        // Gate para 'viewAny' (listagens condicionadas por um pai)
        Gate::define('viewAny', function(User $user, string $modelClass, $parentModel = null) {
            if ($modelClass === Site::class && $parentModel instanceof Company) { // Ver Sites de uma Company
                // SuperAdmin já coberto pelo Gate::before se tiver a permissão 'sites:view-any'
                // Este Gate define a condição para CompanyAdmin ver sites da sua empresa.
                if ($user->hasRole('company-admin') && $user->company_id === $parentModel->id && $user->hasPermissionTo('sites:view-own-company')) {
                    return true;
                }
                return false;
            }
            if ($modelClass === Barrier::class && $parentModel instanceof Site) { // Ver Barriers de um Site
                 if ($user->isSuperAdmin() && $user->hasPermissionTo('barriers:view-any')) {
                    return true;
                }
                if ($user->hasRole('company-admin') && $user->company_id === $parentModel->company_id && $user->hasPermissionTo('barriers:view-own-company')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $user->company_id === $parentModel->company_id && $user->hasPermissionTo('barriers:view-assigned-site')) {
                    // Simplificado: SiteManager pode ver barreiras de qualquer site da sua empresa.
                    return true;
                }
                return false;
            }
            return null;
        });

        Gate::define('update', function (User $user, $model) {
            if ($model instanceof Company) {
                // SuperAdmin já coberto pelo Gate::before
                if ($user->hasRole('company-admin') && $user->company_id === $model->id && $user->hasPermissionTo('companies:update-own')) {
                    return true;
                }
                return false;
            }
            if ($model instanceof Site) {
                // SuperAdmin já coberto
                if ($user->hasRole('company-admin') && $user->company_id === $model->company_id && $user->hasPermissionTo('sites:update-own-company')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $user->company_id === $model->company_id && $user->hasPermissionTo('sites:update-assigned')) {
                    // Simplificado: SiteManager pode atualizar qualquer site da sua empresa se tiver a permissão.
                    return true;
                }
                return false;
            }
            if ($model instanceof Barrier) {
                // SuperAdmin já coberto
                if ($user->hasRole('company-admin') && $model->site->company_id === $user->company_id && $user->hasPermissionTo('barriers:update-own-company-site')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $model->site->company_id === $user->company_id && $user->hasPermissionTo('barriers:update-assigned-site')) {
                    // Simplificado: SiteManager pode atualizar qualquer barreira de sites da sua empresa.
                    return true;
                }
                return false;
            }
            if ($model instanceof User) { // Managed User
                // SuperAdmin já coberto
                if ($user->id === $model->id && $user->hasPermissionTo('users:update-own')) { // Editar próprio perfil
                    return true;
                }
                if ($user->hasRole('company-admin') && $user->company_id === $model->company_id && $user->hasPermissionTo('users:update-own-company-user')) {
                     return true; // Lógica de só poder editar site-managers está no controller/request validation
                }
                return false;
            }
            return null;
        });

        Gate::define('delete', function (User $user, $model) {
            if ($model instanceof Company) {
                return $user->isSuperAdmin() && $user->hasPermissionTo('companies:delete-any');
            }
            if ($model instanceof Site) {
                if ($user->isSuperAdmin() && $user->hasPermissionTo('sites:delete-any')) return true;
                if ($user->hasRole('company-admin') && $user->company_id === $model->company_id && $user->hasPermissionTo('sites:delete-own-company')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $user->company_id === $model->company_id && $user->hasPermissionTo('sites:delete-assigned')) {
                    // Simplificado: SiteManager pode excluir qualquer site da sua empresa.
                    return true;
                }
                return false;
            }
            if ($model instanceof Barrier) {
                if ($user->isSuperAdmin() && $user->hasPermissionTo('barriers:delete-any')) return true;
                if ($user->hasRole('company-admin') && $model->site->company_id === $user->company_id && $user->hasPermissionTo('barriers:delete-own-company-site')) {
                    return true;
                }
                if ($user->hasRole('site-manager') && $model->site->company_id === $user->company_id && $user->hasPermissionTo('barriers:delete-assigned-site')) {
                    // Simplificado: SiteManager pode excluir qualquer barreira de sites da sua empresa.
                    return true;
                }
                return false;
            }
            if ($model instanceof Vehicle) {
                // Apenas SuperAdmin pode excluir o registo de um veículo
                return $user->isSuperAdmin() && $user->hasPermissionTo('vehicles:delete-any');
            }
            if ($model instanceof User) { // Managed User
                if ($user->id === $model->id) { return false; }
                if ($user->isSuperAdmin() && $user->hasPermissionTo('users:delete-any')) return true;
                if ($user->hasRole('company-admin') && $user->company_id === $model->company_id && $user->hasPermissionTo('users:delete-own-company-user')) {
                    return true;
                }
                return false;
            }
            return null;
        });
    }
}
