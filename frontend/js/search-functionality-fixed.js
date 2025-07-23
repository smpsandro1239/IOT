/**
 * Search and Metrics Functionality - FINAL CORRIGIDO
 * Suporte completo para formatos portugueses de matrícula e MAC
 */

class SearchManager {
    constructor() {
        const defaultVehicles = [
            { mac: '24:A1:60:12:34:56', plate: 'AB-12-34', authorized: true, lastAccess: '2025-01-18 10:30:00' },
            { mac: 'AA:BB:CC:DD:EE:FF', plate: '12-AB-34', authorized: true, lastAccess: '2025-01-18 09:15:00' },
            { mac: '12:34:56:78:9A:BC', plate: '12-34-AB', authorized: true, lastAccess: '2025-01-18 11:45:00' },
            { mac: 'FE:DC:BA:98:76:54', plate: 'CD-56-78', authorized: false, lastAccess: '2025-01-18 08:20:00' }
        ];

        this.authorizedVehicles = this.loadVehicles() || defaultVehicles;
        this.currentPage = 1;
        this.currentPageAdvanced = 1;
        this.itemsPerPage = 10;
        this.itemsPerPageAdvanced = 5;
        this.filteredResults = [...this.authorizedVehicles];
        this.filteredAdvancedResults = [...this.authorizedVehicles];

        this.init();
    }

    init() {
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

        this.bindEvents();
        this.populateDataLists();
        this.displayResults();
        this.displayAdvancedResults();
    }

