var VERSION = '1.1';
var CACHE_KEY = 'readerself-v' + VERSION;
var CACHE_FILES = [
    '.',
    'manifest.json',
    'index.html',
    'app/core.css',
    'app/functions.js',
    'app/core.js',
    'app/routes.js',
    'app/shortcuts.js',
    'app/icons/icon-32x32.png',
    'app/icons/icon-192x192.png',
    'app/icons/icon-512x512.png',
    'app/translations/en.json',
    'app/translations/fr.json',
    'app/views/author.html',
    'app/views/category.html',
    'app/views/feed.html',
    'app/views/item.html',
    'app/views/member.html',
    'app/views/misc.html',
    'node_modules/dialog-polyfill/dialog-polyfill.css',
    'node_modules/material-design-lite/material.min.css',
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/dialog-polyfill/dialog-polyfill.js',
    'node_modules/material-design-lite/material.min.js',
    'node_modules/i18next/i18next.min.js',
    'node_modules/moment/min/moment.min.js',
    'node_modules/jquery.scrollto/jquery.scrollTo.min.js',
    'node_modules/handlebars/dist/handlebars.min.js'
];

self.addEventListener('install', function(InstallEvent) {
    if('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(
            caches.open(CACHE_KEY).then(function(cache) {
                cache.addAll(CACHE_FILES);
            })
        );
    }
});

self.addEventListener('activate', function(ExtendableEvent) {
    if('waitUntil' in ExtendableEvent) {
        ExtendableEvent.waitUntil(
            caches.keys().then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if(cacheName !== CACHE_KEY) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            }).then(function() {
                return self.clients.claim();
            })
        );
    }
});

self.addEventListener('fetch', function(FetchEvent) {
    if(FetchEvent.request.url.indexOf('/api/') === -1) {
        FetchEvent.respondWith(
            caches.match(FetchEvent.request).then(function(response) {
                if(response) {
                    return response;
                }
                return fetch(FetchEvent.request).then(function(response) {
                    return response;
                });
            })
        );
    }
});
