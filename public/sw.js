const CACHE_NAME = 'v5_cache_bypass';

self.addEventListener('install', function(event) {
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          return caches.delete(cacheName);
        })
      );
    }).then(function() {
      return self.clients.claim();
    })
  );
});

// Do not intercept network requests. Pass everything directly to the network
// so dynamic Laravel session, login POSTs, redirects, and navigation are never broken.
self.addEventListener('fetch', function(event) {
  return;
});
