var apiUrl = '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api';
var connectionToken = store.get('Connection_login_token');
var snackbarContainer = document.querySelector('.mdl-snackbar');

var routes = [];
routes['#login'] = {template: 'template-login', display: 'now'};
routes['#404'] = {template: 'template-404', display: 'now'};
routes['#feeds'] = {template: 'template-feeds', title: 'Feeds', display: 'yopla', store: true, path: '/feeds'};
routes['#folders'] = {template: 'template-folders', title: 'Folders', display: 'yopla', store: true, path: '/folders'};
routes['#items/unread'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?unread=1'};
routes['#items/shared'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?shared=1'};
routes['#items/starred'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?starred=1'};
routes['#items/feed/{id}'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?feed={id}'};
routes['#items/author/{id}'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?author={id}'};
routes['#items/category/{id}'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?category={id}'};

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
        window.document.title = route.title;//TODO: get first h1 in card
        //history.pushState({}, key, key);

        if(route.display == 'now') {
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
                    403: function() {
                        loadRoute('#login');
                    },
                    200: function(data_return) {
                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(var i in data_return.entries) {
                                store.set(data_return.entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                            }
                        }

                        var source = $('#' + route.template).text();
                        var template = Handlebars.compile(source);
                        $('main > .mdl-grid').html(template(data_return));
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

function loadTemplate(obj) {
    var source = $(obj.attr('href')).text();
    var template = Handlebars.compile(source);
    $('main > .mdl-grid').html(template(store.get(obj.data('entry'))));
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
            403: function() {
                loadRoute('#login');
            },
            200: function() {
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

    $('main > .mdl-grid').on('click', '.load-template', function(event) {
        event.preventDefault();
        loadTemplate($(this));
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
                }
            },
            type: form.attr('method'),
            url: apiUrl + form.data('path')
        });
    });
});
