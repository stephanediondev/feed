var files = [
    'app/views/misc.html',
    'app/views/member.html',
    'app/views/item.html',
    'app/views/feed.html',
    'app/views/category.html',
    'app/views/author.html',
];

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
if(window.location.hostname == 'localhost') {
    apiUrl = apiUrl.replace('client/', 'app_dev.php/api');
} else {
    apiUrl = apiUrl.replace('client/', 'api');
}

var connectionData = explainConnection(store.get('connection'));

var snackbarContainer = document.querySelector('.mdl-snackbar');

var languages = ['en', 'fr'];
if($.inArray(language, languages)) {
    languageFinal = language;
} else {
    languageFinal = 'en';
}

if(languageFinal != 'en') {
    $.get('vendor/moment/locale/' + languageFinal + '.js', function() {
    });
}

$.ajax({
    async: false,
    dataType: 'json',
    url: 'app/translations/' + languageFinal + '.json',
    success: function(data) {
        $.i18n.load(data);
        Handlebars.registerHelper('trans', function(key) {
            var result = $.i18n._(key);
            return new Handlebars.SafeString(result);
        });

        Handlebars.registerHelper('score', function(key) {
            key = key * 100;
            return Math.round(key) / 100;
        });
    }
});

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

function explainConnection(connection) {
    if(typeof connection === 'undefined') {
        connection = {id: false, token: false, member: {id: false, administrator: false, member: false, demo: false}};

        $('body').removeClass('connected');
        $('body').addClass('anonymous');

        $('body').removeClass('administrator');
        $('body').addClass('not_administrator');

    } else {
        $('body').removeClass('anonymous');
        $('body').addClass('connected');

        $('body').removeClass('not_administrator');
        $('body').addClass('administrator');

        if('serviceWorker' in navigator && window.location.protocol == 'https:') {
            navigator.serviceWorker.register('serviceworker.js').then(function(reg) {

                reg.pushManager.permissionState({userVisibleOnly: true}).then(function(status) {
                    var pushData = store.get('push');
                    if(status == 'denied' && pushData) {
                        $.ajax({
                            headers: {
                                'X-CONNECTION-TOKEN': connection.token
                            },
                            async: true,
                            cache: false,
                            dataType: 'json',
                            statusCode: {
                                200: function() {
                                    store.remove('push');
                                }
                            },
                            type: 'DELETE',
                            url: apiUrl + '/push/' + pushData.id
                        });
                    }

                    if(status == 'prompt' || status == 'granted') {
                        reg.pushManager.subscribe({userVisibleOnly: true}).then(function(pushSubscription) {
                            var toJSON = pushSubscription.toJSON();
                            $.ajax({
                                headers: {
                                    'X-CONNECTION-TOKEN': connection.token
                                },
                                async: true,
                                cache: false,
                                data: {
                                    endpoint: pushSubscription.endpoint,
                                    public_key: toJSON.keys.p256dh,
                                    authentication_secret: toJSON.keys.auth
                                },
                                dataType: 'json',
                                statusCode: {
                                    200: function(data_return) {
                                        store.set('push', data_return.entry);
                                    }
                                },
                                type: 'POST',
                                url: apiUrl + '/push'
                            });
                        });
                    }
                });
            }).catch(function() {
            });
        }
    }
    return connection;
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

    if(typeof parameters.snackbar === 'undefined') {
        parameters.snackbar = true;
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
                window.document.title = $.i18n._(route.title);
            }
        }

        if(!route.query && route.view) {
            data_return = {};
            data_return.connectionData = connectionData;

            var source = $('#' + route.view).text();
            var template = Handlebars.compile(source);
            $('main > .mdl-grid').html(template(data_return));

        } else if(route.query) {
            $.ajax({
                headers: {
                    'X-CONNECTION-TOKEN': connectionData.token
                },
                async: true,
                cache: false,
                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        data_return.connectionData = connectionData;

                        data_return.current_key = key;
                        data_return.current_key_markallasread = key.replace('#items', '#items/markallasread');
                        data_return.current_q = parameters.q ? decodeURIComponent(parameters.q) : '';

                        if(route.title) {
                            data_return.current_title = route.title;
                        }

                        /*if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(i in data_return.entries) {
                                if(data_return.entries.hasOwnProperty(i)) {
                                    store.set(data_return.entries_entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                                }
                            }
                        }*/

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
                                        $('main > .mdl-grid').append(template_unit({connectionData: connectionData, entry: data_return.entries[i]}));
                                    }
                                }

                                if(route.title) {
                                    window.document.title = $.i18n._(route.title) + ' (' + data_return.entries_total + ')';
                                }
                                $('.count').text(data_return.entries_total);

                                if(data_return.entries_page_next) {
                                    var source_more = $('#view-more').text();
                                    var template_more = Handlebars.compile(source_more);
                                    $('main > .mdl-grid').append(template_more(data_return));
                                }
                            }

                            $('main > .mdl-grid').find('img').each(function() {
                                var img = $(this);
                                if(img.data('src')) {
                                    $(this).attr('src', $(this).attr('data-src'));
                                    $(this).removeAttr('data-src');
                                }
                            });

                            $('main > .mdl-grid').find('.timeago').each(function() {
                                var result = moment($(this).attr('title')).add(timezone, 'hours');
                                $(this).attr('title', result.format('LLLL'));
                                $(this).text(result.fromNow());
                            });

                            componentHandler.upgradeDom('MaterialMenu', 'mdl-menu');
                        } else {
                            if(parameters.link) {
                                parameters.link.text($.i18n._(data_return.action_reverse));
                                parameters.link.addClass(data_return.action);
                                parameters.link.removeClass(data_return.action_reverse);
                            }
                            if(typeof data_return.entry == 'object' && typeof data_return.action == 'string') {
                                if(parameters.snackbar) {
                                    setSnackbar($.i18n._(data_return.action) + ' ' + data_return.entry.title);
                                }
                            }
                            /*if(data_return.entry_entity == 'Item' && data_return.action == 'read') {
                                store.remove(data_return.entry_entity + '_' + data_return.entry.id);
                            }*/
                        }

                        if(route.query == '/logout') {
                            store.remove('connection');
                            $('body').removeClass('connected');
                            $('body').addClass('anonymous');
                            loadRoute('#login');
                            setSnackbar($.i18n._('logout'));
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
    var _window_height = $(window).height();
    var _offset = $('.mdl-layout__content').offset();
    var _height = _window_height - _offset.top;
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
    var itm_id = false;
    var next = false;
    if($('.mdl-grid .card-selected').length === 0) {
        itm_id = $('.mdl-grid').find('.mdl-card:first').attr('id');
        next = $('#' + itm_id).attr('id');
        $('#' + itm_id).addClass('card-selected');
    } else {
        itm_id = $('.mdl-grid .card-selected').attr('id');
        next = $('#' + itm_id).next().attr('id');
    }
    if(next) {
        scrollTo('#' + next);

        if($('#' + next).hasClass('more')) {
            actionMore($('#' + next).find('.more'));
        }

        if($('#' + next).hasClass('item') && $('body').hasClass('connected')) {
            actionRead($('#' + next).find('.action-read'));
        }
    }
}

