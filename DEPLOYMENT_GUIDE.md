# Guia de Implantação - Sistema de Controle de Barreiras IoT

Este guia fornece instruções detalhadas para implantar o Sistema de Controle de Barreiras IoT em um ambiente de produção.

## 1. Requisitos de Servidor

### 1.1. Servidor Web

- **Sistema Operacional:** Ubuntu 22.04 LTS ou superior
- **CPU:** 2+ núcleos
- **RAM:** 4GB+ (recomendado 8GB)
- **Armazenamento:** 20GB+ SSD
- **Largura de banda:** 10Mbps+ (para comunicação com dispositivos IoT)

### 1.2. Banco de Dados

- MySQL 8.0+ ou MariaDB 10.5+
- Armazenamento: 10GB+ (dependendo do volume de logs esperado)

### 1.3. Requisitos de Software

- PHP 8.1+
- Composer 2.0+
- Node.js 16+ (para compilação de assets)
- Nginx ou Apache
- Supervisor (para gerenciar processos em segundo plano)
- Redis (opcional, para cache e filas)

## 2. Configuração do Servidor

### 2.1. Instalação de Dependências

```bash
# Atualizar pacotes
sudo apt update
sudo apt upgrade -y

# Instalar PHP e extensões necessárias
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath

# Instalar Nginx
sudo apt install -y nginx

# Instalar MySQL
sudo apt install -y mysql-server

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar Supervisor
sudo apt install -y supervisor

# Instalar Redis (opcional)
sudo apt install -y redis-server
```

### 2.2. Configuração do Banco de Dados

```bash
# Acessar MySQL
sudo mysql

# Criar banco de dados e usuário
CREATE DATABASE laravel_barrier_control;
CREATE USER 'barrier_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON laravel_barrier_control.* TO 'barrier_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.3. Configuração do Nginx

Crie um arquivo de configuração para o site:

```bash
sudo nano /etc/nginx/sites-available/barrier-control
```

Adicione a seguinte configuração:

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/barrier-control/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

server {
    listen 80;
    server_name frontend.seu-dominio.com;
    root /var/www/barrier-control/frontend;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative a configuração:

```bash
sudo ln -s /etc/nginx/sites-available/barrier-control /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 2.4. Configuração do SSL (Recomendado)

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obter certificado SSL
sudo certbot --nginx -d seu-dominio.com -d frontend.seu-dominio.com
```

## 3. Implantação do Código

### 3.1. Clonar o Repositório

```bash
# Criar diretório para a aplicação
sudo mkdir -p /var/www/barrier-control
sudo chown -R $USER:$USER /var/www/barrier-control

# Clonar o repositório
git clone https://github.com/seu-usuario/controle-barreiras-iot.git /var/www/barrier-control
cd /var/www/barrier-control
```

### 3.2. Configuração do Backend

```bash
cd /var/www/barrier-control/backend

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Configurar ambiente
cp .env.example .env
nano .env
```

Edite o arquivo `.env` com as configurações apropriadas:

```
APP_NAME="Sistema de Controle de Barreiras"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://seu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=barrier_user
DB_PASSWORD=senha_segura

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

PUSHER_APP_ID=iot-app
PUSHER_APP_KEY=iot-key
PUSHER_APP_SECRET=iot-secret-production
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

Gere a chave da aplicação e execute as migrações:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.3. Configuração do Frontend

```bash
cd /var/www/barrier-control/frontend

# Atualizar URLs da API no frontend
nano js/api-client.js
```

Atualize a URL base da API para apontar para seu domínio:

```javascript
constructor(baseUrl = 'https://seu-dominio.com/api/v1') {
```

### 3.4. Configuração do Supervisor

Crie um arquivo de configuração para o WebSocket:

```bash
sudo nano /etc/supervisor/conf.d/websockets.conf
```

Adicione a seguinte configuração:

```ini
[program:websockets]
command=php /var/www/barrier-control/backend/artisan websockets:serve
numprocs=1
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/barrier-control/backend/storage/logs/websockets.log
```

