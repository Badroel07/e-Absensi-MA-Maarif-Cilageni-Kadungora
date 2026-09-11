const CACHE_NAME = 'maarif-absensi-v2';

// Hanya asset statis yang di-cache lama (hashed build + manifest).
// Halaman dinamis (guru/*, siswa/*, admin/*, /, /login) TIDAK pernah di-cache
// agar update jadwal / data langsung fresh tanpa ganti halaman manual.
const STATIC_ASSETS = [
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch(() => {});
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Jangan cache halaman dinamis / navigasi dokumen — selalu network-only
  const isPageNavigation = event.request.mode === 'navigate'
    || event.request.destination === 'document'
    || event.request.headers.get('X-Partial-Nav') === 'true';

  const isDynamicPath = /^(\/(guru|siswa|admin|profile|login|logout|kiosk|dev)(\/|$))/.test(url.pathname)
    || url.pathname === '/';

  if (isPageNavigation || isDynamicPath) {
    event.respondWith(
      fetch(event.request, { cache: 'no-store' }).catch(() => caches.match(event.request))
    );
    return;
  }

  // Asset statis: cache-first untuk kecepatan, fallback ke network
  const isStaticAsset = url.pathname.startsWith('/build/')
    || /\.(js|css|png|jpg|jpeg|webp|svg|avif|gif|ico|woff|woff2)$/i.test(url.pathname)
    || url.pathname === '/manifest.json';

  if (isStaticAsset) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) return cached;
        return fetch(event.request).then((response) => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // Default: network-first tanpa menyimpan halaman
  event.respondWith(
    fetch(event.request, { cache: 'no-store' }).catch(() => caches.match(event.request))
  );
});
