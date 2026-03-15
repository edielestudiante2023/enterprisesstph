const CACHE_NAME = 'chat-sst-v1';
const STATIC_ASSETS = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
];

// Install: cachear assets estáticos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate: limpiar caches viejos
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch: estrategia según tipo de request
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // API calls (chat/send, chat/schema) → Network only
    if (url.pathname.includes('/chat/send') || url.pathname.includes('/chat/schema')) {
        event.respondWith(fetch(event.request));
        return;
    }

    // POST requests → Network only
    if (event.request.method !== 'GET') {
        event.respondWith(fetch(event.request));
        return;
    }

    // Páginas del chat → Network first, fallback cache
    if (url.pathname.includes('/consultant/chat')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Assets estáticos → Cache first, fallback network
    event.respondWith(
        caches.match(event.request)
            .then(cached => cached || fetch(event.request))
    );
});
