var LOG_ENABLED = true;
var FETCH_ENABLED = true;
var FETCH_EXCLUDE = [
    '/#',
    '/api/',
    '/_wdt/',
];
var VERSION = '1.0';
var CACHE_KEY = 'feed-v' + VERSION;
var CACHE_FILES = [
];

self.addEventListener('install', function(InstallEvent) {
    self.skipWaiting();

    if ('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(function() {
            cacheAddAll();
        });
    }
});

self.addEventListener('activate', function(ExtendableEvent) {
    if ('waitUntil' in ExtendableEvent) {
        ExtendableEvent.waitUntil(
            caches.keys().then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if (cacheName !== CACHE_KEY) {
                            sendLog('delete cache storage ' + cacheName);
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
    if (false === FETCH_ENABLED) {
        return;
    }

    if ('only-if-cached' === FetchEvent.request.cache && 'same-origin' !== FetchEvent.request.mode) {
        return;
    }

    var url = new URL(FetchEvent.request.url);
    if (self.location.origin !== url.origin) {
        sendLog('excluded ' + FetchEvent.request.url);
        return;
    }

    var fetchAllowed = true;
    FETCH_EXCLUDE.forEach(function(item, i) {
        if (FetchEvent.request.url.indexOf(item) !== -1) {
            sendLog('excluded ' + FetchEvent.request.url);
            fetchAllowed = false;
        }
    });

    if (true === fetchAllowed) {
        FetchEvent.respondWith(
            caches.open(CACHE_KEY).then(function(cache) {
                return cache.match(FetchEvent.request).then(function(Response) {
                    if (Response) {
                        sendLog('found in cache storage ' + FetchEvent.request.url);
                        return Response;
                    }
                    return fetch(FetchEvent.request).then(function(Response) {
                        sendLog('added to cache storage ' + FetchEvent.request.url);
                        cache.put(FetchEvent.request, Response.clone());
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
        sendLog('set cache storage ' + CACHE_KEY);
        return cache.addAll(CACHE_FILES);
    });
}

function sendLog(log) {
    if (LOG_ENABLED) {
        console.log(log);
    }
}
