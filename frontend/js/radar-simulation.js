/**
 * Radar Simulation Module for IoT Barrier Control System
 * Handles vehicle detection and radar visualization
 */

class RadarSimulation {
    constructor() {
        // Simulation state
        this.active = false;
        this.vehicles = [];
        this.interval = null;
        this.radarElement = null;
        this.maxDistance = 500; // meters

        // DOM elements
        this.vehicleCountDisplay = document.getElementById('vehicle-count-display');
        this.vehiclePlate = document.getElementById('vehicle-plate');
        this.vehicleDirection = document.getElementById('vehicle-direction');
        this.vehicleDistance = document.getElementById('vehicle-distance');
        this.vehicleMac = document.getElementById('vehicle-mac');
        this.signalStrengthBar = document.getElementById('signal-strength');
        this.systemStatus = document.getElementById('system-status');
        this.statusText = document.getElementById('status-text');
        this.startButton = document.getElementById('start-sim');
        this.stopButton = document.getElementById('stop-sim');
        this.simPlate = document.getElementById('sim-plate');
        this.simDirection = document.getElementById('sim-direction');
        this.signalIndicator = document.querySelector('.signal-strength-indicator');

        // Initialize
        this.init();
    }

    /**
     * Initialize radar simulation
     */
    init() {
        this.radarElement = document.querySelector('.radar-scan');

        // Add event listeners
        if (this.startButton) {
            this.startButton.addEventListener('click', () => this.startSimulation());
        }
        if (this.stopButton) {
            this.stopButton.addEventListener('click', () => this.stopSimulation());
        }

        // Initialize barrier controls
        this.initializeBarrierControls();

        // Update initial display
        this.updateVehicleCount();

        // Start radar animation
        this.startRadarScan();

        // Disable stop button initially
        if (this.stopButton) {
            this.stopButton.disabled = true;
        }
    }

    /**
     * Start radar scanning animation
     */
    startRadarScan() {
        // The CSS animation handles the radar line rotation
        // We just need to update vehicle positions periodically
        setInterval(() => {
            this.updateRadarDisplay();
        }, 100);
    }

    /**
     * Start vehicle simulation
     */
    startSimulation() {
        if (this.active) return;

        this.active = true;

        // Generate MAC from plate
        const plateValue = this.simPlate.value || 'ABC-1234';
        const generatedMAC = this.generateMACFromPlate(plateValue);

        // Create a new vehicle
        const vehicle = {
            id: 'VH' + Date.now(),
            plate: plateValue,
            mac: generatedMAC,
            direction: this.simDirection.value,
            distance: this.maxDistance,
            angle: this.simDirection.value === 'north' ? 0 : 180, // 0° = North, 180° = South
            authorized: true,
            status: 'Aproximando'
        };

        this.vehicles.push(vehicle);

        // Update UI
        this.vehiclePlate.textContent = vehicle.plate;
        this.vehicleMac.textContent = vehicle.mac;
        this.vehicleDirection.textContent = vehicle.direction === 'north' ? 'Norte → Sul' : 'Sul → Norte';
        this.vehicleDistance.textContent = `${vehicle.distance} m`;

        // Update system status
        this.systemStatus.className = 'w-3 h-3 rounded-full bg-yellow-500 mr-2';
        this.statusText.textContent = 'Simulação em andamento';

        // Update buttons
        this.startButton.disabled = true;
        this.stopButton.disabled = false;

        // Start vehicle movement
        this.startVehicleMovement(vehicle);

        // Add log
        this.addLog(`Simulação iniciada - Veículo ${vehicle.plate} (${vehicle.mac}) detectado`);

        // Update displays
        this.updateVehicleCount();
        this.updateRadarDisplay();
    }

    /**
     * Stop simulation
     */
    stopSimulation() {
        if (!this.active) return;

        this.active = false;
        this.vehicles = [];

        // Clear radar markers
        this.clearRadarMarkers();

        // Reset UI
        this.vehiclePlate.textContent = '---';
        this.vehicleMac.textContent = '---';
        this.vehicleDirection.textContent = '---';
        this.vehicleDistance.textContent = '0 m';
        this.signalStrengthBar.style.width = '0%';
        this.systemStatus.className = 'w-3 h-3 rounded-full bg-green-500 mr-2';
        this.statusText.textContent = 'Operacional';

        // Update buttons
        this.startButton.disabled = false;
        this.stopButton.disabled = true;

        // Update signal indicator
        if (this.signalIndicator) {
            this.signalIndicator.classList.remove('active-signal');
        }

        // Clear active vehicle info
        this.clearActiveVehicleInfo();

        // Add log
        this.addLog('Simulação parada');

        // Update displays
        this.updateVehicleCount();
    }

