// Service Worker for IoT Barrier Control System
const CACHE_NAME = 'barrier-control-v1';
const urlsToCache = [
  '/',
  '/index.html',
  '/login.html',
  '/manifest.json',
  '/css/tailwind-local.css',
  '/js/app.js',
  '/js/simulation.js',
  '/js/api-client.js',
  '/js/ui-components.js'
];

// Install event - cache assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache opened');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch event - serve from cache, fall back to network
self.addEventListener('fetch', event => {
  // Skip cross-origin requests and API calls
  if (event.request.url.includes('/api/') ||
      !event.request.url.startsWith(self.location.origin) ||
      event.request.url.includes('pusher') ||
      event.request.url.includes('echo')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }

        // Clone the request
        const fetchRequest = event.request.clone();

        return fetch(fetchRequest).then(
          response => {
            // Check if valid response
            if(!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            const responseToCache = response.clone();

            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });

            return response;
          }
        );
      })
  );
});

// Background sync for offline data
self.addEventListener('sync', event => {
  if (event.tag === 'sync-access-logs') {
    event.waitUntil(syncAccessLogs());
  } else if (event.tag === 'sync-mac-auth') {
    event.waitUntil(syncMacAuth());
  }
});

// Function to sync access logs when back online
async function syncAccessLogs() {
  try {
    const db = await openDB();
    const offlineLogs = await db.getAll('offlineAccessLogs');

    if (offlineLogs.length === 0) return;

    for (const log of offlineLogs) {
      try {
        const response = await fetch('/api/v1/access-logs', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          },
          body: JSON.stringify(log)
        });

        if (response.ok) {
          await db.delete('offlineAccessLogs', log.id);
        }
      } catch (error) {
        console.error('Failed to sync log:', error);
      }
    }
  } catch (error) {
    console.error('Error in syncAccessLogs:', error);
  }
}

// Function to sync MAC authorizations when back online
async function syncMacAuth() {
  try {
    const db = await openDB();
    const offlineMacs = await db.getAll('offlineMacAuth');

    if (offlineMacs.length === 0) return;

    for (const mac of offlineMacs) {
      try {
        const response = await fetch('/api/v1/macs-autorizados', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          },
          body: JSON.stringify(mac)
        });

        if (response.ok) {
          await db.delete('offlineMacAuth', mac.id);
        }
      } catch (error) {
        console.error('Failed to sync MAC:', error);
      }
    }
  } catch (error) {
    console.error('Error in syncMacAuth:', error);
  }
}

// Helper function to open IndexedDB
function openDB() {
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
