# 📸 Como Adicionar Imagens ao Projeto

## 📁 Estrutura de Pastas

```
docs/
├── images/
│   ├── screenshots/          # Capturas de tela da interface
│   ├── diagrams/             # Diagramas de arquitetura
│   └── logos/                # Logotipos e ícones
└── COMO_ADICIONAR_IMAGENS.md
```

## 🖼️ Tipos de Imagens Recomendadas

### **Screenshots da Interface**
- `interface-principal.png` - Dashboard principal
- `gestao-macs.png` - Tela de gestão de MACs
- `simulacao-radar.png` - Simulação de radar
- `importar-csv.png` - Funcionalidade de importação
- `metricas-graficos.png` - Gráficos e métricas

### **Diagramas Técnicos**
- `arquitetura-sistema.png` - Arquitetura geral
- `fluxo-comunicacao.png` - Fluxo de comunicação LoRa
- `estrutura-banco.png` - Estrutura da base de dados
- `api-endpoints.png` - Mapa dos endpoints da API

### **Logos e Ícones**
- `logo-projeto.png` - Logo principal
- `favicon.ico` - Ícone do navegador
- `esp32-icon.png` - Ícone do ESP32

## 📝 Como Usar no README.md

### **Sintaxe Básica**
```markdown
![Texto Alternativo](docs/images/pasta/nome-arquivo.png)
```

### **Com Legenda**
```markdown
![Interface Principal](docs/images/screenshots/interface-principal.png)
*Legenda explicativa da imagem*
```

### **Com Link**
```markdown
[![Interface](docs/images/screenshots/interface-principal.png)](docs/images/screenshots/interface-principal.png)
```

### **Redimensionada (HTML)**
```html
<img src="docs/images/screenshots/interface-principal.png" alt="Interface" width="600">
```

## 🎯 Boas Práticas

### **Nomes de Arquivos**
- Use kebab-case: `interface-principal.png`
- Seja descritivo: `gestao-macs-pesquisa.png`
- Inclua versão se necessário: `arquitetura-v2.png`

### **Formatos Recomendados**
- **Screenshots**: PNG (melhor qualidade)
- **Diagramas**: SVG (escalável) ou PNG
- **Logos**: SVG ou PNG com transparência

### **Tamanhos Recomendados**
- **Screenshots**: 1200x800px (máximo)
- **Diagramas**: 800x600px
- **Logos**: 200x200px

### **Otimização**
- Comprima imagens para reduzir tamanho
- Use ferramentas como TinyPNG
- Mantenha qualidade visual adequada

## 📋 Checklist para Adicionar Imagens

- [ ] Imagem salva na pasta correta
- [ ] Nome descritivo e padronizado
- [ ] Formato adequado (PNG/SVG)
- [ ] Tamanho otimizado
- [ ] Adicionada ao README.md
- [ ] Legenda explicativa
- [ ] Testada no GitHub

## 🔧 Ferramentas Úteis

- **Captura de Tela**: Snipping Tool, Lightshot
- **Edição**: GIMP, Paint.NET, Canva
- **Diagramas**: Draw.io, Lucidchart
- **Compressão**: TinyPNG, ImageOptim
- **SVG**: Inkscape, Adobe Illustrator

## 📖 Exemplo Completo

```markdown
## 📸 Funcionalidades

### Dashboard Principal
![Dashboard](docs/images/screenshots/dashboard.png)
*Dashboard em tempo real com status das barreiras*

### Gestão de Veículos
![Gestão MACs](docs/images/screenshots/gestao-macs.png)
*Interface para adicionar, editar e remover veículos autorizados*

### Arquitetura do Sistema
![Arquitetura](docs/images/diagrams/arquitetura.png)
*Diagrama da arquitetura completa do sistema IoT*
```