function actionMore(liknActionMore) {
    if(liknActionMore.hasClass('progress')) {
    } else {
        liknActionMore.addClass('progress');
        liknActionMore.trigger('click');
        //loadRoute(liknActionMore.attr('href'));
    }
}

function actionRead(liknActionRead) {
    if(liknActionRead.hasClass('read')) {
    } else if(liknActionRead.hasClass('unread')) {
    } else if(liknActionRead.hasClass('progress')) {
    } else {
        liknActionRead.addClass('progress');
        loadRoute(liknActionRead.attr('href'), {link: liknActionRead, snackbar: false});
    }
}

function updateOnlineStatus() {
    if(navigator.onLine) {
        $('body').removeClass('offline');
        $('body').addClass('online');
    } else {
        $('body').removeClass('online');
        $('body').addClass('offline');
    }
}

$(document).ready(function() {
    updateOnlineStatus();

    window.addEventListener('online',  updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    var sourceNavigation = $('#view-navigation').text();
    var templateNavigation = Handlebars.compile(sourceNavigation);
    $('.mdl-navigation').html(templateNavigation());

    var sourceAside = $('#view-aside').text();
    var templateAside = Handlebars.compile(sourceAside);
    $('.mdl-layout__drawer').html(templateAside());

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

    $('.mdl-layout__drawer').on('click', '.mdl-list__item a', function() {
        if($('.mdl-layout__drawer').hasClass('is-visible')) {
            var d = document.querySelector('.mdl-layout');
            d.MaterialLayout.toggleDrawer();
        }
    });

    $(document).on('click', '.load-route', function(event) {
        event.preventDefault();

        loadRoute($(this).attr('href'), {page: $(this).data('page'), q: $(this).data('q'), link: $(this)});
    });

    $(document).on('click', '.dialog', function(event) {
        event.preventDefault();

        var id = $(this).attr('id');

        if($('body > dialog[for="' + id + '"]').length === 0) {
            var html = $('dialog[for="' + id + '"]')[0].outerHTML;
            $('dialog[for="' + id + '"]').remove();
            $('body').append(html);
        }

        var dialog = document.querySelector('dialog[for="' + id + '"]');

        if(!dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }
        dialog.showModal();
    });

    $(document).on('click', 'dialog .close', function(event) {
        if($(this).hasClass('load-route')) {
        } else if($(this).attr('target') == '_blank') {
        } else {
            event.preventDefault();
        }
        var id = $(this).parents('.mdl-dialog').attr('for');

        var dialog = document.querySelector('dialog[for="' + id + '"]');
        dialog.close();
    });

    $(document).on('click', 'dialog .close_submit', function() {
        var id = $(this).parents('.mdl-dialog').attr('for');

        var dialog = document.querySelector('dialog[for="' + id + '"]');
        dialog.close();
    });

    $('.mdl-grid').on('click', '.item .mdl-card__title h1 a, .item .mdl-card__supporting-text a', function(event) {
        var ref = $(this).parents('.item');

        $(this).attr('target', '_blank');

        if(ref.hasClass('collapse')) {
            event.preventDefault();
            if(ref.hasClass('collapse')) {
                ref.removeClass('collapse');
                ref.addClass('expand');
            } else {
                ref.removeClass('expand');
                ref.addClass('collapse');
            }
        }
        var action = ref.find('.action-read');
        if(action.hasClass('read')) {
        } else {
            action.trigger('click');
        }
    });

    $(document).on('click', '.action-toggle', function(event) {
        event.preventDefault();
        if($('body').hasClass('collapse')) {
            $('body').removeClass('collapse');
        } else {
            $('body').addClass('collapse');
        }
    });

    $(document).on('click', '.action-toggle-unit', function(event) {
        event.preventDefault();
        var ref = $( $(this).attr('href') );
        if(ref.hasClass('collapse')) {
            ref.removeClass('collapse');
            ref.addClass('expand');
        } else {
            ref.removeClass('expand');
            ref.addClass('collapse');
        }
    });

    $(document).on('click', '.action-refresh', function(event) {
        event.preventDefault();
        if(window.location.hash) {
            loadRoute(window.location.hash);
        }
    });

    $(document).on('click', '.action-up', function(event) {
        event.preventDefault();
        item_up();
    });

    $(document).on('click', '.action-down', function(event) {
        event.preventDefault();
        item_down();
    });

    $(document).on('click', '.action-top', function(event) {
        event.preventDefault();
        scrollTo('#top');
    });

    $(document).on('click', '.more', function(event) {
        event.preventDefault();
        $(this).parent().parent().remove();
    });

    $('body').on('submit', 'form', function(event) {
        event.preventDefault();

        var form = $(this);
        var id = form.attr('id');

        if(form.hasClass('share-form')) {
            var choice = form.find('input[type="radio"]:checked').val();
            if(choice) {
                window.open(choice, 'share');
            }
            

        } else if(typeof id != 'undefined' && id.indexOf('form-search-') != -1) {
            loadRoute(form.attr('action'), {page: 1, q: encodeURIComponent( form.find('input[name="q"]').val() )});

        } else if(form.data('query')) {

            if(window.FormData && form.attr('enctype') == 'multipart/form-data') {
                contentType = false;
                data = new FormData();
                var file = document.getElementById('file');
                if(file.files.length === 1 && window.FileReader) {
                    data.append('file', file.files[0]);
                }

            } else {
                contentType = 'application/x-www-form-urlencoded';
                data = form.serialize();
            }

            $.ajax({
                headers: {
                    'X-CONNECTION-TOKEN': connectionData.token
                },
                async: true,
                cache: false,
                data: data,

                processData: false,
                contentType: contentType,

                dataType: 'json',
                statusCode: {
                    200: function(data_return) {
                        if(typeof data_return.entry !== 'undefined') {
                            if(data_return.entry.title) {
                                setSnackbar($.i18n._(form.attr('method')) + ' ' + data_return.entry.title);
                            }
                        }
                        if(form.data('query') == '/login') {
                            store.set('connection', data_return.entry);
                            connectionData = explainConnection(data_return.entry);

                            setSnackbar($.i18n._('login'));
                        }
                        loadRoute(form.attr('action'));
                    },
                    401: function() {
                        setSnackbar($.i18n._('error_401'));
                    },
                    403: function() {
                        loadRoute('#login');
                    },
                    404: function() {
                        setSnackbar($.i18n._('error_404'));
                    },
                    500: function() {
                        setSnackbar($.i18n._('error_500'));
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
                        actionMore(ref.find('.more'));
                    }

                    if($(this).hasClass('item') && $('body').hasClass('connected')) {// && items_display == 'expand'
                        actionRead(ref.find('.action-read'));
                    }
                    return true;
                } else {
                    return false;
                }
            }
        });
    });
});
