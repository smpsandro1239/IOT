/**
 * System Configuration Module
 * Handles LoRa and security configuration settings
 */

class SystemConfiguration {
    constructor() {
        // Default configuration
        this.config = {
            lora: {
                frequency: 868,
                power: 14,
                spreadingFactor: 10,
                bandwidth: 125
            },
            security: {
                blockTime: 5,
                activationDistance: 100,
                maxRange: 500,
                autoClose: true,
                emergencyMode: false
            }
        };

        this.init();
    }

    /**
     * Initialize configuration system
     */
    init() {
        // Get DOM elements
        this.loraFrequency = document.getElementById('lora-frequency');
        this.loraPower = document.getElementById('lora-power');
        this.powerValue = document.getElementById('power-value');
        this.loraSF = document.getElementById('lora-sf');
        this.loraBW = document.getElementById('lora-bw');

        this.blockTime = document.getElementById('block-time');
        this.activationDistance = document.getElementById('activation-distance');
        this.maxRange = document.getElementById('max-range');
        this.autoClose = document.getElementById('auto-close');
        this.emergencyMode = document.getElementById('emergency-mode');

        this.saveButton = document.getElementById('save-config');
        this.resetButton = document.getElementById('reset-config');
        this.exportButton = document.getElementById('export-config');
        this.configStatus = document.getElementById('config-status');
        this.configMessage = document.getElementById('config-message');

        // Add event listeners
        this.addEventListeners();

        // Load saved configuration or set defaults
        this.loadConfiguration();

        // Update UI
        this.updateUI();
    }

    /**
     * Add event listeners to configuration controls
     */
    addEventListeners() {
        // LoRa configuration
        if (this.loraFrequency) {
            this.loraFrequency.addEventListener('change', () => this.updateConfig());
        }

        if (this.loraPower) {
            this.loraPower.addEventListener('input', () => {
                this.updatePowerDisplay();
                this.updateConfig();
            });
        }

        if (this.loraSF) {
            this.loraSF.addEventListener('change', () => this.updateConfig());
        }

        if (this.loraBW) {
            this.loraBW.addEventListener('change', () => this.updateConfig());
        }

        // Security configuration
        if (this.blockTime) {
            this.blockTime.addEventListener('change', () => this.updateConfig());
        }

        if (this.activationDistance) {
            this.activationDistance.addEventListener('input', () => this.updateConfig());
        }

        if (this.maxRange) {
            this.maxRange.addEventListener('input', () => this.updateConfig());
        }

        if (this.autoClose) {
            this.autoClose.addEventListener('change', () => this.updateConfig());
        }

        if (this.emergencyMode) {
            this.emergencyMode.addEventListener('change', () => this.updateConfig());
        }

        // Action buttons
        if (this.saveButton) {
            this.saveButton.addEventListener('click', () => this.saveConfiguration());
        }

        if (this.resetButton) {
            this.resetButton.addEventListener('click', () => this.resetConfiguration());
        }

        if (this.exportButton) {
            this.exportButton.addEventListener('click', () => this.exportConfiguration());
        }
    }

    /**
     * Update power display value
     */
    updatePowerDisplay() {
        if (this.loraPower && this.powerValue) {
            this.powerValue.textContent = `${this.loraPower.value} dBm`;
        }
    }

    /**
     * Update configuration from UI
     */
    updateConfig() {
        if (this.loraFrequency) {
            this.config.lora.frequency = parseInt(this.loraFrequency.value);
        }

        if (this.loraPower) {
            this.config.lora.power = parseInt(this.loraPower.value);
        }

        if (this.loraSF) {
            this.config.lora.spreadingFactor = parseInt(this.loraSF.value);
        }

        if (this.loraBW) {
            this.config.lora.bandwidth = parseFloat(this.loraBW.value);
        }

        if (this.blockTime) {
            this.config.security.blockTime = parseInt(this.blockTime.value);
        }

        if (this.activationDistance) {
            this.config.security.activationDistance = parseInt(this.activationDistance.value);
        }

        if (this.maxRange) {
            this.config.security.maxRange = parseInt(this.maxRange.value);
        }

        if (this.autoClose) {
            this.config.security.autoClose = this.autoClose.checked;
        }

        if (this.emergencyMode) {
            this.config.security.emergencyMode = this.emergencyMode.checked;
        }

        // Update power display
        this.updatePowerDisplay();

        // Apply emergency mode if enabled
        this.applyEmergencyMode();

        // Log configuration change
        this.addLog('Configuração atualizada');

        // Apply configuration changes to the system
        this.applyConfigChanges();

        // Auto-save configuration
        this.saveConfiguration(false);
    }

    /**
     * Apply configuration changes to the system
     */
    applyConfigChanges() {
        // Apply activation distance to radar simulation
        if (window.radarSimulation && this.config.security.activationDistance) {
            window.radarSimulation.activationDistance = this.config.security.activationDistance;
        }

        // Apply max range to radar simulation
        if (window.radarSimulation && this.config.security.maxRange) {
            window.radarSimulation.maxDistance = this.config.security.maxRange;
        }

        // Apply auto-close setting
        if (window.radarSimulation && this.config.security.autoClose !== undefined) {
            window.radarSimulation.autoClose = this.config.security.autoClose;
        }
    }

