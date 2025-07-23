/**
 * Main Application for IoT Barrier Control System
 * Handles all application logic and UI interactions
 */

// Main application class
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
      // Definir Echo globalmente para desenvolvimento
      window.Echo = {
        channel: function(name) {
          console.log(`Simulando canal Echo: ${name}`);
          return {
            listen: function(event, callback) {
              console.log(`Simulando escuta de evento Echo: ${event}`);
              return this;
            }
          };
        },
        connector: {
          pusher: {
            connection: {
              bind: function(event, callback) {
                console.log(`Simulando binding de evento Echo: ${event}`);
              }
            }
          }
        },
        connect: function() {
          console.log('Simulando conexão Echo');
        }
      };

      // Usar o Echo global
      this.echo = window.Echo;

      this.addSystemLog('WebSockets simulados para desenvolvimento');

      // Atualizar UI para mostrar que estamos "conectados"
      document.getElementById('system-status').classList.remove('bg-red-500');
      document.getElementById('system-status').classList.add('bg-green-500');
      document.getElementById('status-text').textContent = 'Operacional';

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
    console.log('Inicializando gráficos...');

    // Verificar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
      console.error('Chart.js não está disponível. Tentando novamente em 1 segundo...');
      setTimeout(() => this.initializeCharts(), 1000);
      return;
    }

    try {
      // Daily access chart
      console.log('Criando gráfico diário...');
      this.charts.daily = UIComponents.createChart('daily-chart', 'bar', {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
        datasets: [{
          label: 'Acessos',
          data: [2, 1, 4, 8, 6, 3],
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgb(59, 130, 246)',
          borderWidth: 1
        }]
      });

      // Weekly access chart
      console.log('Criando gráfico semanal...');
      this.charts.weekly = UIComponents.createChart('weekly-chart', 'line', {
        labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        datasets: [{
          label: 'Acessos',
          data: [12, 19, 15, 25, 22, 8, 5],
          backgroundColor: 'rgba(16, 185, 129, 0.2)',
          borderColor: 'rgb(16, 185, 129)',
          borderWidth: 2,
          tension: 0.3,
          fill: true
        }]
      });

      // Monthly access chart
      console.log('Criando gráfico mensal...');
      this.charts.monthly = UIComponents.createChart('monthly-chart', 'bar', {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        datasets: [{
          label: 'Acessos',
          data: [65, 59, 80, 81, 56, 72],
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
    } catch (error) {
      console.error('Error initializing charts:', error);
      this.addSystemLog('Erro ao inicializar gráficos: ' + error.message);
    }
  }

  /**
   * Initialize data tables
   */
  initializeTables() {
    try {
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
    } catch (error) {
      console.error('Error initializing tables:', error);
      this.addSystemLog('Erro ao inicializar tabelas: ' + error.message);
    }
  }

  /**
   * Initialize vehicle visualization
   */
  initializeVehicleVisualization() {
    // Set initial position for vehicle marker (hidden)
    const vehicleMarker = document.getElementById('vehicle-marker');
    if (vehicleMarker) {
      vehicleMarker.style.display = 'none';
      vehicleMarker.style.left = '50%';
      vehicleMarker.style.top = '50%';
    }

    // Set initial position for direction indicator (hidden)
    const directionIndicator = document.getElementById('direction-indicator');
    if (directionIndicator) {
      directionIndicator.style.display = 'none';
    }
  }

  /**
   * Load initial data
   */
  async loadInitialData() {
    try {
      // Show loading state
      const systemStatus = document.getElementById('system-status');
      const statusText = document.getElementById('status-text');

      if (systemStatus && statusText) {
        systemStatus.classList.add('bg-yellow-500');
        statusText.textContent = 'Carregando...';
      }

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
      if (systemStatus && statusText) {
        systemStatus.classList.remove('bg-yellow-500');
        systemStatus.classList.add('bg-green-500');
        statusText.textContent = 'Operacional';
      }

      // Add log
      this.addSystemLog('Sistema inicializado com sucesso');
    } catch (error) {
      console.error('Failed to load initial data:', error);

      const systemStatus = document.getElementById('system-status');
      const statusText = document.getElementById('status-text');

      if (systemStatus && statusText) {
        systemStatus.classList.remove('bg-yellow-500');
        systemStatus.classList.add('bg-red-500');
        statusText.textContent = 'Erro';
      }

      this.addSystemLog('Erro ao carregar dados iniciais: ' + error.message);
    }
  }

  /**
   * Add event listeners
   */
  addEventListeners() {
    // Gate control buttons
    const northToggle = document.getElementById('north-toggle');
    const southToggle = document.getElementById('south-toggle');

    if (northToggle) {
      northToggle.addEventListener('click', () => this.toggleGate('north'));
    }

    if (southToggle) {
      southToggle.addEventListener('click', () => this.toggleGate('south'));
    }

    // MAC authorization form
    const addMac = document.getElementById('add-mac');
    if (addMac) {
      addMac.addEventListener('click', this.addAuthorizedMac.bind(this));
    }

    // MAC file upload
    const macFile = document.getElementById('mac-file');
    if (macFile) {
      macFile.addEventListener('change', this.handleFileUpload.bind(this));
    }

    // MAC download
    const downloadMacs = document.getElementById('download-macs');
    if (downloadMacs) {
      downloadMacs.addEventListener('click', this.downloadMacs.bind(this));
    }

    // File instructions modal
    const fileInstructions = document.getElementById('file-instructions');
    const closeModal = document.getElementById('close-modal');
    const instructionsModal = document.getElementById('instructions-modal');

    if (fileInstructions && instructionsModal) {
      fileInstructions.addEventListener('click', () => {
        instructionsModal.classList.remove('hidden');
      });
    }

    if (closeModal && instructionsModal) {
      closeModal.addEventListener('click', () => {
        instructionsModal.classList.add('hidden');
      });
    }

    // Simulation controls
    const startSim = document.getElementById('start-sim');
    const stopSim = document.getElementById('stop-sim');

    if (startSim) {
      startSim.addEventListener('click', this.startSimulation.bind(this));
    }

    if (stopSim) {
      stopSim.addEventListener('click', this.stopSimulation.bind(this));
    }

    // MAC search
    const macSearch = document.getElementById('mac-search');
    if (macSearch) {
      macSearch.addEventListener('input', (e) => {
        this.state.searchTerm = e.target.value;
        this.fetchAuthorizedMacs();
      });
    }

    // Pagination
    const prevPage = document.getElementById('prev-page');
    const nextPage = document.getElementById('next-page');

    if (prevPage) {
      prevPage.addEventListener('click', () => {
        if (this.state.currentPage > 1) {
          this.state.currentPage--;
          this.fetchAuthorizedMacs();
        }
      });
    }

    if (nextPage) {
      nextPage.addEventListener('click', () => {
        this.state.currentPage++;
        this.fetchAuthorizedMacs();
      });
    }

    // MAC metrics
    const macInput = document.getElementById('mac-input');
    if (macInput) {
      macInput.addEventListener('change', (e) => {
        const mac = e.target.value;
        if (mac) {
          this.fetchMacMetrics(mac);
        }
      });
    }

    // Connection change events
    window.addEventListener('connectionChange', (e) => {
      this.state.offlineMode = !e.detail.online;

      const systemStatus = document.getElementById('system-status');
      const statusText = document.getElementById('status-text');

      if (e.detail.online) {
        if (systemStatus && statusText) {
          systemStatus.classList.remove('bg-red-500');
          systemStatus.classList.add('bg-green-500');
          statusText.textContent = 'Operacional';
        }
        this.addSystemLog('Conexão com o servidor restaurada');
      } else {
        if (systemStatus && statusText) {
          systemStatus.classList.remove('bg-green-500');
          systemStatus.classList.add('bg-red-500');
          statusText.textContent = 'Offline';
        }
        this.addSystemLog('Conexão com o servidor perdida. Modo offline ativado');
      }
    });

    // API error events
    window.addEventListener('apiError', (e) => {
      this.addSystemLog(`Erro na API (${e.detail.endpoint}): ${e.detail.message}`);
      UIComponents.showToast(`Erro: ${e.detail.message}`, 'error');
    });

    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', this.logout.bind(this));
    }
  }

  /**
   * Logout user
   */
  logout() {
    // Clear authentication token
    localStorage.removeItem('auth_token');

    // Redirect to login page
    window.location.href = 'login.html';
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

    // Controle para evitar atualizações excessivas dos gráficos
    let lastMetricsUpdate = 0;
    const METRICS_UPDATE_INTERVAL = 300000; // 5 minutos em milissegundos

    // Update metrics every 5 minutes (controlled)
    setInterval(async () => {
      try {
        const now = Date.now();

        // Só atualizar se já se passaram pelo menos 5 minutos desde a última atualização
        if (now - lastMetricsUpdate >= METRICS_UPDATE_INTERVAL) {
          console.log('Atualizando métricas...');
          await this.fetchMetrics();
          lastMetricsUpdate = now;

          // Update MAC-specific metrics if a MAC is selected
          const macInput = document.getElementById('mac-input');
          if (macInput && macInput.value) {
            await this.fetchMacMetrics(macInput.value);
          }
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
    const vehiclePlate = document.getElementById('vehicle-plate');
    const vehicleDirection = document.getElementById('vehicle-direction');
    const vehicleDistance = document.getElementById('vehicle-distance');
    const signalStrengthBar = document.getElementById('signal-strength');

    if (vehiclePlate) {
      vehiclePlate.textContent = e.placa || 'Desconhecida';
    }

    if (vehicleDirection) {
      vehicleDirection.textContent = e.direcao === 'NS' ? 'Norte → Sul' : 'Sul → Norte';
    }

    if (vehicleDistance) {
      vehicleDistance.textContent = e.distance ? `${e.distance} m` : '100 m';
    }

    if (signalStrengthBar) {
      signalStrengthBar.style.width = e.signal_strength ? `${e.signal_strength}%` : '80%';
    }

    // Update vehicle visualization
    this.updateVehicleVisualization(e.direcao, e.status);

    // Update charts
    this.fetchMetrics();

    // Update MAC-specific charts if this MAC is selected
    const macInput = document.getElementById('mac-input');
    if (macInput && macInput.value === e.mac) {
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

    if (systemStatusEl && statusTextEl) {
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
    }

    // Update latest log if available
    if (data.latest_log) {
      const log = data.latest_log;
      const vehiclePlate = document.getElementById('vehicle-plate');
      const vehicleDirection = document.getElementById('vehicle-direction');

      if (vehiclePlate) {
        vehiclePlate.textContent = log.placa || 'Desconhecida';
      }

      if (vehicleDirection) {
        vehicleDirection.textContent = log.direcao === 'NS' ? 'Norte → Sul' : 'Sul → Norte';
      }
    }
  }

  /**
   * Update gate UI
   */
  updateGateUI(gate) {
    const gateEl = document.getElementById(`${gate}-gate`);
    const toggleBtn = document.getElementById(`${gate}-toggle`);

    if (!gateEl || !toggleBtn) return;

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

    if (!vehicleMarker || !directionIndicator) return;

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
   * Add authorized MAC with duplicate validation
   */
  async addAuthorizedMac() {
    const macAddress = document.getElementById('mac-address');
    const macPlate = document.getElementById('mac-plate');

    if (!macAddress || !macPlate) return;

    const mac = macAddress.value.trim();
    const placa = macPlate.value.trim();

    if (!mac || !placa) {
      UIComponents.showToast('Erro: MAC e placa são obrigatórios', 'error');
      this.addSystemLog('Erro: MAC e placa são obrigatórios');
      return;
    }

    // Validate MAC format (accept both with and without colons)
    const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$|^[0-9A-Fa-f]{12}$/;
    if (!macRegex.test(mac)) {
      UIComponents.showToast('Erro: Formato de MAC inválido. Use formato XX:XX:XX:XX:XX:XX ou XXXXXXXXXXXX', 'error');
      this.addSystemLog('Erro: Formato de MAC inválido');
      return;
    }

    // Validate plate format (basic validation)
    const plateRegex = /^[A-Z0-9]{2,3}-[A-Z0-9]{2,4}$/i;
    if (!plateRegex.test(placa)) {
      UIComponents.showToast('Erro: Formato de matrícula inválido. Use formato ABC-1234', 'error');
      this.addSystemLog('Erro: Formato de matrícula inválido');
      return;
    }

    try {
      // Check for duplicates using search manager
      if (window.searchManager) {
        const result = await window.searchManager.addVehicle(mac, placa.toUpperCase(), true);
        
        if (result === null) {
          // User cancelled the operation
          this.addSystemLog('Operação cancelada pelo utilizador');
          return;
        }

        // If we get here, the vehicle was added/updated successfully
        UIComponents.showToast('Veículo processado com sucesso', 'success');
        
        // Clear form only if successful
        macAddress.value = '';
        macPlate.value = '';

        // Try to add to API as well
        const cleanMac = mac.replace(/[:-]/g, ''); // Remove separators for API
        const response = await apiClient.addAuthorizedMac({ mac: cleanMac, placa: placa.toUpperCase() });

        if (response.ok) {
          if (response.offline) {
            UIComponents.showToast('Dados também salvos para sincronização quando online', 'info');
            this.addSystemLog(`MAC ${cleanMac} salvo para sincronização quando online`);
          } else {
            this.addSystemLog(`MAC ${cleanMac} sincronizado com servidor`);
          }
        } else {
          // Even if API fails, local storage worked
          UIComponents.showToast('Salvo localmente. Erro na sincronização: ' + response.error, 'warning');
          this.addSystemLog(`Erro na sincronização: ${response.error}`);
        }

        // Refresh list
        await this.fetchAuthorizedMacs();
      } else {
        // Fallback to original method if search manager not available
        const response = await apiClient.addAuthorizedMac({ mac: mac.replace(/[:-]/g, ''), placa: placa.toUpperCase() });

        if (response.ok) {
          if (response.offline) {
            UIComponents.showToast('MAC salvo para sincronização quando online', 'warning');
            this.addSystemLog(`MAC ${mac} salvo para sincronização quando online`);
          } else {
            UIComponents.showToast('MAC adicionado com sucesso', 'success');
            this.addSystemLog(`MAC ${mac} adicionado com sucesso`);
          }

          // Clear form
          macAddress.value = '';
          macPlate.value = '';

          // Refresh list
          await this.fetchAuthorizedMacs();
        } else {
          UIComponents.showToast(`Erro ao adicionar MAC: ${response.error}`, 'error');
          this.addSystemLog(`Erro ao adicionar MAC: ${response.error}`);
        }
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
        if (this.tables.authorizedMacs) {
          this.tables.authorizedMacs.refresh(response.data.data);
        }

        // Update MAC datalist for metrics
        const macList = document.getElementById('mac-list');
        if (macList) {
          macList.innerHTML = '';
          response.data.data.forEach(mac => {
            const option = document.createElement('option');
            option.value = mac.mac;
            option.textContent = `${mac.mac} (${mac.placa})`;
            macList.appendChild(option);
          });
        }
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
    // Controle para evitar atualizacoes excessivas
    const now = Date.now();
    if (this._lastMetricsUpdate && (now - this._lastMetricsUpdate) < 60000) {
      console.log("Ignorando atualizacao de metricas (muito frequente)");
      return;
    }
    this._lastMetricsUpdate = now;
    console.log("Atualizando métricas...");
    try {
      const response = await apiClient.getMetrics();

      if (response.ok) {
        console.log("Dados de métricas recebidos:", response.data);

        // Verificar se os gráficos existem, se não, criá-los
        if (!this.charts.daily || !this.charts.weekly || !this.charts.monthly) {
          this.initializeCharts();
        }

        // Atualizar os dados dos gráficos existentes
        if (this.charts.daily && response.data.daily) {
          this.charts.daily.data.labels = response.data.daily.labels;
          this.charts.daily.data.datasets[0].data = response.data.daily.data;
          this.charts.daily.update('none'); // Atualizar sem animação para melhor performance
        }

        if (this.charts.weekly && response.data.weekly) {
          this.charts.weekly.data.labels = response.data.weekly.labels;
          this.charts.weekly.data.datasets[0].data = response.data.weekly.data;
          this.charts.weekly.update('none'); // Atualizar sem animação para melhor performance
        }

        if (this.charts.monthly && response.data.monthly) {
          this.charts.monthly.data.labels = response.data.monthly.labels;
          this.charts.monthly.data.datasets[0].data = response.data.monthly.data;
          this.charts.monthly.update('none'); // Atualizar sem animação para melhor performance
        }
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

      if (response.ok && this.charts.macDaily && this.charts.macWeekly && this.charts.macMonthly) {
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

    const simPlate = document.getElementById('sim-plate');
    const simDirection = document.getElementById('sim-direction');
    const startSim = document.getElementById('start-sim');
    const stopSim = document.getElementById('stop-sim');
    const vehiclePlate = document.getElementById('vehicle-plate');
    const vehicleDirection = document.getElementById('vehicle-direction');
    const signalStrengthBar = document.getElementById('signal-strength');
    const vehicleDistance = document.getElementById('vehicle-distance');

    if (!simPlate || !simDirection || !startSim || !stopSim || !vehiclePlate || !vehicleDirection || !signalStrengthBar || !vehicleDistance) {
      return;
    }

    const plate = simPlate.value || 'ABC-1234';
    const direction = simDirection.value;

    this.state.simulationActive = true;
    this.addSystemLog(`Iniciando simulação: Placa ${plate}, Direção ${direction === 'north' ? 'Norte-Sul' : 'Sul-Norte'}`);

    // Update UI
    startSim.disabled = true;
    stopSim.disabled = false;

    // Set vehicle info
    vehiclePlate.textContent = plate;
    vehicleDirection.textContent = direction === 'north' ? 'Norte → Sul' : 'Sul → Norte';

    // Update vehicle visualization
    this.updateVehicleVisualization(
      direction === 'north' ? 'NS' : 'SN',
      'AUTORIZADO'
    );

    // Simulate signal strength
    let signalStrength = 0;

    // Simulate distance
    let distance = 500;

    // Create simulation interval
    this.state.currentSimulation = setInterval(() => {
      // Update signal strength (increases as vehicle approaches)
      signalStrength = Math.min(100, signalStrength + 5);
      signalStrengthBar.style.width = `${signalStrength}%`;

      // Update distance (decreases as vehicle approaches)
      distance = Math.max(0, distance - 25);
      vehicleDistance.textContent = `${distance} m`;

      // When vehicle is close enough, open gate
      if (distance <= 100) {
        if (direction === 'north') {
          this.state.barrierStatus.north = { state: 'open', lastUpdated: new Date() };
          this.state.barrierStatus.south = { state: 'locked', lastUpdated: new Date() };
        } else {
          this.state.barrierStatus.south = { state: 'open', lastUpdated: new Date() };
          this.state.barrierStatus.north = { state: 'locked', lastUpdated: new Date() };
        }
        this.updateGateUI('north');
        this.updateGateUI('south');
      }

      // End simulation when vehicle reaches destination
      if (distance === 0) {
        this.stopSimulation();
      }
    }, 200);
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

    this.state.simulationActive = false;
    this.addSystemLog('Simulação finalizada');

    // Update UI
    const startSim = document.getElementById('start-sim');
    const stopSim = document.getElementById('stop-sim');
    const vehicleMarker = document.getElementById('vehicle-marker');
    const directionIndicator = document.getElementById('direction-indicator');
    const vehicleDirection = document.getElementById('vehicle-direction');
    const vehicleDistance = document.getElementById('vehicle-distance');
    const signalStrengthBar = document.getElementById('signal-strength');

    if (startSim) startSim.disabled = false;
    if (stopSim) stopSim.disabled = true;

    // Hide vehicle marker and direction indicator
    if (vehicleMarker) vehicleMarker.style.display = 'none';
    if (directionIndicator) directionIndicator.style.display = 'none';

    // Reset vehicle info
    if (vehicleDirection) vehicleDirection.textContent = 'Nenhuma';
    if (vehicleDistance) vehicleDistance.textContent = '0 m';
    if (signalStrengthBar) signalStrengthBar.style.width = '0%';

    // Reset gates after 5 seconds
    setTimeout(() => {
      this.state.barrierStatus.north = { state: 'closed', lastUpdated: new Date() };
      this.state.barrierStatus.south = { state: 'closed', lastUpdated: new Date() };
      this.updateGateUI('north');
      this.updateGateUI('south');
    }, 5000);
  }

  /**
   * Add system log
   */
  addSystemLog(message) {
    const systemLog = document.getElementById('system-log');
    if (!systemLog) return;

    const timestamp = UIComponents.formatDate(new Date(), 'HH:mm:ss');
    const logEntry = document.createElement('div');
    logEntry.className = 'text-gray-700';
    logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;

    systemLog.appendChild(logEntry);
    systemLog.scrollTop = systemLog.scrollHeight;

    // Limit log entries
    while (systemLog.children.length > 100) {
      systemLog.removeChild(systemLog.firstChild);
    }
  }
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.app = new BarrierControlApp();
});
