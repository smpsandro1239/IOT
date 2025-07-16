/**
 * Main Application for IoT Barrier Control System
 * Handles all application logic and UI interactions
 */

import apiClient from './api-client.js';
import UIComponents from './ui-components.js';

class BarrierControlApp {
  constructor() {
    // Application state
    this.state = {
      systemStatus: 'operational',
      barrierStatus: {
        north: { state: 'closed', lastUpdated: null },
        south: { state: 'closed', lastUpdated: null }
      },
      vehicleInfo: {
        plate: 'N/A',
        direction: 'Nenhuma',
        distance: '0 m'
      },
      signalStrength: 0,
      simulationActive: false,
      currentSimulation: null,
      authorizedMacs: [],
      currentPage: 1,
      searchTerm: '',
      offlineMode: false,
      lastUpdated: null
    };

    // Echo instance for real-time updates
    this.echo = null;

    // Charts
    this.charts = {
      daily: null,
      weekly: null,
      monthly: null,
      macDaily: null,
      macWeekly: null,
      macMonthly: null
    };

    // Data tables
    this.tables = {
      authorizedMacs: null
    };

    // Initialize application
    this.init();
  }

  /**
   * Initialize application
   */
  async init() {
    // Check authentication
    if (!localStorage.getItem('auth_token')) {
      window.location.href = 'login.html';
      return;
    }

    // Register service worker
    this.registerServiceWorker();

    // Initialize Echo for real-time updates
    this.initializeEcho();

    // Initialize UI components
    this.initializeUI();

    // Load initial data
    await this.loadInitialData();

    // Add event listeners
    this.addEventListeners();

    // Set up periodic updates
    this.setupPeriodicUpdates();

    // Show welcome message
    UIComponents.showToast('Bem-vindo ao Sistema de Controle de Barreiras IoT', 'info', 5000);
  }

  /**
   * Register service worker
   */
  registerServiceWorker() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js')
        .then(registration => {
          console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch(error => {
          console.error('Service Worker registration failed:', error);
        });
    }
  }

