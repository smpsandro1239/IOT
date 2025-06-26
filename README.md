# Sistema de Gestão de Acesso por Cancelas

Este projeto é um sistema de gestão de acesso baseado em Laravel, permitindo o controlo de cancelas através de identificação de veículos (ex: LoRa ID) e uma interface administrativa para gerir empresas, locais (sites), cancelas, veículos e permissões.

## Funcionalidades Principais

*   Gestão hierárquica: Empresas -> Locais (Sites) -> Cancelas (Barreiras).
*   Registo de Veículos e atribuição flexível de permissões de acesso (nível de Empresa, Site ou Barreira individual).
*   Controlo de Acesso Baseado em Papéis (RBAC) com três níveis iniciais:
    *   **SuperAdmin:** Controlo total do sistema.
    *   **CompanyAdmin:** Gestão de uma empresa específica, seus locais, cancelas, veículos e utilizadores (SiteManagers) dessa empresa.
    *   **SiteManager:** Gestão de todos os locais e cancelas da sua empresa associada (funcionalidade simplificada, originalmente planeada para sites específicos).
*   Interface administrativa para CRUD de todas as entidades.
*   Endpoint de API para comunicação com dispositivos ESP32 para verificação de autorização de veículos.
*   Logging de tentativas de acesso.
*   Gestão de Firmwares para dispositivos.
*   Gestão de Tokens de API.

## Documentação

*   **[Guia de Implementação (Do Clone à Produção)](DEPLOYMENT_GUIDE.md)**: Instruções detalhadas para configurar e implementar a aplicação num servidor de produção.
*   **[Histórico de Desenvolvimento e Funcionalidades](DEVELOPMENT_HISTORY.md)**: Um resumo das principais fases de desenvolvimento e funcionalidades implementadas no sistema.

## Setup Inicial (Desenvolvimento)

1.  **Clone o repositório:**
    ```bash
    git clone <URL_DO_REPOSITORIO_GITHUB> nome_do_projeto
    cd nome_do_projeto
    ```

2.  **Instale as dependências PHP:**
    ```bash
    composer install
    ```

3.  **Configure o ambiente:**
    *   Copie `.env.example` para `.env`: `cp .env.example .env`
    *   Gere a chave da aplicação: `php artisan key:generate`
    *   Configure as variáveis de ambiente no `.env` (APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD, etc.).

4.  **Execute as migrations e seeders:**
    *   `php artisan migrate`
    *   `php artisan db:seed --class=DatabaseSeeder`
        *   Isto irá criar os papéis, permissões e o utilizador SuperAdmin inicial (verifique `UserSeeder` ou `DatabaseSeeder` para as credenciais padrão, ex: `superadmin@example.com` / `password`).

5.  **(Opcional) Instale dependências frontend e compile assets (se houver):**
    ```bash
    npm install
    npm run dev # ou npm run build para produção
    ```

6.  **Inicie o servidor de desenvolvimento (se usar o servidor embutido do PHP):**
    ```bash
    php artisan serve
    ```
    A aplicação estará geralmente disponível em `http://127.0.0.1:8000`.
    A área administrativa geralmente está em `/admin`.

## Contribuições
(Adicionar informações sobre como contribuir, se aplicável)

## Licença
(Adicionar informações sobre a licença do projeto, se aplicável)
