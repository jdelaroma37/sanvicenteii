// Service Worker for Barangay San Vicente II Portal
// Version 1.0.2
//
// NOTE: Update the paths in STATIC_ASSETS below to match your deployment path.
// For local development (XAMPP), paths like '/brgysanvicenteii/' work.
// For production, you may need to adjust these paths based on your domain structure.

const CACHE_NAME = 'bsv-portal-v1.0.2';
const RUNTIME_CACHE = 'bsv-runtime-v1.0.2';

// Get base path from service worker location
const BASE_PATH = self.location.pathname.replace('/sw.js', '');

// Assets to cache on install
// Update these paths to match your deployment structure
const STATIC_ASSETS = [
  // Keep only truly static assets; avoid caching HTML/CSS to prevent stale UI
  BASE_PATH + '/index.js',
  BASE_PATH + '/favicon_io/favicon.ico',
  BASE_PATH + '/favicon_io/android-chrome-192x192.png',
  BASE_PATH + '/favicon_io/android-chrome-512x512.png',
  BASE_PATH + '/images/logo.jpg',
  BASE_PATH + '/images/barangay1.jpg',
  BASE_PATH + '/images/barangay2.jpg',
  BASE_PATH + '/images/barangay3.jpg',
  BASE_PATH + '/images/barangay4.jpg',
  BASE_PATH + '/images/barangay5.jpg',
  BASE_PATH + '/resident/css/base.css',
  BASE_PATH + '/resident/css/resident_dashboard.css',
  BASE_PATH + '/resident/js/resident_dashboard.js',
  BASE_PATH + '/resident/resident_dashboard.php',
  BASE_PATH + '/resident/resident_request.php',
  BASE_PATH + '/resident/res_profile.php',
  BASE_PATH + '/resident/css/res_profile.css',
  BASE_PATH + '/resident/res_profile.js',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS.map(url => {
          try {
            return new Request(url, { mode: 'no-cors' });
          } catch (e) {
            return url;
          }
        })).catch((err) => {
          console.log('[Service Worker] Cache addAll error:', err);
          // Cache what we can, continue even if some fail
          return Promise.allSettled(
            STATIC_ASSETS.map(url => 
              cache.add(url).catch(e => {
                console.log(`[Service Worker] Failed to cache ${url}:`, e);
                return null;
              })
            )
          );
        });
      })
      .then(() => {
        console.log('[Service Worker] Installed successfully');
        return self.skipWaiting(); // Activate immediately
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
    .then(() => {
      console.log('[Service Worker] Activated');
      return self.clients.claim(); // Take control of all pages immediately
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests that we can't cache
  if (url.origin !== location.origin && !url.href.startsWith('https://fonts.googleapis.com') && !url.href.startsWith('https://fonts.gstatic.com')) {
    return; // Let browser handle it normally
  }

  // Handle different types of requests
  if (request.method === 'GET') {
    // Always bypass cache for HTML and CSS to avoid stale UI
    const dest = request.destination;
    if (dest === 'document' || dest === 'style') {
      return; // let the browser fetch fresh
    }

    event.respondWith(
      caches.match(request)
        .then((cachedResponse) => {
          // Return cached version if available
          if (cachedResponse) {
            console.log('[Service Worker] Serving from cache:', request.url);
            return cachedResponse;
          }

          // Otherwise, fetch from network
          return fetch(request)
            .then((response) => {
              // Don't cache if not a valid response
              if (!response || response.status !== 200 || response.type === 'opaque') {
                return response;
              }

              // Clone the response
              const responseToCache = response.clone();

              // Cache the response for future use
              caches.open(RUNTIME_CACHE)
                .then((cache) => {
                  console.log('[Service Worker] Caching new resource:', request.url);
                  cache.put(request, responseToCache);
                });

              return response;
            })
            .catch((error) => {
              console.log('[Service Worker] Fetch failed:', error);
              
              // If it's a navigation request and we're offline, show offline page
              if (request.mode === 'navigate') {
                return caches.match(BASE_PATH + '/index.php') || 
                       caches.match(BASE_PATH + '/') ||
                       new Response('You are offline. Please check your internet connection.', {
                         status: 503,
                         headers: { 'Content-Type': 'text/html' }
                       });
              }
              
              // For other requests, return a basic offline message
              return new Response('Offline - Resource not available', {
                status: 503,
                headers: { 'Content-Type': 'text/plain' }
              });
            });
        })
    );
  }
});

// Handle background sync (for offline form submissions)
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync:', event.tag);
  if (event.tag === 'sync-requests') {
    event.waitUntil(syncRequests());
  }
});

// Function to sync requests when back online
async function syncRequests() {
  // This would sync any pending requests stored in IndexedDB
  // Implementation depends on your specific needs
  console.log('[Service Worker] Syncing requests...');
}

// Handle push notifications (optional, for future use)
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push notification received');
  const data = event.data ? event.data.json() : {};
  
  const options = {
    body: data.body || 'New update from Barangay San Vicente II',
    icon: BASE_PATH + '/favicon_io/android-chrome-192x192.png',
    badge: BASE_PATH + '/favicon_io/favicon-32x32.png',
    vibrate: [200, 100, 200],
    tag: 'barangay-notification',
    data: data
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Barangay San Vicente II', options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked');
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data?.url || BASE_PATH + '/')
  );
});
