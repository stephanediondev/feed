self.addEventListener('install', function() {
    self.skipWaiting();
});

self.addEventListener('activate', function() {
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
