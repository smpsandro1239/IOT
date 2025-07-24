# 🤝 Guia de Contribuição

Obrigado por considerar contribuir para o Sistema de Controle de Barreiras IoT! Este documento fornece diretrizes para contribuições.

## 📋 Índice

- [Como Contribuir](#-como-contribuir)
- [Reportar Bugs](#-reportar-bugs)
- [Solicitar Funcionalidades](#-solicitar-funcionalidades)
- [Desenvolvimento](#-desenvolvimento)
- [Padrões de Código](#-padrões-de-código)
- [Testes](#-testes)
- [Pull Requests](#-pull-requests)
- [Código de Conduta](#-código-de-conduta)

## 🚀 Como Contribuir

### Tipos de Contribuições

Aceitamos vários tipos de contribuições:

- 🐛 **Correção de bugs**
- ✨ **Novas funcionalidades**
- 📚 **Melhorias na documentação**
- 🎨 **Melhorias na interface**
- ⚡ **Otimizações de performance**
- 🧪 **Testes adicionais**
- 🔧 **Melhorias na infraestrutura**

### Primeiros Passos

1. **Fork** o repositório
2. **Clone** seu fork localmente
3. **Configure** o ambiente de desenvolvimento
4. **Crie** uma branch para sua contribuição
5. **Faça** suas alterações
6. **Teste** suas alterações
7. **Submeta** um Pull Request

## 🐛 Reportar Bugs

### Antes de Reportar

- ✅ Verifique se o bug já foi reportado nas [Issues](../../issues)
- ✅ Teste na versão mais recente
- ✅ Verifique a documentação

### Como Reportar

Use o template de bug report:

```markdown
**Descrição do Bug**
Descrição clara e concisa do problema.

**Passos para Reproduzir**
1. Vá para '...'
2. Clique em '...'
3. Role até '...'
4. Veja o erro

**Comportamento Esperado**
Descrição do que deveria acontecer.

**Screenshots**
Se aplicável, adicione screenshots.

**Ambiente:**
- OS: [ex: Windows 10]
- Browser: [ex: Chrome 91]
- Versão: [ex: v2.0.0]

**Informações Adicionais**
Qualquer outra informação relevante.
```

## ✨ Solicitar Funcionalidades

### Template de Feature Request

```markdown
**Funcionalidade Solicitada**
Descrição clara da funcionalidade desejada.

**Problema que Resolve**
Explique o problema que esta funcionalidade resolveria.

**Solução Proposta**
Descrição de como você imagina que funcione.

**Alternativas Consideradas**
Outras soluções que você considerou.

**Informações Adicionais**
Contexto adicional, screenshots, etc.
```

## 💻 Desenvolvimento

### Configuração do Ambiente

```bash
# 1. Fork e clone o repositório
git clone https://github.com/seu-usuario/controle-barreiras-iot.git
cd controle-barreiras-iot

# 2. Configure o ambiente
./configurar_novo_computador_v2.bat  # Windows
# ou
./scripts/setup_linux.sh             # Linux

# 3. Crie uma branch
git checkout -b feature/nova-funcionalidade
```

### Estrutura do Projeto

```
projeto/
├── backend/              # Laravel API
│   ├── app/             # Código da aplicação
│   ├── tests/           # Testes do backend
│   └── ...
├── frontend/            # Interface web
│   ├── js/             # JavaScript modular
│   ├── css/            # Estilos
│   └── ...
├── docs/               # Documentação
├── scripts/            # Scripts de automação
└── ...
```

### Workflow de Desenvolvimento

1. **Análise**: Entenda o problema/funcionalidade
2. **Design**: Planeje a implementação
3. **Implementação**: Escreva o código
4. **Testes**: Teste sua implementação
5. **Documentação**: Atualize a documentação
6. **Review**: Revise seu próprio código
7. **Submit**: Crie o Pull Request

## 📏 Padrões de Código

### Backend (PHP/Laravel)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller para gestão de MACs autorizados
 */
class MacsAutorizadosController extends Controller
{
    /**
     * Lista todos os MACs autorizados
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Implementação...
    }
}
```

**Padrões PHP:**
- PSR-12 para estilo de código
- DocBlocks para métodos públicos
- Type hints sempre que possível
- Nomes descritivos para variáveis
- Validação de dados sempre

### Frontend (JavaScript)

```javascript
/**
 * Cliente API para comunicação com backend
 */
class ApiClient {
    constructor(baseUrl = 'http://localhost:8000/api/v1') {
        this.baseUrl = baseUrl;
        this.setupInterceptors();
    }

    /**
     * Busca MACs autorizados
     * @param {number} page - Página atual
     * @param {string} search - Termo de busca
     * @returns {Promise<Object>} Resposta da API
     */
    async getAuthorizedMacs(page = 1, search = '') {
        // Implementação...
    }
}
```

**Padrões JavaScript:**
- ES6+ features
- JSDoc para documentação
- Nomes descritivos
- Modularização
- Error handling

### CSS (Tailwind)

```html
<!-- Use classes utilitárias do Tailwind -->
<div class="bg-white rounded-lg shadow-md p-6 mb-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">
        Título do Card
    </h2>
    <p class="text-gray-600">
        Conteúdo do card...
    </p>
</div>
```

### Firmware (C++/Arduino)

```cpp
/**
 * Calcula o Ângulo de Chegada (AoA) baseado em RSSI
 * @param rssi1 RSSI da antena 1
 * @param rssi2 RSSI da antena 2
 * @param rssi3 RSSI da antena 3
 * @return Ângulo em graus
 */
float calculateAoA(float rssi1, float rssi2, float rssi3) {
    // Implementação...
    return angle;
}
```

## 🧪 Testes

### Backend (PHPUnit)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MacsAutorizadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_listar_macs_autorizados()
    {
        // Arrange
        $mac = MacAutorizado::factory()->create();

        // Act
        $response = $this->getJson('/api/v1/macs-autorizados');

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'mac_address', 'nome']
                    ]
                ]);
    }
}
```

### Frontend (Jest)

```javascript
describe('ApiClient', () => {
    let apiClient;

    beforeEach(() => {
        apiClient = new ApiClient();
    });

    test('deve buscar MACs autorizados', async () => {
        // Arrange
        const mockResponse = { data: [] };
        global.fetch = jest.fn().mockResolvedValue({
            ok: true,
            json: () => Promise.resolve(mockResponse)
        });

        // Act
        const result = await apiClient.getAuthorizedMacs();

        // Assert
        expect(result).toEqual(mockResponse);
        expect(fetch).toHaveBeenCalledWith(
            expect.stringContaining('/macs-autorizados')
        );
    });
});
```

### Executar Testes

```bash
# Backend
cd backend
php artisan test

# Frontend
cd frontend
npm test

# Cobertura
npm run test:coverage
```

## 📝 Pull Requests

### Checklist

Antes de submeter um PR, verifique:

- [ ] ✅ Código segue os padrões estabelecidos
- [ ] ✅ Testes passam (existentes e novos)
- [ ] ✅ Documentação atualizada
- [ ] ✅ Commit messages são claros
- [ ] ✅ Branch está atualizada com main
- [ ] ✅ Não há conflitos
- [ ] ✅ Screenshots para mudanças visuais

### Template de PR

```markdown
## 📋 Descrição

Breve descrição das mudanças realizadas.

## 🔗 Issue Relacionada

Fixes #123

## 🧪 Testes

- [ ] Testes unitários passam
- [ ] Testes de integração passam
- [ ] Testado manualmente

## 📸 Screenshots

Se aplicável, adicione screenshots das mudanças.

## ✅ Checklist

- [ ] Código revisado
- [ ] Testes adicionados/atualizados
- [ ] Documentação atualizada
- [ ] Padrões de código seguidos
```

### Processo de Review

1. **Automated Checks**: CI/CD executa testes
2. **Code Review**: Maintainers revisam o código
3. **Testing**: Testes manuais se necessário
4. **Approval**: Aprovação dos maintainers
5. **Merge**: Merge para branch principal

## 📊 Convenções de Commit

Use [Conventional Commits](https://www.conventionalcommits.org/):

```bash
# Tipos
feat: nova funcionalidade
fix: correção de bug
docs: documentação
style: formatação
refactor: refatoração
test: testes
chore: manutenção

# Exemplos
feat(api): adicionar endpoint para métricas
fix(frontend): corrigir validação de MAC
docs(readme): atualizar instruções de instalação
style(css): melhorar responsividade
refactor(backend): otimizar queries
test(unit): adicionar testes para ApiClient
chore(deps): atualizar dependências
```

## 🏷️ Versionamento

Seguimos [Semantic Versioning](https://semver.org/):

- **MAJOR**: Mudanças incompatíveis na API
- **MINOR**: Funcionalidades compatíveis
- **PATCH**: Correções compatíveis

Exemplo: `v2.1.3`

## 🎯 Áreas que Precisam de Ajuda

### 🔥 Alta Prioridade
- [ ] Testes automatizados
- [ ] Documentação da API
- [ ] Otimização de performance
- [ ] Correção de bugs críticos

### 📈 Média Prioridade
- [ ] Melhorias na UI/UX
- [ ] Funcionalidades novas
- [ ] Refatoração de código
- [ ] Internacionalização

### 💡 Baixa Prioridade
- [ ] Melhorias na documentação
- [ ] Exemplos de uso
- [ ] Tutoriais
- [ ] Otimizações menores

## 🏆 Reconhecimento

### Contribuidores

Todos os contribuidores são reconhecidos:

- **README.md**: Lista de contribuidores
- **CHANGELOG.md**: Créditos por release
- **GitHub**: Contributor insights
- **Discord**: Role especial para contribuidores

### Tipos de Reconhecimento

- 🥇 **Core Contributor**: 10+ PRs aceitos
- 🥈 **Active Contributor**: 5+ PRs aceitos
- 🥉 **Contributor**: 1+ PR aceito
- 🐛 **Bug Hunter**: Reporta bugs importantes
- 📚 **Documentation**: Melhora documentação
- 🎨 **Designer**: Contribuições de design

## 📞 Suporte

### Canais de Comunicação

- **GitHub Issues**: Bugs e feature requests
- **GitHub Discussions**: Discussões gerais
- **Discord**: Chat em tempo real
- **Email**: contato@projeto.com

### Mentoria

Para novos contribuidores:
- 👋 **First-time contributors**: Issues marcadas como "good first issue"
- 🎓 **Mentorship**: Mentores disponíveis para ajudar
- 📖 **Documentation**: Guias detalhados
- 💬 **Community**: Comunidade acolhedora

## 📜 Código de Conduta

### Nossos Padrões

- ✅ Seja respeitoso e inclusivo
- ✅ Aceite críticas construtivas
- ✅ Foque no que é melhor para a comunidade
- ✅ Mostre empatia com outros membros

### Comportamentos Inaceitáveis

- ❌ Linguagem ou imagens ofensivas
- ❌ Ataques pessoais ou políticos
- ❌ Assédio público ou privado
- ❌ Publicar informações privadas

### Aplicação

Violações podem ser reportadas para moderadores@projeto.com.

---

## 🙏 Agradecimentos

Obrigado por contribuir para tornar este projeto melhor! Sua ajuda é muito valorizada.

**Juntos construímos um sistema IoT incrível!** 🚀