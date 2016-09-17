var routes = [];
routes['#login'] = {view: 'view-login', query: false, title: 'Login'};
routes['#logout'] = {view: 'view-logout', query: false};
routes['#profile'] = {view: 'view-profile', query: '/profile', title: 'Profile'};
routes['#401'] = {view: 'view-401', query: false, title: 'Error 401'};
routes['#404'] = {view: 'view-404', query: false, title: 'Error 404'};
routes['#500'] = {view: 'view-500', query: false, title: 'Error 500'};
routes['#configuration'] = {view: 'view-configuration', query: false, title: 'Configuration'};

routes['#feeds'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds', title: 'Feeds', store: false};
routes['#feeds/errors'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?errors=1', title: 'Error feeds', store: false};
routes['#feeds/subscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?subscribed=1', title: 'Subscribed feeds', store: false};
routes['#feeds/not_subscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?not_subscribed=1', title: 'Unsubscribed feeds', store: false};

routes['#feed/create'] = {view: 'view-feed-create', query: false};
routes['#feed/read/{id}'] = {view: 'view-feed-read', query: '/feed/{id}', store: false};
routes['#feed/update/{id}'] = {view: 'view-feed-update', query: '/feed/{id}', store: false};
routes['#feed/delete/{id}'] = {view: 'view-feed-delete', query: '/feed/{id}', store: false};
routes['#feed/subscribe/{id}'] = {view: false, query: '/feed/subscribe/{id}', store: false};

routes['#items/all'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items', title: 'All items', store: false};
routes['#items/unread'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?unread=1', title: 'Unread items', store: false};
routes['#items/starred'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?starred=1', title: 'Starred items', store: false};
routes['#items/feed/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?feed={id}', store: false};
routes['#items/author/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?category={id}', store: false};

routes['#item/read/{id}'] = {view: false, query: '/item/read/{id}', store: false};
routes['#item/star/{id}'] = {view: false, query: '/item/star/{id}', store: false};