Reinicie o Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start websockets
```

### 3.5. Configuração de Permissões

```bash
# Definir permissões corretas
sudo chown -R www-data:www-data /var/www/barrier-control
sudo find /var/www/barrier-control -type f -exec chmod 644 {} \;
sudo find /var/www/barrier-control -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/barrier-control/backend/storage
sudo chmod -R 775 /var/www/barrier-control/backend/bootstrap/cache
```

## 4. Configuração de Firewall

```bash
# Permitir tráfego HTTP, HTTPS e WebSocket
sudo ufw allow 'Nginx Full'
sudo ufw allow 6001/tcp
sudo ufw enable
```

## 5. Configuração de Cron

Configure o cron do Laravel para executar tarefas agendadas:

```bash
sudo crontab -e
```

Adicione a seguinte linha:

```
* * * * * cd /var/www/barrier-control/backend && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Monitoramento e Logs

### 6.1. Configuração de Logs

Os logs do Laravel estão disponíveis em:

```
/var/www/barrier-control/backend/storage/logs/laravel.log
```

Considere configurar a rotação de logs:

```bash
sudo nano /etc/logrotate.d/barrier-control
```

Adicione:

```
/var/www/barrier-control/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### 6.2. Monitoramento

Considere configurar ferramentas de monitoramento como:

- Prometheus + Grafana
- New Relic
- Laravel Telescope (para ambiente de desenvolvimento)

## 7. Backup

Configure backups regulares do banco de dados e arquivos:

```bash
# Criar script de backup
sudo nano /usr/local/bin/backup-barrier-control.sh
```

Adicione:

```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/barrier-control"

# Criar diretório de backup
mkdir -p $BACKUP_DIR

# Backup do banco de dados
mysqldump -u barrier_user -p'senha_segura' laravel_barrier_control > $BACKUP_DIR/db_$TIMESTAMP.sql

# Backup dos arquivos
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz -C /var/www barrier-control

# Remover backups antigos (manter últimos 7 dias)
find $BACKUP_DIR -name "db_*.sql" -type f -mtime +7 -delete
find $BACKUP_DIR -name "files_*.tar.gz" -type f -mtime +7 -delete
```

Torne o script executável e adicione ao cron:

```bash
sudo chmod +x /usr/local/bin/backup-barrier-control.sh
sudo crontab -e
```

Adicione:

```
0 2 * * * /usr/local/bin/backup-barrier-control.sh
```

## 8. Atualizações

Para atualizar o sistema:

```bash
cd /var/www/barrier-control

# Puxar alterações do repositório
git pull

# Atualizar backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar WebSockets
sudo supervisorctl restart websockets
```

## 9. Configuração dos Dispositivos ESP32

Atualize o firmware dos dispositivos ESP32 com as novas URLs de produção:

```cpp
const char* serverUrl = "https://seu-dominio.com/api/v1/access-logs";
const char* authUrl = "https://seu-dominio.com/api/v1/macs-autorizados/authorize";
```

## 10. Solução de Problemas

### 10.1. Verificar Status dos Serviços

```bash
# Verificar status do Nginx
sudo systemctl status nginx

# Verificar status do PHP-FPM
sudo systemctl status php8.1-fpm

# Verificar status do MySQL
sudo systemctl status mysql

# Verificar status do WebSocket
sudo supervisorctl status websockets
```

### 10.2. Verificar Logs

```bash
# Logs do Nginx
sudo tail -f /var/log/nginx/error.log

# Logs do Laravel
sudo tail -f /var/www/barrier-control/backend/storage/logs/laravel.log

# Logs do WebSocket
sudo tail -f /var/www/barrier-control/backend/storage/logs/websockets.log
```

### 10.3. Problemas Comuns

- **Erro 502 Bad Gateway**: Verifique se o PHP-FPM está em execução e configurado corretamente.
- **WebSocket não conecta**: Verifique se a porta 6001 está aberta e se o Supervisor está executando o serviço.
- **Erros de permissão**: Verifique as permissões dos diretórios `storage` e `bootstrap/cache`.

## 11. Considerações de Segurança

- Mantenha todos os pacotes atualizados regularmente
- Configure um firewall adequado
- Use HTTPS para todas as comunicações
- Implemente limites de taxa para a API
- Considere usar um WAF (Web Application Firewall)
- Revise regularmente os logs em busca de atividades suspeitas
- Implemente autenticação de dois fatores para contas administrativas
