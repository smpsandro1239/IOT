# 🚀 Guia de Instalação Completa

## 📋 Pré-requisitos Detalhados

### 🖥️ Sistema Operacional
- **Windows 10/11** (recomendado)
- **Linux Ubuntu 20.04+**
- **macOS 10.15+**

### 🛠️ Software Necessário

#### Obrigatórios
1. **PHP 8.1+**
   - [Download PHP](https://www.php.net/downloads)
   - Ou use [Laragon](https://laragon.org/download/) (recomendado para Windows)

2. **Composer**
   - [Download Composer](https://getcomposer.org/download/)
   - Já incluído no Laragon

3. **Node.js LTS**
   - [Download Node.js](https://nodejs.org/)
   - Versão 18+ recomendada

4. **MySQL/MariaDB**
   - [MySQL](https://dev.mysql.com/downloads/)
   - [MariaDB](https://mariadb.org/download/)
   - Já incluído no Laragon

5. **Git**
   - [Download Git](https://git-scm.com/downloads)

#### Opcionais (Recomendados)
- **Laragon** - Ambiente completo para Windows
- **VS Code** - Editor de código
- **Postman** - Teste de APIs
- **Arduino IDE** - Para firmware ESP32

## 🏗️ Instalação Passo a Passo

### 1️⃣ Preparação do Ambiente

#### Windows (Recomendado: Laragon)
```batch
# 1. Baixe e instale o Laragon
# https://laragon.org/download/

# 2. Inicie o Laragon
# Start All (Apache, MySQL)

# 3. Verifique se PHP está funcionando
php --version

# 4. Verifique se Composer está funcionando
composer --version
```

#### Linux (Ubuntu/Debian)
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.1
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar MySQL
sudo apt install mysql-server

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs

# Instalar Git
sudo apt install git
```

### 2️⃣ Clonagem do Projeto

```bash
# Clonar repositório
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot

# Verificar estrutura
ls -la
```

### 3️⃣ Configuração Automática (Recomendada)

#### Windows
```batch
# Verificar pré-requisitos
verificar_requisitos.bat

# Configuração completa
configurar_novo_computador_v2.bat

# Testar configuração
teste_configuracao.bat
```

#### Linux/macOS
```bash
# Dar permissões aos scripts
chmod +x scripts/*.sh

# Executar configuração
./scripts/setup_linux.sh
```

### 4️⃣ Configuração Manual (Alternativa)

#### Backend (Laravel)
```bash
cd backend

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados (edite .env)
nano .env
```

Configuração do `.env`:
```env
APP_NAME=IOT
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=sua_senha_aqui

LOG_CHANNEL=single
```

```bash
# Executar migrações
php artisan migrate:fresh --seed

# Testar servidor
php artisan serve
```

#### Frontend
```bash
cd frontend

# Instalar dependências
npm install

# Compilar CSS
npm run build:css

# Testar servidor
php -S localhost:8080
```

### 5️⃣ Configuração do Banco de Dados

#### Criação Manual
```sql
-- Conectar ao MySQL
mysql -u root -p

-- Criar banco de dados
CREATE DATABASE laravel_barrier_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário (opcional)
CREATE USER 'iot_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON laravel_barrier_control.* TO 'iot_user'@'localhost';
FLUSH PRIVILEGES;

-- Sair
EXIT;
```

#### Usando Script
```batch
# Windows
criar_banco.bat

# Linux
./scripts/create_database.sh
```

### 6️⃣ Configuração do Git (Primeira vez)

```bash
# Configurar identidade
git config --global user.name "Seu Nome"
git config --global user.email "seu.email@example.com"

# Verificar configuração
git config --list
```

## 🧪 Testes de Verificação

### 1. Teste do Backend
```bash
cd backend

# Testar conexão com banco
php artisan migrate:status

# Testar servidor
php artisan serve

# Em outro terminal, testar API
curl http://localhost:8000/api/v1/test
```

### 2. Teste do Frontend
```bash
cd frontend

# Iniciar servidor
php -S localhost:8080

# Abrir no navegador
# http://localhost:8080
```

### 3. Teste Completo
```batch
# Windows
teste_configuracao.bat

# Linux
./scripts/test_configuration.sh
```

## 🔧 Solução de Problemas Comuns

### PHP não encontrado
```bash
# Windows: Adicionar PHP ao PATH
# Ou usar Laragon que configura automaticamente

# Linux: Instalar PHP
sudo apt install php8.1-cli

# Verificar instalação
php --version
```

### Composer não encontrado
```bash
# Baixar e instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalação
composer --version
```

### Erro de conexão com banco
```bash
# Verificar se MySQL está rodando
# Windows (Laragon): Start MySQL
# Linux: sudo systemctl start mysql

# Testar conexão
mysql -u root -p

# Verificar configuração .env
cat backend/.env | grep DB_
```

### Dependências não instalam
```bash
# Limpar cache do Composer
composer clear-cache

# Reinstalar dependências
rm -rf vendor/
composer install

# Para Node.js
rm -rf node_modules/
npm install
```

### Permissões (Linux/macOS)
```bash
# Dar permissões ao storage
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache

# Proprietário correto
sudo chown -R $USER:www-data backend/storage
sudo chown -R $USER:www-data backend/bootstrap/cache
```

## 📱 Configuração do Firmware ESP32

### Pré-requisitos
- Arduino IDE 2.0+
- Biblioteca ESP32
- Bibliotecas: LoRa, WiFi, HTTPClient, ArduinoJson

### Instalação
```cpp
// 1. Abrir Arduino IDE
// 2. File > Preferences
// 3. Additional Board Manager URLs:
//    https://dl.espressif.com/dl/package_esp32_index.json

// 4. Tools > Board > Boards Manager
// 5. Pesquisar "ESP32" e instalar

// 6. Tools > Manage Libraries
// 7. Instalar: LoRa, ArduinoJson, Adafruit SSD1306
```

### Configuração
```cpp
// Editar arquivo: base/src/main.cpp

// WiFi
const char* ssid = "SUA_REDE_WIFI";
const char* password = "SUA_SENHA_WIFI";

// API
const char* api_url = "http://SEU_IP:8000/api/v1";

// LoRa
#define LORA_FREQUENCY 433E6
```

### Upload
```cpp
// 1. Conectar ESP32 via USB
// 2. Tools > Board > ESP32 Dev Module
// 3. Tools > Port > Selecionar porta COM
// 4. Sketch > Upload
```

## 🌐 Configuração de Produção

### Servidor Web (Nginx)
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/controle-barreiras-iot/backend/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### SSL (Let's Encrypt)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obter certificado
sudo certbot --nginx -d seu-dominio.com

# Renovação automática
sudo crontab -e
# Adicionar: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Otimizações Laravel
```bash
cd backend

# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Otimização geral
php artisan optimize

# Configurar supervisor para queues
sudo apt install supervisor
```

## 📊 Monitoramento

### Logs
```bash
# Laravel logs
tail -f backend/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# MySQL logs
tail -f /var/log/mysql/error.log
```

### Métricas
- CPU e RAM usage
- Disk space
- Database connections
- Response times
- Error rates

## 🔄 Backup

### Automático
```bash
#!/bin/bash
# backup.sh

# Backup do banco
mysqldump -u root -p laravel_barrier_control > backup_$(date +%Y%m%d).sql

# Backup dos arquivos
tar -czf storage_backup_$(date +%Y%m%d).tar.gz backend/storage/

# Limpar backups antigos (>30 dias)
find . -name "backup_*.sql" -mtime +30 -delete
find . -name "storage_backup_*.tar.gz" -mtime +30 -delete
```

### Cron job
```bash
# Editar crontab
crontab -e

# Backup diário às 2h
0 2 * * * /path/to/backup.sh
```

## 📞 Suporte

### Documentação
- [README.md](../README.md) - Visão geral
- [DOCUMENTACAO_TECNICA.md](DOCUMENTACAO_TECNICA.md) - Detalhes técnicos
- [API.md](API.md) - Documentação da API

### Logs de Debug
```bash
# Ativar debug no .env
APP_DEBUG=true
LOG_LEVEL=debug

# Verificar logs
tail -f backend/storage/logs/laravel.log
```

### Comunidade
- GitHub Issues
- Stack Overflow
- Laravel Community
- ESP32 Forums

---

**Instalação completa finalizada! 🎉**

Para iniciar o sistema após a instalação:
```batch
iniciar_sistema_otimizado.bat
```