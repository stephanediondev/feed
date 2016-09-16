var routes = [];
routes['#login'] = {view: 'view-login', query: false, title: 'Login'};
routes['#logout'] = {view: 'view-logout', query: false};
routes['#profile'] = {view: 'view-profile', query: '/profile', title: 'Profile'};
routes['#401'] = {view: 'view-401', query: false, title: 'Error 401'};
routes['#404'] = {view: 'view-404', query: false, title: 'Error 404'};
routes['#500'] = {view: 'view-500', query: false, title: 'Error 500'};
routes['#configuration'] = {view: 'view-configuration', query: false, title: 'Configuration'};

routes['#feeds'] = {view: 'view-feeds', query: '/feeds', title: 'Feeds', store: true};
routes['#feed/create'] = {view: 'view-feed-create', query: false};
routes['#feed/read/{id}'] = {view: 'view-feed-read', query: '/feed/{id}', store: false};
routes['#feed/update/{id}'] = {view: 'view-feed-update', query: '/feed/{id}', store: false};
routes['#feed/delete/{id}'] = {view: 'view-feed-delete', query: '/feed/{id}', store: false};

routes['#subscriptions'] = {view: 'view-subscriptions', query: '/subscriptions', title: 'Subscriptions', store: true};
routes['#subscription/create'] = {view: 'view-subscription-create', query: false};
routes['#subscription/read/{id}'] = {view: 'view-subscription-read', query: '/subscription/{id}', store: false};
routes['#subscription/update/{id}'] = {view: 'view-subscription-update', query: '/subscription/{id}', store: false};
routes['#subscription/delete/{id}'] = {view: 'view-subscription-delete', query: '/subscription/{id}', store: false};

routes['#folders'] = {view: 'view-folders', query: '/folders', title: 'Folders', store: true};
routes['#folder/create'] = {view: 'view-folder-create', query: false};
routes['#folder/read/{id}'] = {view: 'view-folder-read', query: '/folder/{id}', store: false};
routes['#folder/update/{id}'] = {view: 'view-folder-update', query: '/folder/{id}', store: false};
routes['#folder/delete/{id}'] = {view: 'view-folder-delete', query: '/folder/{id}', store: false};

routes['#item/read/{id}'] = {view: false, query: '/item/read/{id}', store: false};
routes['#item/star/{id}'] = {view: false, query: '/item/star/{id}', store: false};

routes['#items/unread'] = {view: 'view-items', query: '/items?unread=1', title: 'Items unread', store: true};
routes['#items/shared'] = {view: 'view-items', query: '/items?shared=1', title: 'Items shared', store: false};
routes['#items/starred'] = {view: 'view-items', query: '/items?starred=1', title: 'Items starred', store: false};
routes['#items/priority'] = {view: 'view-items', query: '/items?priority=1', title: 'items with priority subscription', store: true};
routes['#items/geolocation'] = {view: 'view-items', query: '/items?geolocation=1', title: 'items with geolocation', store: true};
routes['#items/feed/{id}'] = {view: 'view-items', query: '/items?feed={id}', store: false};
routes['#items/folder/{id}'] = {view: 'view-items', query: '/items?folder={id}', store: false};
routes['#items/author/{id}'] = {view: 'view-items', query: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {view: 'view-items', query: '/items?category={id}', store: false};