    bindEvents() {
        this.macSearchInput?.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        this.plateSearchInput?.addEventListener('input', () => this.debounce(() => this.performSearch(), 300));
        this.macSearchAdvancedInput?.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        this.plateSearchAdvancedInput?.addEventListener('input', () => this.debounce(() => this.performAdvancedSearch(), 300));
        this.prevPageBtn?.addEventListener('click', () => this.previousPage());
        this.nextPageBtn?.addEventListener('click', () => this.nextPage());
        this.prevPageAdvancedBtn?.addEventListener('click', () => this.previousPageAdvanced());
        this.nextPageAdvancedBtn?.addEventListener('click', () => this.nextPageAdvanced());
    }

    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }

    validateMacFormat(mac) {
        const clean = mac.replace(/[^0-9A-Fa-f]/g, '');
        return /^[0-9A-Fa-f]{12}$/.test(clean);
    }

    validatePlateFormat(plate) {
        const clean = plate.replace(/[^0-9A-Za-z]/g, '');
        return clean.length === 6 && /[A-Za-z]{2}/.test(clean) && /[0-9]{2}/.test(clean);
    }

    formatMacWithColons(mac) {
        const clean = mac.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
        return clean.match(/.{1,2}/g).join(':');
    }

    formatPlateStandard(plate) {
        const clean = plate.replace(/[^0-9A-Za-z]/g, '').toUpperCase();
        return `${clean.slice(0, 2)}-${clean.slice(2, 4)}-${clean.slice(4, 6)}`;
    }

    populateDataLists() {
        const macList = document.getElementById('mac-list');
        const plateList = document.getElementById('plate-list');
        if (!macList || !plateList) return;

        macList.innerHTML = '';
        plateList.innerHTML = '';
        this.authorizedVehicles.forEach(v => {
            const optMac = document.createElement('option');
            optMac.value = v.mac;
            optMac.textContent = `${v.mac} (${v.plate})`;
            macList.appendChild(optMac);

            const optPlate = document.createElement('option');
            optPlate.value = v.plate;
            optPlate.textContent = `${v.plate} (${v.mac})`;
            plateList.appendChild(optPlate);
        });
    }

    performSearch() {
        const macQuery = this.macSearchInput?.value.toLowerCase() || '';
        const plateQuery = this.plateSearchInput?.value.toLowerCase() || '';
        this.filteredResults = this.authorizedVehicles.filter(v =>
            (!macQuery || v.mac.toLowerCase().includes(macQuery)) &&
            (!plateQuery || v.plate.toLowerCase().includes(plateQuery))
        );
        this.currentPage = 1;
        this.displayResults();
    }

    performAdvancedSearch() {
        const macQuery = this.macSearchAdvancedInput?.value.toLowerCase() || '';
        const plateQuery = this.plateSearchAdvancedInput?.value.toLowerCase() || '';
        this.filteredAdvancedResults = this.authorizedVehicles.filter(v =>
            (!macQuery || v.mac.toLowerCase().includes(macQuery)) &&
            (!plateQuery || v.plate.toLowerCase().includes(plateQuery))
        );
        this.currentPageAdvanced = 1;
        this.displayAdvancedResults();
    }

    displayResults() {
        if (!this.authorizedMacsContainer || !this.searchResultsCount) return;
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const page = this.filteredResults.slice(start, start + this.itemsPerPage);
        this.authorizedMacsContainer.innerHTML = page.map(v => `
            <div class="flex justify-between items-center p-2 border-b ${v.authorized ? 'bg-green-50' : 'bg-red-50'}">
                <div>
                    <div class="font-bold">${v.plate}</div>
                    <div class="text-xs text-gray-600">${v.mac}</div>
                    <div class="text-xs text-gray-500">Último: ${v.lastAccess}</div>
                </div>
                <span class="px-2 py-1 text-xs rounded-full ${v.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${v.authorized ? 'Autorizado' : 'Não Autorizado'}
                </span>
            </div>
        `).join('');
        this.searchResultsCount.textContent = this.filteredResults.length;
        this.updatePaginationButtons();
    }

    displayAdvancedResults() {
        if (!this.authorizedMacsAdvancedContainer || !this.searchResultsCountAdvanced) return;
        const start = (this.currentPageAdvanced - 1) * this.itemsPerPageAdvanced;
        const page = this.filteredAdvancedResults.slice(start, start + this.itemsPerPageAdvanced);
        this.authorizedMacsAdvancedContainer.innerHTML = page.map(v => `
            <div class="flex justify-between items-center p-3 border-b ${v.authorized ? 'bg-green-50' : 'bg-red-50'}">
                <div>
                    <div class="font-bold">${v.plate}</div>
                    <div class="text-xs text-gray-600">${v.mac}</div>
                    <div class="text-xs text-gray-500">Último: ${v.lastAccess}</div>
                </div>
                <span class="px-2 py-1 text-xs rounded-full ${v.authorized ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${v.authorized ? 'Autorizado' : 'Não Autorizado'}
                </span>
            </div>
        `).join('');
        this.searchResultsCountAdvanced.textContent = this.filteredAdvancedResults.length;
        this.updateAdvancedPaginationButtons();
    }

    updatePaginationButtons() {
        const total = Math.ceil(this.filteredResults.length / this.itemsPerPage);
        this.prevPageBtn && (this.prevPageBtn.disabled = this.currentPage <= 1);
        this.nextPageBtn && (this.nextPageBtn.disabled = this.currentPage >= total);
    }

    updateAdvancedPaginationButtons() {
        const total = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);
        this.prevPageAdvancedBtn && (this.prevPageAdvancedBtn.disabled = this.currentPageAdvanced <= 1);
        this.nextPageAdvancedBtn && (this.nextPageAdvancedBtn.disabled = this.currentPageAdvanced >= total);
    }

    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.displayResults();
        }
    }

    nextPage() {
        const total = Math.ceil(this.filteredResults.length / this.itemsPerPage);
        if (this.currentPage < total) {
            this.currentPage++;
            this.displayResults();
        }
    }

    previousPageAdvanced() {
        if (this.currentPageAdvanced > 1) {
            this.currentPageAdvanced--;
            this.displayAdvancedResults();
        }
    }

    nextPageAdvanced() {
        const total = Math.ceil(this.filteredAdvancedResults.length / this.itemsPerPageAdvanced);
        if (this.currentPageAdvanced < total) {
            this.currentPageAdvanced++;
            this.displayAdvancedResults();
        }
    }

    viewDetails(mac) {
        const v = this.authorizedVehicles.find(x => x.mac === mac);
        if (v) alert(`Matrícula: ${v.plate}\nMAC: ${v.mac}\nStatus: ${v.authorized ? 'Autorizado' : 'Não Autorizado'}`);
    }

    saveVehicles() {
        localStorage.setItem('authorizedVehicles', JSON.stringify(this.authorizedVehicles));
    }

    loadVehicles() {
        const data = localStorage.getItem('authorizedVehicles');
        return data ? JSON.parse(data) : null;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});