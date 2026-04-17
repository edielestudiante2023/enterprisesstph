const CACHE_NAME = 'inspecciones-v7';
const ASSETS_TO_CACHE = [
    '/inspecciones',
    '/js/offline_queue.js',
    '/js/prevent_double_tap.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://code.jquery.com/jquery-3.7.0.min.js'
];

// ── IndexedDB helpers (duplicated from offline_queue.js for SW scope) ──
const DB_NAME = 'inspecciones_offline';
const DB_VERSION = 1;
const STORE_NAME = 'pending_signatures';

function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = function(e) {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                store.createIndex('type', 'type', { unique: false });
                store.createIndex('id_asistencia', 'id_asistencia', { unique: false });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function getAllPending() {
    return openDB().then(db => new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
        tx.oncomplete = () => db.close();
    }));
}

function removePending(id) {
    return openDB().then(db => new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        const req = store.delete(id);
        req.onsuccess = () => resolve();
        req.onerror = () => reject(req.error);
        tx.oncomplete = () => db.close();
    }));
}

// ── Install - cache assets ──
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS_TO_CACHE))
            .then(() => self.skipWaiting())
    );
});

// ── Activate - clean old caches ──
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Background Sync - sincronizar firmas pendientes ──
self.addEventListener('sync', event => {
    if (event.tag === 'sync-firmas') {
        event.waitUntil(syncPendingSignatures());
    }
});

async function syncPendingSignatures() {
    const items = await getAllPending();
    if (items.length === 0) return;

    let synced = 0;
    for (const item of items) {
        try {
            const formData = new FormData();
            for (const [key, value] of Object.entries(item.payload)) {
                formData.append(key, value);
            }

            const response = await fetch(item.url, { method: 'POST', body: formData });
            if (!response.ok) continue;

            const data = await response.json();
            if (data.success) {
                await removePending(item.id);
                synced++;
            }
        } catch (e) {
            // Still offline or server error, will retry on next sync
            break;
        }
    }

    // Notify all clients about sync results
    if (synced > 0) {
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({ type: 'sync-firmas-complete', synced });
        });
    }
}

// ── Fetch - network first, cache fallback for assets ──
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests (POST for signatures handled by offline_queue.js client-side)
    if (event.request.method !== 'GET') return;

    // IA endpoints del Plan de Emergencia - passthrough (siempre network, sin cache).
    // Estas llamadas al Claude API tardan 20-40s y el SW interfiere con el fetch.
    if (url.pathname.includes('/plan-emergencia/enriquecer-pons-ia') ||
        url.pathname.includes('/plan-emergencia/generar-diagrama-ia') ||
        url.pathname.includes('/plan-emergencia/generar-matriz-ia') ||
        url.pathname.includes('/plan-emergencia/generar-brigada-ia') ||
        url.pathname.includes('/plan-emergencia/ia-save-contexto') ||
        url.pathname.includes('/plan-emergencia/ia-aprobar') ||
        url.pathname.includes('/plan-emergencia/ia-review')) {
        return; // no respondWith → el browser hace fetch normal sin SW
    }

    // For navigation requests (HTML pages) - always go to network
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match('/inspecciones'))
        );
        return;
    }

    // For CDN assets - cache first
    if (url.hostname !== location.hostname) {
        event.respondWith(
            caches.match(event.request)
                .then(cached => cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                }))
        );
        return;
    }

    // For local assets - network first
    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
