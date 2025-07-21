// Service Worker para Sistema de Controle de Barreiras IoT
const CACHE_NAME = 'radar-system-cache-v' + new Date().getTime();
const OFFLINE_URL = 'offline.html';

// Arquivos para cache
const CACHE_ASSETS = [
  './',
  './index.html',
  './css/tailwind-local.css',
  './css/chart-styles.css',
  './js/chart-polyfill.js',
  './js/api-client.js',
  './js/ui-components.js',
  './js/app.js',
  './js/radar-simulation.js',
  './js/search-functionality.js',
  './js/system-configuration.js',
  './js/chart-resize.js',
  './manifest.json',
  './offline.html'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
  console.log('[Service Worker] Instalando...');

  // Forçar o service worker a se tornar ativo imediatamente
  self.skipWaiting();

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[Service Worker] Cacheando arquivos');
        return cache.addAll(CACHE_ASSETS);
      })
  );
});

// Ativação do Service Worker
self.addEventListener('activate', event => {
  console.log('[Service Worker] Ativando...');

  // Tomar controle de todas as páginas imediatamente
  self.clients.claim();

  // Remover caches antigos
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Removendo cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Interceptação de requisições
self.addEventListener('fetch', event => {
  // Verificar se a requisição é para um arquivo de API
  if (event.request.url.includes('/api/')) {
    // Para APIs, sempre buscar da rede primeiro
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          // Se falhar, tentar do cache
          return caches.match(event.request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              // Se não estiver no cache, retornar página offline
              return caches.match(OFFLINE_URL);
            });
        })
    );
  } else {
    // Para arquivos estáticos, verificar se há parâmetro de versão
    const url = new URL(event.request.url);
    const hasVersionParam = url.search.includes('v=');

    if (hasVersionParam) {
      // Se tem parâmetro de versão, buscar da rede e atualizar o cache
      event.respondWith(
        fetch(event.request)
          .then(response => {
            // Clonar a resposta para poder usá-la duas vezes
            const responseClone = response.clone();

            // Atualizar o cache
            caches.open(CACHE_NAME).then(cache => {
              // Armazenar a resposta sem o parâmetro de versão
              const cacheUrl = new URL(event.request.url);
              cacheUrl.search = '';
              cache.put(new Request(cacheUrl.toString()), responseClone);
            });

            return response;
          })
          .catch(() => {
            // Se falhar, tentar do cache
            return caches.match(event.request);
          })
      );
    } else {
      // Para requisições sem parâmetro de versão, usar estratégia cache-first
      event.respondWith(
        caches.match(event.request)
          .then(cachedResponse => {
            // Retornar do cache se disponível
            if (cachedResponse) {
              return cachedResponse;
            }

            // Se não estiver no cache, buscar da rede
            return fetch(event.request)
              .then(response => {
                // Clonar a resposta para poder usá-la duas vezes
                const responseClone = response.clone();

                // Atualizar o cache
                caches.open(CACHE_NAME).then(cache => {
                  cache.put(event.request, responseClone);
                });

                return response;
              })
              .catch(() => {
                // Se falhar, retornar página offline para navegação
                if (event.request.mode === 'navigate') {
                  return caches.match(OFFLINE_URL);
                }

                // Para outros recursos, retornar erro 404
                return new Response('Recurso não disponível offline', {
                  status: 404,
                  statusText: 'Not Found'
                });
              });
          })
      );
    }
  }
});

// Evento de mensagem para limpar o cache
self.addEventListener('message', event => {
  if (event.data && event.data.action === 'CLEAR_CACHE') {
    console.log('[Service Worker] Limpando cache por solicitação');

    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            return caches.delete(cacheName);
          })
        ).then(() => {
          // Notificar cliente que o cache foi limpo
          self.clients.matchAll().then(clients => {
            clients.forEach(client => {
              client.postMessage({
                action: 'CACHE_CLEARED',
                timestamp: new Date().getTime()
              });
            });
          });
        });
      })
    );
  }
});
