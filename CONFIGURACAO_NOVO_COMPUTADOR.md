# 🚀 Configuração em Novo Computador

Guia completo para configurar o Sistema de Controle de Barreiras IoT em um novo computador após clonar do GitHub.

## 📋 Pré-requisitos

Antes de começar, você precisa ter instalado:

### Obrigatórios
- **PHP 8.1+** (recomendado: Laragon)
- **Composer** (gerenciador de dependências PHP)
- **Node.js LTS** (para frontend)
- **MySQL/MariaDB** (banco de dados)

### Recomendado
- **Laragon** - Inclui PHP, MySQL, Composer em um pacote
- **Git** - Para controle de versão

## 🔧 Configuração Rápida

### 1. Verificar Pré-requisitos
```batch
verificar_requisitos.bat
```

### 2. Configuração Completa
```batch
configurar_novo_computador.bat
```

### 3. Testar Configuração
```batch
teste_configuracao.bat
```

### 4. Iniciar Sistema
```batch
iniciar_sistema_otimizado.bat
```

## 📁 Scripts Disponíveis

| Script | Descrição |
|--------|-----------|
| `verificar_requisitos.bat` | Verifica se todos os pré-requisitos estão instalados |
| `configurar_novo_computador.bat` | Configuração completa do projeto |
| `iniciar_sistema_otimizado.bat` | Inicia o sistema (backend + frontend) |
| `teste_configuracao.bat` | Testa se a configuração está correta |
| `clear_cache.bat` | Limpa todos os caches do sistema |
| `reiniciar_sistema.bat` | Reinicia o sistema |

## 🛠️ Configuração Manual (se necessário)

### Backend (Laravel)
```batch
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

### Frontend
```batch
cd frontend
npm install
npm run build:css
```

## 🔐 Credenciais Padrão

- **Email:** admin@example.com
- **Senha:** password

## 🌐 URLs do Sistema

- **Frontend:** http://localhost:8080
- **Backend API:** http://localhost:8000/api

## ⚠️ Configuração do Banco de Dados

Edite o arquivo `backend/.env` com suas credenciais:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=sua_senha_aqui
```

## 🚨 Solução de Problemas

### Erro: PHP não encontrado
- Instale PHP 8.1+ ou use Laragon
- Adicione PHP ao PATH do sistema

### Erro: Composer não encontrado
- Instale Composer: https://getcomposer.org/download/
- Ou use Laragon que já inclui

### Erro: Node.js não encontrado
- Instale Node.js LTS: https://nodejs.org/

### Erro: MySQL não conecta
- Verifique se MySQL está rodando
- Confirme credenciais no arquivo .env
- Use Laragon para facilitar

### Erro: Dependências não instalam
```batch
clear_cache.bat
configurar_novo_computador.bat
```

## 🔄 Fluxo de Desenvolvimento

1. **Primeira vez:**
   ```batch
   verificar_requisitos.bat
   configurar_novo_computador.bat
   ```

2. **Uso diário:**
   ```batch
   iniciar_sistema_otimizado.bat
   ```

3. **Problemas:**
   ```batch
   clear_cache.bat
   teste_configuracao.bat
   ```

## 📊 Estrutura do Projeto

```
projeto/
├── backend/          # API Laravel
├── frontend/         # Interface web
├── base/            # Firmware ESP32 base
├── auto/            # Firmware detecção auto
├── direcao/         # Firmware detecção direção
└── *.bat            # Scripts de automação
```

## 🎯 Próximos Passos

Após a configuração:

1. Acesse http://localhost:8080
2. Faça login com as credenciais padrão
3. Teste o modo simulação
4. Configure os dispositivos ESP32 (se disponível)

## 📞 Suporte

Se encontrar problemas:

1. Execute `teste_configuracao.bat`
2. Verifique os logs em `backend/storage/logs/`
3. Consulte a documentação técnica
4. Use o modo simulação para testes

---

**Dica:** Use sempre os scripts .bat fornecidos para evitar problemas de configuração manual.