var apiUrl = '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api';
var connectionToken = store.get('Connection_login_token');
var snackbarContainer = document.querySelector('.mdl-snackbar');

var files = [
    'app/views/misc.html',
    'app/views/folder.html',
    'app/views/feed.html',
    'app/views/item.html',
];
function loadFile(url) {
    $.ajax({
        async: false,
        cache: true,
        dataType: 'json',
        statusCode: {
            200: function(data_return) {
                $('body').append(data_return.responseText);
            }
        },
        type: 'GET',
        url: url
    });
}
for(var i in files) {
    loadFile(files[i]);
}

var source = $('#view-feed').text();
Handlebars.registerPartial('cardFeed', source);

var source = $('#view-folder').text();
Handlebars.registerPartial('cardFolder', source);

var source = $('#view-item').text();
Handlebars.registerPartial('cardItem', source);

var source = $('#view-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

var source = $('#view-folder-title').text();
Handlebars.registerPartial('titleFolders', source);

/*window.addEventListener('popstate', function(event) {
    console.log(event);
});*/

function loadRoute(key) {
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

        if(route.query) {
            var url = apiUrl + route.query;
            if(replaceId) {
                url = url.replace('{id}', replaceId);
                key = key.replace('{id}', replaceId);
            }
        }

        if(route.view) {
            $('main > .mdl-grid').html('<div class="mdl-spinner mdl-js-spinner is-active"></div>');
            componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');

            if(key != '#404' && key != '#500') {
                window.location.hash = key;
                history.pushState({}, key, key);
            }

            if(route.title) {
                window.document.title = route.title;//TODO: get first h1 in card
            }
        }

        if(!route.query && route.view) {
            var source = $('#' + route.view).text();
            var template = Handlebars.compile(source);
            $('main > .mdl-grid').html(template());

        } else if(route.query) {
            $.ajax({
                headers: {
                    'X-CONNECTION-TOKEN': connectionToken
                },
                async: true,
                cache: false,
                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(var i in data_return.entries) {
                                store.set(data_return.entries_entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                            }
                        }

                        if(route.view) {
                            if(typeof data_return.entry == 'object' && typeof data_return.entry_entity == 'string') {
                                window.document.title = data_return.entry.title + ' (' + data_return.entry_entity + ')';
                            }

                            var source = $('#' + route.view).text();
                            var template = Handlebars.compile(source);
                            $('main > .mdl-grid').html(template(data_return));
                        } else {
                            setSnackbar('TODO');
                            /*if(data_return.entry_entity == 'Item' && data_return.action == 'read') {
                                store.remove(data_return.entry_entity + '_' + data_return.entry.id);//TODO
                            }*/
                        }
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
        } else {
            loadRoute('#404');
        }
    } else {
        loadRoute('#404');
    }
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

    $(document).on('click', '.load-route', function(event) {
        event.preventDefault();
        loadRoute($(this).attr('href'));
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
                        store.remove(data_return.entry_entity + '_' + data_return.entry.id);
                    }
                    if(form.attr('method') == 'PUT') {
                        store.set(data_return.entry_entity + '_' + data_return.entry.id, data_return.entry);
                    }
                    if(form.attr('method') == 'POST') {
                        if(form.data('path') == '/login') {
                            connectionToken = data_return.entry.token;
                            store.set('Connection_login_token', connectionToken);
                            setSnackbar(form.attr('method') + ' ' + data_return.entry.type);
                        } else {
                            store.set(data_return.entry_entity + '_' + data_return.entry.id, data_return.entry);
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
