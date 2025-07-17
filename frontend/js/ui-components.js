/**
 * UI Components for IoT Barrier Control System
 * Provides reusable UI components and utilities
 */

// UI Components class
class UIComponents {
  /**
   * Show a toast notification
   * @param {string} message - Message to display
   * @param {string} type - Type of notification (success, error, warning, info)
   * @param {number} duration - Duration in milliseconds
   */
  static showToast(message, type = 'info', duration = 3000) {
    // Remove existing toasts
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
      existingToast.remove();
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification fixed bottom-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-opacity duration-300 flex items-center`;

    // Set background color based on type
    switch (type) {
      case 'success':
        toast.classList.add('bg-green-500', 'text-white');
        break;
      case 'error':
        toast.classList.add('bg-red-500', 'text-white');
        break;
      case 'warning':
        toast.classList.add('bg-yellow-500', 'text-white');
        break;
      default:
        toast.classList.add('bg-blue-500', 'text-white');
    }

    // Add icon based on type
    let icon;
    switch (type) {
      case 'success':
        icon = '<i class="fas fa-check-circle mr-2"></i>';
        break;
      case 'error':
        icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
        break;
      case 'warning':
        icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
        break;
      default:
        icon = '<i class="fas fa-info-circle mr-2"></i>';
    }

    // Set content
    toast.innerHTML = `
      ${icon}
      <span>${message}</span>
      <button class="ml-4 text-white hover:text-gray-200">
        <i class="fas fa-times"></i>
      </button>
    `;

    // Add to DOM
    document.body.appendChild(toast);

    // Add close button functionality
    const closeButton = toast.querySelector('button');
    closeButton.addEventListener('click', () => {
      toast.classList.add('opacity-0');
      setTimeout(() => toast.remove(), 300);
    });

    // Auto-remove after duration
    setTimeout(() => {
      toast.classList.add('opacity-0');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }

  /**
   * Show a confirmation dialog
   * @param {string} message - Message to display
   * @param {string} confirmText - Text for confirm button
   * @param {string} cancelText - Text for cancel button
   * @returns {Promise} - Resolves to true if confirmed, false if canceled
   */
  static showConfirmation(message, confirmText = 'Confirmar', cancelText = 'Cancelar') {
    return new Promise((resolve) => {
      // Create modal backdrop
      const backdrop = document.createElement('div');
      backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center';

      // Create modal content
      const modal = document.createElement('div');
      modal.className = 'bg-white rounded-lg p-6 max-w-md mx-4 shadow-xl';
      modal.innerHTML = `
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmação</h3>
        <p class="text-gray-700 mb-6">${message}</p>
        <div class="flex justify-end space-x-3">
          <button id="cancel-btn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
            ${cancelText}
          </button>
          <button id="confirm-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            ${confirmText}
          </button>
        </div>
      `;

      // Add to DOM
      backdrop.appendChild(modal);
      document.body.appendChild(backdrop);

      // Add button functionality
      const cancelBtn = modal.querySelector('#cancel-btn');
      const confirmBtn = modal.querySelector('#confirm-btn');

      cancelBtn.addEventListener('click', () => {
        backdrop.remove();
        resolve(false);
      });

      confirmBtn.addEventListener('click', () => {
        backdrop.remove();
        resolve(true);
      });
    });
  }

  /**
   * Show a loading spinner
   * @param {string} containerId - ID of container element
   * @param {string} size - Size of spinner (sm, md, lg)
   * @returns {Object} - Object with show and hide methods
   */
  static loadingSpinner(containerId, size = 'md') {
    const container = document.getElementById(containerId);
    if (!container) return null;

    // Determine spinner size
    let spinnerSize;
    switch (size) {
      case 'sm':
        spinnerSize = 'w-4 h-4 border-2';
        break;
      case 'lg':
        spinnerSize = 'w-8 h-8 border-4';
        break;
      default:
        spinnerSize = 'w-6 h-6 border-3';
    }

    // Create spinner element
    const spinner = document.createElement('div');
    spinner.className = `spinner ${spinnerSize} border-t-blue-500 rounded-full animate-spin mx-auto`;

    return {
      show: () => {
        // Store original content
        spinner.dataset.originalContent = container.innerHTML;
        container.innerHTML = '';
        container.appendChild(spinner);
      },
      hide: () => {
        // Restore original content if available
        if (spinner.dataset.originalContent) {
          container.innerHTML = spinner.dataset.originalContent;
        } else {
          container.innerHTML = '';
        }
      }
    };
  }

  /**
   * Create a data table
   * @param {string} containerId - ID of container element
   * @param {Array} columns - Array of column definitions
   * @param {Array} data - Array of data objects
   * @param {Object} options - Table options
   * @returns {Object} - Table control object
   */
  static createDataTable(containerId, columns, data, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    // Default options
    const defaultOptions = {
      pagination: true,
      pageSize: 10,
      search: true,
      emptyMessage: 'Nenhum dado encontrado',
      rowActions: []
    };

    const tableOptions = { ...defaultOptions, ...options };

    // Create table structure
    container.innerHTML = `
      <div class="overflow-x-auto">
        ${tableOptions.search ? `
          <div class="mb-4 flex justify-between items-center">
            <input type="text" id="${containerId}-search" placeholder="Pesquisar..."
              class="p-2 border rounded-lg w-full md:w-64">
          </div>
        ` : ''}
        <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              ${columns.map(col => `
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">
                  ${col.title}
                </th>
              `).join('')}
              ${tableOptions.rowActions.length > 0 ? `
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-b">
                  Ações
                </th>
              ` : ''}
            </tr>
          </thead>
          <tbody id="${containerId}-body">
            <!-- Data rows will be inserted here -->
          </tbody>
        </table>
        ${tableOptions.pagination ? `
          <div class="mt-4 flex justify-between items-center">
            <div>
              <span id="${containerId}-page-info">Mostrando 0-0 de 0</span>
            </div>
            <div>
              <button id="${containerId}-prev-page" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-l-lg disabled:opacity-50">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button id="${containerId}-next-page" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-r-lg disabled:opacity-50">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        ` : ''}
      </div>
    `;

    // Table state
    const state = {
      data: [...data],
      filteredData: [...data],
      currentPage: 1,
      pageSize: tableOptions.pageSize,
      searchTerm: ''
    };

    // Get DOM elements
    const tableBody = document.getElementById(`${containerId}-body`);
    const searchInput = tableOptions.search ? document.getElementById(`${containerId}-search`) : null;
    const prevPageBtn = tableOptions.pagination ? document.getElementById(`${containerId}-prev-page`) : null;
    const nextPageBtn = tableOptions.pagination ? document.getElementById(`${containerId}-next-page`) : null;
    const pageInfo = tableOptions.pagination ? document.getElementById(`${containerId}-page-info`) : null;

    // Render table rows
    function renderRows() {
      // Calculate pagination
      const startIndex = tableOptions.pagination ? (state.currentPage - 1) * state.pageSize : 0;
      const endIndex = tableOptions.pagination ? startIndex + state.pageSize : state.filteredData.length;
      const paginatedData = state.filteredData.slice(startIndex, endIndex);

      // Clear table body
      tableBody.innerHTML = '';

      // Check if we have data
      if (paginatedData.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
          <td colspan="${columns.length + (tableOptions.rowActions.length > 0 ? 1 : 0)}" class="px-4 py-4 text-center text-gray-500">
            ${tableOptions.emptyMessage}
          </td>
        `;
        tableBody.appendChild(emptyRow);
      } else {
        // Render data rows
        paginatedData.forEach((item, index) => {
          const row = document.createElement('tr');
          row.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';

          // Render cells
          columns.forEach(col => {
            const cell = document.createElement('td');
            cell.className = 'px-4 py-2 border-t';

            // Use renderer if provided, otherwise use raw value
            if (col.render) {
              cell.innerHTML = col.render(item[col.field], item);
            } else {
              cell.textContent = item[col.field] || '';
            }

            row.appendChild(cell);
          });

          // Add action buttons if needed
          if (tableOptions.rowActions.length > 0) {
            const actionsCell = document.createElement('td');
            actionsCell.className = 'px-4 py-2 border-t';

            tableOptions.rowActions.forEach(action => {
              const button = document.createElement('button');
              button.className = `mr-2 p-1 rounded-full ${action.className || 'text-blue-600 hover:text-blue-800'}`;
              button.innerHTML = action.icon || '';
              button.title = action.title || '';
              button.addEventListener('click', () => action.onClick(item));
              actionsCell.appendChild(button);
            });

            row.appendChild(actionsCell);
          }

          tableBody.appendChild(row);
        });
      }

