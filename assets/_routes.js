const routes = [];
routes['#login'] = {view: 'view-login', query: false, title: 'login'};
routes['#logout'] = {view: 'view-logout', query: '/logout'};
routes['#profile'] = {view: 'view-profile', query: '/profile', title: 'profile'};
routes['#profile/connections'] = {view: 'view-profile-connections', query: '/profile/connections', title: 'profile'};

//Feed
routes['#feeds/recent'] = {view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?sort=-fed.dateCreated', title: 'title.recent_feeds'};
routes['#feeds/subscribed'] = {view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?filter[subscribed]=true', title: 'title.subscribed_feeds'};
routes['#feeds/unsubscribed'] = {view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?filter[unsubscribed]=true', title: 'title.unsubscribed_feeds'};
routes['#feeds/witherrors'] = {view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?filter[witherrors]=true', title: 'title.feeds_witherrors'};

routes['#feeds/search'] = {view: 'view-search-feeds', query: false, title: 'title.search_feeds'};
routes['#feeds/search/result'] = {view: 'view-search-feeds', viewUnit: 'view-feeds-unit', query: '/feeds/search', title: 'title.search_feeds'};

routes['#feed/action/subscribe/{id}'] = {view: false, query: '/feed/action/subscribe/{id}'};

routes['#feeds/category/{id}'] = {hightlightIncluded: 'category', view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?filter[category]={id}'};
routes['#feeds/author/{id}'] = {hightlightIncluded: 'author', view: 'view-feeds', viewUnit: 'view-feeds-unit', query: '/feeds?filter[author]={id}'};

routes['#feed/{id}'] = {view: 'view-feeds', viewUnit: 'view-feed-read', query: '/feed/{id}'};

//Item
routes['#items/recent'] = {view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[days]=7', title: 'title.recent_items'};
routes['#items/unread'] = {view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[unread]=true', title: 'title.unread_items'};
routes['#items/starred'] = {view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[starred]=true', title: 'title.starred_items'};

routes['#items/search'] = {view: 'view-search-items', query: false, title: 'title.search_items'};
routes['#items/search/result'] = {view: 'view-search-items', viewUnit: 'view-items-unit', query: '/items/search', title: 'title.search_items'};

routes['#item/action/read/{id}'] = {view: false, query: '/item/action/read/{id}'};
routes['#item/action/star/{id}'] = {view: false, query: '/item/action/star/{id}'};

routes['#items/feed/{id}'] = {hightlightIncluded: 'feed', view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[feed]={id}'};
routes['#items/author/{id}'] = {hightlightIncluded: 'author', view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[author]={id}'};
routes['#items/category/{id}'] = {hightlightIncluded: 'category', view: 'view-items', viewUnit: 'view-items-unit', query: '/items?filter[category]={id}'};

routes['#item/{id}'] = {view: 'view-items', viewUnit: 'view-items-unit', query: '/item/{id}'};

routes['#items/markallasread/all'] = {view: false, query: '/items/markallasread'};
routes['#items/markallasread/unread'] = {view: false, query: '/items/markallasread?filter[unread]=true'};
routes['#items/markallasread/starred'] = {view: false, query: '/items/markallasread?filter[starred]=true'};
routes['#items/markallasread/feed/{id}'] = {view: false, query: '/items/markallasread?filter[feed]={id}'};
routes['#items/markallasread/author/{id}'] = {view: false, query: '/items/markallasread?filter[author]={id}'};
routes['#items/markallasread/category/{id}'] = {view: false, query: '/items/markallasread?filter[category]={id}'};

//Category
routes['#categories/trendy'] = {view: 'view-categories-trendy', query: '/categories/trendy', title: 'title.trendy_categories'};
routes['#categories/excluded'] = {view: 'view-categories', viewUnit: 'view-categories-unit', query: '/categories?filter[excluded]=true', title: 'title.excluded_categories'};
routes['#categories/usedbyfeeds'] = {view: 'view-categories', viewUnit: 'view-categories-unit', query: '/categories?filter[usedbyfeeds]=true', title: 'title.categories_usedbyfeeds'};

routes['#categories/search'] = {view: 'view-search-categories', query: false, title: 'title.search_categories'};
routes['#categories/search/result'] = {view: 'view-search-categories', viewUnit: 'view-categories-unit', query: '/categories/search', title: 'title.search_categories'};

routes['#category/action/exclude/{id}'] = {view: false, query: '/category/action/exclude/{id}'};

routes['#category/{id}'] = {view: 'view-categories', viewUnit: 'view-categories-unit', query: '/category/{id}'};

//Author
routes['#authors/trendy'] = {view: 'view-authors-trendy', query: '/authors/trendy', title: 'title.trendy_authors'};
routes['#authors/excluded'] = {view: 'view-authors', viewUnit: 'view-authors-unit', query: '/authors?filter[excluded]=true', title: 'title.excluded_authors'};

routes['#authors/search'] = {view: 'view-search-authors', query: false, title: 'title.search_authors'};
routes['#authors/search/result'] = {view: 'view-search-authors', viewUnit: 'view-authors-unit', query: '/authors/search', title: 'title.search_authors'};

routes['#author/action/exclude/{id}'] = {view: false, query: '/author/action/exclude/{id}'};

routes['#authors/feed/{id}'] = {hightlightIncluded: 'feed', view: 'view-authors', viewUnit: 'view-authors-unit', query: '/authors?filter[feed]={id}'};

routes['#author/{id}'] = {view: 'view-authors', viewUnit: 'view-authors-unit', query: '/author/{id}'};

export { routes };
