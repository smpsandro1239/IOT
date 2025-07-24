/**
 * API Client for IoT Barrier Control System
 * Handles all API communication with error handling and offline support
 */

// API Client class
class ApiClient {
  constructor(baseUrl = 'http://localhost:8000/api/v1') {
    this.baseUrl = baseUrl;
    this.offlineMode = false;
    this.checkConnection();

    // Check connection status periodically
    setInterval(() => this.checkConnection(), 30000);

    // Listen for online/offline events
    window.addEventListener('online', () => {
      this.offlineMode = false;
      this.triggerSync();
      this.notifyConnectionChange(true);
    });

    window.addEventListener('offline', () => {
      this.offlineMode = true;
      this.notifyConnectionChange(false);
    });
  }

  /**
   * Check if we have an internet connection
   */
  async checkConnection() {
    try {
      const response = await fetch(this.baseUrl + '/status/latest.php', {
        method: 'HEAD',
        cache: 'no-store',
        mode: 'cors',
        credentials: 'include'
      });
      const wasOffline = this.offlineMode;
      this.offlineMode = !response.ok;

      if (wasOffline && !this.offlineMode) {
        this.triggerSync();
        this.notifyConnectionChange(true);
      } else if (!wasOffline && this.offlineMode) {
        this.notifyConnectionChange(false);
      }
    } catch (error) {
      console.warn('Connection check failed:', error);
      this.offlineMode = true;
      this.notifyConnectionChange(false);
    }
  }

  /**
   * Notify app components about connection changes
   */
  notifyConnectionChange(isOnline) {
    const event = new CustomEvent('connectionChange', {
      detail: { online: isOnline }
    });
    window.dispatchEvent(event);

    if (isOnline) {
      console.log('🌐 Connection restored. Syncing data...');
    } else {
      console.log('⚠️ Connection lost. Switching to offline mode.');
    }
  }

