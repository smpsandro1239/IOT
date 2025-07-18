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

        // Generate MAC address from plate
        const plateValue = this.simPlate.value || 'ABC-1234';
        const generatedMAC = this.generateMACFromPlate(plateValue);

        // Update UI
        this.vehiclePlate.textContent = plateValue;
        this.vehicleDirection.textContent = this.direction === 'north' ? 'Oeste → Leste' : 'Leste → Oeste';

        // Update MAC display
        const vehicleMacElement = document.getElementById('vehicle-mac');
        if (vehicleMacElement) {
            vehicleMacElement.textContent = generatedMAC;
        }
        this.vehicleDistance.textContent = `${this.position} m`;
        this.systemStatus.className = 'w-3 h-3 rounded-full bg-yellow-500 mr-2';
        this.statusText.textContent = 'Simulação em andamento';

        // Show vehicle marker
        this.vehicleMarker.style.display = 'flex';

        // Position vehicle marker based on direction
        // Oeste → Leste: Veículo começa na esquerda e vai para a direita
        // Leste → Oeste: Veículo começa na direita e vai para a esquerda
        if (this.direction === 'north') { // Oeste → Leste (equivalente a Norte → Sul)
            this.vehicleMarker.style.left = '5%'; // Começa na esquerda (oeste)
            this.vehicleMarker.style.top = '50%'; // Centralizado na estrada
            this.vehicleMarker.style.transform = 'translateY(-50%)';
            this.directionIndicator.style.left = '15%';
            this.directionIndicator.style.top = '50%';
            this.directionIndicator.style.transform = 'translateY(-50%)';
            this.directionIndicator.innerHTML = '<i class="fas fa-arrow-right direction-arrow text-green-500 text-2xl"></i>'; // Seta para direita
        } else { // Leste → Oeste (equivalente a Sul → Norte)
            this.vehicleMarker.style.left = '95%'; // Começa na direita (leste)
            this.vehicleMarker.style.top = '50%'; // Centralizado na estrada
            this.vehicleMarker.style.transform = 'translateY(-50%)';
            this.directionIndicator.style.left = '85%';
            this.directionIndicator.style.top = '50%';
            this.directionIndicator.style.transform = 'translateY(-50%)';
            this.directionIndicator.innerHTML = '<i class="fas fa-arrow-left direction-arrow text-green-500 text-2xl"></i>'; // Seta para esquerda
        }

        // Inicializar o estado das barreiras
        this.initializeGates();

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

        // Clear vehicle info displays
        this.vehiclePlate.textContent = '---';
        const vehicleMacElement = document.getElementById('vehicle-mac');
        if (vehicleMacElement) {
            vehicleMacElement.textContent = '---';
        }
        this.vehicleDirection.textContent = '---';

        // Clear active vehicle info
        this.clearActiveVehicleInfo();

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

            // Definir a posição central (estação base) como 50% da largura
            const centerPosition = 50;

            // Calcular a posição do veículo com base na distância
            // A distância começa em 500m e vai até 0m
            // Quando a distância é 250m, o veículo está no centro (estação base)
            let vehiclePosition;

            if (this.direction === 'north') { // Oeste → Leste: movimento da esquerda para direita
                if (this.position > 250) {
                    // Veículo ainda não chegou ao centro
                    // Mapear de 500m-250m para 5%-50%
                    const progress = (500 - this.position) / 250; // 0 quando position=500, 1 quando position=250
                    vehiclePosition = 5 + progress * 45;
                } else {
                    // Veículo já passou do centro
                    // Mapear de 250m-0m para 50%-95%
                    const progress = (250 - this.position) / 250; // 0 quando position=250, 1 quando position=0
                    vehiclePosition = 50 + progress * 45;
                }
            } else { // Leste → Oeste: movimento da direita para esquerda
                if (this.position > 250) {
                    // Veículo ainda não chegou ao centro
                    // Mapear de 500m-250m para 95%-50%
                    const progress = (500 - this.position) / 250; // 0 quando position=500, 1 quando position=250
                    vehiclePosition = 95 - progress * 45;
                } else {
                    // Veículo já passou do centro
                    // Mapear de 250m-0m para 50%-5%
                    const progress = (250 - this.position) / 250; // 0 quando position=250, 1 quando position=0
                    vehiclePosition = 50 - progress * 45;
                }
            }

            // Atualizar a posição do veículo (agora horizontalmente)
            this.vehicleMarker.style.left = `${vehiclePosition}%`;

            // Update distance display
            this.vehicleDistance.textContent = `${this.position} m`;

            // Calcular a força do sinal com base na distância do centro
            // A força do sinal deve ser máxima quando o veículo está no centro (250m)
            // e diminuir à medida que se afasta
            const distanceFromCenter = Math.abs(this.position - 250);
            const maxDistance = 250; // Distância máxima do centro

            // Normalizar para uma escala de 0 a 1 (0 = no centro, 1 = distância máxima)
            const normalizedDistance = distanceFromCenter / maxDistance;

            // Calcular a força do sinal (100% quando está no centro, diminui à medida que se afasta)
            const strength = 100 * (1 - normalizedDistance);

            // Atualizar a barra de força do sinal
            this.signalStrengthBar.style.width = `${strength}%`;

            // Check for gate opening distance (100m)
            if (this.position <= 100 && window.gateStates) {
                const plateValue = this.vehiclePlate.textContent;
                const macValue = document.getElementById('vehicle-mac').textContent;

                // Corrigindo a lógica das barreiras:
                // - Se a direção é 'north' (Oeste → Leste), devemos abrir a barreira leste
                // - Se a direção é 'south' (Leste → Oeste), devemos abrir a barreira oeste
                if (this.direction === 'north' && window.gateStates.east !== 'open') {
                    // Veículo indo de Oeste para Leste, então abrimos a barreira leste
                    window.gateStates.east = 'open';
                    window.gateStates.west = 'locked';
                    window.updateGates();
                    this.updateActiveVehicleInfo(plateValue, macValue, 'Leste');
                    this.updateLastAccess('east', plateValue, macValue);
                    this.addLog(`Barreira Leste aberta para veículo ${plateValue} (direção Oeste → Leste)`);
                } else if (this.direction === 'south' && window.gateStates.west !== 'open') {
                    // Veículo indo de Leste para Oeste, então abrimos a barreira oeste
                    window.gateStates.west = 'open';
                    window.gateStates.east = 'locked';
                    window.updateGates();
                    this.updateActiveVehicleInfo(plateValue, macValue, 'Oeste');
                    this.updateLastAccess('west', plateValue, macValue);
                    this.addLog(`Barreira Oeste aberta para veículo ${plateValue} (direção Leste → Oeste)`);
                }
            }
        }, 100);
    }

    /**
     * Initialize gates state and visual representation
     */
    initializeGates() {
        // Initialize global gate states if not exists
        if (!window.gateStates) {
            window.gateStates = {
                west: 'closed',
                east: 'closed'
            };
        }

        // Initialize global updateGates function if not exists
        if (!window.updateGates) {
            window.updateGates = () => {
                const westGate = document.getElementById('west-gate');
                const eastGate = document.getElementById('east-gate');

                if (westGate) {
                    if (window.gateStates.west === 'open') {
                        westGate.className = 'gate-animation absolute left-0 top-1/2 transform -translate-y-1/2 w-8 h-16 bg-green-600 rounded-l-lg flex justify-center items-center text-white font-bold';
                        westGate.innerHTML = '<i class="fas fa-unlock"></i>';
                    } else {
                        westGate.className = 'gate-animation absolute left-0 top-1/2 transform -translate-y-1/2 w-8 h-16 bg-red-600 rounded-l-lg flex justify-center items-center text-white font-bold';
                        westGate.innerHTML = '<i class="fas fa-lock"></i>';
                    }
                }

                if (eastGate) {
                    if (window.gateStates.east === 'open') {
                        eastGate.className = 'gate-animation absolute right-0 top-1/2 transform -translate-y-1/2 w-8 h-16 bg-green-600 rounded-r-lg flex justify-center items-center text-white font-bold';
                        eastGate.innerHTML = '<i class="fas fa-unlock"></i>';
                    } else {
                        eastGate.className = 'gate-animation absolute right-0 top-1/2 transform -translate-y-1/2 w-8 h-16 bg-red-600 rounded-r-lg flex justify-center items-center text-white font-bold';
                        eastGate.innerHTML = '<i class="fas fa-lock"></i>';
                    }
                }
            };
        }

        // Reset gates to closed state
        window.gateStates.west = 'closed';
        window.gateStates.east = 'closed';
        window.updateGates();
    }

    /**
     * Generate MAC address from vehicle plate
     */
    generateMACFromPlate(plate) {
        // Remove special characters and convert to uppercase
        const cleanPlate = plate.replace(/[^A-Z0-9]/g, '').toUpperCase();

        // Create a simple hash from the plate
        let hash = 0;
        for (let i = 0; i < cleanPlate.length; i++) {
            const char = cleanPlate.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }

        // Convert hash to positive number and create MAC-like format
        const positiveHash = Math.abs(hash);
        const macParts = [];

        // Generate 6 pairs of hex digits
        for (let i = 0; i < 6; i++) {
            const part = ((positiveHash + i * 17) % 256).toString(16).toUpperCase().padStart(2, '0');
            macParts.push(part);
        }

        return macParts.join(':');
    }

    /**
     * Update active vehicle info display
     */
    updateActiveVehicleInfo(plate, mac, gate) {
        const activeVehicleInfo = document.getElementById('active-vehicle-info');
        const activePlateElement = document.getElementById('active-vehicle-plate');
        const activeMacElement = document.getElementById('active-vehicle-mac');
        const activeGateElement = document.getElementById('active-vehicle-gate');

        if (activeVehicleInfo && activePlateElement && activeMacElement && activeGateElement) {
            activeVehicleInfo.classList.remove('hidden');
            activePlateElement.textContent = plate;
            activeMacElement.textContent = mac;
            activeGateElement.textContent = `Barreira ${gate}`;
        }
    }

    /**
     * Update last access information (placeholder for future implementation)
     */
    updateLastAccess(gate, plate, mac) {
        // Store last access information for future use
        if (!window.lastAccess) {
            window.lastAccess = {};
        }

        window.lastAccess[gate] = {
            plate: plate,
            mac: mac,
            timestamp: new Date().toISOString(),
            gate: gate
        };

        // Log the access
        this.addLog(`Acesso registrado: ${plate} (${mac}) via barreira ${gate}`);
    }

    /**
     * Clear active vehicle info when simulation stops
     */
    clearActiveVehicleInfo() {
        const activeVehicleInfo = document.getElementById('active-vehicle-info');
        if (activeVehicleInfo) {
            activeVehicleInfo.classList.add('hidden');
        }
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
