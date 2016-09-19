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
apiUrl = apiUrl.replace('client/', 'api');

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
    'app/views/member.html',
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

function loadRoute(key, parameters) {
    if(typeof parameters === 'undefined') {
        parameters = {};
    }

    if(typeof parameters.page === 'undefined') {
        parameters.page = false;
    }

    if(typeof parameters.q === 'undefined') {
        parameters.q = false;
    }

    if(typeof parameters.link === 'undefined') {
        parameters.link = false;
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
            if(parameters.page) {
                if(url.indexOf('?') != -1) {
                    url = url + '&page=' + parameters.page;
                } else {
                    url = url + '?page=' + parameters.page;
                }
            }
            if(parameters.q) {
                if(url.indexOf('?') != -1) {
                    url = url + '&q=' + parameters.q;
                } else {
                    url = url + '?q=' + parameters.q;
                }
            }
            if(replaceId) {
                url = url.replace('{id}', replaceId);
                key = key.replace('{id}', replaceId);
            }
        }

        if(route.view) {
            if(!parameters.page || parameters.page == 1) {
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
                    'X-CONNECTION-TOKEN': connectionToken
                },
                async: true,
                cache: false,
                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        data_return.current_key = key;
                        data_return.current_q = parameters.q;

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
                                if(typeof data_return.entry.title == 'string') {
                                    window.document.title = data_return.entry.title + ' (' + $.i18n._(data_return.entry_entity) + ')';
                                }
                            }

                            if(!parameters.page || parameters.page == 1) {
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
                            if(parameters.link) {
                                parameters.link.text($.i18n._(data_return.action_reverse));
                                parameters.link.addClass(data_return.action);
                            }
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
    //snackbarContainer.MaterialSnackbar.showSnackbar({message: message, timeout: 1000});
}

function setPositions() {
    _window_height = $(window).height();
    _offset = $('.mdl-layout__content').offset();
    _height = _window_height - _offset.top;
    $('.mdl-layout__content').css({ 'height': _height});
    $('main > .mdl-grid').css({ 'padding-bottom': _height});
}

function scrollTo(anchor) {
    $('.mdl-layout__content').scrollTo(anchor);
}

function item_up() {
    var itm_id = $('.mdl-grid .card-selected').attr('id');
    var prev = $('#' + itm_id).prev().attr('id');
    if(prev) {
        scrollTo('#' + prev);
    }
}
function item_down() {
    if($('.mdl-grid .card-selected').length === 0) {
        itm_id = $('.mdl-grid').find('.item:first').attr('id');
        next = $('#' + itm_id).attr('id');
        $('#' + itm_id).addClass('card-selected');
    } else {
        itm_id = $('.mdl-grid .card-selected').attr('id');
        next = $('#' + itm_id).next().attr('id');
    }
    if(next) {
        scrollTo('#' + next);

        if($('#' + next).hasClass('more')) {
            liknActionMore = $('#' + next).find('.more');

            if(liknActionMore.hasClass('progress')) {
            } else {
                liknActionMore.addClass('progress');
                liknActionMore.trigger('click');
                //loadRoute(liknActionMore.attr('href'));
            }
        }

        if($('#' + next).hasClass('item')) {
            liknActionRead = $('#' + next).find('.action-read');
            if(liknActionRead.hasClass('read')) {
            } else if(liknActionRead.hasClass('unread')) {
            } else if(liknActionRead.hasClass('progress')) {
            } else {
                liknActionRead.addClass('progress');
                loadRoute(liknActionRead.attr('href'), {link: liknActionRead});
            }
        }
    }
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
        loadRoute($(this).attr('href'), {page: $(this).data('page'), q: $(this).data('q'), link: $(this)});
    });

    $(document).on('click', '.dialog', function(event) {
        event.preventDefault();

        id = $(this).attr('id');

        if($('body > dialog[for="' + id + '"]').length === 0) {
            html = $('dialog[for="' + id + '"]')[0].outerHTML;
            $('dialog[for="' + id + '"]').remove();
            $('body').append(html);
        }

        var dialog = document.querySelector('dialog[for="' + id + '"]');

        if(!dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }
        dialog.showModal();
    });

    $(document).on('click', 'dialog .close', function() {
        id = $(this).parent().parent().attr('for');

        var dialog = document.querySelector('dialog[for="' + id + '"]');
        dialog.close();
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

        if(form.attr('id') == 'form-search-feeds') {
            loadRoute('#search/feeds/result', {page: 1, q: form.find('input[name="q"]').val()});

        } else if(form.attr('id') == 'form-search-items') {
            loadRoute('#search/items/result', {page: 1, q:form.find('input[name="q"]').val()});

        } else {
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

    $('.mdl-layout__content').bind('scroll', function() {
        $('main > .mdl-grid').find('.mdl-card').each(function() {
            if($(this).attr('id')) {
                var id_loop = $(this).attr('id');
                var ref = $('#' + id_loop);

                $('main > .mdl-grid .card-selected').removeClass('card-selected');
                ref.addClass('card-selected');

                /*if($(this).hasClass('item')) {
                    var last = $('main > .mdl-grid').find('.item:last').attr('id');
                    if(last == itm_id) {
                        add_items( $('.mdl-navigation').find('li.active').find('a.mdl-navigation__link').attr('href') );
                    }
                }*/

                var offset = $(this).offset();
                if(offset.top + ref.height() - 60 < 0) {
                    if($(this).hasClass('more')) {
                        liknActionMore = ref.find('.more');

                        if(liknActionMore.hasClass('progress')) {
                        } else {
                            liknActionMore.addClass('progress');
                            liknActionMore.trigger('click');
                            //loadRoute(liknActionMore.attr('href'));
                        }
                    }

                    if($(this).hasClass('item')) {// && items_display == 'expand'
                        liknActionRead = ref.find('.action-read');

                        if(liknActionRead.hasClass('read')) {
                        } else if(liknActionRead.hasClass('unread')) {
                        } else if(liknActionRead.hasClass('progress')) {
                        } else {
                            liknActionRead.addClass('progress');
                            loadRoute(liknActionRead.attr('href'), {link: liknActionRead});
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            }
        });
    });
});
