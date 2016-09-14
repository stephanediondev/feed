var routes = [];
routes['#login'] = {template: 'template-login', path: false, title: 'Login'};
routes['#logout'] = {template: 'template-logout', path: false};
routes['#404'] = {template: 'template-404', path: false, history: false, title: 'Error 404'};
routes['#500'] = {template: 'template-500', path: false, history: false, title: 'Error 500'};
routes['#configuration'] = {template: 'template-configuration', path: false, title: 'Configuration'};

routes['#feeds'] = {template: 'template-feeds', path: '/feeds', title: 'Feeds', store: true};
routes['#feed/create'] = {template: 'template-feed-create', path: false};
routes['#feed/read/{id}'] = {template: 'template-feed-read', path: '/feed/{id}', store: false};
routes['#feed/update/{id}'] = {template: 'template-feed-update', path: '/feed/{id}', store: false};
routes['#feed/delete/{id}'] = {template: 'template-feed-delete', path: '/feed/{id}', store: false};

routes['#subscriptions'] = {template: 'template-subscriptions', path: '/subscriptions', title: 'Feeds', store: true};
routes['#subscription/create'] = {template: 'template-subscription-create', path: false};
routes['#subscription/read/{id}'] = {template: 'template-subscription-read', path: '/subscription/{id}', store: false};
routes['#subscription/update/{id}'] = {template: 'template-subscription-update', path: '/subscription/{id}', store: false};
routes['#subscription/delete/{id}'] = {template: 'template-subscription-delete', path: '/subscription/{id}', store: false};

routes['#folders'] = {template: 'template-folders', path: '/folders', title: 'Folders', store: true};
routes['#folder/create'] = {template: 'template-folder-create', path: false};
routes['#folder/read/{id}'] = {template: 'template-folder-read', path: '/folder/{id}', store: false};
routes['#folder/update/{id}'] = {template: 'template-folder-update', path: '/folder/{id}', store: false};
routes['#folder/delete/{id}'] = {template: 'template-folder-delete', path: '/folder/{id}', store: false};

routes['#item/read/{id}'] = {template: false, path: '/item/read/{id}', store: false};
routes['#item/star/{id}'] = {template: false, path: '/item/star/{id}', store: false};
routes['#item/share/{id}'] = {template: false, path: '/item/share/{id}', store: false};

routes['#items/unread'] = {template: 'template-items', path: '/items?unread=1', title: 'Unread', store: true};
routes['#items/shared'] = {template: 'template-items', path: '/items?shared=1', title: 'Shared', store: false};
routes['#items/starred'] = {template: 'template-items', path: '/items?starred=1', title: 'Starred', store: false};
routes['#items/feed/{id}'] = {template: 'template-items', path: '/items?feed={id}', store: false};
routes['#items/folder/{id}'] = {template: 'template-items', path: '/items?folder={id}', store: false};
routes['#items/author/{id}'] = {template: 'template-items', path: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {template: 'template-items', path: '/items?category={id}', store: false};
