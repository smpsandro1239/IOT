/**
 * Search and Metrics Functionality - FIXED VERSION
 * Handles MAC and plate search features with Portuguese plate formats
 */

class SearchManager {
    constructor() {
        // Default sample data with correct Portuguese plate formats
        const defaultVehicles = [
            { mac: '24:A1:60:12:34:56', plate: 'AB-12-34', authorized: true, lastAccess: '2025-01-18 10:30:00' },
            { mac: 'AA:BB:CC:DD:EE:FF', plate: '12-AB-34', authorized: true, lastAccess: '2025-01-18 09:15:00' },
            { mac: '12:34:56:78:9A:BC', plate: '12-34-AB', authorized: true, lastAccess: '2025-01-18 11:45:00' },
            { mac: 'FE:DC:BA:98:76:54', plate: 'CD-56-78', authorized: false, lastAccess: '2025-01-18 08:20:00' },
            { mac: '11:22:33:44:55:66', plate: '56-CD-78', authorized: true, lastAccess: '2025-01-18 12:10:00' },
            { mac: '99:88:77:66:55:44', plate: '78-90-EF', authorized: true, lastAccess: '2025-01-18 13:15:00' },
            { mac: '33:22:11:AA:BB:CC', plate: 'GH-90-12', authorized: true, lastAccess: '2025-01-18 14:20:00' },
            { mac: 'DD:EE:FF:11:22:33', plate: '34-56-GH', authorized: false, lastAccess: '2025-01-18 15:25:00' }
        ];

        // Load vehicles from localStorage or use defaults
        this.authorizedVehicles = this.loadVehicles() || defaultVehicles;

        this.currentPage = 1;
        this.currentPageAdvanced = 1;
        this.itemsPerPage = 10;
        this.itemsPerPageAdvanced = 5; // 5 items per page for advanced search
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
        // Get DOM elements for both sections
        this.macSearchInput = document.getElementById('mac-search');
        this.plateSearchInput = document.getElementById('plate-search');
        this.macSearchAdvancedInput = document.getElementById('mac-search-advanced');
        this.plateSearchAdvancedInput = document.getElementById('plate-search-advanced');
        
        this.macInputMetrics = document.getElementById('mac-input');
        this.plateInputMetrics = document.getElementById('plate-input');
        
        this.authorizedMacsContainer = document.getElementById('authorized-macs');
        this.authorizedMacsAdvancedContainer = document.getElementById('authorized-macs-advanced');
        
        this.searchResultsCount = document.getElementById('search-results-count');
        this.searchResultsCountAdvanced = document.getElementById('search-results-count-advanced');
        
        this.prevPageBtn = document.getElementById('prev-page');
        this.nextPageBtn = document.getElementById('next-page');
        this.prevPageAdvancedBtn = document.getElementById('prev-page-advanced');
        this.nextPageAdvancedBtn = document.getElementById('next-page-advanced');

        // Add event listeners for original search with debounce
        if (this.macSearchInput) {
            this.macSearchInput.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        }

        if (this.plateSearchInput) {
            this.plateSearchInput.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        }

        // Add event listeners for advanced search with debounce
        if (this.macSearchAdvancedInput) {
            this.macSearchAdvancedInput.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        }

        if (this.plateSearchAdvancedInput) {
            this.plateSearchAdvancedInput.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        }

        // Metrics event listeners
        if (this.macInputMetrics) {
            this.macInputMetrics.addEventListener('input', () => this.updateMetricsByMAC());
        }

        if (this.plateInputMetrics) {
            this.plateInputMetrics.addEventListener('input', () => this.updateMetricsByPlate());
        }

        // Pagination event listeners - original
        if (this.prevPageBtn) {
            this.prevPageBtn.addEventListener('click', () => this.previousPage());
        }

        if (this.nextPageBtn) {
            this.nextPageBtn.addEventListener('click', () => this.nextPage());
        }

        // Pagination event listeners - advanced
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
        this.setupSystemLogScroll();
    }

