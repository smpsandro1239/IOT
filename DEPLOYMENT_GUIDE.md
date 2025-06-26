# Guia de Implementação: Do Repositório GitHub à Produção

Este tutorial assume um ambiente de servidor Linux típico (ex: Ubuntu) com Nginx, PHP (com extensões relevantes para Laravel como mbstring, xml, curl, etc.), Composer, e uma base de dados (ex: MySQL/PostgreSQL).

**1. Pré-requisitos do Servidor:**
    *   Servidor web (Nginx recomendado)
    *   PHP (versão compatível com o projeto Laravel, verificar `composer.json`)
    *   Extensões PHP necessárias (listar as principais: `php-fpm`, `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-bcmath`, `php-tokenizer`, `php-gd` se houver manipulação de imagens)
    *   Composer (gestor de dependências PHP)
    *   Git
    *   Base de dados (MySQL, PostgreSQL, etc.)
    *   Node.js e npm/yarn (se o frontend usar assets que precisam ser compilados)
    *   Acesso SSH ao servidor

**2. Clonar o Repositório:**
    *   Navegar para o diretório onde a aplicação será alojada (ex: `/var/www/`).
    *   `git clone <URL_DO_REPOSITORIO_GITHUB> nome_do_projeto`
    *   `cd nome_do_projeto`

**3. Configuração Inicial do Laravel:**
    *   **Instalar Dependências PHP:**
        *   `composer install --optimize-autoloader --no-dev` (para produção, `--no-dev` é importante)
    *   **Ficheiro de Ambiente (`.env`):**
        *   Copiar o ficheiro de exemplo: `cp .env.example .env`
        *   Gerar a chave da aplicação: `php artisan key:generate`
        *   Editar o `.env` e configurar:
            *   `APP_NAME`: Nome da sua aplicação.
            *   `APP_ENV`: `production`
            *   `APP_DEBUG`: `false` (MUITO IMPORTANTE para produção)
            *   `APP_URL`: URL completo da sua aplicação (ex: `https://seusite.com`)
            *   **Configurações da Base de Dados:**
                *   `DB_CONNECTION`: `mysql` (ou `pgsql`, etc.)
                *   `DB_HOST`: Endereço do servidor da base de dados (ex: `127.0.0.1`)
                *   `DB_PORT`: Porta da base de dados (ex: `3306`)
                *   `DB_DATABASE`: Nome da base de dados.
                *   `DB_USERNAME`: Utilizador da base de dados.
                *   `DB_PASSWORD`: Senha do utilizador da base de dados.
            *   **Outras Configurações:**
                *   `MAIL_MAILER`, `MAIL_HOST`, etc. (se for usar envio de emails)
                *   Configurações de Cache, Sessão, Filas (se aplicável).
    *   **Permissões de Diretório:**
        *   Garantir que o servidor web tem permissão de escrita nos diretórios `storage` e `bootstrap/cache`.
        *   `chown -R www-data:www-data storage bootstrap/cache` (ajustar `www-data` se o seu servidor web usar um utilizador diferente)
        *   `chmod -R 775 storage bootstrap/cache` (ou 755 dependendo da configuração do servidor)
    *   **Otimizações de Produção:**
        *   `php artisan config:cache`
        *   `php artisan route:cache`
        *   `php artisan view:cache` (se aplicável)
        *   `php artisan event:cache` (se usar eventos)
    *   **Executar Migrations e Seeders:**
        *   `php artisan migrate --force` (o `--force` é para produção, para não pedir confirmação)
        *   `php artisan db:seed --class=DatabaseSeeder` (ou seeders específicos, se necessário)
            *   Isto irá criar os papéis, permissões e o utilizador SuperAdmin inicial.

**4. Compilação de Assets Frontend (se aplicável):**
    *   Se o projeto usar Laravel Mix/Vite para gerir CSS/JS:
        *   `npm install` (ou `yarn install`)
        *   `npm run build` (ou `yarn build`)

