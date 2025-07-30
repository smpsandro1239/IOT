# 🔒 GUIA COMPLETO DE SEGURANÇA - SISTEMA IOT CONTROLE DE BARREIRAS

## 🎯 **VISÃO GERAL**

Este documento estabelece as práticas de segurança obrigatórias para o projeto Sistema IOT Controle de Barreiras. O cumprimento destas diretrizes é **CRÍTICO** para proteger credenciais, dados sensíveis e informações confidenciais.

**⚠️ IMPORTANTE**: Este projeto implementa um sistema robusto de segurança que **DEVE** ser seguido rigorosamente.

---

## 🛡️ **ARQUITETURA DE SEGURANÇA**

### **Camadas de Proteção**

```
┌─────────────────────────────────────────────────────────────┐
│                    CAMADA 1: .gitignore                    │
│  Proteção automática contra commit de ficheiros sensíveis  │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                 CAMADA 2: Sistema de Templates             │
│     Ficheiros .example para configuração segura            │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│                CAMADA 3: Scripts de Automação              │
│    Configuração e verificação automática de segurança      │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│               CAMADA 4: Verificação Pré-Commit             │
│      Validação obrigatória antes de cada commit            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚫 **FICHEIROS NUNCA COMMITADOS**

### **🔴 CRÍTICOS - BLOQUEIAM COMMIT**

```
❌ backend/.env                    - Configurações Laravel
❌ base/src/config.h               - Configurações ESP32 Base
❌ auto/src/config.h               - Configurações ESP32 Auto
❌ direcao/src/config.h            - Configurações ESP32 Direção
❌ .gitconfig                      - Configurações Git pessoais
❌ *.pem, *.key, *.crt            - Chaves e certificados
❌ base/private_key.pem            - Chave privada específica
```

### **🟡 SENSÍVEIS - GERAM AVISOS**

```
⚠️ backend/storage/logs/           - Logs do sistema
⚠️ backend/storage/framework/sessions/ - Sessões ativas
⚠️ *_backup.*, *.bak, *.dump      - Ficheiros de backup
⚠️ *_real.*, *_production.*       - Dados reais/produção
```

### **🔵 ASSISTENTES IA - OCULTOS**

```
🤖 .kiro/                         - Kiro IDE (OCULTO)
🤖 .emergent/                     - Dados emergentes (OCULTO)
🤖 *conversation*, *chat_history*  - Histórico de conversas IA
```

---

## ✅ **FICHEIROS SEMPRE COMMITADOS**

### **📋 Templates Obrigatórios**

```
✅ backend/.env.example           - Template configuração Laravel
✅ base/src/config.h.example      - Template configuração ESP32 Base
✅ auto/src/config.h.example      - Template configuração ESP32 Auto
✅ direcao/src/config.h.example   - Template configuração ESP32 Direção
✅ .gitconfig.example             - Template configuração Git
```

### **📚 Documentação**

```
✅ README.md                      - Documentação principal
✅ SEGURANCA.md                   - Este guia de segurança
✅ CHANGELOG.md                   - Histórico de alterações
✅ CONTRIBUTING.md                - Guia de contribuição
```

### **🔧 Scripts de Segurança**

```
✅ configurar_ambiente_seguro.bat - Configuração inicial
✅ verificar_seguranca_simples.bat - Verificação rápida
✅ preparar_commit_seguro.bat     - Verificação pré-commit
✅ limpar_credenciais_emergencia.bat - Limpeza de emergência
```

---

## 🚀 **CONFIGURAÇÃO INICIAL SEGURA**

### **1️⃣ Primeiro Setup (Obrigatório)**

```bash
# 1. Clonar repositório
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot

# 2. Configurar ambiente seguro (OBRIGATÓRIO)
.\configurar_ambiente_seguro.bat

# 3. Verificar segurança
.\verificar_seguranca_simples.bat
```

### **2️⃣ Configuração de Credenciais**

#### **Backend Laravel**
```bash
# Editar backend/.env (criado automaticamente)
# ALTERAR ESTAS CONFIGURAÇÕES:

