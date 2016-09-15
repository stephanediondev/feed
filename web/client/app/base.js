var timezone = new Date();
timezone = -timezone.getTimezoneOffset() / 60;

var language = navigator.languages ? navigator.languages[0] : (navigator.language || navigator.userLanguage);
if(language) {
    language = language.substr(0, 2);
}

var apiUrl = '//' + window.location.hostname + window.location.pathname;
apiUrl = apiUrl.replace('client/', 'api');
var connectionToken = store.get('Connection_login_token');
var snackbarContainer = document.querySelector('.mdl-snackbar');

var languages = ['en', 'fr'];
if($.inArray(language, languages)) {
    languageFinal = language;
} else {
    languageFinal = 'en';
}

$.getJSON('app/translations/' + languageFinal + '.json', function(data) {
    $.i18n.load(data);
    Handlebars.registerHelper('trans', function(key) {
        var result = $.i18n._(key);
        return new Handlebars.SafeString(result);
    });
});

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
    if(files.hasOwnProperty(i)) {
        loadFile(files[i]);
    }
}

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
        var url = false;

        if(route.query) {
            url = apiUrl + route.query;
            if(replaceId) {
                url = url.replace('{id}', replaceId);
                key = key.replace('{id}', replaceId);
            }
        }

        if(route.view) {
            $('main > .mdl-grid').html('<div class="mdl-spinner mdl-js-spinner is-active"></div>');
            componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');

            if(key != '#404' && key != '#500') {
                //window.location.hash = key;
                history.pushState({key: key}, null, key);
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
                    'X-CONNECTION-TOKEN': connectionToken,
                    'X-MEMBER-TIMEZONE': timezone,
                    'X-MEMBER-LANGUAGE': language
                },
                async: true,
                cache: false,
                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(var i in data_return.entries) {
                                if(data_return.entries.hasOwnProperty(i)) {
                                    store.set(data_return.entries_entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                                }
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
    window.addEventListener('onpopstate', function() {
        console.log(location.pathname);
    });

    if(window.location.hash) {
        loadRoute(window.location.hash);
    } else {
        loadRoute('#items/unread');
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
                'X-CONNECTION-TOKEN': connectionToken,
                'X-MEMBER-TIMEZONE': timezone,
                'X-MEMBER-LANGUAGE': language
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
                        if(form.data('query') == '/login') {
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
            url: apiUrl + form.data('query')
        });
    });
});