    /**
     * Update UI from configuration
     */
    updateUI() {
        if (this.loraFrequency) {
            this.loraFrequency.value = this.config.lora.frequency;
        }

        if (this.loraPower) {
            this.loraPower.value = this.config.lora.power;
        }

        if (this.loraSF) {
            this.loraSF.value = this.config.lora.spreadingFactor;
        }

        if (this.loraBW) {
            this.loraBW.value = this.config.lora.bandwidth;
        }

        if (this.blockTime) {
            this.blockTime.value = this.config.security.blockTime;
        }

        if (this.activationDistance) {
            this.activationDistance.value = this.config.security.activationDistance;
        }

        if (this.maxRange) {
            this.maxRange.value = this.config.security.maxRange;
        }

        if (this.autoClose) {
            this.autoClose.checked = this.config.security.autoClose;
        }

        if (this.emergencyMode) {
            this.emergencyMode.checked = this.config.security.emergencyMode;
        }

        // Update power display
        this.updatePowerDisplay();
    }

    /**
     * Apply emergency mode settings
     */
    applyEmergencyMode() {
        if (this.config.security.emergencyMode) {
            // In emergency mode, keep all barriers open
            if (window.barrierStates) {
                window.barrierStates.west = 'open';
                window.barrierStates.east = 'open';

                if (window.radarSimulation && window.radarSimulation.updateBarrierDisplay) {
                    window.radarSimulation.updateBarrierDisplay();
                }
            }

            this.addLog('Modo de emergência ativado - Todas as barreiras abertas');
        }
    }

    /**
     * Save configuration to localStorage
     * @param {boolean} showNotification - Whether to show a notification (default: true)
     */
    saveConfiguration(showNotification = true) {
        try {
            localStorage.setItem('systemConfig', JSON.stringify(this.config));

            if (showNotification) {
                this.showConfigStatus('Configuração salva com sucesso!', 'success');
                this.addLog('Configuração salva no armazenamento local');
            }
        } catch (error) {
            if (showNotification) {
                this.showConfigStatus('Erro ao salvar configuração', 'error');
                this.addLog(`Erro ao salvar configuração: ${error.message}`);
            }
        }
    }

    /**
     * Load configuration from localStorage
     */
    loadConfiguration() {
        try {
            const savedConfig = localStorage.getItem('systemConfig');
            if (savedConfig) {
                const parsedConfig = JSON.parse(savedConfig);
                this.config = { ...this.config, ...parsedConfig };
                this.addLog('Configuração carregada do armazenamento local');
            }
        } catch (error) {
            this.addLog(`Erro ao carregar configuração: ${error.message}`);
        }
    }

    /**
     * Reset configuration to defaults
     */
    resetConfiguration() {
        this.config = {
            lora: {
                frequency: 868,
                power: 14,
                spreadingFactor: 10,
                bandwidth: 125
            },
            security: {
                blockTime: 5,
                activationDistance: 100,
                maxRange: 500,
                autoClose: true,
                emergencyMode: false
            }
        };

        this.updateUI();
        this.showConfigStatus('Configuração restaurada para os padrões', 'info');
        this.addLog('Configuração restaurada para os valores padrão');
    }

    /**
     * Export configuration as JSON file
     */
    exportConfiguration() {
        try {
            const configData = {
                ...this.config,
                exportDate: new Date().toISOString(),
                version: '1.0'
            };

            const dataStr = JSON.stringify(configData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `sistema-barreiras-config-${new Date().toISOString().split('T')[0]}.json`;
            link.click();

            this.showConfigStatus('Configuração exportada com sucesso!', 'success');
            this.addLog('Configuração exportada para arquivo JSON');
        } catch (error) {
            this.showConfigStatus('Erro ao exportar configuração', 'error');
            this.addLog(`Erro ao exportar configuração: ${error.message}`);
        }
    }

    /**
     * Show configuration status message
     */
    showConfigStatus(message, type = 'info') {
        if (this.configStatus && this.configMessage) {
            this.configMessage.textContent = message;

            // Update status styling based on type
            this.configStatus.className = 'mt-3 p-3 rounded border';

            switch (type) {
                case 'success':
                    this.configStatus.classList.add('bg-green-50', 'border-green-200');
                    this.configMessage.className = 'text-green-700 text-xs mt-1';
                    break;
                case 'error':
                    this.configStatus.classList.add('bg-red-50', 'border-red-200');
                    this.configMessage.className = 'text-red-700 text-xs mt-1';
                    break;
                default:
                    this.configStatus.classList.add('bg-blue-50', 'border-blue-200');
                    this.configMessage.className = 'text-blue-700 text-xs mt-1';
            }

            this.configStatus.classList.remove('hidden');

            // Hide after 3 seconds
            setTimeout(() => {
                this.configStatus.classList.add('hidden');
            }, 3000);
        }
    }

    /**
     * Get current configuration
     */
    getConfiguration() {
        return { ...this.config };
    }

    /**
     * Add log message
     */
    addLog(message) {
        if (window.addLog) {
            window.addLog(message);
        }
    }
}

// Initialize system configuration when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.systemConfiguration = new SystemConfiguration();
});
