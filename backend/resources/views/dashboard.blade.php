<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controle de Barreiras</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .gate-animation, .vehicle-marker {
            transition: all 0.5s ease-in-out;
        }
        .direction-arrow {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 0.7; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8">

<div class="container mx-auto">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 text-center">Sistema de Controle de Barreiras</h1>
        <p class="text-center text-gray-600">Detecção de direção com LoRa para abertura seletiva de barreiras</p>
    </header>

    <!-- Main Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Panel - Vehicle Detection -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2 flex items-center">
                <i class="fas fa-car-side mr-2 text-blue-500"></i>Monitoramento de Veículos
            </h2>

            <!-- Road Visualization -->
            <div class="relative bg-gray-200 rounded-lg p-4 h-64 mb-6 overflow-hidden">
                <!-- North-South Road -->
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-1/3 h-full bg-gray-400"></div>

                <!-- South-North Road -->
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-full h-1/3 bg-gray-400 rotate-90"></div>

                <!-- Gates -->
                <div id="north-gate" class="gate-animation absolute top-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-red-600 rounded-t-lg flex justify-center items-center text-white font-bold">
                    <i class="fas fa-lock"></i>
                </div>
                <div id="south-gate" class="gate-animation absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-red-600 rounded-b-lg flex justify-center items-center text-white font-bold">
                    <i class="fas fa-lock"></i>
                </div>

                <!-- Vehicle Markers -->
                <div id="vehicle-marker" class="vehicle-marker absolute w-12 h-6 bg-blue-600 rounded-lg flex justify-center items-center text-white">
                    <i class="fas fa-car"></i>
                </div>

                <!-- Direction Indicators -->
                <div id="direction-indicator" class="absolute hidden">
                    <i class="fas fa-arrow-up direction-arrow text-green-500 text-2xl"></i>
                </div>

                <!-- Base Station -->
                <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 bg-gray-800 rounded-full flex flex-col justify-center items-center text-white">
                    <i class="fas fa-broadcast-tower"></i>
                    <span class="text-xs mt-1">Estação Base</span>
                </div>
            </div>

            <!-- Vehicle Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-2">Placa do Veículo</h3>
                    <div class="bg-white p-2 rounded text-center font-mono font-bold" id="vehicle-plate">ABC-1234</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-2">Direção</h3>
                    <div class="bg-white p-2 rounded text-center font-bold" id="vehicle-direction">Nenhuma</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-2">Distância</h3>
                    <div class="bg-white p-2 rounded text-center font-bold" id="vehicle-distance">0 m</div>
                </div>
            </div>

            <!-- Signal Strength -->
            <div class="mt-6">
                <h3 class="font-semibold text-gray-700 mb-2">Força do Sinal LoRa</h3>
                <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                    <div id="signal-strength" class="h-full bg-green-500 signal-strength" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>0m</span>
                    <span>250m</span>
                    <span>500m</span>
                </div>
            </div>
        </div>

        <!-- Right Panel - System Controls -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2 flex items-center">
                <i class="fas fa-cogs mr-2 text-blue-500"></i>Controles do Sistema
            </h2>

            <!-- System Status -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 mb-2">Status do Sistema</h3>
                <div class="flex items-center">
                    <div id="system-status" class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                    <span id="status-text" class="font-medium">Operacional</span>
                </div>
            </div>

            <!-- Gate Controls -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Controle de Barreiras</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Norte-Sul</h4>
                        <button id="north-toggle" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition">
                            <i class="fas fa-lock mr-1"></i> Fechada
                        </button>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Sul-Norte</h4>
                        <button id="south-toggle" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition">
                            <i class="fas fa-lock mr-1"></i> Fechada
                        </button>
                    </div>
                </div>
            </div>

            <!-- Simulation Controls -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Simulação de Veículo</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Placa do Veículo</label>
                        <input type="text" id="sim-plate" class="w-full p-2 border rounded-lg" value="ABC-1234">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Direção</label>
                        <select id="sim-direction" class="w-full p-2 border rounded-lg">
                            <option value="north">Norte-Sul</option>
                            <option value="south">Sul-Norte</option>
                        </select>
                    </div>
                    <button id="start-sim" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">
                        <i class="fas fa-play mr-1"></i> Iniciar Simulação
                    </button>
                    <button id="stop-sim" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition">
                        <i class="fas fa-stop mr-1"></i> Parar Simulação
                    </button>
                </div>
            </div>

            <!-- System Log -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Registro do Sistema</h3>
                <div class="bg-gray-50 p-3 rounded-lg h-40 overflow-y-auto text-sm font-mono" id="system-log">
                    <div class="text-gray-500">Sistema inicializado...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Info -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
            <i class="fas fa-info-circle mr-2 text-blue-500"></i>Informações Técnicas
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Detecção de Direção com LoRa</h3>
                <p class="text-gray-600 mb-4">
                    O sistema utiliza tecnologia LoRa para estimar o Ângulo de Chegada (AoA) com precisão de 4-14 graus,
                    permitindo determinar a direção de aproximação do veículo (Norte-Sul ou Sul-Norte).
                </p>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-800 mb-2">Especificações:</h4>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                        <li>Alcance de detecção: até 500m</li>
                        <li>Frequência: 868 MHz (EU) / 915 MHz (US)</li>
                        <li>Consumo energético: ~45mA durante transmissão</li>
                        <li>Array de 3 antenas para detecção AoA</li>
                        <li>Protocolo seguro com autenticação</li>
                    </ul>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Lógica de Controle</h3>
                <p class="text-gray-600 mb-4">
                    Quando um veículo é detectado se aproximando em uma direção específica, a barreira correspondente
                    é liberada enquanto a oposta é bloqueada temporariamente para evitar abertura simultânea.
                </p>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2">Fluxo de Operação:</h4>
                    <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                        <li>Detecção do veículo a 500m</li>
                        <li>Determinação da direção (AoA)</li>
                        <li>Verificação de permissões</li>
                        <li>Abertura da barreira correspondente</li>
                        <li>Bloqueio da barreira oposta (5 minutos)</li>
                        <li>Registro no sistema</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // DOM Elements
        const vehicleMarker = document.getElementById('vehicle-marker');
        const directionIndicator = document.getElementById('direction-indicator');
        const northGate = document.getElementById('north-gate');
        const southGate = document.getElementById('south-gate');
        const vehiclePlate = document.getElementById('vehicle-plate');
        const vehicleDirection = document.getElementById('vehicle-direction');
        const vehicleDistance = document.getElementById('vehicle-distance');
        const signalStrengthBar = document.getElementById('signal-strength');
        const systemStatus = document.getElementById('system-status');
        const statusText = document.getElementById('status-text');
        const systemLog = document.getElementById('system-log');
        const northToggle = document.getElementById('north-toggle');
        const southToggle = document.getElementById('south-toggle');

        // Hide simulation controls as they are no longer needed
        const simControls = document.getElementById('start-sim').parentElement.parentElement;
        simControls.style.display = 'none';

        let lastLogId = null;

        function addLog(message, timestamp) {
            const timeString = new Date(timestamp).toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.textContent = `[${timeString}] ${message}`;
            systemLog.appendChild(logEntry);
            systemLog.scrollTop = systemLog.scrollHeight;
        }

        function updateGates(gateStates) {
            // North gate
            const northState = gateStates.north.state;
            if (northState === 'open') {
                northGate.className = 'gate-animation absolute top-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-green-500 rounded-t-lg flex justify-center items-center text-white font-bold';
                northGate.innerHTML = '<i class="fas fa-lock-open"></i>';
                northToggle.className = 'w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition';
                northToggle.innerHTML = '<i class="fas fa-lock-open mr-1"></i> Aberta';
            } else if (northState === 'locked') {
                northGate.className = 'gate-animation absolute top-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-yellow-500 rounded-t-lg flex justify-center items-center text-white font-bold';
                northGate.innerHTML = '<i class="fas fa-lock"></i>';
                northToggle.className = 'w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition';
                northToggle.innerHTML = '<i class="fas fa-lock mr-1"></i> Bloqueada';
            } else {
                northGate.className = 'gate-animation absolute top-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-red-500 rounded-t-lg flex justify-center items-center text-white font-bold';
                northGate.innerHTML = '<i class="fas fa-lock"></i>';
                northToggle.className = 'w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition';
                northToggle.innerHTML = '<i class="fas fa-lock mr-1"></i> Fechada';
            }

            // South gate
            const southState = gateStates.south.state;
            if (southState === 'open') {
                southGate.className = 'gate-animation absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-green-500 rounded-b-lg flex justify-center items-center text-white font-bold';
                southGate.innerHTML = '<i class="fas fa-lock-open"></i>';
                southToggle.className = 'w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition';
                southToggle.innerHTML = '<i class="fas fa-lock-open mr-1"></i> Aberta';
            } else if (southState === 'locked') {
                southGate.className = 'gate-animation absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-yellow-500 rounded-b-lg flex justify-center items-center text-white font-bold';
                southGate.innerHTML = '<i class="fas fa-lock"></i>';
                southToggle.className = 'w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition';
                southToggle.innerHTML = '<i class="fas fa-lock mr-1"></i> Bloqueada';
            } else {
                southGate.className = 'gate-animation absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-8 bg-red-500 rounded-b-lg flex justify-center items-center text-white font-bold';
                southGate.innerHTML = '<i class="fas fa-lock"></i>';
                southToggle.className = 'w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition';
                southToggle.innerHTML = '<i class="fas fa-lock mr-1"></i> Fechada';
            }
        }

        async function fetchLatestData() {
            try {
                const response = await fetch("{{ route('api.status.latest') }}");
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                // Update UI with new data
                updateUI(data);

            } catch (error) {
                console.error("Failed to fetch latest data:", error);
                statusText.textContent = 'Erro de Conexão';
                systemStatus.className = 'w-3 h-3 rounded-full bg-red-500 mr-2';
            }
        }

        function updateUI(data) {
            // Update System Status
            statusText.textContent = data.system_status === 'operational' ? 'Operacional' : 'Com Falhas';
            systemStatus.className = `w-3 h-3 rounded-full ${data.system_status === 'operational' ? 'bg-green-500' : 'bg-red-500'} mr-2`;

            // Update Gates
            updateGates(data.barrier_status);

            // Update Vehicle Info from the latest log
            const log = data.latest_log;
            if (log) {
                vehiclePlate.textContent = log.vehicle_lora_id;

                let directionText = 'Indefinida';
                if (log.direction_detected === 'north_south') {
                    directionText = 'Norte → Sul';
                } else if (log.direction_detected === 'south_north') {
                    directionText = 'Sul → Norte';
                } else if (log.direction_detected === 'conflito') {
                    directionText = 'Conflito';
                }
                vehicleDirection.textContent = directionText;

                // Since we don't have real-time distance, we can show a static value or base it on log time
                vehicleDistance.textContent = 'N/A'; // Or calculate time since event

                // Signal strength from sensor report if available
                let rssi = 0;
                if(log.sensor_reports) {
                    const reports = JSON.parse(log.sensor_reports);
                    if(reports.length > 0 && reports[0].rssi) {
                        rssi = reports[0].rssi;
                    }
                }
                // Convert RSSI to percentage (simple example, needs calibration)
                // Assuming RSSI range from -120 (weak) to -30 (strong)
                const signalPercentage = Math.max(0, Math.min(100, (120 + rssi) / 90 * 100));
                signalStrengthBar.style.width = `${signalPercentage}%`;

                // Add to log if it's a new event
                if (log.id !== lastLogId) {
                    lastLogId = log.id;
                    const authStatus = log.authorization_status ? 'Autorizado' : 'Negado';
                    const logMessage = `Veículo ${log.vehicle_lora_id} detectado. Direção: ${directionText}. Status: ${authStatus}.`;
                    addLog(logMessage, log.timestamp_event);
                }
            } else {
                // No logs yet
                vehiclePlate.textContent = 'N/A';
                vehicleDirection.textContent = 'Nenhuma';
                vehicleDistance.textContent = '0 m';
                signalStrengthBar.style.width = '0%';
            }
        }

        // Initial fetch and set interval to fetch data every 5 seconds
        fetchLatestData();
        setInterval(fetchLatestData, 5000);
    });
</script>
</body>
</html>
