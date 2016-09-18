var timezone = new Date();
timezone = -timezone.getTimezoneOffset() / 60;
var lastHistory = false;

var language = navigator.languages ? navigator.languages[0] : (navigator.language || navigator.userLanguage);
if(language) {
    language = language.substr(0, 2);
}

if(window.location.port) {
    var apiUrl = '//' + window.location.hostname + ':' + window.location.port + window.location.pathname;
} else {
    var apiUrl = '//' + window.location.hostname + window.location.pathname;
}
apiUrl = apiUrl.replace('index.html', '');
apiUrl = apiUrl.replace('client/', 'app_dev.php/api');

var connectionToken = store.get('Connection_login_token');
var snackbarContainer = document.querySelector('.mdl-snackbar');

var languages = ['en', 'fr'];
if($.inArray(language, languages)) {
    languageFinal = language;
} else {
    languageFinal = 'en';
}

$.get('vendor/jquery-timeago/locales/jquery.timeago.' + languageFinal + '.js', function() {
});

$.getJSON('app/translations/' + languageFinal + '.json', function(data) {
    $.i18n.load(data);
    Handlebars.registerHelper('trans', function(key) {
        var result = $.i18n._(key);
        return new Handlebars.SafeString(result);
    });
});

var files = [
    'app/views/misc.html',
    'app/views/item.html',
    'app/views/feed.html',
    'app/views/category.html',
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

function loadRoute(key, page, q) {
    if(typeof page === 'undefined') {
        page = false;
    }

    if(typeof q === 'undefined') {
        q = false;
    }

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
            if(page) {
                if(url.indexOf('?') != -1) {
                    url = url + '&page=' + page;
                } else {
                    url = url + '?page=' + page;
                }
            }
            if(q) {
                if(url.indexOf('?') != -1) {
                    url = url + '&q=' + q;
                } else {
                    url = url + '?q=' + q;
                }
            }
            if(replaceId) {
                url = url.replace('{id}', replaceId);
                key = key.replace('{id}', replaceId);
            }
        }

        if(route.view) {
            if(!page || page == 1) {
                scrollTo('#top');
                $('main > .mdl-grid').html('<div class="mdl-spinner mdl-js-spinner is-active"></div>');
                componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');
            }

            if(key != '#401' && key != '#404' && key != '#500') {
                if(key != window.location.hash) {
                    history.pushState({key: key}, null, key);
                    lastHistory = window.location.hash;
                }
            }

            if(route.title) {
                window.document.title = $.i18n._(route.title);//TODO: get first h1 in card
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
                        data_return.current_key = key;

                        if(route.title) {
                            data_return.current_title = route.title;
                        }

                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(i in data_return.entries) {
                                if(data_return.entries.hasOwnProperty(i)) {
                                    store.set(data_return.entries_entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                                }
                            }
                        }

                        if(route.view) {
                            if(typeof data_return.entry == 'object' && typeof data_return.entry_entity == 'string') {
                                window.document.title = data_return.entry.title + ' (' + $.i18n._(data_return.entry_entity) + ')';
                            }

                            if(!page || page == 1) {
                                var source = $('#' + route.view).text();
                                var template = Handlebars.compile(source);
                                $('main > .mdl-grid').html(template(data_return));
                            }

                            if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.view_unit == 'string') {
                                var source_unit = $('#' + route.view_unit).text();
                                var template_unit = Handlebars.compile(source_unit);

                                for(i in data_return.entries) {
                                    if(data_return.entries.hasOwnProperty(i)) {
                                        $('main > .mdl-grid').append(template_unit({entry: data_return.entries[i]}));
                                    }
                                }

                                if(data_return.entries_page_next) {
                                    var source_more = $('#view-more').text();
                                    var template_more = Handlebars.compile(source_more);
                                    $('main > .mdl-grid').append(template_more(data_return));
                                }
                            }

                            $('.timeago').timeago();
                            componentHandler.upgradeDom('MaterialMenu', 'mdl-menu');
                        } else {
                            if(typeof data_return.entry == 'object' && typeof data_return.action == 'string') {
                                setSnackbar(data_return.action + ' ' + data_return.entry.title);
                            }
                            if(data_return.entry_entity == 'Item' && data_return.action == 'read') {
                                store.remove(data_return.entry_entity + '_' + data_return.entry.id);
                            }
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
    snackbarContainer.MaterialSnackbar.showSnackbar({message: message, timeout: 1000});
}

function setPositions() {
    _window_height = $(window).height();
    _offset = $('.mdl-layout__content').offset();
    _height = _window_height - _offset.top;
    $('.mdl-layout__content').css({ 'height': _height});
    $('.mdl-grid').css({ 'padding-bottom': _height});
}

function scrollTo(anchor) {
    //$('.mdl-layout__content').scrollTo(anchor);
    $(anchor).ScrollTo();
}

$(document).ready(function() {
    var source = $('#view-aside').text();
    var template = Handlebars.compile(source);
    $('.mdl-layout__drawer').html(template());

    setPositions();

    $(window).bind('resize', function() {
        setPositions();
    });

    window.addEventListener('popstate', function() {
        if(lastHistory != window.location.hash) {
            loadRoute(window.location.hash);
        }
    });

    if(window.location.hash) {
        loadRoute(window.location.hash);
    } else {
        loadRoute('#items/unread');
    }

    $(document).on('click', '.load-route', function(event) {
        event.preventDefault();
        loadRoute($(this).attr('href'), $(this).data('page'));
    });

    $(document).on('click', '.action-refresh', function(event) {
        event.preventDefault();
        if(window.location.hash) {
            loadRoute(window.location.hash);
        }
    });

    $(document).on('click', '.more', function(event) {
        event.preventDefault();
        $(this).parent().parent().remove();
    });

    $('main > .mdl-grid').on('submit', 'form', function(event) {
        event.preventDefault();

        var form = $(this);

        if(form.attr('id') == 'form-search') {
            loadRoute('#search/result', 1, form.find('input[name="q"]').val());

        } else {
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
                    401: function() {
                        loadRoute('#401');
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
        }
    });
});
