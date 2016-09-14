var routes = [];
routes['#login'] = {template: 'template-login', path: false, title: 'Login'};
routes['#logout'] = {template: 'template-logout', path: false};
routes['#404'] = {template: 'template-404', path: false, history: false, title: 'Error 404'};
routes['#500'] = {template: 'template-500', path: false, history: false, title: 'Error 500'};

routes['#feeds'] = {template: 'template-feeds', path: '/feeds', title: 'Feeds', store: true};
routes['#feed/create'] = {template: 'template-feed-create', path: false};
routes['#feed/read/{id}'] = {template: 'template-feed-read', path: '/feed/{id}', store: false};
routes['#feed/update/{id}'] = {template: 'template-feed-update', path: '/feed/{id}', store: false};
routes['#feed/delete/{id}'] = {template: 'template-feed-delete', path: '/feed/{id}', store: false};

routes['#folders'] = {template: 'template-folders', path: '/folders', title: 'Folders', store: true};
routes['#folder/create'] = {template: 'template-folder-create', path: false};
routes['#folder/read/{id}'] = {template: 'template-folder-read', path: '/folder/{id}', store: false};
routes['#folder/update/{id}'] = {template: 'template-folder-update', path: '/folder/{id}', store: false};
routes['#folder/delete/{id}'] = {template: 'template-folder-delete', path: '/folder/{id}', store: false};

routes['#items/unread'] = {template: 'template-items', path: '/items?unread=1', title: 'Unread', store: false};
routes['#items/shared'] = {template: 'template-items', path: '/items?shared=1', title: 'Shared', store: false};
routes['#items/starred'] = {template: 'template-items', path: '/items?starred=1', title: 'Starred', store: false};
routes['#items/feed/{id}'] = {template: 'template-items', path: '/items?feed={id}', store: false};
routes['#items/folder/{id}'] = {template: 'template-items', path: '/items?folder={id}', store: false};
routes['#items/author/{id}'] = {template: 'template-items', path: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {template: 'template-items', path: '/items?category={id}', store: false};
