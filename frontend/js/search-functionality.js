/**
 * Search and Metrics Functionality
 * Handles MAC and plate search features
 */

class SearchManager {
    constructor() {
        // Sample data for demonstration
        this.authorizedVehicles = [
            { mac: '24:A1:60:12:34:56', plate: 'ABC-1234', authorized: true, lastAccess: '2025-01-18 10:30:00' },
            { mac: 'AA:BB:CC:DD:EE:FF', plate: 'XYZ-5678', authorized: true, lastAccess: '2025-01-18 09:15:00' },
            { mac: '12:34:56:78:9A:BC', plate: 'DEF-9012', authorized: true, lastAccess: '2025-01-18 11:45:00' },
            { mac: 'FE:DC:BA:98:76:54', plate: 'GHI-3456', authorized: false, lastAccess: '2025-01-18 08:20:00' },
            { mac: '11:22:33:44:55:66', plate: 'JKL-7890', authorized: true, lastAccess: '2025-01-18 12:10:00' }
        ];

        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.filteredResults = [...this.authorizedVehicles];

        this.init();
    }

    /**
     * Initialize search functionality
     */
    init() {
        // Get DOM elements
        this.macSearchInput = document.getElementById('mac-search');
        this.plateSearchInput = document.getElementById('plate-search');
        this.macInputMetrics = document.getElementById('mac-input');
        this.plateInputMetrics = document.getElementById('plate-input');
        this.authorizedMacsContainer = document.getElementById('authorized-macs');
        this.searchResultsCount = document.getElementById('search-results-count');
        this.prevPageBtn = document.getElementById('prev-page');
        this.nextPageBtn = document.getElementById('next-page');

        // Add event listeners
        if (this.macSearchInput) {
            this.macSearchInput.addEventListener('input', () => this.performSearch());
        }

        if (this.plateSearchInput) {
            this.plateSearchInput.addEventListener('input', () => this.performSearch());
        }

        if (this.macInputMetrics) {
            this.macInputMetrics.addEventListener('input', () => this.updateMetricsByMAC());
        }

        if (this.plateInputMetrics) {
            this.plateInputMetrics.addEventListener('input', () => this.updateMetricsByPlate());
        }

        if (this.prevPageBtn) {
            this.prevPageBtn.addEventListener('click', () => this.previousPage());
        }

        if (this.nextPageBtn) {
            this.nextPageBtn.addEventListener('click', () => this.nextPage());
        }

        // Initialize displays
        this.populateDataLists();
        this.displayResults();
    }

