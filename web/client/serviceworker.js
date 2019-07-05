var LOG_ENABLED = true;
var FETCH_IN_CACHE = false;
var FETCH_EXCLUDE = [
    '/api/',
];
var VERSION = '1.3';
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
    'node_modules/dialog-polyfill/dist/dialog-polyfill.css',
    'node_modules/material-design-lite/material.min.css',
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/dialog-polyfill/dialog-polyfill.js',
    'node_modules/material-design-lite/material.min.js',
    'node_modules/i18next/i18next.min.js',
    'node_modules/moment/min/moment.min.js',
    'node_modules/jquery.scrollto/jquery.scrollTo.min.js',
    'node_modules/handlebars/dist/handlebars.min.js',
    'node_modules/file-saver/dist/FileSaver.min.js'
];

self.addEventListener('install', function(InstallEvent) {
    sendLog(InstallEvent);

    self.skipWaiting();

    if('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(function() {
            cacheAddAll();
        });
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
    sendLog(FetchEvent);

    if('only-if-cached' === FetchEvent.request.cache && 'same-origin' !== FetchEvent.request.mode) {
        return;
    }

    var fetchAllowed = true;
    FETCH_EXCLUDE.forEach(function(item, i) {
        if(FetchEvent.request.url.indexOf(item) !== -1) {
            fetchAllowed = false;
        }
    });

    if(fetchAllowed) {
        FetchEvent.respondWith(
            caches.open(CACHE_KEY).then(function(cache) {
                return cache.match(FetchEvent.request).then(function(Response) {
                    if(Response) {
                        sendLog(Response);
                        return Response;
                    }
                    return fetch(FetchEvent.request).then(function(Response) {
                        sendLog(Response);
                        if(FETCH_IN_CACHE) {
                            cache.put(FetchEvent.request, Response.clone());
                        }
                        return Response;
                    });
                });
            })
        );
    }
});

function cacheAddAll() {
    caches.delete(CACHE_KEY);
    return caches.open(CACHE_KEY).then(function(cache) {
        return cache.addAll(CACHE_FILES);
    });
}

function sendLog(log) {
    if(LOG_ENABLED) {
        console.log(log);
    }
}
