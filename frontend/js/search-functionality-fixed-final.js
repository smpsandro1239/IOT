/**
 * Search and Metrics Functionality - FINAL FIXED VERSION
 * Handles MAC and plate search with Portuguese formats and database connection
 */

class SearchManager {
    constructor() {
        // Default sample data with correct Portuguese plate formats
        const defaultVehicles = [
            { mac: '24:A1:60:12:34:56', plate: 'AB-12-34', authorized: true, lastAccess: '2025-01-18 10:30:00' },
            { mac: 'AA:BB:CC:DD:EE:FF', plate: '12-AB-34', authorized: true, lastAccess: '2025-01-18 09:15:00' },
            { mac: '12:34:56:78:9A:BC', plate: '12-34-AB', authorized: true, lastAccess: '2025-01-18 11:45:00' },
            { mac: 'FE:DC:BA:98:76:54', plate: 'CD-56-78', authorized: false, lastAccess: '2025-01-18 08:20:00' },
            { mac: '11:22:33:44:55:66', plate: '56-CD-78', authorized: true, lastAccess: '2025-01-18 12:10:00' }
        ];

        // Load vehicles from localStorage or use defaults
        this.authorizedVehicles = this.loadVehicles() || defaultVehicles;

        this.currentPage = 1;
        this.currentPageAdvanced = 1;
        this.itemsPerPage = 10;
        this.itemsPerPageAdvanced = 5;
        this.filteredResults = [...this.authorizedVehicles];
        this.filteredAdvancedResults = [...this.authorizedVehicles];

        this.init();
    }