APP_NAME="Sistema IOT Controle de Barreiras"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Gerar chave automaticamente:
cd backend
php artisan key:generate
```

#### **ESP32 Base**
```cpp
// Editar base/src/config.h (criado automaticamente)
// ALTERAR ESTAS CONFIGURAÇÕES:

#define WIFI_SSID "SUA_REDE_WIFI"
#define WIFI_PASSWORD "SUA_SENHA_WIFI"
#define API_HOST "192.168.1.100"  // IP do seu servidor
```

#### **ESP32 Auto e Direção**
```cpp
// Editar auto/src/config.h e direcao/src/config.h
// Configurações similares ao ESP32 Base
```

#### **Git**
```bash
# Editar .gitconfig (criado automaticamente)
# ALTERAR ESTAS CONFIGURAÇÕES:

[user]
    name = Seu Nome Completo
    email = seu.email@exemplo.com
```

---

## 🔍 **VERIFICAÇÃO DE SEGURANÇA**

### **Verificação Rápida (Diária)**
```bash
.\verificar_seguranca_simples.bat
```

### **Verificação Completa (Antes de Releases)**
```bash
.\verificar_seguranca.bat
```

### **Verificação Pré-Commit (Obrigatória)**
```bash
# Adicionar ficheiros
git add .

# Verificar e fazer commit seguro
.\preparar_commit_seguro.bat
```

---

## 🚨 **PROCEDIMENTOS DE EMERGÊNCIA**

### **🔥 Credenciais Comprometidas**

```bash
# 1. LIMPEZA IMEDIATA
.\limpar_credenciais_emergencia.bat

# 2. GERAR NOVAS CREDENCIAIS
.\configurar_ambiente_seguro.bat

# 3. VERIFICAR SEGURANÇA
.\verificar_seguranca_simples.bat

# 4. LIMPAR HISTÓRICO GIT (se necessário)
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch ficheiro_sensivel' \
  --prune-empty --tag-name-filter cat -- --all
```

### **🚫 Commit Acidental de Credenciais**

```bash
# 1. REVERTER COMMIT IMEDIATAMENTE
git reset --hard HEAD~1

# 2. LIMPAR STAGING AREA
git reset HEAD .

# 3. EXECUTAR LIMPEZA
.\limpar_credenciais_emergencia.bat

