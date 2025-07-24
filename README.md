# 🚀 Sistema de Controle de Barreiras IoT

Sistema completo de controle de barreiras baseado em IoT, utilizando Laravel para o backend, HTML/JavaScript/Tailwind CSS para o frontend, e ESP32 com LoRa para comunicação entre dispositivos.

## 📸 Screenshots

### 🏠 Interface Principal
![Interface Principal](docs/images/screenshots/interface-principal.png)
*Dashboard principal com status em tempo real das barreiras e métricas do sistema*

### 🔐 Sistema de Login
![Login](docs/images/screenshots/Login.png)
*Tela de autenticação segura para acesso ao sistema*

### 📊 Dashboard Inicial
![Index](docs/images/screenshots/Index.png)
*Página inicial com visão geral do sistema e navegação principal*

### 🚗 Gestão de MACs Autorizados
![Gestão de MACs](docs/images/screenshots/gestao-macs.png)
*Interface completa para gestão de veículos autorizados com pesquisa avançada, importação CSV e validação*

### 🎯 Simulação de Radar
![Simulação de Radar](docs/images/screenshots/simulacao-radar.png)
*Simulador de radar com detecção de veículos, cálculo de AoA e visualização em tempo real*

### 📈 Métricas de Acesso
![Métricas](docs/images/screenshots/Metricas%20de%20acesso.png)
*Dashboard de métricas com gráficos de acessos, estatísticas e histórico detalhado*

### ⚙️ Configurações do Sistema
![Configurações](docs/images/screenshots/Configuracoes%20do%20sistema.png)
*Painel de configurações para personalização do sistema e parâmetros operacionais*

### 🔌 API Endpoints
![API](docs/images/screenshots/API.png)
*Documentação interativa da API com endpoints disponíveis e exemplos de uso*

## 📋 Índice