    /**
     * Start vehicle movement animation
     */
    startVehicleMovement(vehicle) {
        const moveInterval = setInterval(() => {
            if (!this.active || !this.vehicles.includes(vehicle)) {
                clearInterval(moveInterval);
                return;
            }

            // Move vehicle closer
            vehicle.distance -= 15; // 15 meters per update

            // Update distance display for the current vehicle
            if (this.vehicles[0] === vehicle) {
                this.vehicleDistance.textContent = `${Math.max(0, vehicle.distance)} m`;

                // Update signal strength based on distance
                const strength = Math.max(0, 100 - (vehicle.distance / this.maxDistance * 100));
                this.signalStrengthBar.style.width = `${strength}%`;

                // Activate signal indicator when vehicle is close
                if (vehicle.distance < 200 && this.signalIndicator) {
                    this.signalIndicator.classList.add('active-signal');
                } else if (this.signalIndicator) {
                    this.signalIndicator.classList.remove('active-signal');
                }
            }

            // Check for barrier opening distance (100m)
            if (vehicle.distance <= 100 && vehicle.distance > 50) {
                this.handleBarrierOpening(vehicle);
            }

            // Remove vehicle when it reaches the center
            if (vehicle.distance <= 0) {
                this.removeVehicle(vehicle);
                clearInterval(moveInterval);

                // Auto-stop simulation when vehicle passes
                setTimeout(() => {
                    if (this.vehicles.length === 0) {
                        this.stopSimulation();
                    }
                }, 1000);
            }

            // Update radar display
            this.updateRadarDisplay();
        }, 200);
    }

    /**
     * Handle barrier opening logic
     */
    handleBarrierOpening(vehicle) {
        if (!vehicle.barrierOpened) {
            vehicle.barrierOpened = true;

            // Determine which barrier to open based on direction
            const barrierToOpen = vehicle.direction === 'north' ? 'north' : 'south';
            const direction = vehicle.direction === 'north' ? 'Norte → Sul' : 'Sul → Norte';

            // Open the appropriate barrier
            this.toggleBarrier(barrierToOpen, true);

            // Update active vehicle info
            this.updateActiveVehicleInfo(vehicle.plate, vehicle.mac, direction);

            // Update last access info
            this.updateLastAccess(barrierToOpen, vehicle.plate, vehicle.mac);

            this.addLog(`Veículo autorizado ${vehicle.plate} (${vehicle.mac}) detectado - Barreira ${direction} aberta automaticamente`);
        }
    }

    /**
     * Remove vehicle from simulation
     */
    removeVehicle(vehicle) {
        const index = this.vehicles.indexOf(vehicle);
        if (index > -1) {
            this.vehicles.splice(index, 1);
            this.addLog(`Veículo ${vehicle.plate} passou pela barreira`);
            this.updateVehicleCount();
        }
    }

    /**
     * Update radar display with vehicle markers
     */
    updateRadarDisplay() {
        // Clear existing markers
        this.clearRadarMarkers();

        // Add markers for each vehicle
        this.vehicles.forEach(vehicle => {
            this.addRadarMarker(vehicle);
        });
    }

    /**
     * Clear all radar markers
     */
    clearRadarMarkers() {
        if (this.radarElement) {
            const existingMarkers = this.radarElement.querySelectorAll('.radar-vehicle, .radar-direction');
            existingMarkers.forEach(marker => marker.remove());
        }
    }

    /**
     * Add vehicle marker to radar
     */
    addRadarMarker(vehicle) {
        if (!this.radarElement) return;

        // Calculate position on radar
        const distanceRatio = Math.min(vehicle.distance / this.maxDistance, 1);
        const radarRadius = 45; // 45% of radar size

        // Convert angle to radians and calculate position
        const angleRad = (vehicle.angle - 90) * Math.PI / 180; // -90 to make 0° point up
        const x = 50 + Math.cos(angleRad) * distanceRatio * radarRadius;
        const y = 50 + Math.sin(angleRad) * distanceRatio * radarRadius;

        // Create vehicle marker
        const marker = document.createElement('div');
        marker.className = `radar-vehicle ${vehicle.authorized ? 'authorized' : ''}`;
        marker.style.left = `${x}%`;
        marker.style.top = `${y}%`;
        marker.title = `${vehicle.plate} (${vehicle.mac}) - ${vehicle.distance}m`;

        // Add pulsing animation for close vehicles
        if (vehicle.distance < 150) {
            marker.style.animation = 'pulse 1s infinite';
        }

        this.radarElement.appendChild(marker);

        // Add direction indicator
        const directionIcon = document.createElement('i');
        directionIcon.className = 'radar-direction fas fa-arrow-down';
        directionIcon.style.position = 'absolute';
        directionIcon.style.left = `${x}%`;
        directionIcon.style.top = `${y - 3}%`;
        directionIcon.style.transform = 'translate(-50%, -50%)';
        directionIcon.style.color = vehicle.authorized ? '#10B981' : '#EF4444';
        directionIcon.style.fontSize = '12px';
        directionIcon.style.zIndex = '9';

        // Rotate arrow based on direction
        if (vehicle.direction === 'south') {
            directionIcon.style.transform += ' rotate(180deg)';
        }

        this.radarElement.appendChild(directionIcon);
    }

