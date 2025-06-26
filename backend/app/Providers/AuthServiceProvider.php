<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Será descomentado e usado
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // Adicionado para uso
use App\Models\User;
use App\Models\Permission; // Precisaremos disto para iterar sobre as permissões

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Exemplo padrão
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para Super Admin - concede todas as permissões
        Gate::before(function (User $user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
            return null; // Deixa outros gates decidirem
        });

        // Registrar Gates dinamicamente a partir das permissões na BD
        // É importante que as permissões já estejam na BD (via seeder) para isto funcionar na inicialização.
        // Envolver em try-catch para evitar erros durante migrations/setup inicial antes da BD estar pronta.
        try {
            // Verifica se a tabela permissions existe para evitar erros durante as migrations iniciais
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                Permission::all()->each(function (Permission $permission) {
                    Gate::define($permission->name, function (User $user) use ($permission) {
                        return $user->hasPermissionTo($permission->name);
                    });
                });
            }
        } catch (\Exception $e) {
            // Logar o erro ou lidar com ele de forma apropriada se ocorrer durante o boot
            // Pode ser que a BD não esteja acessível durante o artisan config:cache ou similar
            \Illuminate\Support\Facades\Log::error('AuthServiceProvider: Could not register dynamic gates: ' . $e->getMessage());
        }

        // NOTA: Se houver permissões que necessitem de lógica mais complexa
        // (ex: verificar se o utilizador é dono do recurso - companies:update-own),
        // elas podem precisar de Gates definidos explicitamente aqui ou através de Policies.
        // Por exemplo, para 'companies:update-own':
        // Gate::define('companies:update-own', function (User $user, \App\Models\Company $company) {
        //     // Primeiro, o utilizador tem a permissão genérica 'companies:update-own'?
        //     if (!$user->hasPermissionTo('companies:update-own')) {
        //         return false;
        //     }
        //     // Se sim, ele é o dono desta empresa específica? (se for um company-admin)
        //     if ($user->hasRole('company-admin')) {
        //         return $user->company_id === $company->id;
        //     }
        //     // Se não for company-admin mas tiver a permissão (ex: super-admin já tratado pelo Gate::before), permite.
        //     // Ou se a lógica for que SÓ company-admin pode ter 'companies:update-own', então o hasPermissionTo já filtraria.
        //     // A lógica exata aqui depende de como as permissões são atribuídas.
        //     // O Gate::before para super-admin já cobre o acesso total.
        //     // Para company-admin, o hasPermissionTo('companies:update-own') será verificado,
        //     // e a lógica de propriedade será adicionalmente verificada no controlador ou policy.
        //     // Por agora, o Gate dinâmico acima é suficiente para a verificação base da permissão.
        //     // A lógica de propriedade será melhor tratada em Policies ou diretamente nos controladores após a autorização base.
        //     return true; // Este Gate específico pode não ser necessário se a Policy tratar disso.
        // });
    }
}