  /**
   * Trigger background sync when connection is restored
   */
  triggerSync() {
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
      navigator.serviceWorker.ready.then(registration => {
        registration.sync.register('sync-access-logs');
        registration.sync.register('sync-mac-auth');
      });
    } else {
      // Manual sync for browsers without background sync support
      this.manualSync();
    }
  }

  /**
   * Manual sync for browsers without background sync support
   */
  async manualSync() {
    try {
      const db = await this.openDB();

      // Sync access logs
      const offlineLogs = await db.getAll('offlineAccessLogs');
      for (const log of offlineLogs) {
        try {
          const response = await this.postAccessLog(log, true);
          if (response.ok) {
            await db.delete('offlineAccessLogs', log.id);
          }
        } catch (error) {
          console.error('Failed to sync log:', error);
        }
      }

      // Sync MAC authorizations
      const offlineMacs = await db.getAll('offlineMacAuth');
      for (const mac of offlineMacs) {
        try {
          const response = await this.addAuthorizedMac(mac, true);
          if (response.ok) {
            await db.delete('offlineMacAuth', mac.id);
          }
        } catch (error) {
          console.error('Failed to sync MAC:', error);
        }
      }
    } catch (error) {
      console.error('Error in manualSync:', error);
    }
  }

  /**
   * Open IndexedDB for offline storage
   */
  openDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('BarrierControlDB', 1);

      request.onerror = event => {
        reject('IndexedDB error: ' + event.target.errorCode);
      };

      request.onsuccess = event => {
        resolve(event.target.result);
      };

      request.onupgradeneeded = event => {
        const db = event.target.result;

        if (!db.objectStoreNames.contains('offlineAccessLogs')) {
          db.createObjectStore('offlineAccessLogs', { keyPath: 'id', autoIncrement: true });
        }

        if (!db.objectStoreNames.contains('offlineMacAuth')) {
          db.createObjectStore('offlineMacAuth', { keyPath: 'id', autoIncrement: true });
        }
      };
    });
  }

  /**
   * Save data for offline use
   */
  async saveOfflineData(storeName, data) {
    try {
      const db = await this.openDB();
      const transaction = db.transaction(storeName, 'readwrite');
      const store = transaction.objectStore(storeName);
      await store.add(data);
    } catch (error) {
      console.error(`Error saving offline data to ${storeName}:`, error);
    }
  }

  /**
   * Get authentication token
   */
  getAuthToken() {
    return localStorage.getItem('auth_token');
  }

  /**
   * Handle API errors
   */
  handleApiError(error, endpoint) {
    console.error(`API Error (${endpoint}):`, error);

    // Dispatch error event for UI components to handle
    const event = new CustomEvent('apiError', {
      detail: {
        endpoint,
        message: error.message || 'Unknown error occurred',
        status: error.status || 0
      }
    });
    window.dispatchEvent(event);

    return {
      ok: false,
      error: error.message || 'Unknown error occurred'
    };
  }

  /**
   * Make API request with error handling
   */
  async request(endpoint, options = {}) {
    const url = this.baseUrl + endpoint;

    // Add auth token if available
    const token = this.getAuthToken();
    if (token) {
      options.headers = {
        ...options.headers,
        'Authorization': `Bearer ${token}`
      };
    }

    // Add default headers
    options.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers
    };

    try {
      // Check if we're offline
      if (this.offlineMode && options.offlineSupport) {
        // Store request for later if it supports offline mode
        if (options.method === 'POST' || options.method === 'PUT') {
          await this.saveOfflineData(
            options.offlineStore || 'offlineRequests',
            {
              endpoint,
              options,
              timestamp: new Date().toISOString()
            }
          );

          return {
            ok: true,
            offline: true,
            message: 'Request saved for sync when online'
          };
        } else {
          return {
            ok: false,
            offline: true,
            error: 'Cannot perform this operation while offline'
          };
        }
      }

      // Add CORS options
      options.mode = 'cors';
      options.credentials = 'include';

      const response = await fetch(url, options);

      // Parse response
      let data;
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        data = await response.json();
      } else {
        data = await response.text();
      }

      if (!response.ok) {
        throw {
          status: response.status,
          message: data.message || data.error || `HTTP error ${response.status}`,
          data
        };
      }

      return {
        ok: true,
        status: response.status,
        data
      };
    } catch (error) {
      return this.handleApiError(error, endpoint);
    }
  }

  /**
   * Get latest system status
   */
  async getLatestStatus() {
    return this.request('/status/latest.php', { method: 'GET' });
  }

  /**
   * Get authorized MACs with pagination and search
   */
  async getAuthorizedMacs(page = 1, search = '') {
    return this.request(`/macs-autorizados.php?page=${page}&search=${encodeURIComponent(search)}`, {
      method: 'GET'
    });
  }

  /**
   * Add a new authorized MAC
   */
  async addAuthorizedMac(data, skipOfflineCheck = false) {
    const options = {
      method: 'POST',
      body: JSON.stringify(data),
      offlineSupport: true,
      offlineStore: 'offlineMacAuth'
    };

    // Skip offline check for sync operations
    if (skipOfflineCheck) {
      delete options.offlineSupport;
    }

    return this.request('/macs-autorizados.php', options);
  }

  /**
   * Delete an authorized MAC
   */
  async deleteAuthorizedMac(mac) {
    return this.request(`/macs-autorizados.php/${mac}`, { method: 'DELETE' });
  }

  /**
   * Add multiple authorized MACs
   */
  async addBulkAuthorizedMacs(macs) {
    return this.request('/macs-autorizados.php/bulk', {
      method: 'POST',
      body: JSON.stringify({ macs }),
      offlineSupport: true,
      offlineStore: 'offlineMacAuth'
    });
  }

  /**
   * Download authorized MACs as text file
   */
  async downloadAuthorizedMacs() {
    try {
      const response = await fetch(this.baseUrl + '/macs-autorizados.php/download', {
        headers: {
          'Authorization': `Bearer ${this.getAuthToken()}`
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'macs_autorizados.txt';
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);

      return { ok: true };
    } catch (error) {
      return this.handleApiError(error, '/macs-autorizados.php/download');
    }
  }

  /**
   * Post access log
   */
  async postAccessLog(data, skipOfflineCheck = false) {
    const options = {
      method: 'POST',
      body: JSON.stringify(data),
      offlineSupport: true,
      offlineStore: 'offlineAccessLogs'
    };

    // Skip offline check for sync operations
    if (skipOfflineCheck) {
      delete options.offlineSupport;
    }

    return this.request('/access-logs', options);
  }

  /**
   * Get metrics data
   */
  async getMetrics() {
    return this.request('/metrics', { method: 'GET' });
  }

  /**
   * Get metrics for specific MAC
   */
  async getMacMetrics(mac) {
    return this.request(`/metrics/${mac}`, { method: 'GET' });
  }

  /**
   * Control gate
   */
  async controlGate(gate, action) {
    return this.request('/gate/control', {
      method: 'POST',
      body: JSON.stringify({ gate, action })
    });
  }

  /**
   * Login user
   */
  async login(email, password) {
    try {
      console.log('Login attempt with:', email);

      // Use the simulated login endpoint directly
      const response = await fetch('./api/login/index.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
      });

      console.log('Login response status:', response.status);

      // Try to parse the response as JSON
      let data;
      try {
        const text = await response.text();
        console.log('Raw response:', text);
        data = JSON.parse(text);
      } catch (e) {
        console.error('Failed to parse JSON response:', e);
        return {
          ok: false,
          error: 'Invalid server response'
        };
      }

      console.log('Parsed response data:', data);

      if (response.ok && data.token) {
        console.log('Login successful, storing token');
        localStorage.setItem('auth_token', data.token);
        return {
          ok: true,
          data: data
        };
      } else {
        console.log('Login failed:', data.message || 'Unknown error');
        return {
          ok: false,
          error: data.message || 'Login failed'
        };
      }
    } catch (error) {
      console.error('Login exception:', error);
      return this.handleApiError(error, '/login');
    }
  }

  /**
   * Logout user
   */
  logout() {
    localStorage.removeItem('auth_token');
  }
}

// Create global instance
window.apiClient = new ApiClient();