    /**
     * Setup system log with scroll and max 20 lines
     */
    setupSystemLogScroll() {
        const systemLog = document.getElementById('system-log');
        if (systemLog) {
            // Set max height and enable scroll
            systemLog.style.maxHeight = '300px';
            systemLog.style.overflowY = 'auto';
            systemLog.style.scrollBehavior = 'smooth';
            
            // Monitor for new log entries and limit to 20 lines
            const observer = new MutationObserver(() => {
                const logEntries = systemLog.children;
                if (logEntries.length > 20) {
                    // Remove oldest entries to keep only 20
                    while (logEntries.length > 20) {
                        systemLog.removeChild(logEntries[0]);
                    }
                }
                // Auto-scroll to bottom
                systemLog.scrollTop = systemLog.scrollHeight;
            });
            
            observer.observe(systemLog, { childList: true });
        }
    }

    /**
     * Validate MAC format (accepts multiple input formats)
     */
    validateMacFormat(mac) {
        if (!mac || typeof mac !== 'string') return false;
        
        // Remove any separators and spaces
        const cleanMac = mac.replace(/[^0-9A-Fa-f]/g, '');
        
        // Check if it's exactly 12 hex characters
        const macRegex = /^[0-9A-Fa-f]{12}$/;
        return macRegex.test(cleanMac);
    }

    /**
     * Validate Portuguese plate format (accepts multiple input formats)
     */
    validatePlateFormat(plate) {
        if (!plate || typeof plate !== 'string') return false;
        
        // Remove any separators and spaces
        const cleanPlate = plate.replace(/[^0-9A-Za-z]/g, '');
        
        // Check if it's exactly 6 alphanumeric characters
        if (cleanPlate.length !== 6) return false;
        
        // Portuguese formats (all possible combinations):
        // - AA0000 (2 letras + 4 números) - formato antigo
        // - 00AA00 (2 números + 2 letras + 2 números) - formato intermédio
        // - 0000AA (4 números + 2 letras) - formato atual
        // - AA00AA (2 letras + 2 números + 2 letras) - formato especial
        // - 00AAAA (2 números + 4 letras) - formato especial
        // - AAAA00 (4 letras + 2 números) - formato especial
        
        // Verificar se tem pelo menos 2 caracteres de cada tipo (letras e números)
        const letters = cleanPlate.match(/[A-Za-z]/g) || [];
        const numbers = cleanPlate.match(/[0-9]/g) || [];
        
        // Deve ter pelo menos 2 letras e 2 números
        if (letters.length < 2 || numbers.length < 2) return false;
        
        // Aceitar qualquer combinação válida de 6 caracteres com letras e números
        return true;
    }

    /**
     * Format MAC address with colons (standardized format)
     */
    formatMacWithColons(mac) {
        if (!mac || typeof mac !== 'string') {
            throw new Error('MAC inválido');
        }
        
        // Remove any non-hex characters
        const cleanMac = mac.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();

        // Validate length (must be 12 characters)
        if (cleanMac.length !== 12) {
            throw new Error('MAC deve ter exatamente 12 caracteres hexadecimais');
        }

        // Insert colons every 2 characters
        const formattedMac = cleanMac.match(/.{1,2}/g)?.join(':') || cleanMac;

        return formattedMac;
    }

    /**
     * Format plate to Portuguese standard format (XX-XX-XX)
     */
    formatPlateStandard(plate) {
        if (!plate || typeof plate !== 'string') {
            throw new Error('Matrícula inválida');
        }
        
        // Remove any non-alphanumeric characters and convert to uppercase
        const cleanPlate = plate.replace(/[^0-9A-Za-z]/g, '').toUpperCase();

        // Validate length (must be 6 characters)
        if (cleanPlate.length !== 6) {
            throw new Error('Matrícula deve ter exatamente 6 caracteres (formato português)');
        }

        // Format as XX-XX-XX (Portuguese standard with hyphens between each pair)
        const formattedPlate = cleanPlate.substring(0, 2) + '-' + 
                              cleanPlate.substring(2, 4) + '-' + 
                              cleanPlate.substring(4, 6);

        return formattedPlate;
    }