    /**
     * Populate datalists for autocomplete
     */
    populateDataLists() {
        const macList = document.getElementById('mac-list');
        const plateList = document.getElementById('plate-list');

        if (macList) {
            macList.innerHTML = '';
            this.authorizedVehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.mac;
                option.textContent = `${vehicle.mac} (${vehicle.plate})`;
                macList.appendChild(option);
            });
        }

        if (plateList) {
            plateList.innerHTML = '';
            this.authorizedVehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.plate;
                option.textContent = `${vehicle.plate} (${vehicle.mac})`;
                plateList.appendChild(option);
            });
        }
    }

    /**
     * Perform search based on MAC and plate inputs
     */
    performSearch() {
        const macQuery = this.macSearchInput ? this.macSearchInput.value.toLowerCase() : '';
        const plateQuery = this.plateSearchInput ? this.plateSearchInput.value.toLowerCase() : '';

        this.filteredResults = this.authorizedVehicles.filter(vehicle => {
            const macMatch = vehicle.mac.toLowerCase().includes(macQuery);
            const plateMatch = vehicle.plate.toLowerCase().includes(plateQuery);

            // If both fields have input, both must match
            if (macQuery && plateQuery) {
                return macMatch && plateMatch;
            }

            // If only one field has input, that one must match
            if (macQuery) return macMatch;
            if (plateQuery) return plateMatch;

            // If no input, show all
            return true;
        });

        this.currentPage = 1;
        this.displayResults();
    }

    /**
     * Display search results
     */
    displayResults() {
        if (!this.authorizedMacsContainer || !this.searchResultsCount) return;

        // Update results count
        this.searchResultsCount.textContent = this.filteredResults.length;

        // Calculate pagination
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageResults = this.filteredResults.slice(startIndex, endIndex);

        // Clear container
        this.authorizedMacsContainer.innerHTML = '';

        if (pageResults.length === 0) {
            this.authorizedMacsContainer.innerHTML = '<div class="text-gray-500 text-center py-4">Nenhum resultado encontrado</div>';
            return;
        }

        // Display results
        pageResults.forEach(vehicle => {
            const resultDiv = document.createElement('div');
            resultDiv.className = `flex justify-between items-center p-2 border-b border-gray-200 ${vehicle.authorized ? 'bg-green-50' : 'bg-red-50'}`;

            resultDiv.innerHTML = `
                <div class="flex-1">
                    <div class="font-mono text-sm font-bold">${vehicle.plate}</div>
                    <div class="font-mono text-xs text-gray-600">${vehicle.mac}</div>
                    <div class="text-xs text-gray-500">Último acesso: ${vehicle.lastAccess}</div>
                </div>
                <div class="flex items-center">
                    <span class="px-2 py-1 text-xs rounded-full ${vehicle.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${vehicle.authorized ? 'Autorizado' : 'Não Autorizado'}
                    </span>
                    <button class="ml-2 text-blue-600 hover:text-blue-800" onclick="searchManager.viewDetails('${vehicle.mac}')">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            `;

            this.authorizedMacsContainer.appendChild(resultDiv);
        });

        // Update pagination buttons
        this.updatePaginationButtons();
    }

    /**
     * Update pagination buttons state
     */
    updatePaginationButtons() {
        const totalPages = Math.ceil(this.filteredResults.length / this.itemsPerPage);

        if (this.prevPageBtn) {
            this.prevPageBtn.disabled = this.currentPage <= 1;
        }

        if (this.nextPageBtn) {
            this.nextPageBtn.disabled = this.currentPage >= totalPages;
        }
    }

    /**
     * Go to previous page
     */
    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.displayResults();
        }
    }

    /**
     * Go to next page
     */
    nextPage() {
        const totalPages = Math.ceil(this.filteredResults.length / this.itemsPerPage);
        if (this.currentPage < totalPages) {
            this.currentPage++;
            this.displayResults();
        }
    }

    /**
     * Update metrics based on MAC selection
     */
    updateMetricsByMAC() {
        const selectedMAC = this.macInputMetrics.value;
        const vehicle = this.authorizedVehicles.find(v => v.mac === selectedMAC);

        if (vehicle && this.plateInputMetrics) {
            this.plateInputMetrics.value = vehicle.plate;
        }

        this.updateCharts('mac', selectedMAC);
    }

    /**
     * Update metrics based on plate selection
     */
    updateMetricsByPlate() {
        const selectedPlate = this.plateInputMetrics.value;
        const vehicle = this.authorizedVehicles.find(v => v.plate === selectedPlate);

        if (vehicle && this.macInputMetrics) {
            this.macInputMetrics.value = vehicle.mac;
        }

        this.updateCharts('plate', selectedPlate);
    }

    /**
     * Update charts based on selection
     */
    updateCharts(type, value) {
        // This would integrate with the existing chart system
        console.log(`Updating charts for ${type}: ${value}`);

        // Add log entry
        if (window.addLog) {
            const displayValue = type === 'mac' ? value : `${value} (${this.getVehicleByPlate(value)?.mac || 'N/A'})`;
            window.addLog(`Métricas atualizadas para ${type === 'mac' ? 'MAC' : 'matrícula'}: ${displayValue}`);
        }
    }

    /**
     * Get vehicle by plate
     */
    getVehicleByPlate(plate) {
        return this.authorizedVehicles.find(v => v.plate === plate);
    }

    /**
     * View vehicle details (placeholder)
     */
    viewDetails(mac) {
        const vehicle = this.authorizedVehicles.find(v => v.mac === mac);
        if (vehicle) {
            alert(`Detalhes do Veículo:\n\nMatrícula: ${vehicle.plate}\nMAC: ${vehicle.mac}\nStatus: ${vehicle.authorized ? 'Autorizado' : 'Não Autorizado'}\nÚltimo Acesso: ${vehicle.lastAccess}`);
        }
    }

    /**
     * Add new vehicle to the system
     */
    addVehicle(mac, plate, authorized = true) {
        const newVehicle = {
            mac: mac,
            plate: plate,
            authorized: authorized,
            lastAccess: new Date().toLocaleString('pt-PT')
        };

        this.authorizedVehicles.push(newVehicle);
        this.populateDataLists();
        this.performSearch(); // Refresh display

        if (window.addLog) {
            window.addLog(`Novo veículo adicionado: ${plate} (${mac})`);
        }
    }
}

// Initialize search manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});
