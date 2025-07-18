/**
 * Simulation Module for IoT Barrier Control System
 * Handles vehicle simulation functionality
 */

class VehicleSimulation {
    constructor() {
        // Simulation state
        this.active = false;
        this.position = 0;
        this.direction = 'north';
        this.interval = null;
        this.speed = 10; // meters per update

        // DOM elements
        this.vehicleMarker = document.getElementById('vehicle-marker');
        this.directionIndicator = document.getElementById('direction-indicator');
        this.vehiclePlate = document.getElementById('vehicle-plate');
        this.vehicleDirection = document.getElementById('vehicle-direction');
        this.vehicleDistance = document.getElementById('vehicle-distance');
        this.signalStrengthBar = document.getElementById('signal-strength');
        this.systemStatus = document.getElementById('system-status');
        this.statusText = document.getElementById('status-text');
        this.startButton = document.getElementById('start-sim');
        this.stopButton = document.getElementById('stop-sim');
        this.simPlate = document.getElementById('sim-plate');
        this.simDirection = document.getElementById('sim-direction');

        // Initialize
        this.init();
    }

    /**
     * Initialize simulation
     */
    init() {
        // Hide vehicle marker initially
        this.vehicleMarker.style.display = 'none';
        this.directionIndicator.style.display = 'none';

        // Add event listeners
        this.startButton.addEventListener('click', () => this.start());
        this.stopButton.addEventListener('click', () => this.stop());

        // Disable stop button initially
        this.stopButton.disabled = true;
    }