  /**
   * Initialize Echo for real-time updates
   */
  initializeEcho() {
    try {
      this.echo = new Echo({
        broadcaster: 'pusher',
        key: 'iot-key',
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
        cluster: 'mt1'
      });

      // Listen for telemetry updates
      this.echo.channel('telemetria')
        .listen('TelemetriaUpdated', this.handleTelemetryUpdate.bind(this));

      // Listen for gate control updates
      this.echo.channel('gate-control')
        .listen('GateControl', this.handleGateControl.bind(this));

      // Handle connection events
      this.echo.connector.pusher.connection.bind('connected', () => {
        console.log('WebSocket connected');
        this.addSystemLog('WebSocket conectado');
        document.getElementById('system-status').classList.remove('bg-red-500');
        document.getElementById('system-status').classList.add('bg-green-500');
        document.getElementById('status-text').textContent = 'Operacional';
      });

      this.echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('WebSocket disconnected');
        this.addSystemLog('WebSocket desconectado. Tentando reconectar...');
        document.getElementById('system-status').classList.remove('bg-green-500');
        document.getElementById('system-status').classList.add('bg-red-500');
        document.getElementById('status-text').textContent = 'Desconectado';

        // Try to reconnect after 5 seconds
        setTimeout(() => this.echo.connect(), 5000);
      });
    } catch (error) {
      console.error('Failed to initialize Echo:', error);
      this.addSystemLog('Erro ao inicializar WebSocket: ' + error.message);
    }
  }

  /**
   * Initialize UI components
   */
  initializeUI() {
    // Initialize charts
    this.initializeCharts();

    // Initialize data tables
    this.initializeTables();

    // Initialize vehicle visualization
    this.initializeVehicleVisualization();
  }

  /**
   * Initialize charts
   */
  initializeCharts() {
    // Daily access chart
    this.charts.daily = UIComponents.createChart('daily-chart', 'bar', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(59, 130, 246, 0.5)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1
      }]
    });

    // Weekly access chart
    this.charts.weekly = UIComponents.createChart('weekly-chart', 'line', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(16, 185, 129, 0.2)',
        borderColor: 'rgb(16, 185, 129)',
        borderWidth: 2,
        tension: 0.3,
        fill: true
      }]
    });

    // Monthly access chart
    this.charts.monthly = UIComponents.createChart('monthly-chart', 'bar', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(139, 92, 246, 0.5)',
        borderColor: 'rgb(139, 92, 246)',
        borderWidth: 1
      }]
    });

    // MAC-specific charts
    this.charts.macDaily = UIComponents.createChart('mac-daily-chart', 'bar', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(245, 158, 11, 0.5)',
        borderColor: 'rgb(245, 158, 11)',
        borderWidth: 1
      }]
    });

    this.charts.macWeekly = UIComponents.createChart('mac-weekly-chart', 'line', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(239, 68, 68, 0.2)',
        borderColor: 'rgb(239, 68, 68)',
        borderWidth: 2,
        tension: 0.3,
        fill: true
      }]
    });

    this.charts.macMonthly = UIComponents.createChart('mac-monthly-chart', 'bar', {
      labels: [],
      datasets: [{
        label: 'Acessos',
        data: [],
        backgroundColor: 'rgba(6, 182, 212, 0.5)',
        borderColor: 'rgb(6, 182, 212)',
        borderWidth: 1
      }]
    });
  }

  /**
   * Initialize data tables
   */
  initializeTables() {
    // Authorized MACs table
    this.tables.authorizedMacs = UIComponents.createDataTable('authorized-macs', [
      { field: 'mac', title: 'MAC' },
      { field: 'placa', title: 'Placa' },
      {
        field: 'data_adicao',
        title: 'Data Adição',
        render: (value) => UIComponents.formatDate(value)
      }
    ], [], {
      rowActions: [
        {
          title: 'Excluir',
          icon: '<i class="fas fa-trash text-red-500"></i>',
          className: 'text-red-500 hover:text-red-700',
          onClick: (item) => this.deleteMac(item.mac)
        }
      ]
    });
  }

  /**
   * Initialize vehicle visualization
   */
  initializeVehicleVisualization() {
    // Set initial position for vehicle marker (hidden)
    const vehicleMarker = document.getElementById('vehicle-marker');
    vehicleMarker.style.display = 'none';
    vehicleMarker.style.left = '50%';
    vehicleMarker.style.top = '50%';

    // Set initial position for direction indicator (hidden)
    const directionIndicator = document.getElementById('direction-indicator');
    directionIndicator.style.display = 'none';
  }

  /**
   * Load initial data
   */
  async loadInitialData() {
    try {
      // Show loading state
      document.getElementById('system-status').classList.add('bg-yellow-500');
      document.getElementById('status-text').textContent = 'Carregando...';

      // Get latest status
      const statusResponse = await apiClient.getLatestStatus();
      if (statusResponse.ok) {
        this.updateSystemStatus(statusResponse.data);
      }

      // Get authorized MACs
      await this.fetchAuthorizedMacs();

      // Get metrics data
      await this.fetchMetrics();

      // Update UI
      document.getElementById('system-status').classList.remove('bg-yellow-500');
      document.getElementById('system-status').classList.add('bg-green-500');
      document.getElementById('status-text').textContent = 'Operacional';

      // Add log
      this.addSystemLog('Sistema inicializado com sucesso');
    } catch (error) {
      console.error('Failed to load initial data:', error);
      document.getElementById('system-status').classList.remove('bg-yellow-500');
      document.getElementById('system-status').classList.add('bg-red-500');
      document.getElementById('status-text').textContent = 'Erro';
      this.addSystemLog('Erro ao carregar dados iniciais: ' + error.message);
    }
  }

  /**
   * Add event listeners
   */
  addEventListeners() {
    // Gate control buttons
    document.getElementById('north-toggle').addEventListener('click', () => this.toggleGate('north'));
    document.getElementById('south-toggle').addEventListener('click', () => this.toggleGate('south'));

    // MAC authorization form
    document.getElementById('add-mac').addEventListener('click', this.addAuthorizedMac.bind(this));

    // MAC file upload
    document.getElementById('mac-file').addEventListener('change', this.handleFileUpload.bind(this));

    // MAC download
    document.getElementById('download-macs').addEventListener('click', this.downloadMacs.bind(this));

    // File instructions modal
    document.getElementById('file-instructions').addEventListener('click', () => {
      document.getElementById('instructions-modal').classList.remove('hidden');
    });

    document.getElementById('close-modal').addEventListener('click', () => {
      document.getElementById('instructions-modal').classList.add('hidden');
    });

    // Simulation controls
    document.getElementById('start-sim').addEventListener('click', this.startSimulation.bind(this));
    document.getElementById('stop-sim').addEventListener('click', this.stopSimulation.bind(this));

    // MAC search
    document.getElementById('mac-search').addEventListener('input', (e) => {
      this.state.searchTerm = e.target.value;
      this.fetchAuthorizedMacs();
    });

    // Pagination
    document.getElementById('prev-page').addEventListener('click', () => {
      if (this.state.currentPage > 1) {
        this.state.currentPage--;
        this.fetchAuthorizedMacs();
      }
    });

    document.getElementById('next-page').addEventListener('click', () => {
      this.state.currentPage++;
      this.fetchAuthorizedMacs();
    });

    // MAC metrics
    document.getElementById('mac-input').addEventListener('change', (e) => {
      const mac = e.target.value;
      if (mac) {
        this.fetchMacMetrics(mac);
      }
    });

    // Connection change events
    window.addEventListener('connectionChange', (e) => {
      this.state.offlineMode = !e.detail.online;

      if (e.detail.online) {
        document.getElementById('system-status').classList.remove('bg-red-500');
        document.getElementById('system-status').classList.add('bg-green-500');
        document.getElementById('status-text').textContent = 'Operacional';
        this.addSystemLog('Conexão com o servidor restaurada');
      } else {
        document.getElementById('system-status').classList.remove('bg-green-500');
        document.getElementById('system-status').classList.add('bg-red-500');
        document.getElementById('status-text').textContent = 'Offline';
        this.addSystemLog('Conexão com o servidor perdida. Modo offline ativado');
      }
    });

    // API error events
    window.addEventListener('apiError', (e) => {
      this.addSystemLog(`Erro na API (${e.detail.endpoint}): ${e.detail.message}`);
      UIComponents.showToast(`Erro: ${e.detail.message}`, 'error');
    });
  }

  /**
   * Set up periodic updates
   */
  setupPeriodicUpdates() {
    // Update system status every 30 seconds
    setInterval(async () => {
      try {
        const response = await apiClient.getLatestStatus();
        if (response.ok) {
          this.updateSystemStatus(response.data);
        }
      } catch (error) {
        console.error('Failed to update system status:', error);
      }
    }, 30000);

    // Update metrics every 5 minutes
    setInterval(async () => {
      try {
        await this.fetchMetrics();

        // Update MAC-specific metrics if a MAC is selected
        const macInput = document.getElementById('mac-input');
        if (macInput.value) {
          await this.fetchMacMetrics(macInput.value);
        }
      } catch (error) {
        console.error('Failed to update metrics:', error);
      }
    }, 5 * 60 * 1000);
  }

  /**
   * Handle telemetry update
   */
  handleTelemetryUpdate(e) {
    this.addSystemLog(`Novo evento: MAC ${e.mac}, Direção ${e.direcao}, Status ${e.status}`);

    // Update vehicle info
    document.getElementById('vehicle-plate').textContent = e.placa || 'Desconhecida';
    document.getElementById('vehicle-direction').textContent = e.direcao === 'NS' ? 'Norte → Sul' : 'Sul → Norte';
    document.getElementById('vehicle-distance').textContent = e.distance ? `${e.distance} m` : '100 m';

    // Update signal strength
    const signalStrengthBar = document.getElementById('signal-strength');
    signalStrengthBar.style.width = e.signal_strength ? `${e.signal_strength}%` : '80%';

    // Update vehicle visualization
    this.updateVehicleVisualization(e.direcao, e.status);

    // Update charts
    this.fetchMetrics();

    // Update MAC-specific charts if this MAC is selected
    const macInput = document.getElementById('mac-input');
    if (macInput.value === e.mac) {
      this.fetchMacMetrics(e.mac);
    }
  }

  /**
   * Handle gate control update
   */
  handleGateControl(e) {
    const gate = e.gate;
    const action = e.action;

    // Update gate state
    this.state.barrierStatus[gate] = {
      state: action ? 'open' : 'closed',
      lastUpdated: new Date()
    };

    // Update UI
    this.updateGateUI(gate);

    // Add log
    this.addSystemLog(`Barreira ${gate === 'north' ? 'Norte-Sul' : 'Sul-Norte'} ${action ? 'aberta' : 'fechada'}`);
  }

  /**
   * Update system status
   */
  updateSystemStatus(data) {
    // Update state
    this.state.systemStatus = data.system_status;
    this.state.barrierStatus = data.barrier_status;
    this.state.lastUpdated = new Date();

    // Update UI
    this.updateGateUI('north');
    this.updateGateUI('south');

    // Update system status indicator
    const systemStatusEl = document.getElementById('system-status');
    const statusTextEl = document.getElementById('status-text');

    if (data.system_status === 'operational') {
      systemStatusEl.classList.remove('bg-red-500', 'bg-yellow-500');
      systemStatusEl.classList.add('bg-green-500');
      statusTextEl.textContent = 'Operacional';
    } else if (data.system_status === 'warning') {
      systemStatusEl.classList.remove('bg-red-500', 'bg-green-500');
      systemStatusEl.classList.add('bg-yellow-500');
      statusTextEl.textContent = 'Atenção';
    } else {
      systemStatusEl.classList.remove('bg-green-500', 'bg-yellow-500');
      systemStatusEl.classList.add('bg-red-500');
      statusTextEl.textContent = 'Erro';
    }

    // Update latest log if available
    if (data.latest_log) {
      const log = data.latest_log;
      document.getElementById('vehicle-plate').textContent = log.placa || 'Desconhecida';
      document.getElementById('vehicle-direction').textContent = log.direcao === 'NS' ? 'Norte → Sul' : 'Sul → Norte';
    }
  }

  /**
   * Update gate UI
   */
  updateGateUI(gate) {
    const gateEl = document.getElementById(`${gate}-gate`);
    const toggleBtn = document.getElementById(`${gate}-toggle`);
    const state = this.state.barrierStatus[gate].state;

    if (state === 'open') {
      gateEl.classList.remove('bg-red-600');
      gateEl.classList.add('bg-green-600');
      gateEl.innerHTML = '<i class="fas fa-lock-open"></i>';

      toggleBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
      toggleBtn.classList.add('bg-green-500', 'hover:bg-green-600');
      toggleBtn.innerHTML = '<i class="fas fa-lock-open mr-1"></i> Aberta';
    } else {
      gateEl.classList.remove('bg-green-600');
      gateEl.classList.add('bg-red-600');
      gateEl.innerHTML = '<i class="fas fa-lock"></i>';

      toggleBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
      toggleBtn.classList.add('bg-red-500', 'hover:bg-red-600');
      toggleBtn.innerHTML = '<i class="fas fa-lock mr-1"></i> Fechada';
    }
  }

  /**
   * Update vehicle visualization
   */
  updateVehicleVisualization(direction, status) {
    const vehicleMarker = document.getElementById('vehicle-marker');
    const directionIndicator = document.getElementById('direction-indicator');

    // Show vehicle marker
    vehicleMarker.style.display = 'flex';

    // Set initial position based on direction
    if (direction === 'NS') {
      vehicleMarker.style.left = '50%';
      vehicleMarker.style.top = '10%';

      directionIndicator.innerHTML = '<i class="fas fa-arrow-down direction-arrow text-green-500 text-2xl"></i>';
      directionIndicator.style.left = '55%';
      directionIndicator.style.top = '20%';
    } else {
      vehicleMarker.style.left = '50%';
      vehicleMarker.style.top = '90%';

      directionIndicator.innerHTML = '<i class="fas fa-arrow-up direction-arrow text-green-500 text-2xl"></i>';
      directionIndicator.style.left = '55%';
      directionIndicator.style.top = '80%';
    }

    // Show direction indicator
    directionIndicator.style.display = 'block';

    // Animate vehicle movement
    if (status === 'AUTORIZADO') {
      const targetTop = direction === 'NS' ? '90%' : '10%';

      // Animate vehicle
      vehicleMarker.style.transition = 'top 4s linear';
      vehicleMarker.style.top = targetTop;

      // Hide after animation
      setTimeout(() => {
        vehicleMarker.style.display = 'none';
        directionIndicator.style.display = 'none';

        // Reset transition for next use
        vehicleMarker.style.transition = '';
      }, 4000);
    } else {
      // For unauthorized vehicles, just show briefly and hide
      setTimeout(() => {
        vehicleMarker.style.display = 'none';
        directionIndicator.style.display = 'none';
      }, 2000);
    }
  }

  /**
   * Toggle gate
   */
  async toggleGate(gate) {
    try {
      const currentState = this.state.barrierStatus[gate].state;
      const newAction = currentState === 'closed';

      // Update UI immediately for better UX
      this.state.barrierStatus[gate] = {
        state: newAction ? 'open' : 'closed',
        lastUpdated: new Date()
      };
      this.updateGateUI(gate);

      // Send request to API
      const response = await apiClient.controlGate(gate, newAction);

      if (response.ok) {
        this.addSystemLog(`Barreira ${gate === 'north' ? 'Norte-Sul' : 'Sul-Norte'} ${newAction ? 'aberta' : 'fechada'} manualmente`);
      } else {
        // Revert UI if request failed
        this.state.barrierStatus[gate] = {
          state: currentState,
          lastUpdated: new Date()
        };
        this.updateGateUI(gate);

        this.addSystemLog(`Erro ao controlar barreira ${gate}: ${response.error}`);
        UIComponents.showToast(`Erro ao controlar barreira: ${response.error}`, 'error');
      }
    } catch (error) {
      console.error(`Failed to toggle gate ${gate}:`, error);
      this.addSystemLog(`Erro ao controlar barreira ${gate}: ${error.message}`);
      UIComponents.showToast(`Erro ao controlar barreira: ${error.message}`, 'error');
    }
  }

  /**
   * Add authorized MAC
   */
  async addAuthorizedMac() {
    const mac = document.getElementById('mac-address').value;
    const placa = document.getElementById('mac-plate').value;

    if (!mac || !placa) {
      UIComponents.showToast('Erro: MAC e placa são obrigatórios', 'error');
      this.addSystemLog('Erro: MAC e placa são obrigatórios');
      return;
    }

    // Validate MAC format
    const macRegex = /^[0-9A-Fa-f]{12}$/;
    if (!macRegex.test(mac)) {
      UIComponents.showToast('Erro: Formato de MAC inválido. Use 12 caracteres hexadecimais sem separadores', 'error');
      this.addSystemLog('Erro: Formato de MAC inválido');
      return;
    }

    try {
      const response = await apiClient.addAuthorizedMac({ mac, placa });

      if (response.ok) {
        if (response.offline) {
          UIComponents.showToast('MAC salvo para sincronização quando online', 'warning');
          this.addSystemLog(`MAC ${mac} salvo para sincronização quando online`);
        } else {
          UIComponents.showToast('MAC adicionado com sucesso', 'success');
          this.addSystemLog(`MAC ${mac} adicionado com sucesso`);
        }

        // Clear form
        document.getElementById('mac-address').value = '';
        document.getElementById('mac-plate').value = '';

        // Refresh list
        await this.fetchAuthorizedMacs();
      } else {
        UIComponents.showToast(`Erro ao adicionar MAC: ${response.error}`, 'error');
        this.addSystemLog(`Erro ao adicionar MAC: ${response.error}`);
      }
    } catch (error) {
      console.error('Failed to add MAC:', error);
      UIComponents.showToast(`Erro ao adicionar MAC: ${error.message}`, 'error');
      this.addSystemLog(`Erro ao adicionar MAC: ${error.message}`);
    }
  }

  /**
   * Delete MAC
   */
  async deleteMac(mac) {
    // Ask for confirmation
    const confirmed = await UIComponents.showConfirmation(`Tem certeza que deseja excluir o MAC ${mac}?`);
    if (!confirmed) return;

    try {
      const response = await apiClient.deleteAuthorizedMac(mac);

      if (response.ok) {
        UIComponents.showToast('MAC excluído com sucesso', 'success');
        this.addSystemLog(`MAC ${mac} excluído com sucesso`);

        // Refresh list
        await this.fetchAuthorizedMacs();
      } else {
        UIComponents.showToast(`Erro ao excluir MAC: ${response.error}`, 'error');
        this.addSystemLog(`Erro ao excluir MAC: ${response.error}`);
      }
    } catch (error) {
      console.error('Failed to delete MAC:', error);
      UIComponents.showToast(`Erro ao excluir MAC: ${error.message}`, 'error');
      this.addSystemLog(`Erro ao excluir MAC: ${error.message}`);
    }
  }

  /**
   * Handle file upload
   */
  async handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (e) => {
      const content = e.target.result;
      const lines = content.split('\n').filter(line => line.trim() !== '');
      const macs = lines.map(line => {
        const [mac, placa] = line.split(',');
        return { mac: mac.trim(), placa: placa.trim() };
      });

      // Validate MACs
      const validMacs = macs.filter(mac => {
        const macRegex = /^[0-9A-Fa-f]{12}$/;
        return macRegex.test(mac.mac) && mac.placa.length <= 10;
      });

      if (validMacs.length === 0) {
        UIComponents.showToast('Erro: Nenhum MAC válido encontrado no ficheiro', 'error');
        this.addSystemLog('Erro: Nenhum MAC válido encontrado no ficheiro');
        return;
      }

      try {
        const response = await apiClient.addBulkAuthorizedMacs(validMacs);

        if (response.ok) {
          if (response.offline) {
            UIComponents.showToast(`${validMacs.length} MACs salvos para sincronização quando online`, 'warning');
            this.addSystemLog(`${validMacs.length} MACs salvos para sincronização quando online`);
          } else {
            UIComponents.showToast(`${validMacs.length} MACs adicionados com sucesso`, 'success');
            this.addSystemLog(`${validMacs.length} MACs adicionados com sucesso`);
          }

          // Clear file input
          event.target.value = '';

          // Refresh list
          await this.fetchAuthorizedMacs();
        } else {
          UIComponents.showToast(`Erro ao adicionar MACs: ${response.error}`, 'error');
          this.addSystemLog(`Erro ao adicionar MACs: ${response.error}`);
        }
      } catch (error) {
        console.error('Failed to add MACs:', error);
        UIComponents.showToast(`Erro ao adicionar MACs: ${error.message}`, 'error');
        this.addSystemLog(`Erro ao adicionar MACs: ${error.message}`);
      }
    };
    reader.readAsText(file);
  }

  /**
   * Download MACs
   */
  async downloadMacs() {
    try {
      const response = await apiClient.downloadAuthorizedMacs();

      if (!response.ok) {
        UIComponents.showToast(`Erro ao baixar MACs: ${response.error}`, 'error');
        this.addSystemLog(`Erro ao baixar MACs: ${response.error}`);
      }
    } catch (error) {
      console.error('Failed to download MACs:', error);
      UIComponents.showToast(`Erro ao baixar MACs: ${error.message}`, 'error');
      this.addSystemLog(`Erro ao baixar MACs: ${error.message}`);
    }
  }

  /**
   * Fetch authorized MACs
   */
  async fetchAuthorizedMacs() {
    try {
      const response = await apiClient.getAuthorizedMacs(
        this.state.currentPage,
        this.state.searchTerm
      );

      if (response.ok) {
        this.state.authorizedMacs = response.data.data;

        // Update table
        this.tables.authorizedMacs.refresh(response.data.data);

        // Update MAC datalist for metrics
        const macList = document.getElementById('mac-list');
        macList.innerHTML = '';
        response.data.data.forEach(mac => {
          const option = document.createElement('option');
          option.value = mac.mac;
          option.textContent = `${mac.mac} (${mac.placa})`;
          macList.appendChild(option);
        });
      } else {
        UIComponents.showToast(`Erro ao carregar MACs: ${response.error}`, 'error');
        this.addSystemLog(`Erro ao carregar MACs: ${response.error}`);
      }
    } catch (error) {
      console.error('Failed to fetch authorized MACs:', error);
      UIComponents.showToast(`Erro ao carregar MACs: ${error.message}`, 'error');
      this.addSystemLog(`Erro ao carregar MACs: ${error.message}`);
    }
  }

  /**
   * Fetch metrics
   */
  async fetchMetrics() {
    try {
      const response = await apiClient.getMetrics();

      if (response.ok) {
        // Update daily chart
        this.charts.daily.data.labels = response.data.daily.labels;
        this.charts.daily.data.datasets[0].data = response.data.daily.data;
        this.charts.daily.update();

        // Update weekly chart
        this.charts.weekly.data.labels = response.data.weekly.labels;
        this.charts.weekly.data.datasets[0].data = response.data.weekly.data;
        this.charts.weekly.update();

        // Update monthly chart
        this.charts.monthly.data.labels = response.data.monthly.labels;
        this.charts.monthly.data.datasets[0].data = response.data.monthly.data;
        this.charts.monthly.update();
      } else {
        console.error('Failed to fetch metrics:', response.error);
      }
    } catch (error) {
      console.error('Failed to fetch metrics:', error);
    }
  }

  /**
   * Fetch MAC-specific metrics
   */
  async fetchMacMetrics(mac) {
    try {
      const response = await apiClient.getMacMetrics(mac);

      if (response.ok) {
        // Update daily chart
        this.charts.macDaily.data.labels = response.data.daily.labels;
        this.charts.macDaily.data.datasets[0].data = response.data.daily.data;
        this.charts.macDaily.update();

        // Update weekly chart
        this.charts.macWeekly.data.labels = response.data.weekly.labels;
        this.charts.macWeekly.data.datasets[0].data = response.data.weekly.data;
        this.charts.macWeekly.update();

        // Update monthly chart
        this.charts.macMonthly.data.labels = response.data.monthly.labels;
        this.charts.macMonthly.data.datasets[0].data = response.data.monthly.data;
        this.charts.macMonthly.update();
      } else {
        console.error('Failed to fetch MAC metrics:', response.error);
      }
    } catch (error) {
      console.error('Failed to fetch MAC metrics:', error);
    }
  }

  /**
   * Start simulation
   */
  startSimulation() {
    if (this.state.simulationActive) {
      UIComponents.showToast('Simulação já está ativa', 'warning');
      return;
    }

    const plate = document.getElementById('sim-plate').value || 'ABC-1234';
    const direction = document.getElementById('sim-direction').value;

    this.state.simulationActive = true;
    this.addSystemLog(`Iniciando simulação: Placa ${plate}, Direção ${direction === 'north' ? 'Norte-Sul' : 'Sul-Norte'}`);

    // Update UI
    document.getElementById('start-sim').disabled = true;
    document.getElementById('stop-sim').disabled = false;

    // Set vehicle info
    document.getElementById('vehicle-plate').textContent = plate;
    document.getElementById('vehicle-direction').textContent = direction === 'north' ? 'Norte → Sul' : 'Sul → Norte';

    // Update vehicle visualization
    this.updateVehicleVisualization(
      direction === 'north' ? 'NS' : 'SN',
      'AUTORIZADO'
    );

    // Simulate signal strength
    let signalStrength = 0;
    const signalStrengthBar = document.getElementById('signal-strength');

    // Simulate distance
    let distance = 500;
    const vehicleDistance = document.getElementById('vehicle-distance');

    // Create simulation interval
    this.state.currentSimulation = setInterval(() => {
      // Update signal strength (increases as vehicle approaches)
      signalStrength = Math.min(100, signalStrength + 5);
      signalStrengthBar.style.width = `${signalStrength}%`;

      // Update distance (decreases as vehicle approaches)
      distance = Math.max(0, distance - 25);
      vehicleDistance.textContent = `${distance} m`;

      // When vehicle is close enough, open the gate
      if (distance <= 50 && distance > 0) {
        const gate = direction === 'north' ? 'north' : 'south';

        // Only open if not already open
        if (this.state.barrierStatus[gate].state === 'closed') {
          this.toggleGate(gate);
        }
      }

      // End simulation when vehicle has passed
      if (distance === 0) {
        setTimeout(() => {
          if (this.state.simulationActive) {
            this.stopSimulation();
          }
        }, 2000);
      }
    }, 500);
  }

  /**
   * Stop simulation
   */
  stopSimulation() {
    if (!this.state.simulationActive) return;

    // Clear interval
    if (this.state.currentSimulation) {
      clearInterval(this.state.currentSimulation);
      this.state.currentSimulation = null;
    }

    // Update state
    this.state.simulationActive = false;

    // Update UI
    document.getElementById('start-sim').disabled = false;
    document.getElementById('stop-sim').disabled = true;

    // Reset vehicle visualization
    const vehicleMarker = document.getElementById('vehicle-marker');
    const directionIndicator = document.getElementById('direction-indicator');
    vehicleMarker.style.display = 'none';
    directionIndicator.style.display = 'none';

    // Reset signal strength
    document.getElementById('signal-strength').style.width = '0%';

    // Reset distance
    document.getElementById('vehicle-distance').textContent = '0 m';

    this.addSystemLog('Simulação encerrada');
  }

  /**
   * Add system log
   */
  addSystemLog(message) {
    const systemLog = document.getElementById('system-log');
    const logEntry = document.createElement('div');

    // Format timestamp
    const now = new Date();
    const timestamp = UIComponents.formatDate(now, 'HH:mm:ss');

    // Create log entry
    logEntry.className = 'text-gray-700';
    logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;

    // Add to log
    systemLog.appendChild(logEntry);

    // Scroll to bottom
    systemLog.scrollTop = systemLog.scrollHeight;

    // Limit log entries
    while (systemLog.children.length > 100) {
      systemLog.removeChild(systemLog.firstChild);
    }
  }
}

// Initialize application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.app = new BarrierControlApp();
});