    /**
     * Add new vehicle to the system with duplicate validation and format standardization
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
                throw new Error('Formato de matrícula inválido. Use formato português: AA1234, 12AB34 ou 1234AB');
            }
            const formattedPlate = this.formatPlateStandard(plate);

            // Check for duplicates if validation is not skipped
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
            } else {
                // Add new vehicle
                this.authorizedVehicles.push(newVehicle);
                if (window.addLog) {
                    window.addLog(`Novo veículo adicionado: ${formattedPlate} (${formattedMac})`);
                }
            }

            // Update UI
            this.populateDataLists();
            this.performSearch(); // Refresh original display
            this.performAdvancedSearch(); // Refresh advanced display

            // Update metrics dropdowns
            this.updateMetricsDropdowns(formattedMac, formattedPlate);

            // Save to localStorage for persistence
            this.saveVehicles();

            return newVehicle;
        } catch (error) {
            // Show error message to user
            if (window.UIComponents && window.UIComponents.showToast) {
                window.UIComponents.showToast(`Erro: ${error.message}`, 'error');
            } else {
                alert(`Erro: ${error.message}`);
            }
            
            if (window.addLog) {
                window.addLog(`Erro ao adicionar veículo: ${error.message}`);
            }
            
            throw error; // Re-throw for handling by calling function
        }
    }

    /**
     * Check for duplicate MAC or plate
     */
    checkForDuplicates(mac, plate) {
        const macDuplicate = this.authorizedVehicles.find(v => v.mac === mac);
        const plateDuplicate = this.authorizedVehicles.find(v => v.plate === plate);

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
                                </div>
                                <div class="flex justify-center space-x-3 mt-4">
                                    <button id="cancel-duplicate" class="modal-button px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                        <i class="fas fa-times mr-1"></i> Cancelar
                                    </button>
                                    <button id="edit-duplicate" class="modal-button px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <i class="fas fa-edit mr-1"></i> Editar
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

            document.getElementById('edit-duplicate').addEventListener('click', async () => {
                const confirmEdit = await this.showEditConfirmation(duplicateResult, newMac, newPlate);
                document.getElementById('duplicate-modal').remove();
                resolve(confirmEdit);
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
     * Show edit confirmation dialog with before/after comparison
     */
    async showEditConfirmation(duplicateResult, newMac, newPlate) {
        return new Promise((resolve) => {
            // Get existing vehicle data
            const existingVehicle = duplicateResult.macDuplicate || duplicateResult.plateDuplicate;
            
            const confirmHTML = `
                <div id="edit-confirm-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-overlay">
                    <div class="relative top-20 mx-auto p-5 border w-auto max-w-lg shadow-lg rounded-md bg-white modal-content">
                        <div class="mt-3">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                                <i class="fas fa-exchange-alt text-orange-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">🔄 Confirmar Alteração</h3>
                                <div class="mt-2 px-4 py-3">
                                    <p class="text-sm text-gray-600 mb-4">
                                        Tem a certeza que deseja alterar os dados do veículo existente?
                                    </p>
                                    
                                    <!-- Before/After Comparison -->
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i class="fas fa-eye mr-2 text-blue-500"></i>Comparação de Alterações
                                        </h4>
                                        
                                        <div class="grid grid-cols-2 gap-4 text-xs">
                                            <!-- Before Column -->
                                            <div class="bg-red-50 border border-red-200 rounded p-3">
                                                <h5 class="font-semibold text-red-700 mb-2 flex items-center">
                                                    <i class="fas fa-arrow-left mr-1"></i>Dados Atuais
                                                </h5>
                                                <div class="space-y-1">
                                                    <div><strong>MAC:</strong> <code class="bg-red-100 px-1 rounded">${existingVehicle.mac}</code></div>
                                                    <div><strong>Matrícula:</strong> <code class="bg-red-100 px-1 rounded">${existingVehicle.plate}</code></div>
                                                    <div><strong>Status:</strong> <span class="px-1 py-0.5 rounded text-xs ${existingVehicle.authorized ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">${existingVehicle.authorized ? 'Autorizado' : 'Não Autorizado'}</span></div>
                                                </div>
                                            </div>
                                            
                                            <!-- After Column -->
                                            <div class="bg-green-50 border border-green-200 rounded p-3">
                                                <h5 class="font-semibold text-green-700 mb-2 flex items-center">
                                                    <i class="fas fa-arrow-right mr-1"></i>Novos Dados
                                                </h5>
                                                <div class="space-y-1">
                                                    <div><strong>MAC:</strong> <code class="bg-green-100 px-1 rounded">${newMac}</code></div>
                                                    <div><strong>Matrícula:</strong> <code class="bg-green-100 px-1 rounded">${newPlate}</code></div>
                                                    <div><strong>Status:</strong> <span class="px-1 py-0.5 rounded text-xs bg-green-100 text-green-700">Autorizado</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                        <p class="text-xs text-yellow-700 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Atenção:</strong> Esta ação irá substituir permanentemente os dados atuais pelos novos dados.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex justify-center space-x-3 mt-4">
                                    <button id="cancel-edit" class="modal-button px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                        <i class="fas fa-times mr-1"></i> Cancelar
                                    </button>
                                    <button id="confirm-edit" class="modal-button px-4 py-2 bg-orange-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        <i class="fas fa-check mr-1"></i> Confirmar Alteração
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', confirmHTML);

            document.getElementById('cancel-edit').addEventListener('click', () => {
                document.getElementById('edit-confirm-modal').remove();
                resolve(false);
            });

            document.getElementById('confirm-edit').addEventListener('click', () => {
                document.getElementById('edit-confirm-modal').remove();
                resolve(true);
            });

            // Close on outside click
            document.getElementById('edit-confirm-modal').addEventListener('click', (e) => {
                if (e.target.id === 'edit-confirm-modal') {
                    document.getElementById('edit-confirm-modal').remove();
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
     * Perform advanced search based on MAC and plate inputs
     */
    performAdvancedSearch() {
        const macQuery = this.macSearchAdvancedInput ? this.macSearchAdvancedInput.value.toLowerCase() : '';
        const plateQuery = this.plateSearchAdvancedInput ? this.plateSearchAdvancedInput.value.toLowerCase() : '';

        this.filteredAdvancedResults = this.authorizedVehicles.filter(vehicle => {
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

        this.currentPageAdvanced = 1;
        this.displayAdvancedResults();
    }edResults = this.authorizedVehicles.filter(vehicle => {
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

        this.currentPageAdvanced = 1;
        this.displayAdvancedResults();
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
     * Display advanced search results with 5 items per page
     */
    displayAdvancedResults() {
        if (!this.authorizedMacsAdvancedContainer || !this.searchResultsCountAdvanced) return;

        // Initialize filtered results if not exists
        if (!this.filteredAdvancedResults) {
            this.filteredAdvancedResults = [...this.authorizedVehicles];
        }

        // Update results count
        this.searchResultsCountAdvanced.textContent = this.filteredAdvancedResults.length;

        // Calculate pagination with 5 items per page
        const startIndex = (this.currentPageAdvanced - 1) * this.itemsPerPageAdvanced;
        const endIndex = startIndex + this.itemsPerPageAdvanced;
        const pageResults = this.filteredAdvancedResults.slice(startIndex, endIndex);

        // Clear container
        this.authorizedMacsAdvancedContainer.innerHTML = '';

        if (pageResults.length === 0) {
            this.authorizedMacsAdvancedContainer.innerHTML = '<div class="text-gray-500 text-center py-4">Nenhum resultado encontrado</div>';
            return;
        }

        // Display results
        pageResults.forEach(vehicle => {
            const resultDiv = document.createElement('div');
            resultDiv.className = `flex justify-between items-center p-3 border-b border-gray-200 ${vehicle.authorized ? 'bg-green-50' : 'bg-red-50'} hover:bg-blue-50 transition-colors duration-200`;

            resultDiv.innerHTML = `
                <div class="flex-1">
                    <div class="font-mono text-sm font-bold text-gray-800">${vehicle.plate}</div>
                    <div class="font-mono text-xs text-gray-600">${vehicle.mac}</div>
                    <div class="text-xs text-gray-500">Último acesso: ${vehicle.lastAccess}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 text-xs rounded-full ${vehicle.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${vehicle.authorized ? 'Autorizado' : 'Não Autorizado'}
                    </span>
                    <button class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-100" onclick="searchManager.viewDetails('${vehicle.mac}')" title="Ver detalhes">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            `;

            this.authorizedMacsAdvancedContainer.appendChild(resultDiv);
        });

        // Update pagination buttons
        this.updateAdvancedPaginationButtons();
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
     * Update advanced pagination buttons state
     */
    updateAdvancedPaginationButtons() {
        if (!this.filteredAdvancedResults) {
            this.filteredAdvancedResults = [...this.authorizedVehicles];
        }

        const totalPages = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);

        if (this.prevPageAdvancedBtn) {
            this.prevPageAdvancedBtn.disabled = this.currentPageAdvanced <= 1;
            this.prevPageAdvancedBtn.classList.toggle('opacity-50', this.currentPageAdvanced <= 1);
            this.prevPageAdvancedBtn.classList.toggle('cursor-not-allowed', this.currentPageAdvanced <= 1);
        }

        if (this.nextPageAdvancedBtn) {
            this.nextPageAdvancedBtn.disabled = this.currentPageAdvanced >= totalPages;
            this.nextPageAdvancedBtn.classList.toggle('opacity-50', this.currentPageAdvanced >= totalPages);
            this.nextPageAdvancedBtn.classList.toggle('cursor-not-allowed', this.currentPageAdvanced >= totalPages);
        }

        // Add page info
        const pageInfo = document.getElementById('page-info-advanced');
        if (pageInfo) {
            pageInfo.textContent = `Página ${this.currentPageAdvanced} de ${totalPages}`;
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
     * Go to previous page (advanced)
     */
    previousPageAdvanced() {
        if (this.currentPageAdvanced > 1) {
            this.currentPageAdvanced--;
            this.displayAdvancedResults();
        }
    }

    /**
     * Go to next page (advanced)
     */
    nextPageAdvanced() {
        if (!this.filteredAdvancedResults) {
            this.filteredAdvancedResults = [...this.authorizedVehicles];
        }

        const totalPages = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);
        if (this.currentPageAdvanced < totalPages) {
            this.currentPageAdvanced++;
            this.displayAdvancedResults();
        }
    }

    /**
     * Update metrics by MAC selection
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
     * Update metrics by plate selection
     */
    updateMetricsByPlate() {
        const selectedPlate = this.plateInputMetrics.value;
        const vehicle = this.authorizedVehicles.find(v => v.plate === selectedPlate);

        if (vehicle && this.macInputMetrics) {
            this.macInputMetrics.value = vehicle.mac;
            this.updateCharts('mac', vehicle.mac);
        } else {
            const partialMatches = this.authorizedVehicles.filter(v =>
                v.plate.toLowerCase().includes(selectedPlate.toLowerCase()));

            if (partialMatches.length > 0 && this.macInputMetrics) {
                this.macInputMetrics.value = partialMatches[0].mac;
                this.updateCharts('mac', partialMatches[0].mac);
                this.plateInputMetrics.value = partialMatches[0].plate;
            } else {
                this.updateCharts('plate', selectedPlate);
            }
        }
    }

    /**
     * Update charts based on selection
     */
    updateCharts(type, value) {
        console.log(`Updating charts for ${type}: ${value}`);

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
     * View vehicle details
     */
    viewDetails(mac) {
        const vehicle = this.authorizedVehicles.find(v => v.mac === mac);
        if (vehicle) {
            alert(`Detalhes do Veículo:\n\nMatrícula: ${vehicle.plate}\nMAC: ${vehicle.mac}\nStatus: ${vehicle.authorized ? 'Autorizado' : 'Não Autorizado'}\nÚltimo Acesso: ${vehicle.lastAccess}`);
        }
    }

    /**
     * Update metrics dropdowns
     */
    updateMetricsDropdowns(mac, plate) {
        if (this.macInputMetrics && this.plateInputMetrics) {
            const macList = document.getElementById('mac-list');
            const plateList = document.getElementById('plate-list');

            if (macList) {
                const existingOption = macList.querySelector(`option[value="${mac}"]`);
                if (existingOption) {
                    existingOption.remove();
                }
                
                const option = document.createElement('option');
                option.value = mac;
                option.textContent = `${mac} (${plate})`;
                macList.appendChild(option);
            }

            if (plateList) {
                const existingOption = plateList.querySelector(`option[value="${plate}"]`);
                if (existingOption) {
                    existingOption.remove();
                }
                
                const option = document.createElement('option');
                option.value = plate;
                option.textContent = `${plate} (${mac})`;
                plateList.appendChild(option);
            }
        }
    }

    /**
     * Save vehicles to localStorage
     */
    saveVehicles() {
        try {
            localStorage.setItem('authorizedVehicles', JSON.stringify(this.authorizedVehicles));
        } catch (error) {
            console.error('Error saving vehicles:', error);
        }
    }

    /**
     * Load vehicles from localStorage
     */
    loadVehicles() {
        try {
            const savedVehicles = localStorage.getItem('authorizedVehicles');
            return savedVehicles ? JSON.parse(savedVehicles) : null;
        } catch (error) {
            console.error('Error loading vehicles:', error);
            return null;
        }
    }
}

// Initialize search manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});