    /**
     * Start simulation
     */
    async start() {
        if (this.active) return;

        // Update simulation state
        this.active = true;
        this.direction = this.simDirection.value;
        this.position = 500; // Start at 500m

        // Update UI
        this.vehiclePlate.textContent = this.simPlate.value || 'ABC-1234';
        this.vehicleDirection.textContent = this.direction === 'north' ? 'Norte → Sul' : 'Sul → Norte';
        this.vehicleDistance.textContent = `${this.position} m`;
        this.systemStatus.className = 'w-3 h-3 rounded-full bg-yellow-500 mr-2';
        this.statusText.textContent = 'Simulação em andamento';

        // Show vehicle marker
        this.vehicleMarker.style.display = 'flex';

        // Position vehicle marker based on direction
        // Norte → Sul: Veículo começa no norte (topo) e vai para o sul (baixo)
        // Sul → Norte: Veículo começa no sul (baixo) e vai para o norte (topo)
        if (this.direction === 'north') { // Norte → Sul
            this.vehicleMarker.style.left = '25%';
            this.vehicleMarker.style.top = '5%'; // Começa no topo (norte)
            this.directionIndicator.style.left = '25%';
            this.directionIndicator.style.top = '15%';
            this.directionIndicator.innerHTML = '<i class="fas fa-arrow-down direction-arrow text-green-500 text-2xl"></i>'; // Seta para baixo
        } else { // Sul → Norte
            this.vehicleMarker.style.left = '75%';
            this.vehicleMarker.style.top = '95%'; // Começa embaixo (sul)
            this.directionIndicator.style.left = '75%';
            this.directionIndicator.style.top = '85%';
            this.directionIndicator.innerHTML = '<i class="fas fa-arrow-up direction-arrow text-green-500 text-2xl"></i>'; // Seta para cima
        }

        // Show direction indicator
        this.directionIndicator.style.display = 'block';

        // Update buttons
        this.startButton.disabled = true;
        this.stopButton.disabled = false;

        // Log event
        this.addLog('Simulação iniciada');

        // Simulate sending data to backend
        try {
            const mac = this.simPlate.value.replace(/[^a-zA-Z0-9]/g, '');
            const response = await fetch('./api/v1/access-logs', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    mac,
                    direcao: this.direction === 'north' ? 'NS' : 'SN',
                    datahora: new Date().toISOString(),
                    status: 'AUTORIZADO'
                })
            });

            if (response.ok) {
                const result = await response.json();
                this.addLog(`Simulação: ${result.message || 'Evento registrado'}`);
            } else {
                this.addLog('Erro ao registrar evento de simulação');
            }
        } catch (error) {
            this.addLog(`Erro na simulação: ${error.message}`);
        }

        // Start animation
        this.startAnimation();
    }

    /**
     * Stop simulation
     */
    stop() {
        if (!this.active) return;

        // Update simulation state
        this.active = false;

        // Stop animation
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }

        // Hide vehicle marker and direction indicator
        this.vehicleMarker.style.display = 'none';
        this.directionIndicator.style.display = 'none';

        // Reset UI
        this.vehicleDistance.textContent = '0 m';
        this.signalStrengthBar.style.width = '0%';
        this.systemStatus.className = 'w-3 h-3 rounded-full bg-green-500 mr-2';
        this.statusText.textContent = 'Operacional';

        // Update buttons
        this.startButton.disabled = false;
        this.stopButton.disabled = true;

        // Log event
        this.addLog('Simulação parada');
    }

    /**
     * Start animation
     */
    startAnimation() {
        // Clear any existing interval
        if (this.interval) {
            clearInterval(this.interval);
        }

        // Set up animation interval
        this.interval = setInterval(() => {
            // Update position
            this.position -= this.speed;

            // Check if simulation is complete
            if (this.position <= 0) {
                this.position = 0;
                this.stop();
                return;
            }

            // Update vehicle marker position
            if (this.direction === 'north') { // Norte → Sul: movimento de cima para baixo
                // Começa em 5% (topo) e vai até 85% (baixo) à medida que a distância diminui
                const progress = 1 - (this.position / 500); // 0 no início, 1 no final
                this.vehicleMarker.style.top = `${5 + progress * 80}%`;
            } else { // Sul → Norte: movimento de baixo para cima
                // Começa em 95% (baixo) e vai até 15% (topo) à medida que a distância diminui
                const progress = 1 - (this.position / 500); // 0 no início, 1 no final
                this.vehicleMarker.style.top = `${95 - progress * 80}%`;
            }

            // Update distance display
            this.vehicleDistance.textContent = `${this.position} m`;

            // Update signal strength (inverse of distance)
            const strength = 100 - (this.position / 500) * 100;
            this.signalStrengthBar.style.width = `${strength}%`;

            // Check for gate opening distance (100m)
            if (this.position <= 100 && window.gateStates) {
                // Corrigindo a lógica das barreiras:
                // - Se a direção é 'north' (Norte → Sul), devemos abrir a barreira sul
                // - Se a direção é 'south' (Sul → Norte), devemos abrir a barreira norte
                if (this.direction === 'north' && window.gateStates.south !== 'open') {
                    // Veículo indo de Norte para Sul, então abrimos a barreira sul
                    window.gateStates.south = 'open';
                    window.gateStates.north = 'locked';
                    window.updateGates();
                    this.addLog(`Barreira Sul aberta para veículo ${this.vehiclePlate.textContent} (direção Norte → Sul)`);
                } else if (this.direction === 'south' && window.gateStates.north !== 'open') {
                    // Veículo indo de Sul para Norte, então abrimos a barreira norte
                    window.gateStates.north = 'open';
                    window.gateStates.south = 'locked';
                    window.updateGates();
                    this.addLog(`Barreira Norte aberta para veículo ${this.vehiclePlate.textContent} (direção Sul → Norte)`);
                }
            }
        }, 100);
    }

    /**
     * Add log message
     */
    addLog(message) {
        if (window.addLog) {
            window.addLog(message);
        } else {
            const systemLog = document.getElementById('system-log');
            if (systemLog) {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                const logEntry = document.createElement('div');
                logEntry.textContent = `[${timeString}] ${message}`;
                systemLog.appendChild(logEntry);
                systemLog.scrollTop = systemLog.scrollHeight;
            }
        }
    }
}

// Initialize simulation when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.vehicleSimulation = new VehicleSimulation();
});
