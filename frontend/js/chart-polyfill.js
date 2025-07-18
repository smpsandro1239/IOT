/**
 * Chart.js Polyfill e Utilitarios
 * Este script garante que o Chart.js esteja disponivel globalmente
 * e fornece funcoes utilitarias para gerenciar graficos
 */

(function() {
  // Verificar se o Chart.js ja esta carregado
  if (typeof Chart !== 'undefined') {
    console.log('Chart.js ja esta disponivel');
    setupChartHelpers();
    return;
  }

  console.log('Carregando Chart.js dinamicamente...');

  // Criar elemento script
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
  script.async = false; // Importante: carregar de forma sincrona

  // Adicionar evento de carregamento
  script.onload = function() {
    console.log('Chart.js carregado com sucesso');
    setupChartHelpers();

    // Disparar evento personalizado para notificar que o Chart.js foi carregado
    document.dispatchEvent(new CustomEvent('chartjsloaded'));
  };

  // Adicionar evento de erro
  script.onerror = function() {
    console.error('Falha ao carregar Chart.js');

    // Criar um polyfill minimo para evitar erros
    window.Chart = class Chart {
      constructor(canvas, config) {
        console.warn('Usando Chart.js polyfill - os graficos nao serao renderizados');
        this.canvas = canvas;
        this.config = config;

        // Adicionar mensagem de erro no canvas
        if (canvas) {
          const ctx = canvas.getContext('2d');
          if (ctx) {
            ctx.font = '14px Arial';
            ctx.fillStyle = 'red';
            ctx.textAlign = 'center';
            ctx.fillText('Erro ao carregar Chart.js', canvas.width / 2, canvas.height / 2);
          }
        }
      }

      update() { return this; }
      destroy() { return this; }
    };

    // Disparar evento personalizado para notificar que o polyfill foi criado
    document.dispatchEvent(new CustomEvent('chartjspolyfill'));
  };

  // Adicionar script ao documento
  document.head.appendChild(script);

  // Configurar helpers para o Chart.js
  function setupChartHelpers() {
    if (typeof Chart === 'undefined') return;

    // Adicionar metodo para atualizar dados sem acumular
    Chart.prototype.updateData = function(labels, data) {
      if (!this.data || !this.data.datasets || this.data.datasets.length === 0) return this;

      // Atualizar labels
      this.data.labels = labels;

      // Atualizar dados do primeiro dataset
      this.data.datasets[0].data = data;

      // Atualizar o grafico sem animacao para melhor performance
      this.update('none');

      return this;
    };

    // Adicionar metodo para limpar dados
    Chart.prototype.clearData = function() {
      if (!this.data || !this.data.datasets || this.data.datasets.length === 0) return this;

      // Limpar dados de todos os datasets
      this.data.datasets.forEach(dataset => {
        dataset.data = [];
      });

      // Atualizar o grafico sem animacao
      this.update('none');

      return this;
    };

    console.log('Helpers do Chart.js configurados com sucesso');
  }
})();
