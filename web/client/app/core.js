var readyPromises = [];

var applicationServerKey;

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
if(window.location.hostname === 'local.sdion.net') {
    apiUrl = apiUrl.replace('client/', 'app_dev.php/api');
} else {
    apiUrl = apiUrl.replace('client/', 'api');
}

if('serviceWorker' in navigator && window.location.protocol === 'https:') {
    navigator.serviceWorker.register('serviceworker.js').then(function() {
    }).catch(function() {
    });
}

var connectionData = explainConnection(JSON.parse(localStorage.getItem('connection')));

var snackbarContainer = document.querySelector('.mdl-snackbar');

var languages = ['en', 'fr'];
var languageFinal = 'en';
if($.inArray(language, languages)) {
    languageFinal = language;
}

if(languageFinal !== 'en') {
    readyPromises.push(getMomentLocale());
}

readyPromises.push(getTranslation());

for(var i in files) {
    if(files.hasOwnProperty(i)) {
        readyPromises.push(loadFile(files[i]));
    }
}

function getMomentLocale() {
    return fetch('vendor/moment/locale/' + languageFinal + '.js').then(function(response) {
    }).catch(function(err) {
    });
}

function getTranslation() {
    return fetch('app/translations/' + languageFinal + '.json').then(function(response) {
        return response.json().then(function(json) {
            if(response.ok) {
                $.i18n.load(json);

                Handlebars.registerHelper('trans', function(key) {
                    var result = $.i18n._(key);
                    return new Handlebars.SafeString(result);
                });

                Handlebars.registerHelper('encode', function(key) {
                    return encodeURIComponent(key);
                });

                Handlebars.registerHelper('score', function(key) {
                    key = key * 100;
                    return Math.round(key) / 100;
                });

                Handlebars.registerHelper('equal', function(a, b, options) {
                    if(a === b) {
                        return options.fn(this);
                    } else {
                        return options.inverse(this);
                    }
                });
            } else {
                Promise.reject(json);
            }
        });
    }).catch(function(err) {
    });
}

function getTemplate(key) {
    return Handlebars.compile( $('#' + key).text() );
}

