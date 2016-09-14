var apiUrl = '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api';
var connectionToken = store.get('Connection_login_token');
var snackbarContainer = document.querySelector('.mdl-snackbar');

var routes = [];
routes['#login'] = {template: 'template-login', path: false};
routes['#logout'] = {template: 'template-logout', path: false};
routes['#404'] = {template: 'template-404', path: false};
routes['#500'] = {template: 'template-500', path: false};

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

routes['#items/unread'] = {template: 'template-items', path: '/items?unread=1', store: false};
routes['#items/shared'] = {template: 'template-items', path: '/items?shared=1', store: false};
routes['#items/starred'] = {template: 'template-items', path: '/items?starred=1', store: false};
routes['#items/feed/{id}'] = {template: 'template-items', path: '/items?feed={id}', store: false};
routes['#items/author/{id}'] = {template: 'template-items', path: '/items?author={id}', store: false};
routes['#items/category/{id}'] = {template: 'template-items', path: '/items?category={id}', store: false};

var source = $('#template-feed').text();
Handlebars.registerPartial('cardFeed', source);

var source = $('#template-folder').text();
Handlebars.registerPartial('cardFolder', source);

var source = $('#template-item').text();
Handlebars.registerPartial('cardItem', source);

var source = $('#template-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

var source = $('#template-folder-title').text();
Handlebars.registerPartial('titleFolders', source);

/*window.addEventListener('popstate', function(event) {
    console.log(event);
});*/

function loadRoute(key) {
    $('main > .mdl-grid').html('<div class="mdl-spinner mdl-js-spinner is-active"></div>');
    componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');

    var replaceId = false;
    var parts = key.split('/');
    for(var i in parts) {
        if($.isNumeric(parts[i])) {
            key = key.replace(parts[i], '{id}');
            replaceId = parts[i];
            break;
        }
    }

    if(key in routes || replaceId) {
        var route = routes[key];

        var url = apiUrl + route.path;
        if(replaceId) {
            url = url.replace('{id}', replaceId);
            key = key.replace('{id}', replaceId);
        }

        window.location.hash = key;
        if(route.title) {
            window.document.title = route.title;//TODO: get first h1 in card
        }
        //history.pushState({}, key, key);

        if(!route.path) {
            var source = $('#' + route.template).text();
            var template = Handlebars.compile(source);
            $('main > .mdl-grid').html(template());
        } else {
            $.ajax({
                headers: {
                    'X-CONNECTION-TOKEN': connectionToken
                },
                async: true,
                cache: false,
                data: {
                },
                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(var i in data_return.entries) {
                                store.set(data_return.entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                            }
                        }

                        var source = $('#' + route.template).text();
                        var template = Handlebars.compile(source);
                        $('main > .mdl-grid').html(template(data_return));
                    },
                    403: function() {
                        loadRoute('#login');
                    },
                    404: function() {
                        loadRoute('#404');
                    },
                    500: function() {
                        loadRoute('#500');
                    }
                },
                type: 'GET',
                url: url
            });
        }
    } else {
        loadRoute('#404');
    }
}

function loadAction(obj) {
    $.ajax({
        headers: {
            'X-CONNECTION-TOKEN': connectionToken
        },
        async: true,
        cache: false,
        data: {
        },
        dataType: 'json',
        statusCode: {
            200: function() {
            },
            403: function() {
                loadRoute('#login');
            },
            404: function() {
                loadRoute('#404');
            },
            500: function() {
                loadRoute('#500');
            }
        },
        type: 'GET',
        url: apiUrl + obj.attr('href')
    });
}

function setSnackbar(message) {
    snackbarContainer.MaterialSnackbar.showSnackbar({message: message});
}
$(document).ready(function() {
    if(window.location.hash) {
        loadRoute(window.location.hash);
    } else {
        loadRoute('#feeds');
    }

    $(document).on('click', '.load-route', function() {
        loadRoute($(this).attr('href'));
    });

    $('main > .mdl-grid').on('click', '.load-action', function(event) {
        event.preventDefault();
        loadAction($(this));
    });

    $('main > .mdl-grid').on('submit', 'form', function(event) {
        event.preventDefault();

        var form = $(this);

        $.ajax({
            headers: {
                'X-CONNECTION-TOKEN': connectionToken
            },
            async: true,
            cache: false,
            data: form.serialize(),
            dataType: 'json',
            statusCode: {
                200: function(data_return) {
                    if(data_return.entry.title) {
                        setSnackbar(form.attr('method') + ' ' + data_return.entry.title);
                    }

                    if(form.attr('method') == 'DELETE') {
                        store.remove(data_return.entity + '_' + data_return.id);
                    }
                    if(form.attr('method') == 'PUT') {
                        store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                    }
                    if(form.attr('method') == 'POST') {
                        if(form.data('path') == '/login') {
                            connectionToken = data_return.entry.token;
                            store.set('Connection_login_token', connectionToken);
                            setSnackbar(form.attr('method') + ' ' + data_return.entry.type);
                        } else {
                            store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                        }
                    }
                    loadRoute(form.attr('action'));
                },
                403: function() {
                    loadRoute('#login');
                },
                404: function() {
                    loadRoute('#404');
                },
                500: function() {
                    loadRoute('#500');
                }
            },
            type: form.attr('method'),
            url: apiUrl + form.data('path')
        });
    });
});
