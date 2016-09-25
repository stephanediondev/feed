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
                icon: 'icon-192x192.png',
                actions: [
                    //{action: 'action1', title: 'test action', icon: 'icon-192x192.png'}
                ],
                tag: 'readerself'
            })
        );
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    /*event.waitUntil(
        clients.matchAll({
            type: "window"
        }).then(function(clientList) {
            if(clientList.length && 'focus' in client) {
                return client.focus();
            }

            if(clients.openWindow) {
                return clients.openWindow('/#items/unread');
            }
        })
    );*/
});
