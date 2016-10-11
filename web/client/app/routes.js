var routes = [];
routes['#test'] = {view: false, query: '/test', title: 'init'};
routes['#login'] = {view: 'view-login', query: false, title: 'login'};
routes['#logout'] = {view: 'view-logout', query: '/logout'};
routes['#profile'] = {view: 'view-profile', query: '/profile', title: 'profile'};
routes['#profile/connections'] = {view: 'view-profile-connections', query: '/profile/connections', title: 'profile'};
routes['#profile/notifications'] = {view: 'view-profile-notifications', query: '/profile/notifications', title: 'profile'};
routes['#401'] = {view: 'view-401', query: false, title: 'error_401'};
routes['#404'] = {view: 'view-404', query: false, title: 'error_404'};
routes['#500'] = {view: 'view-500', query: false, title: 'error_500'};
routes['#status'] = {view: 'view-status', query: '/status', title: 'status'};

//Feed
routes['#feeds/recent'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?sortField=date_created&sortDirection=DESC', title: 'title.recent_feeds'};
routes['#feeds/subscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?subscribed=1', title: 'title.subscribed_feeds'};
routes['#feeds/unsubscribed'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?unsubscribed=1', title: 'title.unsubscribed_feeds'};
routes['#feeds/witherrors'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?witherrors=1', title: 'title.feeds_witherrors'};

routes['#feeds/search'] = {view: 'view-search-feeds', query: false, title: 'title.search_feeds'};
routes['#feeds/search/result'] = {view: 'view-search-feeds', view_unit: 'view-feeds-unit', query: '/feeds/search', title: 'title.search_feeds'};

routes['#feed/action/subscribe/{id}'] = {view: false, query: '/feed/action/subscribe/{id}'};

routes['#feeds/category/{id}'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?category={id}'};
routes['#feeds/author/{id}'] = {view: 'view-feeds', view_unit: 'view-feeds-unit', query: '/feeds?author={id}'};

routes['#feed/{id}'] = {view: 'view-feed-read', query: '/feed/{id}'};

//Item
routes['#items/recent'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?days=7', title: 'title.recent_items'};
routes['#items/unread'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?unread=1', title: 'title.unread_items'};
routes['#items/starred'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?starred=1', title: 'title.starred_items'};

routes['#items/search'] = {view: 'view-search-items', query: false, title: 'title.search_items'};
routes['#items/search/result'] = {view: 'view-search-items', view_unit: 'view-items-unit', query: '/items/search', title: 'title.search_items'};

routes['#item/action/read/{id}'] = {view: false, query: '/item/action/read/{id}'};
routes['#item/action/star/{id}'] = {view: false, query: '/item/action/star/{id}'};

routes['#items/feed/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?feed={id}'};
routes['#items/author/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?author={id}'};
routes['#items/category/{id}'] = {view: 'view-items', view_unit: 'view-items-unit', query: '/items?category={id}'};

routes['#items/markallasread/all'] = {view: false, query: '/items/markallasread'};
routes['#items/markallasread/unread'] = {view: false, query: '/items/markallasread?unread=1'};
routes['#items/markallasread/starred'] = {view: false, query: '/item/markallasreads?starred=1'};
routes['#items/markallasread/feed/{id}'] = {view: false, query: '/items/markallasread?feed={id}'};
routes['#items/markallasread/author/{id}'] = {view: false, query: '/items/markallasread?author={id}'};
routes['#items/markallasread/category/{id}'] = {view: false, query: '/items/markallasread?category={id}'};

//Category
routes['#categories/recent'] = {view: 'view-categories', view_unit: 'view-categories-unit', query: '/categories?sortField=date_created&sortDirection=DESC&days=7', title: 'title.recent_categories'};
routes['#categories/excluded'] = {view: 'view-categories', view_unit: 'view-categories-unit', query: '/categories?excluded=1', title: 'title.excluded_categories'};
routes['#categories/usedbyfeeds'] = {view: 'view-categories', view_unit: 'view-categories-unit', query: '/categories?usedbyfeeds=1', title: 'title.categories_usedbyfeeds'};

routes['#categories/search'] = {view: 'view-search-categories', query: false, title: 'title.search_categories'};
routes['#categories/search/result'] = {view: 'view-search-categories', view_unit: 'view-categories-unit', query: '/categories/search', title: 'title.search_categories'};

routes['#category/action/exclude/{id}'] = {view: false, query: '/category/action/exclude/{id}'};

routes['#category/{id}'] = {view: 'view-category-read', query: '/category/{id}'};

//Author
routes['#authors/recent'] = {view: 'view-authors', view_unit: 'view-authors-unit', query: '/authors?sortField=date_created&sortDirection=DESC&days=7', title: 'title.recent_authors'};

routes['#authors/search'] = {view: 'view-search-authors', query: false, title: 'title.search_authors'};
routes['#authors/search/result'] = {view: 'view-search-authors', view_unit: 'view-authors-unit', query: '/authors/search', title: 'title.search_authors'};

routes['#authors/feed/{id}'] = {view: 'view-authors', view_unit: 'view-authors-unit', query: '/authors?feed={id}'};

routes['#author/{id}'] = {view: 'view-author-read', query: '/author/{id}'};