**5. Configuração do Servidor Web (Nginx Exemplo):**
    *   Criar um ficheiro de configuração do Nginx para o seu site (ex: `/etc/nginx/sites-available/seusite.conf`):
        ```nginx
        server {
            listen 80;
            server_name seusite.com www.seusite.com; # Substituir pelo seu domínio
            root /var/www/nome_do_projeto/public; # Caminho para o diretório public do Laravel

            add_header X-Frame-Options "SAMEORIGIN";
            add_header X-Content-Type-Options "nosniff";

            index index.php index.html index.htm;

            charset utf-8;

            location / {
                try_files $uri $uri/ /index.php?$query_string;
            }

            location = /favicon.ico { access_log off; log_not_found off; }
            location = /robots.txt  { access_log off; log_not_found off; }

            error_page 404 /index.php;

            location ~ \.php$ {
                fastcgi_pass unix:/var/run/php/phpX.Y-fpm.sock; # Ajustar X.Y para a sua versão do PHP-FPM
                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                include fastcgi_params;
            }

            location ~ /\.(?!well-known).* {
                deny all;
            }
        }
        ```
    *   Criar um link simbólico: `sudo ln -s /etc/nginx/sites-available/seusite.conf /etc/nginx/sites-enabled/`
    *   Testar a configuração do Nginx: `sudo nginx -t`
    *   Reiniciar o Nginx: `sudo systemctl restart nginx`

**6. Configurar HTTPS (Certificado SSL - Recomendado):**
    *   Usar Certbot com Let's Encrypt:
        *   `sudo apt install certbot python3-certbot-nginx`
        *   `sudo certbot --nginx -d seusite.com -d www.seusite.com` (seguir as instruções)
    *   Certbot irá modificar a configuração do Nginx para HTTPS e configurar a renovação automática.

**7. Tarefas Agendadas (Cron Jobs):**
    *   Se o Laravel usar o Scheduler para tarefas (ex: `php artisan schedule:run`), configurar um cron job:
        *   `crontab -e`
        *   Adicionar a linha: `* * * * * cd /var/www/nome_do_projeto && php artisan schedule:run >> /dev/null 2>&1`

**8. Configurar Filas (Queues) - Opcional:**
    *   Se a aplicação usar filas para tarefas em background:
        *   Configurar o driver da fila no `.env` (ex: `database`, `redis`, `sqs`).
        *   Configurar um supervisor (como Supervisor) para manter os workers da fila a correr: `php artisan queue:work`.
        *   Exemplo de configuração do Supervisor:
            ```ini
            [program:laravel-worker]
            process_name=%(program_name)s_%(process_num)02d
            command=php /var/www/nome_do_projeto/artisan queue:work <connection> --sleep=3 --tries=3 --max-time=3600
            autostart=true
            autorestart=true
            user=www-data ; ou o seu user
            numprocs=8   ; número de workers
            redirect_stderr=true
            stdout_logfile=/var/www/nome_do_projeto/storage/logs/worker.log
            stopwaitsecs=3600
            ```

**9. Acesso Inicial e Verificações:**
    *   Aceder ao URL da sua aplicação no browser.
    *   Login com o utilizador SuperAdmin (as credenciais seriam definidas nos Seeders, ex: `superadmin@example.com` / `password`).
    *   Verificar funcionalidades chave.
    *   Verificar logs (`storage/logs/laravel.log`) para quaisquer erros.

**10. Manutenção e Atualizações:**
    *   Para atualizar a aplicação:
        *   `cd /var/www/nome_do_projeto`
        *   `git pull origin main` (ou o seu branch de produção)
        *   `composer install --optimize-autoloader --no-dev`
        *   `php artisan migrate --force` (se houver novas migrations)
        *   `php artisan config:cache`
        *   `php artisan route:cache`
        *   `php artisan view:cache`
        *   `npm run build` (se assets frontend mudaram)
        *   (Opcional) `php artisan up` depois de `php artisan down` para modo de manutenção.
