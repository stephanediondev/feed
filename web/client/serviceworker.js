self.addEventListener('install', event => {
    event.waitUntil(
        caches.open('readerself-v1').then(cache => {
            return cache.addAll([
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
                'vendor/store.js/store.min.js',
                'vendor/jquery-i18n/jquery.i18n.min.js',
                'vendor/moment/min/moment.min.js',
                'vendor/jquery.scrollTo/jquery.scrollTo.min.js',
                'vendor/handlebars/handlebars.min.js'
            ]).then(() => self.skipWaiting());
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});

self.addEventListener('activate',  event => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function(event) {
    if(event.data) {
        var data = event.data.json();
        event.waitUntil(
            self.registration.showNotification(data.title, {
                body: data.body,
                icon: 'app/icons/icon-192x192.png',
                tag: 'readerself'
            })
        );
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({
            type: "window"
        }).then(function(clientList) {
            for(var i=0;i<clientList.length;i++) {
                var client = clientList[i];
                return client.focus();
            }

            if(clients.openWindow) {
                return clients.openWindow('./#items/unread');
            }
        })
    );
});
