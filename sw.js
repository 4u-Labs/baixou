const CACHE_NAME = 'baixou-v1';

self.addEventListener('install', (e) => {
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(clients.claim());
});

self.addEventListener('fetch', (e) => {
  // Pass-through fetch
  e.respondWith(fetch(e.request).catch(() => caches.match(e.request)));
});
