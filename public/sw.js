const CACHE_NAME = 'v4_cache_capycontrol';
const urlsToCache = [
  '/',
  '/favicon.ico',
  '/manifest.json'
];

self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(() => {
            // offline fallback if needed
        });
      })
  );
});
