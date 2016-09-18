var routes = [];
routes['#login'] = {view: 'view-login', query: false, title: 'login'};
routes['#logout'] = {view: 'view-logout', query: false};
routes['#profile'] = {view: 'view-profile', query: '/profile', title: 'profile'};
routes['#401'] = {view: 'view-401', query: false, title: 'error_401'};
routes['#404'] = {view: 'view-404', query: false, title: 'error_404'};
routes['#500'] = {view: 'view-500', query: false, title: 'error_500'};
routes['#configuration'] = {view: 'view-configuration', query: false, title: 'configuration'};

routes['#search'] = {view: 'view-search', query: false, title: 'search'};

routes['#search/items'] = {view: 'view-search-items', query: false, title: 'search_items'};
routes['#search/items/result'] = {view: 'view-search-items', view_unit: 'view-items-unit', query: '/search/items', title: 'search_items'};

routes['#search/feeds'] = {view: 'view-search-feeds', query: false, title: 'search_feeds'};
routes['#search/feeds/result'] = {view: 'view-search-feeds', view_unit: 'view-feeds-unit', query: '/search/feeds', title: 'search_feeds'};

routes['#feeds'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds', title: 'all_feeds', store: false};
routes['#feeds/errors'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?errors=1', title: 'feeds_errors', store: false};
routes['#feeds/subscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?subscribed=1', title: 'subscribed_feeds', store: false};
routes['#feeds/not_subscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?not_subscribed=1', title: 'unsubscribed_feeds', store: false};

routes['#feed/create'] = {view: 'view-feed-create', query: false};
routes['#feed/read/{id}'] = {view: 'view-feed-read', query: '/feed/{id}', store: false};
routes['#feed/update/{id}'] = {view: 'view-feed-update', query: '/feed/{id}', store: false};
routes['#feed/delete/{id}'] = {view: 'view-feed-delete', query: '/feed/{id}', store: false};
routes['#feed/subscribe/{id}'] = {view: false, query: '/feed/subscribe/{id}', store: false};

routes['#category/read/{id}'] = {view: 'view-category-read', query: '/category/{id}', store: false};
routes['#category/exclude/{id}'] = {view: false, query: '/category/exclude/{id}', store: false};

routes['#items/all'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items', title: 'all_items', store: false};
routes['#items/unread'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?unread=1', title: 'unread_items', store: false};
routes['#items/starred'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?starred=1', title: 'starred_items', store: false};
routes['#items/feed/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?feed={id}', store: false};
routes['#items/author/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?category={id}', store: false};

routes['#item/read/{id}'] = {view: false, query: '/item/read/{id}', store: false};
routes['#item/star/{id}'] = {view: false, query: '/item/star/{id}', store: false};