    /**
     * Update vehicle count display
     */
    updateVehicleCount() {
        if (this.vehicleCountDisplay) {
            this.vehicleCountDisplay.textContent = this.vehicles.length;
        }
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
     * Initialize barrier controls
     */
    initializeBarrierControls() {
        // Initialize barrier states
        window.barrierStates = {
            west: 'closed',
            east: 'closed'
        };

        // Get barrier control elements
        const westOpen = document.getElementById('west-open');
        const westClose = document.getElementById('west-close');
        const eastOpen = document.getElementById('east-open');
        const eastClose = document.getElementById('east-close');

        // Add event listeners for barrier controls
        if (westOpen) {
            westOpen.addEventListener('click', () => this.toggleBarrier('west', true));
        }
        if (westClose) {
            westClose.addEventListener('click', () => this.toggleBarrier('west', false));
        }
        if (eastOpen) {
            eastOpen.addEventListener('click', () => this.toggleBarrier('east', true));
        }
        if (eastClose) {
            eastClose.addEventListener('click', () => this.toggleBarrier('east', false));
        }

        // Initialize barrier display
        this.updateBarrierDisplay();
    }

    /**
     * Toggle barrier state
     */
    toggleBarrier(barrier, open) {
        window.barrierStates[barrier] = open ? 'open' : 'closed';

        const direction = barrier === 'west' ? 'Norte → Sul' : 'Sul → Norte';
        const action = open ? 'aberta' : 'fechada';

        this.addLog(`Barreira ${direction} ${action}`);

        // If opening one barrier, close the other after a delay
        if (open) {
            const otherBarrier = barrier === 'west' ? 'east' : 'west';
            if (window.barrierStates[otherBarrier] === 'open') {
                setTimeout(() => {
                    this.toggleBarrier(otherBarrier, false);
                }, 500);
            }
        }

        this.updateBarrierDisplay();
    }

    /**
     * Update barrier visual display
     */
    updateBarrierDisplay() {
        // West barrier
        const westStatus = document.getElementById('west-status');
        const westOpen = document.getElementById('west-open');
        const westClose = document.getElementById('west-close');

        if (westStatus) {
            const isOpen = window.barrierStates.west === 'open';

            if (isOpen) {
                westStatus.textContent = 'Aberta';
                westStatus.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
            } else {
                westStatus.textContent = 'Fechada';
                westStatus.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800';
            }

            if (westOpen) westOpen.disabled = isOpen;
            if (westClose) westClose.disabled = !isOpen;
        }

        // East barrier
        const eastStatus = document.getElementById('east-status');
        const eastOpen = document.getElementById('east-open');
        const eastClose = document.getElementById('east-close');

        if (eastStatus) {
            const isOpen = window.barrierStates.east === 'open';

            if (isOpen) {
                eastStatus.textContent = 'Aberta';
                eastStatus.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
            } else {
                eastStatus.textContent = 'Fechada';
                eastStatus.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800';
            }

            if (eastOpen) eastOpen.disabled = isOpen;
            if (eastClose) eastClose.disabled = !isOpen;
        }
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
            activeGateElement.textContent = gate;
        }
    }

    /**
     * Update last access information
     */
    updateLastAccess(gate, plate, mac) {
        // Map gate names to HTML element IDs (north maps to west elements, south maps to east elements)
        const gateMapping = {
            'north': 'west',  // Norte → Sul usa elementos west
            'south': 'east'   // Sul → Norte usa elementos east
        };
        
        const elementGate = gateMapping[gate] || gate;
        
        // Update the last access display for the specific gate
        const plateElement = document.getElementById(`${elementGate}-last-plate`);
        const macElement = document.getElementById(`${elementGate}-last-mac`);
        const timeElement = document.getElementById(`${elementGate}-last-time`);

        const currentTime = new Date().toLocaleString('pt-PT');

        if (plateElement && macElement) {
            plateElement.textContent = plate;
            macElement.textContent = mac;
        }

        if (timeElement) {
            timeElement.textContent = currentTime;
        }

        // Store last access information for future use
        if (!window.lastAccess) {
            window.lastAccess = {};
        }

        window.lastAccess[gate] = {
            plate: plate,
            mac: mac,
            timestamp: new Date().toISOString(),
            gate: gate,
            formattedTime: currentTime
        };

        // Update the vehicle's last access in the SearchManager database
        if (window.searchManager) {
            window.searchManager.updateVehicleLastAccess(mac, plate, currentTime);
        }

        // Log the access
        const direction = gate === 'north' ? 'Norte → Sul' : 'Sul → Norte';
        this.addLog(`Acesso registrado: ${plate} (${mac}) via barreira ${direction} às ${currentTime}`);
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
                logEntry.className = 'text-gray-700';
                logEntry.innerHTML = `<span class="text-gray-500">[${timeString}]</span> ${message}`;
                systemLog.appendChild(logEntry);
                systemLog.scrollTop = systemLog.scrollHeight;
            }
        }
    }
}

// Initialize radar simulation when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.radarSimulation = new RadarSimulation();
});
