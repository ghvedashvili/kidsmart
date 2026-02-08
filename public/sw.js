const CACHE_NAME = 'veravart-cache-v2';

const ASSETS_TO_CACHE = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/favicon.ico',
    '/manifest.json',
];

// Install
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS_TO_CACHE))
    );
});

// Activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );
});

// Fetch
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    if (url.pathname.includes('auth/google')) {
        return;
    }
});