      // Update pagination info
      if (tableOptions.pagination) {
        const total = state.filteredData.length;
        const start = total === 0 ? 0 : startIndex + 1;
        const end = Math.min(startIndex + state.pageSize, total);

        pageInfo.textContent = `Mostrando ${start}-${end} de ${total}`;
        prevPageBtn.disabled = state.currentPage === 1;
        nextPageBtn.disabled = endIndex >= state.filteredData.length;
      }
    }

    // Filter data based on search term
    function filterData() {
      if (!state.searchTerm) {
        state.filteredData = [...state.data];
      } else {
        const term = state.searchTerm.toLowerCase();
        state.filteredData = state.data.filter(item => {
          return columns.some(col => {
            const value = item[col.field];
            if (value === null || value === undefined) return false;
            return String(value).toLowerCase().includes(term);
          });
        });
      }

      state.currentPage = 1;
      renderRows();
    }

    // Add event listeners
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        state.searchTerm = e.target.value;
        filterData();
      });
    }

    if (prevPageBtn) {
      prevPageBtn.addEventListener('click', () => {
        if (state.currentPage > 1) {
          state.currentPage--;
          renderRows();
        }
      });
    }

    if (nextPageBtn) {
      nextPageBtn.addEventListener('click', () => {
        const maxPage = Math.ceil(state.filteredData.length / state.pageSize);
        if (state.currentPage < maxPage) {
          state.currentPage++;
          renderRows();
        }
      });
    }

    // Initial render
    renderRows();

    // Return table control object
    return {
      refresh: (newData = null) => {
        if (newData) {
          state.data = [...newData];
        }
        filterData();
      },
      getState: () => ({ ...state }),
      setPage: (page) => {
        state.currentPage = page;
        renderRows();
      },
      setPageSize: (size) => {
        state.pageSize = size;
        state.currentPage = 1;
        renderRows();
      },
      search: (term) => {
        if (searchInput) {
          searchInput.value = term;
        }
        state.searchTerm = term;
        filterData();
      }
    };
  }

  /**
   * Create a chart
   * @param {string} canvasId - ID of canvas element
   * @param {string} type - Chart type (line, bar, pie, etc.)
   * @param {Object} data - Chart data
   * @param {Object} options - Chart options
   * @returns {Object} - Chart instance
   */
  static createChart(canvasId, type, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    // Default options based on chart type
    const defaultOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          mode: 'index',
          intersect: false,
        }
      }
    };

    // Create chart
    const chart = new Chart(canvas, {
      type,
      data,
      options: { ...defaultOptions, ...options }
    });

    return chart;
  }

  /**
   * Format date
   * @param {string|Date} date - Date to format
   * @param {string} format - Format string
   * @returns {string} - Formatted date
   */
  static formatDate(date, format = 'dd/MM/yyyy HH:mm') {
    if (!date) return '';
    try {
      // Implementação simples de formatação de data sem depender de dateFns
      const d = new Date(date);
      const pad = (num) => num.toString().padStart(2, '0');

      const formatMap = {
        'dd': pad(d.getDate()),
        'MM': pad(d.getMonth() + 1),
        'yyyy': d.getFullYear(),
        'HH': pad(d.getHours()),
        'mm': pad(d.getMinutes()),
        'ss': pad(d.getSeconds())
      };

      let result = format;
      for (const [key, value] of Object.entries(formatMap)) {
        result = result.replace(key, value);
      }

      return result;
    } catch (error) {
      console.error('Error formatting date:', error);
      return String(date);
    }
  }

  /**
   * Format number
   * @param {number} number - Number to format
   * @param {string} locale - Locale
   * @param {Object} options - Format options
   * @returns {string} - Formatted number
   */
  static formatNumber(number, locale = 'pt-BR', options = {}) {
    if (number === null || number === undefined) return '';
    return new Intl.NumberFormat(locale, options).format(number);
  }
}

// Make it globally available
window.UIComponents = UIComponents;
