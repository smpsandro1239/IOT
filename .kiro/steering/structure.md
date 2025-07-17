# Estrutura do Projeto

## Visão Geral

O projeto está organizado em três componentes principais:
1. **Backend**: API Laravel para gerenciamento de dados e lógica de negócios
2. **Frontend**: Interface web para visualização e controle do sistema
3. **Firmware**: Código para os dispositivos ESP32 (base, auto, direcao)

## Estrutura de Diretórios

### Backend (Laravel)

```
backend/
├── app/                    # Código da aplicação
│   ├── Events/             # Eventos (ex: TelemetriaUpdated)
│   ├── Http/
│   │   ├── Controllers/    # Controladores da API
│   │   └── Middleware/     # Middlewares (ex: DevAuthMiddleware)
│   └── Models/             # Modelos de dados
├── config/                 # Configurações
├── database/
│   ├── migrations/         # Migrações do banco de dados
│   └── seeders/            # Seeders para dados iniciais
├── resources/
│   └── views/              # Views (blade templates)
├── routes/                 # Definições de rotas
└── storage/                # Armazenamento (logs, cache)
```

### Frontend

```
frontend/
├── api/                    # Endpoints de proxy para desenvolvimento
│   └── v1/                 # Versão da API
├── css/                    # Arquivos CSS compilados
├── icons/                  # Ícones e recursos gráficos
├── js/                     # JavaScript
│   ├── api-client.js       # Cliente para API
│   ├── app.js             # Lógica principal
│   ├── simulation.js       # Módulo de simulação
│   └── ui-components.js    # Componentes de UI
├── src/                    # Código fonte para build
│   └── input.css          # CSS fonte para Tailwind
├── index.html             # Página principal
├── login.html             # Página de login
├── manifest.json          # Configuração PWA
├── sw.js                  # Service Worker
└── tailwind.config.js     # Configuração do Tailwind
```

### Firmware

```
base/                      # Firmware da estação base
├── platformio.ini         # Configuração PlatformIO
└── src/                   # Código fonte

auto/                      # Firmware para detecção automática
├── platformio.ini         # Configuração PlatformIO
└── src/                   # Código fonte

direcao/                   # Firmware para detecção de direção
├── platformio.ini         # Configuração PlatformIO
└── src/                   # Código fonte
```

### Scripts de Automação

```
setup_desenvolvimento.bat  # Configuração do ambiente de desenvolvimento
iniciar_sistema.bat        # Inicia o sistema básico
iniciar_sistema_completo.bat # Inicia o sistema completo
iniciar_sistema_otimizado.bat # Inicia o sistema otimizado
reiniciar_sistema.bat      # Reinicia o sistema
reiniciar_sistema_corrigido.bat # Reinicia o sistema com correções
```

## Convenções de Código

### Backend (Laravel)
- Seguir PSR-12 para estilo de código PHP
- Nomes de classes em PascalCase
- Nomes de métodos em camelCase
- Nomes de tabelas em snake_case, plural (ex: access_logs)
- Rotas API em kebab-case (ex: /api/v1/macs-autorizados)
- Comentários DocBlock para métodos públicos

### Frontend
- JavaScript em camelCase
- Classes CSS em kebab-case
- IDs HTML em kebab-case
- Organização modular de JavaScript
- Prefixo 'js-' para elementos manipulados via JavaScript

### Firmware
- Nomes de funções em camelCase
- Constantes em UPPER_SNAKE_CASE
- Comentários detalhados para funções complexas
- Prefixo 'g_' para variáveis globais

## Fluxo de Integração

### Comunicação entre Componentes
- **Backend ↔ Frontend**: API RESTful + WebSockets
- **Backend ↔ Firmware**: API RESTful (HTTP)
- **Firmware ↔ Firmware**: Comunicação LoRa

### Ciclo de Desenvolvimento
1. Desenvolvimento local com simulação
2. Testes de integração com hardware
3. Implantação em ambiente de produção

### Arquivos de Configuração Importantes
- `backend/.env`: Configurações do ambiente Laravel
- `backend/config/websockets.php`: Configuração de WebSockets
- `*/platformio.ini`: Configurações de build do firmware
- `frontend/manifest.json`: Configuração da PWA
