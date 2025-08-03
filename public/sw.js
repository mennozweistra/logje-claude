// Logje Health Tracker Service Worker
const CACHE_NAME = 'logje-health-tracker-v1';
const urlsToCache = [
  '/',
  '/dashboard',
  '/reports',
  '/css/app.css',
  '/js/app.js',
  '/manifest.json',
  '/images/icons/icon.svg'
];

// Install event - cache resources
self.addEventListener('install', function(event) {
  console.log('Service Worker installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch(function(error) {
        console.log('Cache installation failed:', error);
      })
  );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Return cached version or fetch from network
        if (response) {
          console.log('Serving from cache:', event.request.url);
          return response;
        }
        
        console.log('Fetching from network:', event.request.url);
        return fetch(event.request).then(function(response) {
          // Check if we received a valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Clone the response since it's a stream
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then(function(cache) {
              cache.put(event.request, responseToCache);
            });

          return response;
        }).catch(function() {
          // Return offline page for navigation requests
          if (event.request.destination === 'document') {
            return caches.match('/');
          }
        });
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', function(event) {
  console.log('Service Worker activating...');
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for offline data (basic implementation)
self.addEventListener('sync', function(event) {
  console.log('Background sync triggered:', event.tag);
  if (event.tag === 'background-sync') {
    event.waitUntil(
      // Here you would implement offline data synchronization
      console.log('Background sync completed')
    );
  }
});

// Push notification support (basic implementation)
self.addEventListener('push', function(event) {
  console.log('Push notification received:', event);
  
  const options = {
    body: event.data ? event.data.text() : 'New health data available',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    tag: 'health-notification',
    renotify: true
  };

  event.waitUntil(
    self.registration.showNotification('Logje Health Tracker', options)
  );
});