var VERSION = '1.0';
var CACHE_KEY = 'feed-v' + VERSION;
var LOG_ENABLED = true;
var FETCH_ENABLED = true;
var FETCH_EXCLUDE = [
    '/#',
    '/api',
    '/login',
    '/forgotpassword',
    '/proxy',
    '/_profiler',
    '/_wdt',
];

self.addEventListener('install', function(InstallEvent) {
    if ('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(
            caches.open(CACHE_KEY)
            .then(function(cache) {
                return fetch('/assets/manifest.json')
                .then(function(Response) {
                    return Response.json();
                }).then(function(json) {
                    let files = [];
                    files.push('/');
                    sendLog('added to cache storage: /');
                    for (const key in json) {
                        files.push(json[key]);
                        sendLog('added to cache storage: ' + json[key]);
                    }
                    return cache.addAll(files);
                });
            })
            .then(function() {
                return self.skipWaiting();
            })
        );
    }
});

self.addEventListener('activate', function(ExtendableEvent) {
    if ('waitUntil' in ExtendableEvent) {
        ExtendableEvent.waitUntil(
            caches.keys()
            .then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if (cacheName !== CACHE_KEY) {
                            sendLog('delete cache storage: ' + cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }).then(function() {
                messageToClient('new-version-installed', VERSION);
                return self.clients.claim();
            })
        );
    }
});

self.addEventListener('fetch', function(FetchEvent) {
    if (false === FETCH_ENABLED) {
        return;
    }

    if ('GET' !== FetchEvent.request.method) {
        sendLog('excluded: ' + FetchEvent.request.method + ' ' + FetchEvent.request.url);
        return;
    }

    var url = new URL(FetchEvent.request.url);
    if (self.location.origin !== url.origin && FetchEvent.request.url.indexOf('www.google.com/s2/favicons') === -1) {
        sendLog('excluded: ' + FetchEvent.request.url);
        return;
    }

    var fetchAllowed = true;
    FETCH_EXCLUDE.forEach(function(item, i) {
        if (FetchEvent.request.url.indexOf(item) !== -1) {
            sendLog('excluded: ' + FetchEvent.request.url);
            fetchAllowed = false;
        }
    });

    if (true === fetchAllowed) {
        FetchEvent.respondWith(
            caches.open(CACHE_KEY)
            .then(function(cache) {
                return cache.match(FetchEvent.request)
                .then(function(Response) {
                    if (Response) {
                        sendLog('found in cache storage: ' + FetchEvent.request.url);
                        return Response;
                    }
                    return fetch(FetchEvent.request)
                    .then(function(Response) {
                        sendLog('added to cache storage: ' + FetchEvent.request.url);
                        cache.put(FetchEvent.request, Response.clone());
                        return Response;
                    });
                });
            })
        );
    }
});

self.addEventListener('push', function(PushEvent) {
    if ('waitUntil' in PushEvent) {
        if (PushEvent.data) {
            var json = PushEvent.data.json();
            PushEvent.waitUntil(setBadge(json.countunread));
        }
    }
});

function setBadge(value) {
    if (navigator.setExperimentalAppBadge) {
        navigator.setExperimentalAppBadge(value).catch(function(error) {
        });
    } else if (navigator.setAppBadge) {
        navigator.setAppBadge(value).catch(function(error) {
        });
    }
}

function messageToClient(type, content) {
    self.clients.matchAll()
    .then(function(clients) {
        clients.map(function(client) {
            client.postMessage({type: type, content: content});
        });
    });
}

function sendLog(log) {
    if (LOG_ENABLED) {
        console.log(log);
    }
}