function loadFile(url) {
    return fetch(url).then(function(response) {
        return response.text().then(function(text) {
            if(response.ok) {
                $('body').append(text);
            } else {
                Promise.reject(text);
            }
        });
    }).catch(function(err) {
    });
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

function explainConnection(connection) {
    if(typeof connection === 'undefined' || null === connection) {
        connection = {id: false, token: false, member: {id: false, administrator: false, member: false, demo: false}};

        $('body').removeClass('connected');
        $('body').addClass('anonymous');

        $('body').removeClass('administrator');
        $('body').addClass('not_administrator');

    } else {
        $('body').removeClass('anonymous');
        $('body').addClass('connected');

        fetch(apiUrl + '/connection/' + connection.id, {
            method: 'PUT',
            mode: 'cors',
            headers: new Headers({
                'X-CONNECTION-TOKEN': connection.token,
                'Content-Type': 'application/json'
            })
    	}).then(function(response) {
            response.json().then(function(dataReturn) {
                if(response.ok) {
                    if(dataReturn.entry.member.administrator) {
                        $('body').removeClass('not_administrator');
                        $('body').addClass('administrator');
                    }

                    localStorage.setItem('connection', JSON.stringify(dataReturn.entry));

                    if('serviceWorker' in navigator && window.location.protocol === 'https:') {
                        navigator.serviceWorker.ready.then(function(ServiceWorkerRegistration) {
                        }).catch(function() {
                        });
                    }
                }
            });
        }).catch(function(err) {
        });
    }
    return connection;
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
                if(url.indexOf('?') !== -1) {
                    url = url + '&page=' + parameters.page;
                } else {
                    url = url + '?page=' + parameters.page;
                }
            }
            if(parameters.q) {
                if(url.indexOf('?') !== -1) {
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
            if(!parameters.page || parameters.page === 1) {
                scrollTo('#top');
                document.querySelector('main > .mdl-grid').innerHTML = '<div class="mdl-spinner mdl-js-spinner is-active"></div>';
                componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');
            }

            if(key !== '#401' && key !== '#404' && key !== '#500') {
                if(key !== window.location.hash) {
                    history.pushState({key: key}, null, key);
                    lastHistory = window.location.hash;
                }
            }

            if(route.title) {
                window.document.title = $.i18n._(route.title);
            }
        }

        if(!route.query && route.view) {
            var dataReturn = {};
            dataReturn.connectionData = connectionData;

            var template = getTemplate(route.view);
            document.querySelector('main > .mdl-grid').innerHTML = template(dataReturn);

        } else if(route.query) {
            fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: new Headers({
                    'X-CONNECTION-TOKEN': connectionData.token,
                    'Content-Type': 'application/json'
                })
        	}).then(function(response) {
                if(response.ok && 200 === response.status) {
                    response.json().then(function(dataReturn) {
                        dataReturn.connectionData = connectionData;

                        dataReturn.current_key = key;
                        dataReturn.current_key_markallasread = key.replace('#items', '#items/markallasread');
                        dataReturn.current_q = parameters.q ? decodeURIComponent(parameters.q) : '';

                        if(route.title) {
                            dataReturn.current_title = route.title;
                        }

                        /*if(Object.prototype.toString.call( dataReturn.entries ) === '[object Array]' && typeof route.store === 'boolean' && route.store) {
                            for(i in dataReturn.entries) {
                                if(dataReturn.entries.hasOwnProperty(i)) {
                                    localStorage.setItem(dataReturn.entries_entity + '_' + dataReturn.entries[i].id, JSON.stringify(dataReturn.entries[i]));
                                }
                            }
                        }*/

                        if(typeof dataReturn.unread !== 'undefined') {
                            if(dataReturn.unread > 0) {
                                if(dataReturn.unread > 99) {
                                    badge = '99+';
                                } else {
                                    badge = dataReturn.unread;
                                }
                                document.querySelector('.count-unread').setAttribute('data-badge', badge);
                                $('.count-unread').addClass('mdl-badge');
                            } else {
                                document.querySelector('.count-unread').removeAttribute('data-badge');
                                $('.count-unread').removeClass('mdl-badge');
                            }
                        }

                        if(route.view) {
                            if(typeof dataReturn.entry === 'object' && typeof dataReturn.entry_entity === 'string') {
                                if(typeof dataReturn.entry.title === 'string') {
                                    window.document.title = dataReturn.entry.title + ' (' + $.i18n._(dataReturn.entry_entity) + ')';
                                }
                            }

                            if(!parameters.page || parameters.page === 1) {
                                var template = getTemplate(route.view);
                                document.querySelector('main > .mdl-grid').innerHTML = template(dataReturn);
                            }

                            if(Object.prototype.toString.call( dataReturn.entries ) === '[object Array]' && typeof route.viewUnit === 'string') {
                                var template_unit = getTemplate(route.viewUnit);

                                for(i in dataReturn.entries) {
                                    if(dataReturn.entries.hasOwnProperty(i)) {
                                        document.querySelector('main > .mdl-grid').innerHTML += template_unit({connectionData: connectionData, entry: dataReturn.entries[i]});
                                    }
                                }

                                if(route.title) {
                                    window.document.title = $.i18n._(route.title) + ' (' + dataReturn.entries_total + ')';
                                }
                                $('.count').text(dataReturn.entries_total);

                                if(dataReturn.entries_page_next) {
                                    var template_more = getTemplate('view-more');
                                    document.querySelector('main > .mdl-grid').innerHTML += template_more(dataReturn);
                                }
                            }

                            if(Object.prototype.toString.call( dataReturn.entries ) === '[object Array]') {
                                $('body').removeClass('no_entries');
                            } else {
                                $('body').addClass('no_entries');
                            }

                            $('main > .mdl-grid').find('img.proxy').each(function() {
                                var img = $(this);
                                if(img.data('src')) {
                                    $(this).attr('src', $(this).attr('data-src'));
                                    $(this).removeAttr('data-src');
                                    $(this).removeClass('proxy');
                                }
                            });

                            $('main > .mdl-grid').find('.timeago').each(function() {
                                var result = moment( $(this).data('date') ).add(timezone, 'hours');
                                $(this).attr('title', result.format('LLLL'));
                                $(this).text(result.fromNow());
                            });

                            componentHandler.upgradeDom('MaterialMenu', 'mdl-menu');
                        } else {
                            if(parameters.link) {
                                parameters.link.text($.i18n._(dataReturn.action_reverse));
                                parameters.link.addClass(dataReturn.action);
                                parameters.link.removeClass(dataReturn.action_reverse);
                            }
                            if(typeof dataReturn.entry === 'object' && typeof dataReturn.action === 'string') {
                                if(parameters.snackbar) {
                                    setSnackbar($.i18n._(dataReturn.action) + ' ' + dataReturn.entry.title);
                                }
                            }
                            /*if(dataReturn.entry_entity === 'Item' && dataReturn.action === 'read') {
                                localStorage.removeItem(dataReturn.entry_entity + '_' + dataReturn.entry.id);
                            }*/
                        }

                        if(route.query === '/test') {
                            loadRoute('#items/unread');
                        }

                        if(route.query === '/logout') {
                            localStorage.removeItem('connection');
                            $('body').removeClass('connected');
                            $('body').addClass('anonymous');
                            loadRoute('#login');
                            setSnackbar($.i18n._('logout'));
                        }
                    });
                }
                if(403 === response.status) {
                    localStorage.removeItem('connection');
                    $('body').removeClass('connected');
                    $('body').addClass('anonymous');
                    loadRoute('#login');
                    setSnackbar($.i18n._('logout'));
                }
                if(404 === response.status) {
                    loadRoute('#404');
                }
                if(500 === response.status) {
                    loadRoute('#500');
                }
            }).catch(function(err) {
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

function itemUp() {
    var item = document.querySelector('.mdl-grid .card-selected');
    if(null !== item) {
        var prev = document.querySelector('#' + item.getAttribute('id')).previousElementSibling;
        if(null !== prev) {
            scrollTo('#' + prev.getAttribute('id'));
        }
    }
}
function itemDown() {
    var itm_id = false;
    var next = false;
    if($('.mdl-grid .card-selected').length === 0) {
        itm_id = $('.mdl-grid').find('.mdl-card:first').attr('id');
        next = $('#' + itm_id).attr('id');
        $('#' + itm_id).addClass('card-selected');
    } else {
        itm_id = document.querySelector('.mdl-grid .card-selected').getAttribute('id');
        next = $('#' + itm_id).next().attr('id');
    }
    if(next) {
        scrollTo('#' + next);

        if($('#' + next).hasClass('more')) {
            actionMore($('#' + next).find('.more'));
        }

        if($('#' + next).hasClass('item') && document.querySelector('body').classList.contains('connected')) {
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
    Promise.all(readyPromises).then(function() {
        updateOnlineStatus();

        window.addEventListener('online',  updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);

        var templateNavigation = getTemplate('view-navigation');
        document.querySelector('.mdl-navigation').innerHTML = templateNavigation();

        var templateAside = getTemplate('view-aside');
        document.querySelector('.mdl-layout__drawer').innerHTML = templateAside();

        setPositions();

        $(window).bind('resize', function() {
            setPositions();
        });

        window.addEventListener('popstate', function() {
            if(lastHistory !== window.location.hash) {
                loadRoute(window.location.hash);
            }
        });

        if(window.location.hash) {
            loadRoute(window.location.hash);
        } else {
            loadRoute('#test');
        }

        $('.mdl-layout__drawer').on('click', '.mdl-list__item a', function() {
            if(document.querySelector('.mdl-layout__drawer').classList.contains('is-visible')) {
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

            if($(this).hasClass('action-share') && 'share' in navigator) {
                navigator.share({
                    title: decodeURIComponent($(this).data('title')),
                    url: decodeURIComponent($(this).data('url'))
                }).then(function() {
                });

            } else {
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
            }
        });

        $(document).on('click', 'dialog .close', function(event) {
            if($(this).hasClass('load-route')) {
            } else if($(this).attr('target') === '_blank') {
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

            if(document.querySelector('body').classList.contains('connected') && document.querySelector('body').classList.contains('online')) {
                var action = ref.find('.action-read');
                if(action.hasClass('read')) {
                } else {
                    action.trigger('click');
                }
            }
        });

        $(document).on('click', '.action-toggle', function(event) {
            event.preventDefault();
            if(document.querySelector('body').classList.contains('collapse')) {
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

        $(document).on('click', '.action-up', function(event) {
            event.preventDefault();
            itemUp();
        });

        $(document).on('click', '.action-down', function(event) {
            event.preventDefault();
            itemDown();
        });

        $(document).on('click', '.more', function(event) {
            event.preventDefault();
            document.querySelector('main > .mdl-grid .card-selected').classList.remove('card-selected');
            $(this).parent().parent().prev().addClass('card-selected');

            $(this).parent().parent().remove();
        });

        $('body').on('submit', 'form', function(event) {
            event.preventDefault();

            var form = $(this);
            var id = form.attr('id');

            if(form.hasClass('share-form')) {
                var choice = form.find('input[type="radio"]:checked').val();
                if(choice) {
                    if(choice.indexOf('mailto:') !== -1) {
                        window.location.href = choice;
                    } else {
                        window.open(choice, 'share');
                    }
                }


            } else if(typeof id !== 'undefined' && id.indexOf('form-search-') !== -1) {
                loadRoute(form.attr('action'), {page: 1, q: encodeURIComponent( form.find('input[name="q"]').val() )});

            } else if(form.data('query')) {
                headers = new Headers({
                    'X-CONNECTION-TOKEN': connectionData.token
                });

                if(window.FormData && form.attr('enctype') === 'multipart/form-data') {
                    body = new FormData();
                    var file = document.getElementById('file');
                    if(file.files.length === 1 && window.FileReader) {
                        body.append('file', file.files[0]);
                    }

                } else {
                    headers.append('Content-Type', 'application/x-www-form-urlencoded');
                    body = form.serialize();
                }

                fetch(apiUrl + form.data('query'), {
                    method: form.attr('method'),
                    mode: 'cors',
                    headers: headers,
                    body: body
            	}).then(function(response) {
                    if(response.ok && 200 === response.status) {
                        response.json().then(function(dataReturn) {
                            if(typeof dataReturn.entry !== 'undefined') {
                                if(dataReturn.entry.title) {
                                    setSnackbar($.i18n._(form.attr('method')) + ' ' + dataReturn.entry.title);
                                }
                            }
                            if(form.data('query') === '/login') {
                                localStorage.setItem('connection', JSON.stringify(dataReturn.entry));
                                connectionData = explainConnection(dataReturn.entry);

                                setSnackbar($.i18n._('login'));
                            }
                            loadRoute(form.attr('action'));
                        });
                    }
                    if(401 === response.status) {
                        setSnackbar($.i18n._('error_401'));
                    }
                    if(403 === response.status) {
                        loadRoute('#login');
                    }
                    if(404 === response.status) {
                        setSnackbar($.i18n._('error_404'));
                    }
                    if(500 === response.status) {
                        setSnackbar($.i18n._('error_500'));
                    }
                }).catch(function(err) {
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
                        if(last === itm_id) {
                            add_items( $('.mdl-navigation').find('li.active').find('a.mdl-navigation__link').attr('href') );
                        }
                    }*/

                    var offset = $(this).offset();
                    if(offset.top + ref.height() - 60 < 0) {
                        if($(this).hasClass('more')) {
                            actionMore(ref.find('.more'));
                        }

                        if($(this).hasClass('item') && document.querySelector('body').classList.contains('connected')) {// && itemsDisplay === 'expand'
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
});