# 4. RECONFIGURAR
.\configurar_ambiente_seguro.bat
```

---

## 📋 **CHECKLISTS DE SEGURANÇA**

### **✅ Checklist Diário**

- [ ] Executar `verificar_seguranca_simples.bat`
- [ ] Confirmar que não há ficheiros sensíveis no staging
- [ ] Verificar que .gitignore está atualizado
- [ ] Confirmar que credenciais estão seguras

### **✅ Checklist Pré-Commit**

- [ ] Executar `preparar_commit_seguro.bat`
- [ ] Verificar mensagem em português de Portugal
- [ ] Confirmar que apenas ficheiros seguros estão no staging
- [ ] Validar que ficheiros .example estão atualizados

### **✅ Checklist Pré-Release**

- [ ] Executar auditoria completa de segurança
- [ ] Verificar todos os templates .example
- [ ] Confirmar documentação atualizada
- [ ] Testar configuração em ambiente limpo
- [ ] Validar que sistema funciona após clone

### **✅ Checklist Novo Desenvolvedor**

- [ ] Clonar repositório
- [ ] Executar `configurar_ambiente_seguro.bat`
- [ ] Configurar credenciais pessoais
- [ ] Testar sistema completo
- [ ] Ler documentação de segurança

---

## 🛠️ **COMANDOS ESSENCIAIS**

### **Configuração**
```bash
.\configurar_ambiente_seguro.bat    # Configuração inicial
.\verificar_seguranca_simples.bat   # Verificação rápida
.\preparar_commit_seguro.bat        # Commit seguro
```

### **Emergência**
```bash
.\limpar_credenciais_emergencia.bat # Limpeza total
git reset HEAD .                    # Limpar staging
git status                          # Verificar estado
```

### **Git Seguro**
```bash
git status                          # Ver estado atual
git diff --cached                   # Ver ficheiros no staging
git reset HEAD ficheiro             # Remover do staging
git check-ignore ficheiro           # Verificar .gitignore
```

---

## 📊 **NÍVEIS DE RISCO**

### **🚨 CRÍTICO - Bloqueia Commit**
- Ficheiros .env, config.h no staging
- Chaves privadas expostas
- .gitignore mal configurado

### **⚠️ ALTO - Gera Avisos**
- Ficheiros .example em falta
- Credenciais em código fonte
- Configurações suspeitas

### **ℹ️ MÉDIO - Requer Atenção**
- Ficheiros de backup presentes
- Logs com informações sensíveis
- Configurações desatualizadas

### **💡 BAIXO - Melhorias**
- Permissões de ficheiros
- Organização de código
- Documentação menor

---

## 🎓 **BOAS PRÁTICAS**

### **Desenvolvimento**
- ✅ Sempre usar ficheiros .example como base
- ✅ Nunca hardcodar credenciais em código
- ✅ Executar verificação antes de cada commit
- ✅ Manter credenciais locais e seguras
- ✅ Usar mensagens de commit em português

### **Colaboração**
- ✅ Partilhar apenas ficheiros .example
- ✅ Documentar alterações de configuração
- ✅ Avisar equipa sobre mudanças de segurança
- ✅ Revisar pull requests por vulnerabilidades

### **Produção**
- ✅ Usar variáveis de ambiente
- ✅ Implementar HTTPS obrigatório
- ✅ Configurar rate limiting
- ✅ Monitorizar logs de segurança
- ✅ Fazer backups seguros regulares

---

## 📞 **SUPORTE E CONTACTOS**

### **Reportar Problemas de Segurança**
- **Email**: security@projeto.com
- **GitHub**: Issues privadas
- **Urgente**: Contacto direto com maintainers

### **Documentação Adicional**
- **Configuração**: README.md
- **Contribuição**: CONTRIBUTING.md
- **Alterações**: CHANGELOG.md
- **Auditoria**: AUDITORIA_SEGURANCA_INICIAL.md

---

## ⚖️ **RESPONSABILIDADES**

### **Todos os Desenvolvedores**
- Seguir este guia rigorosamente
- Executar verificações de segurança
- Reportar vulnerabilidades imediatamente
- Manter credenciais seguras

### **Maintainers**
- Revisar todos os commits por segurança
- Manter documentação atualizada
- Responder a incidentes de segurança
- Treinar novos desenvolvedores

### **Administradores**
- Configurar ambientes seguros
- Monitorizar logs de segurança
- Implementar políticas de backup
- Gerir acessos e permissões

---

## 🏆 **CERTIFICAÇÃO DE SEGURANÇA**

Este projeto implementa:

✅ **Proteção de Credenciais**: Sistema robusto de .gitignore  
✅ **Templates Seguros**: Ficheiros .example para todas as configurações  
✅ **Automação**: Scripts de configuração e verificação  
✅ **Verificação Pré-Commit**: Validação obrigatória antes de commits  
✅ **Documentação Completa**: Guias detalhados e procedimentos  
✅ **Procedimentos de Emergência**: Limpeza e recuperação rápida  
✅ **Auditoria Contínua**: Verificação regular de vulnerabilidades  

**Status**: 🛡️ **SISTEMA SEGURO CERTIFICADO**

---

## 📝 **HISTÓRICO DE ALTERAÇÕES**

| Data | Versão | Alteração |
|------|--------|-----------|
| 2025-01-29 | 1.0.0 | Implementação inicial do sistema de segurança |
| 2025-01-29 | 1.1.0 | Adição de scripts de automação |
| 2025-01-29 | 1.2.0 | Sistema de verificação pré-commit |

---

**🔒 A segurança é responsabilidade de todos. Siga este guia rigorosamente!**