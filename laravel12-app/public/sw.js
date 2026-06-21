const CACHE_NAME = 'siap-smk-v1';
const OFFLINE_PAGE = '/offline';

// Daftar halaman dan aset yang akan di-cache (read-only offline)
const ASSETS_TO_CACHE = [
    '/',
    '/dashboard',
    '/offline',
    '/manifest.json',
    // CDN Resources (pastikan versi stabil)
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css',
    'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js'
];

// Install Event - Cache aset statis
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Caching app shell');
            return cache.addAll(ASSETS_TO_CACHE);
        })
        .catch((error) => {
            console.error('[Service Worker] Cache failed:', error);
        })
    );
    self.skipWaiting();
});

// Activate Event - Hapus cache lama
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event - Network First, fallback to Cache
self.addEventListener('fetch', (event) => {
    // Abaikan request non-GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Abaikan request ke domain lain yang bukan CDN
    const url = new URL(event.request.url);
    if (url.origin !== location.origin && !url.href.includes('cdn.jsdelivr.net') && !url.href.includes('code.jquery.com')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Jika berhasil, clone response dan simpan ke cache
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        // Hanya cache halaman HTML dan aset statis
                        if (event.request.destination === 'document' || 
                            event.request.destination === 'style' || 
                            event.request.destination === 'script' ||
                            event.request.destination === 'image') {
                            cache.put(event.request, responseClone);
                        }
                    });
                }
                return response;
            })
            .catch(() => {
                // Jika network gagal, coba ambil dari cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        console.log('[Service Worker] Serving from cache:', event.request.url);
                        return cachedResponse;
                    }
                    
                    // Jika tidak ada di cache dan ini adalah navigasi halaman, tampilkan offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_PAGE);
                    }
                    
                    // Fallback untuk resource lain
                    return new Response('Offline - Resource not available', {
                        status: 503,
                        statusText: 'Service Unavailable'
                    });
                });
            })
    );
});

// Handle messages from clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
