var VERSION = '1.1';
var CACHE_KEY = 'readerself-v' + VERSION;
var CACHE_FILES = [
    'manifest.json',
    'index.html',
    'app/core.css',
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
    'vendor/dialog-polyfill/dialog-polyfill.css',
    'vendor/material-design-lite/material.min.css',
    'vendor/jquery/dist/jquery.min.js',
    'vendor/dialog-polyfill/dialog-polyfill.js',
    'vendor/material-design-lite/material.min.js',
    'vendor/jquery-i18n/jquery.i18n.min.js',
    'vendor/moment/min/moment.min.js',
    'vendor/jquery.scrollTo/jquery.scrollTo.min.js',
    'vendor/handlebars/handlebars.min.js'
];

self.addEventListener('install', function(InstallEvent) {
    if('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(
            cacheAddAll()
        );
    }

    self.skipWaiting();
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
    var request = FetchEvent.request;

    FetchEvent.respondWith(
        caches.match(request).then(function(response) {
            if(response) {
                return response;
            }
            return fetch(request);
        })
    );
});

function cacheAddAll() {
    caches.delete(CACHE_KEY);
    caches.open(CACHE_KEY).then(function(cache) {
        return cache.addAll(CACHE_FILES);
    });
}