    /**
     * Debounce function to limit search frequency
     */
    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }

    /**
     * Initialize search functionality
     */
    init() {
        // Get DOM elements
        this.macSearchInput = document.getElementById('mac-search');
        this.plateSearchInput = document.getElementById('plate-search');
        this.macSearchAdvancedInput = document.getElementById('mac-search-advanced');
        this.plateSearchAdvancedInput = document.getElementById('plate-search-advanced');
        
        this.authorizedMacsContainer = document.getElementById('authorized-macs');
        this.authorizedMacsAdvancedContainer = document.getElementById('authorized-macs-advanced');
        
        this.searchResultsCount = document.getElementById('search-results-count');
        this.searchResultsCountAdvanced = document.getElementById('search-results-count-advanced');
        
        this.prevPageBtn = document.getElementById('prev-page');
        this.nextPageBtn = document.getElementById('next-page');
        this.prevPageAdvancedBtn = document.getElementById('prev-page-advanced');
        this.nextPageAdvancedBtn = document.getElementById('next-page-advanced');

        // Add event listeners
        if (this.macSearchInput) {
            this.macSearchInput.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        }

        if (this.plateSearchInput) {
            this.plateSearchInput.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        }

        if (this.macSearchAdvancedInput) {
            this.macSearchAdvancedInput.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        }

        if (this.plateSearchAdvancedInput) {
            this.plateSearchAdvancedInput.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        }

        // Pagination event listeners
        if (this.prevPageBtn) {
            this.prevPageBtn.addEventListener('click', () => this.previousPage());
        }

        if (this.nextPageBtn) {
            this.nextPageBtn.addEventListener('click', () => this.nextPage());
        }

        if (this.prevPageAdvancedBtn) {
            this.prevPageAdvancedBtn.addEventListener('click', () => this.previousPageAdvanced());
        }

        if (this.nextPageAdvancedBtn) {
            this.nextPageAdvancedBtn.addEventListener('click', () => this.nextPageAdvanced());
        }

        // Initialize displays
        this.populateDataLists();
        this.displayResults();
        this.displayAdvancedResults();
    }

    /**
     * Validate MAC format
     */
    validateMacFormat(mac) {
        if (!mac || typeof mac !== 'string') return false;
        
        const cleanMac = mac.replace(/[^0-9A-Fa-f]/g, '');
        const macRegex = /^[0-9A-Fa-f]{12}$/;
        return macRegex.test(cleanMac);
    }

    /**
     * Validate Portuguese plate format - FIXED VERSION
     */
    validatePlateFormat(plate) {
        if (!plate || typeof plate !== 'string') return false;
        
        const cleanPlate = plate.replace(/[^0-9A-Za-z]/g, '');
        
        if (cleanPlate.length !== 6) return false;
        
        const letters = cleanPlate.match(/[A-Za-z]/g) || [];
        const numbers = cleanPlate.match(/[0-9]/g) || [];
        
        return letters.length >= 2 && numbers.length >= 2;
    }

    /**
     * Format MAC address with colons
     */
    formatMacWithColons(mac) {
        if (!mac || typeof mac !== 'string') {
            throw new Error('MAC inválido');
        }
        
        const cleanMac = mac.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();

        if (cleanMac.length !== 12) {
            throw new Error('MAC deve ter exatamente 12 caracteres hexadecimais');
        }

        const formattedMac = cleanMac.match(/.{1,2}/g).join(':');
        return formattedMac;
    }

    /**
     * Format plate to Portuguese standard format
     */
    formatPlateStandard(plate) {
        if (!plate || typeof plate !== 'string') {
            throw new Error('Matrícula inválida');
        }
        
        const cleanPlate = plate.replace(/[^0-9A-Za-z]/g, '').toUpperCase();

        if (cleanPlate.length !== 6) {
            throw new Error('Matrícula deve ter exatamente 6 caracteres');
        }

        const formattedPlate = cleanPlate.substring(0, 2) + '-' + 
                              cleanPlate.substring(2, 4) + '-' + 
                              cleanPlate.substring(4, 6);

        return formattedPlate;
    }

    /**
     * Add new vehicle to the system with duplicate validation
     */
    async addVehicle(mac, plate, authorized = true, skipValidation = false) {
        try {
            // Validate and format MAC address
            if (!this.validateMacFormat(mac)) {
                throw new Error('Formato de MAC inválido. Use 12 caracteres hexadecimais (ex: 1234567890aa ou 12:34:56:78:90:aa)');
            }
            const formattedMac = this.formatMacWithColons(mac);

            // Validate and format plate
            if (!this.validatePlateFormat(plate)) {
                throw new Error('Formato de matrícula inválido. Use formato português: AA1212, 12AB34, 1234AB, AA-12-12, 12-AB-34, 12-34-AB');
            }
            const formattedPlate = this.formatPlateStandard(plate);

            // Check for duplicates AFTER formatting
            if (!skipValidation) {
                const duplicateResult = this.checkForDuplicates(formattedMac, formattedPlate);
                if (duplicateResult.hasDuplicate) {
                    const shouldEdit = await this.showDuplicateDialog(duplicateResult, formattedMac, formattedPlate);
                    if (!shouldEdit) {
                        return null; // User cancelled
                    }
                    // If user wants to edit, continue with the update
                }
            }

            const newVehicle = {
                mac: formattedMac,
                plate: formattedPlate,
                authorized: authorized,
                lastAccess: new Date().toLocaleString('pt-PT')
            };

            // Check if vehicle already exists for update
            const existingIndex = this.authorizedVehicles.findIndex(v => v.mac === formattedMac || v.plate === formattedPlate);
            if (existingIndex >= 0) {
                // Update existing vehicle
                this.authorizedVehicles[existingIndex] = newVehicle;
                if (window.addLog) {
                    window.addLog(`Veículo atualizado: ${formattedPlate} (${formattedMac})`);
                }
                if (window.showToast) {
                    window.showToast(`Veículo atualizado: ${formattedPlate} (${formattedMac})`, 'success');
                }
            } else {
                // Add new vehicle
                this.authorizedVehicles.push(newVehicle);
                if (window.addLog) {
                    window.addLog(`Novo veículo adicionado: ${formattedPlate} (${formattedMac})`);
                }
                if (window.showToast) {
                    window.showToast(`Veículo adicionado com sucesso: ${formattedPlate} (${formattedMac})`, 'success');
                }
            }

            // Update UI
            this.populateDataLists();
            this.performSearch();
            this.performAdvancedSearch();
            this.saveVehicles();

            return newVehicle;
        } catch (error) {
            if (window.showToast) {
                window.showToast(`Erro: ${error.message}`, 'error');
            } else {
                alert(`Erro: ${error.message}`);
            }
            
            if (window.addLog) {
                window.addLog(`Erro ao adicionar veículo: ${error.message}`);
            }
            
            throw error;
        }
    }

    /**
     * Check for duplicate MAC or plate AFTER formatting
     */
    checkForDuplicates(formattedMac, formattedPlate) {
        const macDuplicate = this.authorizedVehicles.find(v => v.mac === formattedMac);
        const plateDuplicate = this.authorizedVehicles.find(v => v.plate === formattedPlate);

        return {
            hasDuplicate: !!(macDuplicate || plateDuplicate),
            macDuplicate: macDuplicate,
            plateDuplicate: plateDuplicate,
            duplicateType: macDuplicate && plateDuplicate ? 'both' : 
                          macDuplicate ? 'mac' : 
                          plateDuplicate ? 'plate' : 'none'
        };
    }

    /**
     * Show duplicate confirmation dialog
     */
    async showDuplicateDialog(duplicateResult, newMac, newPlate) {
        return new Promise((resolve) => {
            // Create modal HTML
            const modalHTML = `
                <div id="duplicate-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-overlay">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white modal-content">
                        <div class="mt-3">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">⚠️ Veículo Já Existe</h3>
                                <div class="mt-2 px-7 py-3">
                                    <p class="text-sm text-gray-500">
                                        ${this.getDuplicateMessage(duplicateResult)}
                                    </p>
                                    <div class="mt-4 p-3 duplicate-info rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>Dados existentes:
                                        </h4>
                                        ${this.getDuplicateDetails(duplicateResult)}
                                    </div>
                                    <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <h4 class="text-sm font-medium text-green-700 mb-2">
                                            <i class="fas fa-plus-circle mr-1 text-green-500"></i>Novos dados:
                                        </h4>
                                        <div class="text-xs text-gray-700 p-2 bg-white rounded border">
                                            <div class="space-y-1">
                                                <div><strong>MAC:</strong> <code class="bg-green-100 px-1 rounded">${newMac}</code></div>
                                                <div><strong>Matrícula:</strong> <code class="bg-green-100 px-1 rounded">${newPlate}</code></div>
                                                <div><strong>Status:</strong> <span class="px-1 py-0.5 rounded text-xs bg-green-100 text-green-700">Autorizado</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center space-x-3 mt-4">
                                    <button id="cancel-duplicate" class="modal-button px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                        <i class="fas fa-times mr-1"></i> Cancelar
                                    </button>
                                    <button id="edit-duplicate" class="modal-button px-4 py-2 bg-orange-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        <i class="fas fa-edit mr-1"></i> Substituir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to DOM
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Add event listeners
            document.getElementById('cancel-duplicate').addEventListener('click', () => {
                document.getElementById('duplicate-modal').remove();
                resolve(false);
            });

            document.getElementById('edit-duplicate').addEventListener('click', () => {
                document.getElementById('duplicate-modal').remove();
                resolve(true);
            });

            // Close on outside click
            document.getElementById('duplicate-modal').addEventListener('click', (e) => {
                if (e.target.id === 'duplicate-modal') {
                    document.getElementById('duplicate-modal').remove();
                    resolve(false);
                }
            });
        });
    }

    /**
     * Get duplicate message based on type
     */
    getDuplicateMessage(duplicateResult) {
        switch (duplicateResult.duplicateType) {
            case 'both':
                return 'Já existe um veículo com o mesmo MAC e a mesma matrícula na base de dados.';
            case 'mac':
                return 'Já existe um veículo com o mesmo endereço MAC na base de dados.';
            case 'plate':
                return 'Já existe um veículo com a mesma matrícula na base de dados.';
            default:
                return 'Veículo duplicado encontrado.';
        }
    }

    /**
     * Get duplicate details for display
     */
    getDuplicateDetails(duplicateResult) {
        let details = '';
        
        if (duplicateResult.macDuplicate) {
            const statusClass = duplicateResult.macDuplicate.authorized ? 'status-authorized' : 'status-unauthorized';
            const statusText = duplicateResult.macDuplicate.authorized ? 'Autorizado' : 'Não Autorizado';
            
            details += `<div class="text-xs text-gray-700 mb-2 p-2 bg-white rounded border">
                <div class="flex items-center justify-between mb-1">
                    <strong class="text-gray-800">🚗 Veículo:</strong>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="space-y-1">
                    <div><strong>MAC:</strong> <code class="bg-gray-100 px-1 rounded">${duplicateResult.macDuplicate.mac}</code></div>
                    <div><strong>Matrícula:</strong> <code class="bg-gray-100 px-1 rounded">${duplicateResult.macDuplicate.plate}</code></div>
                    <div><strong>Último Acesso:</strong> ${duplicateResult.macDuplicate.lastAccess}</div>
                </div>
            </div>`;
        }
        
        if (duplicateResult.plateDuplicate && duplicateResult.plateDuplicate !== duplicateResult.macDuplicate) {
            const statusClass = duplicateResult.plateDuplicate.authorized ? 'status-authorized' : 'status-unauthorized';
            const statusText = duplicateResult.plateDuplicate.authorized ? 'Autorizado' : 'Não Autorizado';
            
            details += `<div class="text-xs text-gray-700 p-2 bg-white rounded border">
                <div class="flex items-center justify-between mb-1">
                    <strong class="text-gray-800">🚗 Veículo:</strong>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="space-y-1">
                    <div><strong>MAC:</strong> <code class="bg-gray-100 px-1 rounded">${duplicateResult.plateDuplicate.mac}</code></div>
                    <div><strong>Matrícula:</strong> <code class="bg-gray-100 px-1 rounded">${duplicateResult.plateDuplicate.plate}</code></div>
                    <div><strong>Último Acesso:</strong> ${duplicateResult.plateDuplicate.lastAccess}</div>
                </div>
            </div>`;
        }
        
        return details;
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
     * Normalize MAC for search (remove separators)
     */
    normalizeMacForSearch(mac) {
        return mac.replace(/[^0-9A-Fa-f]/g, '').toLowerCase();
    }

    /**
     * Normalize plate for search (remove separators)
     */
    normalizePlateForSearch(plate) {
        return plate.replace(/[^0-9A-Za-z]/g, '').toLowerCase();
    }

    /**
     * Check if search query matches vehicle data (flexible format matching)
     */
    matchesSearchQuery(vehicleValue, searchQuery, isMAC = false) {
        if (!searchQuery) return true;
        
        const query = searchQuery.toLowerCase();
        const value = vehicleValue.toLowerCase();
        
        // Direct match (with separators)
        if (value.includes(query)) {
            return true;
        }
        
        // Normalized match (without separators)
        if (isMAC) {
            const normalizedValue = this.normalizeMacForSearch(value);
            const normalizedQuery = this.normalizeMacForSearch(query);
            return normalizedValue.includes(normalizedQuery);
        } else {
            const normalizedValue = this.normalizePlateForSearch(value);
            const normalizedQuery = this.normalizePlateForSearch(query);
            return normalizedValue.includes(normalizedQuery);
        }
    }

    /**
     * Perform search based on MAC and plate inputs - ENHANCED VERSION
     */
    performSearch() {
        const macQuery = this.macSearchInput ? this.macSearchInput.value.trim() : '';
        const plateQuery = this.plateSearchInput ? this.plateSearchInput.value.trim() : '';

        this.filteredResults = this.authorizedVehicles.filter(vehicle => {
            const macMatch = this.matchesSearchQuery(vehicle.mac, macQuery, true);
            const plateMatch = this.matchesSearchQuery(vehicle.plate, plateQuery, false);

            if (macQuery && plateQuery) {
                return macMatch && plateMatch;
            }

            if (macQuery) return macMatch;
            if (plateQuery) return plateMatch;

            return true;
        });

        this.currentPage = 1;
        this.displayResults();
    }

    /**
     * Perform advanced search - ENHANCED VERSION
     */
    performAdvancedSearch() {
        const macQuery = this.macSearchAdvancedInput ? this.macSearchAdvancedInput.value.trim() : '';
        const plateQuery = this.plateSearchAdvancedInput ? this.plateSearchAdvancedInput.value.trim() : '';

        this.filteredAdvancedResults = this.authorizedVehicles.filter(vehicle => {
            const macMatch = this.matchesSearchQuery(vehicle.mac, macQuery, true);
            const plateMatch = this.matchesSearchQuery(vehicle.plate, plateQuery, false);

            if (macQuery && plateQuery) {
                return macMatch && plateMatch;
            }

            if (macQuery) return macMatch;
            if (plateQuery) return plateMatch;

            return true;
        });

        this.currentPageAdvanced = 1;
        this.displayAdvancedResults();
    }

    /**
     * Display search results
     */
    displayResults() {
        if (!this.authorizedMacsContainer) return;

        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageResults = this.filteredResults.slice(startIndex, endIndex);

        this.authorizedMacsContainer.innerHTML = '';

        if (pageResults.length === 0) {
            this.authorizedMacsContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-4xl mb-4"></i>
                    <p>Nenhum veículo encontrado</p>
                </div>
            `;
        } else {
            pageResults.forEach(vehicle => {
                const vehicleElement = this.createVehicleElement(vehicle);
                this.authorizedMacsContainer.appendChild(vehicleElement);
            });
        }

        this.updatePagination();

        if (this.searchResultsCount) {
            this.searchResultsCount.textContent = `${this.filteredResults.length} resultado${this.filteredResults.length !== 1 ? 's' : ''}`;
        }
    }

    /**
     * Display advanced search results
     */
    displayAdvancedResults() {
        if (!this.authorizedMacsAdvancedContainer) return;

        const startIndex = (this.currentPageAdvanced - 1) * this.itemsPerPageAdvanced;
        const endIndex = startIndex + this.itemsPerPageAdvanced;
        const pageResults = this.filteredAdvancedResults.slice(startIndex, endIndex);

        this.authorizedMacsAdvancedContainer.innerHTML = '';

        if (pageResults.length === 0) {
            this.authorizedMacsAdvancedContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-4xl mb-4"></i>
                    <p>Nenhum veículo encontrado</p>
                </div>
            `;
        } else {
            pageResults.forEach(vehicle => {
                const vehicleElement = this.createAdvancedVehicleElement(vehicle);
                this.authorizedMacsAdvancedContainer.appendChild(vehicleElement);
            });
        }

        this.updateAdvancedPagination();

        if (this.searchResultsCountAdvanced) {
            this.searchResultsCountAdvanced.textContent = `${this.filteredAdvancedResults.length} resultado${this.filteredAdvancedResults.length !== 1 ? 's' : ''}`;
        }
    }

    /**
     * Create vehicle element for display
     */
    createVehicleElement(vehicle) {
        const div = document.createElement('div');
        div.className = 'bg-white p-4 rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow';

        const statusClass = vehicle.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        const statusText = vehicle.authorized ? 'Autorizado' : 'Não Autorizado';

        div.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 text-lg">${vehicle.plate}</h3>
                    <p class="text-sm text-gray-600 font-mono">${vehicle.mac}</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                    ${statusText}
                </span>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                Último acesso: ${vehicle.lastAccess}
            </div>
        `;

        return div;
    }

    /**
     * Create advanced vehicle element for display
     */
    createAdvancedVehicleElement(vehicle) {
        const div = document.createElement('div');
        div.className = 'bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow';

        const statusClass = vehicle.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        const statusText = vehicle.authorized ? 'Autorizado' : 'Não Autorizado';

        div.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-800">${vehicle.plate}</h4>
                    <p class="text-xs text-gray-600 font-mono">${vehicle.mac}</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                    ${statusText}
                </span>
            </div>
            <div class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                ${vehicle.lastAccess}
            </div>
        `;

        return div;
    }

    /**
     * Update pagination controls
     */
    updatePagination() {
        const totalPages = Math.ceil(this.filteredResults.length / this.itemsPerPage);

        if (this.prevPageBtn) {
            this.prevPageBtn.disabled = this.currentPage <= 1;
        }

        if (this.nextPageBtn) {
            this.nextPageBtn.disabled = this.currentPage >= totalPages;
        }
    }

    /**
     * Update advanced pagination controls
     */
    updateAdvancedPagination() {
        const totalPages = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);

        if (this.prevPageAdvancedBtn) {
            this.prevPageAdvancedBtn.disabled = this.currentPageAdvanced <= 1;
        }

        if (this.nextPageAdvancedBtn) {
            this.nextPageAdvancedBtn.disabled = this.currentPageAdvanced >= totalPages;
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
     * Go to previous page (advanced search)
     */
    previousPageAdvanced() {
        if (this.currentPageAdvanced > 1) {
            this.currentPageAdvanced--;
            this.displayAdvancedResults();
        }
    }

    /**
     * Go to next page (advanced search)
     */
    nextPageAdvanced() {
        const totalPages = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);
        if (this.currentPageAdvanced < totalPages) {
            this.currentPageAdvanced++;
            this.displayAdvancedResults();
        }
    }

    /**
     * Load vehicles from localStorage
     */
    loadVehicles() {
        try {
            const stored = localStorage.getItem('authorizedVehicles');
            return stored ? JSON.parse(stored) : null;
        } catch (error) {
            console.error('Error loading vehicles from localStorage:', error);
            return null;
        }
    }

    /**
     * Save vehicles to localStorage
     */
    saveVehicles() {
        try {
            localStorage.setItem('authorizedVehicles', JSON.stringify(this.authorizedVehicles));
        } catch (error) {
            console.error('Error saving vehicles to localStorage:', error);
        }
    }

    /**
     * Update vehicle's last access time
     */
    updateVehicleLastAccess(mac, plate, accessTime) {
        // Find vehicle by MAC or plate
        const vehicleIndex = this.authorizedVehicles.findIndex(v => v.mac === mac || v.plate === plate);
        
        if (vehicleIndex >= 0) {
            // Update last access time
            this.authorizedVehicles[vehicleIndex].lastAccess = accessTime;
            
            // Save to localStorage
            this.saveVehicles();
            
            // Update displays
            this.performSearch();
            this.performAdvancedSearch();
            
            // Log the update
            if (window.addLog) {
                window.addLog(`Último acesso atualizado: ${plate} (${mac}) - ${accessTime}`);
            }
            
            return true;
        } else {
            // Vehicle not found, try to add it as authorized
            if (window.addLog) {
                window.addLog(`Veículo não encontrado na base de dados: ${plate} (${mac}) - adicionando automaticamente`);
            }
            
            // Add vehicle automatically as authorized
            this.authorizedVehicles.push({
                mac: mac,
                plate: plate,
                authorized: true,
                lastAccess: accessTime
            });
            
            // Save and update displays
            this.saveVehicles();
            this.performSearch();
            this.performAdvancedSearch();
            this.populateDataLists();
            
            return true;
        }
    }

    /**
     * Export vehicles to CSV file
     */
    exportVehiclesToCSV() {
        try {
            // Create CSV header
            const csvHeader = 'MAC,Matrícula,Autorizado,Último Acesso\n';
            
            // Create CSV content
            const csvContent = this.authorizedVehicles.map(vehicle => {
                const authorized = vehicle.authorized ? 'Sim' : 'Não';
                const lastAccess = vehicle.lastAccess || 'Nunca';
                return `"${vehicle.mac}","${vehicle.plate}","${authorized}","${lastAccess}"`;
            }).join('\n');
            
            // Combine header and content
            const fullCSV = csvHeader + csvContent;
            
            // Create blob and download
            const blob = new Blob([fullCSV], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', `veiculos_autorizados_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            if (window.showToast) {
                window.showToast(`Exportados ${this.authorizedVehicles.length} veículos para CSV`, 'success');
            }
            
            if (window.addLog) {
                window.addLog(`Exportação CSV realizada: ${this.authorizedVehicles.length} veículos`);
            }
            
        } catch (error) {
            console.error('Error exporting vehicles:', error);
            if (window.showToast) {
                window.showToast(`Erro ao exportar: ${error.message}`, 'error');
            }
        }
    }

    /**
     * Import vehicles from CSV file
     */
    async importVehiclesFromCSV(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = async (e) => {
                try {
                    const csvContent = e.target.result;
                    const lines = csvContent.split('\n').filter(line => line.trim());
                    
                    if (lines.length < 2) {
                        throw new Error('Arquivo CSV vazio ou inválido');
                    }
                    
                    // Skip header line
                    const dataLines = lines.slice(1);
                    const importResults = {
                        total: dataLines.length,
                        success: 0,
                        errors: 0,
                        duplicates: 0,
                        errorDetails: []
                    };
                    
                    for (let i = 0; i < dataLines.length; i++) {
                        const line = dataLines[i].trim();
                        if (!line) continue;
                        
                        try {
                            // Parse CSV line (handle quoted values)
                            const values = this.parseCSVLine(line);
                            
                            if (values.length < 2) {
                                throw new Error('Linha inválida: dados insuficientes');
                            }
                            
                            const mac = values[0];
                            const plate = values[1];
                            const authorized = values[2] ? values[2].toLowerCase() === 'sim' : true;
                            
                            // Try to add vehicle
                            const result = await this.addVehicle(mac, plate, authorized, true); // Skip validation for import
                            
                            if (result) {
                                importResults.success++;
                            } else {
                                importResults.duplicates++;
                            }
                            
                        } catch (error) {
                            importResults.errors++;
                            importResults.errorDetails.push(`Linha ${i + 2}: ${error.message}`);
                        }
                    }
                    
                    // Show import results
                    this.showImportResults(importResults);
                    resolve(importResults);
                    
                } catch (error) {
                    console.error('Error importing CSV:', error);
                    if (window.showToast) {
                        window.showToast(`Erro ao importar: ${error.message}`, 'error');
                    }
                    reject(error);
                }
            };
            
            reader.onerror = () => {
                const error = new Error('Erro ao ler o arquivo');
                if (window.showToast) {
                    window.showToast(error.message, 'error');
                }
                reject(error);
            };
            
            reader.readAsText(file, 'UTF-8');
        });
    }

    /**
     * Parse CSV line handling quoted values
     */
    parseCSVLine(line) {
        const values = [];
        let current = '';
        let inQuotes = false;
        
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            
            if (char === '"') {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                values.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        
        values.push(current.trim());
        return values;
    }

    /**
     * Show import results modal
     */
    showImportResults(results) {
        const modalHTML = `
            <div id="import-results-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                            <i class="fas fa-file-import text-blue-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">📊 Resultado da Importação</h3>
                            <div class="mt-2 px-7 py-3">
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600">${results.total}</div>
                                            <div class="text-gray-600">Total</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600">${results.success}</div>
                                            <div class="text-gray-600">Sucesso</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-yellow-600">${results.duplicates}</div>
                                            <div class="text-gray-600">Duplicados</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-red-600">${results.errors}</div>
                                            <div class="text-gray-600">Erros</div>
                                        </div>
                                    </div>
                                </div>
                                ${results.errorDetails.length > 0 ? `
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                        <h4 class="text-sm font-medium text-red-700 mb-2">Detalhes dos Erros:</h4>
                                        <div class="text-xs text-red-600 max-h-32 overflow-y-auto">
                                            ${results.errorDetails.map(error => `<div>• ${error}</div>`).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="flex justify-center mt-4">
                                <button id="close-import-results" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700">
                                    <i class="fas fa-check mr-1"></i> OK
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        document.getElementById('close-import-results').addEventListener('click', () => {
            document.getElementById('import-results-modal').remove();
        });

        // Show toast summary
        if (window.showToast) {
            window.showToast(`Importação concluída: ${results.success} sucessos, ${results.errors} erros`, 
                results.errors > 0 ? 'warning' : 'success');
        }

        // Log results
        if (window.addLog) {
            window.addLog(`Importação CSV: ${results.success}/${results.total} veículos importados`);
        }
    }

    /**
     * Show file format instructions
     */
    showFileFormatInstructions() {
        const modalHTML = `
            <div id="format-instructions-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-10 mx-auto p-5 border w-auto max-w-2xl shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <i class="fas fa-info-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">📋 Como Formatar o Arquivo CSV</h3>
                            <div class="mt-2 px-4 py-3 text-left">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <h4 class="text-sm font-medium text-blue-700 mb-2">📝 Formato do Arquivo:</h4>
                                    <div class="text-sm text-blue-600">
                                        <p class="mb-2"><strong>Tipo:</strong> Arquivo CSV (separado por vírgulas)</p>
                                        <p class="mb-2"><strong>Codificação:</strong> UTF-8</p>
                                        <p><strong>Extensão:</strong> .csv</p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">📊 Estrutura das Colunas:</h4>
                                    <div class="text-xs font-mono bg-white p-2 rounded border">
                                        MAC,Matrícula,Autorizado,Último Acesso
                                    </div>
                                    <div class="text-sm text-gray-600 mt-2">
                                        <p><strong>Coluna 1 (MAC):</strong> Endereço MAC (obrigatório)</p>
                                        <p><strong>Coluna 2 (Matrícula):</strong> Matrícula do veículo (obrigatório)</p>
                                        <p><strong>Coluna 3 (Autorizado):</strong> "Sim" ou "Não" (opcional, padrão: Sim)</p>
                                        <p><strong>Coluna 4 (Último Acesso):</strong> Data/hora (opcional)</p>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <h4 class="text-sm font-medium text-green-700 mb-2">✅ Exemplo de Arquivo Válido:</h4>
                                    <div class="text-xs font-mono bg-white p-2 rounded border">
                                        MAC,Matrícula,Autorizado,Último Acesso<br>
                                        "24:A1:60:12:34:56","AB-12-34","Sim","2025-01-18 10:30:00"<br>
                                        "11:22:33:44:55:AA","CD-56-78","Sim","2025-01-18 09:15:00"<br>
                                        "BB:CC:DD:EE:11:22","EF-90-12","Não","2025-01-18 11:45:00"
                                    </div>
                                </div>
                                
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                    <h4 class="text-sm font-medium text-yellow-700 mb-2">⚠️ Formatos Aceitos:</h4>
                                    <div class="text-sm text-yellow-600">
                                        <p><strong>MAC:</strong> 24A160123456 ou 24:A1:60:12:34:56</p>
                                        <p><strong>Matrícula:</strong> AB1234 ou AB-12-34 (formatos portugueses)</p>
                                        <p><strong>Autorizado:</strong> Sim/Não, Yes/No, 1/0, true/false</p>
                                    </div>
                                </div>
                                
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-red-700 mb-2">❌ Erros Comuns:</h4>
                                    <div class="text-sm text-red-600">
                                        <p>• Arquivo sem cabeçalho</p>
                                        <p>• MAC com formato inválido (deve ter 12 caracteres hex)</p>
                                        <p>• Matrícula com formato inválido (deve seguir padrão português)</p>
                                        <p>• Linhas vazias ou incompletas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4">
                                <button id="close-format-instructions" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700">
                                    <i class="fas fa-check mr-1"></i> Entendi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        document.getElementById('close-format-instructions').addEventListener('click', () => {
            document.getElementById('format-instructions-modal').remove();
        });
    }
}

// Initialize search manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});