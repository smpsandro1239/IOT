# Histórico de Desenvolvimento e Funcionalidades Implementadas

Este documento resume as principais funcionalidades e alterações implementadas no sistema de gestão de acesso por cancelas, desde a sua concepção inicial até ao estado atual.

**Introdução:**
Este documento resume as principais funcionalidades e alterações implementadas no sistema de gestão de acesso por cancelas, desde a sua concepção inicial até ao estado atual.

**Fase 1: Estrutura Base e CRUD Inicial (Pré-histórico detalhado aqui)**
    *   Criação dos modelos: `Company`, `Site`, `Barrier`, `Vehicle`, `AccessLog`, `Firmware`, `ApiToken`.
    *   Implementação de CRUD básico (Create, Read, Update, Delete) para estas entidades através de uma interface administrativa web.
    *   Configuração inicial de rotas, controladores e views Blade.
    *   Funcionalidade de upload e gestão de firmwares para ESP32.
    *   Gestão de tokens de API para comunicação com ESP32.
    *   Endpoint de API (`/api/v1/check-authorization`) para o ESP32 verificar se um veículo (via LoRa ID) está autorizado para uma cancela específica (via MAC Address da placa base).
    *   Logging de tentativas de acesso (`AccessLog`).

**Fase 2: Melhorias na Gestão de Veículos e Permissões Iniciais**
    *   Remoção do campo booleano `is_authorized` do modelo `Vehicle`.
    *   Introdução da tabela `vehicle_permissions` para permitir atribuição granular de permissões a veículos.
        *   Um veículo pode ter permissão para uma `Company` inteira, um `Site` específico, ou uma `Barrier` individual.
    *   Atualização da interface de criação/edição de veículos para incluir checkboxes para atribuir estas permissões.
    *   Modificação do endpoint `checkAuthorization` para usar a nova tabela `vehicle_permissions`, verificando a hierarquia (Barreira -> Site -> Empresa).

**Fase 3: Interface do Utilizador (UI/UX) para Permissões de Veículos**
    *   **Dropdowns/Checkboxes Dinâmicos:** No formulário de veículos, as listas de Sites e Barreiras para atribuição de permissões são atualizadas dinamicamente via JavaScript com base nas Empresas/Sites selecionados.
        *   Criados endpoints de API (`/api/v1/companies/{ids}/sites`, `/api/v1/sites/{ids}/barriers`) para suportar esta funcionalidade.
    *   **Visualização Resumida de Permissões:** Adicionada uma coluna na listagem de veículos que mostra uma contagem de permissões por tipo (Empresa, Site, Barreira) para cada veículo (ex: "E:1 | S:2 | B:0").

**Fase 4: Implementação de Controlo de Acesso Baseado em Papéis (RBAC)**
    *   **Definição de Papéis:**
        *   `super-admin`: Acesso total global.
        *   `company-admin`: Gestão completa de uma empresa específica (locais, cancelas, veículos/permissões, utilizadores da sua empresa).
        *   `site-manager`: Gestão de todos os sites/cancelas da sua empresa associada (simplificação provisória da ideia original de gerir sites específicos).
    *   **Definição de Permissões Granulares:** Criada uma lista extensiva de permissões (ex: `companies:create`, `sites:update-own-company`, `users:assign-roles-own-company`, `vehicle-permissions:assign-to-assigned-site`).
    *   **Estrutura da Base de Dados RBAC:**
        *   Adicionada coluna `company_id` à tabela `users`.
        *   Criadas tabelas: `roles`, `permissions`, `role_user` (pivot), `permission_role` (pivot).
    *   **Modelos e Relações Eloquent:** Atualizados/criados modelos `User`, `Role`, `Permission`, `Company` com as devidas relações e métodos helper (ex: `hasRole()`, `hasPermissionTo()`).
    *   **Seeders RBAC:** Criados seeders para popular papéis, permissões, suas associações, e um utilizador SuperAdmin inicial.
    *   **Autorização com Gates:**
        *   Configurado `AuthServiceProvider` com `Gate::before` para SuperAdmin.
        *   Gates dinâmicos criados a partir das permissões na base de dados.
        *   Gates específicos definidos para ações CRUD (`create`, `update`, `delete`) e `viewAny` (para listagens condicionadas) nos modelos `Company`, `Site`, `Barrier`, `User`, `Vehicle`.
    *   **Adaptação de Controladores:**
        *   Aplicada autorização (`$this->authorize()`) nos métodos dos controladores.
        *   Queries adaptadas para filtrar dados com base no papel e `company_id` do utilizador (escopo de dados).
        *   Listas de entidades para filtros e formulários também respeitam o escopo.
    *   **Gestão de Utilizadores (CRUD):**
        *   Criado `UserController` com CRUD, lógica de autorização e escopo para SuperAdmin e CompanyAdmin (gerir SiteManagers).
        *   Views Blade para listagem, criação e edição de utilizadores.
    *   **Refinamento da UI para RBAC:**
        *   Botões de ação (Criar, Editar, Excluir) e links nas views de `Company`, `Site`, `Barrier`, `Vehicle`, `User` são condicionados usando diretivas `@can`.
        *   Formulários adaptados para mostrar/ocultar campos ou fixar valores com base no papel (ex: seleção de empresa para CompanyAdmin).
    *   **Simplificação Provisória para SiteManager:** A lógica de permissão para SiteManagers foi simplificada para permitir gestão de todos os sites/cancelas da sua empresa associada, em vez de requerer atribuição explícita a sites individuais (esta granularidade fica como melhoria futura).

**Próximos Passos / Melhorias Futuras Sugeridas (já discutidas):**
    *   Implementação completa da atribuição de sites específicos a SiteManagers.
    *   Dashboard e Relatórios Avançados.
    *   Estilização e Layout mais robusto para a área administrativa.
    *   Testes Automatizados.
    *   Documentação técnica mais aprofundada.