- [Screenshots](#-screenshots)
- [Características](#-características)
- [Pré-requisitos](#-pré-requisitos)
- [Configuração Rápida](#-configuração-rápida)
- [Configuração Manual](#-configuração-manual)
- [Scripts Disponíveis](#-scripts-disponíveis)
- [Credenciais](#-credenciais)
- [URLs do Sistema](#-urls-do-sistema)
- [Arquitetura](#-arquitetura)
- [Solução de Problemas](#-solução-de-problemas)
- [Desenvolvimento](#-desenvolvimento)
- [Documentação](#-documentação)
- [Contribuição](#-contribuição)
- [Licença](#-licença)

## 🎯 Características

- **Detecção de Veículos:** Detecta veículos usando LoRa e estima o Ângulo de Chegada (AoA)
- **Autorização por MAC:** Controle de acesso baseado em endereços MAC autorizados
- **Dashboard em Tempo Real:** Interface web com atualizações em tempo real
- **API RESTful:** Endpoints completos para gerenciamento do sistema
- **Modo Simulação:** Teste o sistema sem hardware físico
- **PWA:** Progressive Web App instalável em dispositivos móveis
- **Modo Offline:** Funciona offline com sincronização automática
- **Atualizações OTA:** Suporte a atualizações Over-the-Air para ESP32

## 🛠️ Pré-requisitos

### Obrigatórios
- **PHP 8.1+** (recomendado: [Laragon](https://laragon.org/download/))
- **Composer** (gerenciador de dependências PHP)
- **Node.js LTS** (para compilação do frontend)
- **MySQL/MariaDB** (banco de dados)
- **Git** (controle de versão)

### Recomendado
- **Laragon** - Ambiente completo (PHP + MySQL + Composer)

## 🚀 Configuração Rápida

### 1. Clonar o Repositório
```bash
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot
```

### 2. Verificar Pré-requisitos
```batch
verificar_requisitos.bat
```

### 3. Configuração Automática
```batch
configurar_novo_computador_v2.bat
```

### 4. Testar Configuração
```batch
teste_configuracao.bat
```

### 5. Iniciar Sistema
```batch
iniciar_sistema_otimizado.bat
```

## ⚙️ Configuração Manual

### Backend (Laravel)
```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

### Frontend
```bash
cd frontend
npm install
npm run build:css
```

### Banco de Dados
```sql
CREATE DATABASE laravel_barrier_control;
```

Configure o arquivo `backend/.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=sua_senha_aqui
```

## 📜 Scripts Disponíveis

| Script | Descrição | Uso |
|--------|-----------|-----|
| `verificar_requisitos.bat` | Verifica pré-requisitos instalados | Primeira execução |
| `configurar_novo_computador_v2.bat` | Configuração completa do projeto + Git | Novo computador |
| `iniciar_sistema_completo.bat` | Inicialização com verificações + Git | Primeira vez |
| `iniciar_sistema_otimizado.bat` | Inicia sistema com verificações | Uso diário |
| `iniciar_rapido.bat` | Inicialização rápida | Desenvolvimento |
| `teste_configuracao.bat` | Testa se tudo está funcionando | Verificação |
| `clear_cache.bat` | Limpa todos os caches | Problemas |
| `criar_banco.bat` | Cria e configura banco de dados | Configuração DB |
| `corrigir_configuracao.bat` | Corrige problemas comuns | Troubleshooting |

### Comandos de Uso Diário
```batch
# Primeira vez (inclui configuração Git)
iniciar_sistema_completo.bat

# Iniciar sistema (recomendado)
iniciar_sistema_otimizado.bat

# Iniciar rápido (desenvolvimento)
iniciar_rapido.bat

# Testar configuração
teste_configuracao.bat

# Limpar cache (se houver problemas)
clear_cache.bat
```

## 🔐 Credenciais

### Acesso ao Sistema
- **Email:** admin@example.com
- **Senha:** password

### Banco de Dados (padrão)
- **Host:** 127.0.0.1
- **Porta:** 3306
- **Database:** laravel_barrier_control
- **Username:** root
- **Password:** (configurável no .env)

## 🌐 URLs do Sistema

- **Frontend:** http://localhost:8080
- **Backend API:** http://localhost:8000/api
- **Documentação API:** http://localhost:8000/api/documentation

## 🏗️ Arquitetura

### Estrutura do Projeto
```
projeto/
├── backend/              # API Laravel
│   ├── app/             # Código da aplicação
│   ├── config/          # Configurações
│   ├── database/        # Migrações e seeders
│   └── routes/          # Rotas da API
├── frontend/            # Interface web
│   ├── css/            # Estilos compilados
│   ├── js/             # JavaScript modular
│   └── index.html      # Página principal
├── base/               # Firmware ESP32 base
├── auto/               # Firmware detecção automática
├── direcao/            # Firmware detecção direção
└── *.bat               # Scripts de automação
```

### Stack Tecnológico

#### Backend
- **Framework:** Laravel 10
- **PHP:** 8.1+
- **Banco:** MySQL 8.0+ / MariaDB 10.5+
- **Autenticação:** Laravel Sanctum
- **API:** RESTful com recursos JSON

#### Frontend
- **Base:** HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework:** Tailwind CSS
- **PWA:** Service Worker
- **Ícones:** Font Awesome
- **Charts:** Chart.js

#### Firmware
- **Plataforma:** ESP32 (Heltec WiFi LoRa 32 V2)
- **Framework:** Arduino
- **Comunicação:** LoRa, WiFi, HTTP
- **Display:** OLED SSD1306

## 🔧 Solução de Problemas

### Problemas Comuns

#### PHP não encontrado
```bash
# Instale PHP ou use Laragon
# Adicione PHP ao PATH do sistema
```

#### Composer não encontrado
```bash
# Instale: https://getcomposer.org/download/
# Ou use Laragon que já inclui
```

#### Erro de banco de dados
```bash
# 1. Verifique se MySQL está rodando (Laragon: Start All)
# 2. Configure credenciais no backend/.env
# 3. Execute: criar_banco.bat
```

#### Dependências não instalam
```batch
clear_cache.bat
configurar_novo_computador_v2.bat
```

#### Sistema não inicia
```batch
# 1. Verificar pré-requisitos
verificar_requisitos.bat

# 2. Testar configuração
teste_configuracao.bat

# 3. Corrigir problemas
corrigir_configuracao.bat
```

### Logs e Debugging
- **Laravel Logs:** `backend/storage/logs/laravel.log`
- **Frontend Console:** F12 no navegador
- **API Testing:** Use Postman ou curl

## 👨‍💻 Desenvolvimento

### Configuração do Ambiente de Desenvolvimento

#### Git (Primeira configuração)
```bash
git config --global user.email "seu-email@example.com"
git config --global user.name "Seu Nome"
```

#### Comandos Úteis

##### Backend
```bash
cd backend

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Migrações
php artisan migrate:fresh --seed

# Iniciar servidor
php artisan serve
```

##### Frontend
```bash
cd frontend

# Instalar dependências
npm install

# Compilar CSS
npm run build:css

# Monitorar alterações CSS
npm run watch:css

# Servidor local
php -S localhost:8080
```

### API Endpoints

#### Autenticação
- `POST /api/v1/login` - Login
- `POST /api/v1/logout` - Logout

#### Veículos
- `GET /api/v1/macs-autorizados` - Listar MACs autorizados
- `POST /api/v1/macs-autorizados` - Adicionar MAC
- `DELETE /api/v1/macs-autorizados/{id}` - Remover MAC

#### Telemetria
- `POST /api/v1/access-logs` - Registrar acesso
- `GET /api/v1/status/latest` - Status atual
- `GET /api/v1/metrics` - Métricas do sistema

### Testes da API

#### Adicionar MAC autorizado
```bash
curl -X POST http://localhost:8000/api/v1/macs-autorizados \
  -H "Content-Type: application/json" \
  -d '{"mac":"24A160123456","placa":"ABC123"}'
```

#### Testar telemetria
```bash
curl -X POST http://localhost:8000/api/v1/access-logs \
  -H "Content-Type: application/json" \
  -d '{"mac":"24A160123456","direcao":"NS","datahora":"2025-07-23 10:00:00","status":"AUTORIZADO"}'
```

#### Verificar status
```bash
curl http://localhost:8000/api/v1/status/latest
```

### Firmware ESP32

#### Configuração
1. Instale Arduino IDE
2. Adicione suporte ESP32
3. Instale bibliotecas: LoRa, WiFi, HTTPClient, ArduinoJson
4. Configure credenciais WiFi no código

#### Upload
```bash
# Usando PlatformIO
cd base
pio run -t upload
```

## 📊 Monitoramento

### Métricas Disponíveis
- Total de acessos
- Acessos autorizados/negados
- Veículos únicos
- Status das barreiras
- Logs de sistema

### Dashboard
- Gráficos em tempo real
- Histórico de acessos
- Status dos dispositivos
- Configurações do sistema

## 🔄 Fluxo de Operação

1. **Detecção:** Veículo detectado a 500m via LoRa
2. **Identificação:** Captura do endereço MAC
3. **Direção:** Determinação via Ângulo de Chegada (AoA)
4. **Autorização:** Verificação na base de dados
5. **Ação:** Abertura/bloqueio da barreira
6. **Registro:** Log da operação no sistema

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📚 Documentação

### Documentação Completa
- **[Instalação Completa](docs/INSTALACAO_COMPLETA.md)** - Guia detalhado de instalação
- **[Documentação Técnica](docs/DOCUMENTACAO_TECNICA.md)** - Detalhes técnicos e arquitetura
- **[Como Adicionar Imagens](docs/COMO_ADICIONAR_IMAGENS.md)** - Guia para documentação visual

### Arquivos de Projeto
- **[CHANGELOG.md](CHANGELOG.md)** - Histórico de versões e mudanças
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guia para contribuidores
- **[LICENSE](LICENSE)** - Licença do projeto

## 🤝 Contribuição

Contribuições são bem-vindas! Por favor, leia o [Guia de Contribuição](CONTRIBUTING.md) para detalhes sobre:

- Como reportar bugs
- Como solicitar funcionalidades
- Padrões de código
- Processo de Pull Request

### Contribuidores

Agradecemos a todos que contribuíram para este projeto:

- **João Silva** - Desenvolvimento principal
- **Maria Santos** - Frontend e UX/UI
- **Pedro Costa** - Hardware e firmware ESP32

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

### Resumo da Licença
- ✅ Uso comercial permitido
- ✅ Modificação permitida
- ✅ Distribuição permitida
- ✅ Uso privado permitido
- ❗ Sem garantia
- ❗ Limitação de responsabilidade

## 📞 Suporte

### Canais de Suporte
- **📖 Documentação**: [docs/](docs/) - Documentação completa
- **🐛 Issues**: [GitHub Issues](../../issues) - Reportar bugs
- **💬 Discussões**: [GitHub Discussions](../../discussions) - Discussões gerais
- **📧 Email**: suporte@projeto.com
- **📱 Discord**: [Link para servidor Discord]

### Logs e Debug
- **Laravel**: `backend/storage/logs/laravel.log`
- **Frontend**: Console do navegador (F12)
- **Sistema**: Logs do sistema operacional

### FAQ

**P: Como iniciar o sistema rapidamente?**
R: Execute `iniciar_sistema_otimizado.bat`

**P: O sistema funciona sem internet?**
R: Sim, o frontend tem modo offline com sincronização automática

**P: Posso usar com outros bancos de dados?**
R: Sim, suporta MySQL, MariaDB e SQLite

**P: Como contribuir para o projeto?**
R: Leia o [Guia de Contribuição](CONTRIBUTING.md)

---

## 🌟 Agradecimentos Especiais

- **Comunidade Laravel** - Framework incrível
- **Tailwind CSS** - Framework CSS moderno
- **ESP32 Community** - Suporte técnico excepcional
- **Beta Testers** - Feedback valioso
- **Open Source Community** - Inspiração constante

---

**Desenvolvido com ❤️ para controle inteligente de barreiras IoT**

[![Made with Laravel](https://img.shields.io/badge/Made%20with-Laravel-red.svg)](https://laravel.com)
[![Made with Tailwind](https://img.shields.io/badge/Made%20with-Tailwind%20CSS-blue.svg)](https://tailwindcss.com)
[![Made with ESP32](https://img.shields.io/badge/Made%20with-ESP32-green.svg)](https://www.espressif.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)