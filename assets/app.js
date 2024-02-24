import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import './styles/app.css';

import { Modal, Toast } from 'bootstrap';

import $ from 'jquery';

import i18next from 'i18next';

import moment from 'moment-timezone';

import 'jquery.scrollto';

import Handlebars from 'handlebars'

import saveAs from 'file-saver';

import {routes} from './_routes.js'

var serviceWorkerEnabled = false;

if ('serviceWorker' in navigator && window.location.protocol == 'https:') {
    serviceWorkerEnabled = true;
}

const storedTheme = localStorage.getItem('theme')

const getPreferredTheme = () => {
    if (storedTheme) {
        return storedTheme
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

setTheme(getPreferredTheme())

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (storedTheme !== 'light' || storedTheme !== 'dark') {
        setTheme(getPreferredTheme());
    }
})

function setTheme(theme) {
    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-bs-theme', 'dark')
    } else {
        document.documentElement.setAttribute('data-bs-theme', theme)
    }
}

function handleBadResponse(response) {
    if (400 === response.status) {
        setToast({'title': i18next.t('error_400')});
    }

    if (401 === response.status) {
        removeCookie('token_signed');
        $('body').removeClass('connected');
        $('body').addClass('anonymous');
        loadRoute('#login');
        setToast({'title': i18next.t('error_401')});
    }

    if (403 === response.status) {
        setToast({'title': i18next.t('error_403')});
    }

    if (404 === response.status) {
        setToast({'title': i18next.t('error_404')});
    }

    if (405 === response.status) {
        setToast({'title': i18next.t('error_405')});
    }

    if (500 === response.status) {
        setToast({'title': i18next.t('error_500')});
    }
}

function setBadge(value) {
    let countUnread = $('.count-unread');
    if (99 < value) {
        countUnread.text('99+');
    } else {
        countUnread.text(value);
    }

    if (0 === value) {
        countUnread.addClass('d-none');
    } else {
        countUnread.removeClass('d-none');
    }

    if (navigator.setExperimentalAppBadge) {
        navigator.setExperimentalAppBadge(value).catch(function(error) {
        });
    } else if (navigator.setAppBadge) {
        navigator.setAppBadge(value).catch(function(error) {
        });
    }
}

function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let cookie = cname + '=' + cvalue + ';expires=' + d.toUTCString() + ';path=/';
    if (window.location.protocol === 'https:') {
        cookie += ';secure';
    }
    document.cookie = cookie;
}

function getCookie(cname) {
    var cvalue = document.cookie
    .split("; ")
    .find((row) => row.startsWith(cname + '='))
    ?.split('=')[1];

    if ('undefined' === typeof cvalue) {
        return null;
    }
    return cvalue;
}

function removeCookie(cname) {
    const d = new Date();
    d.setTime(d.getTime() - (365 * 24 * 60 * 60 * 1000));
    let cookie = cname + '=;expires=' + d.toUTCString() + ';path=/';
    if (window.location.protocol === 'https:') {
        cookie += ';secure';
    }
    document.cookie = cookie;
}

function ready() {
    Promise.all(readyPromises).then(function() {
        updateOnlineStatus();

        window.addEventListener('online',  function() {
            updateOnlineStatus();
        });

        window.addEventListener('offline', function () {
            updateOnlineStatus();
        });

        var templateNavigation = getTemplate('view-navigation');
        document.querySelector('.mdl-navigation').innerHTML = templateNavigation();

        var templateMenuLeft = getTemplate('view-menu-left');
        document.querySelector('#menu-left').innerHTML = templateMenuLeft();

        var templateMenuRight = getTemplate('view-menu-right');
        document.querySelector('#menu-right').innerHTML = templateMenuRight();

        var templateDialogs = getTemplate('view-dialogs');
        document.querySelector('body').insertAdjacentHTML('beforeend', templateDialogs());

        setPositions();

        document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                const theme = toggle.getAttribute('data-bs-theme-value');
                localStorage.setItem('theme', theme);
                setTheme(theme);
            });
        });

        window.addEventListener('resize', function() {
            setPositions();
        });

        window.addEventListener('popstate', function() {
            if (lastHistory !== window.location.hash) {
                loadRoute(window.location.hash);
            }
        });

        if (serviceWorkerEnabled) {
            navigator.serviceWorker.ready
            .then(function(ServiceWorkerRegistration) {
                if ('pushManager' in ServiceWorkerRegistration) {
                    ServiceWorkerRegistration.pushManager.getSubscription()
                    .then(function(PushSubscription) {
                        var disableNotifications = document.getElementById('disable_notifications');
                        if (disableNotifications) {
                            disableNotifications.addEventListener('click', function(event) {
                                event.preventDefault();
                                pushManagerUnsubscribe();
                            });

                            if (PushSubscription && 'object' === typeof PushSubscription) {
                                disableNotifications.parentNode.classList.remove('d-none');
                            }
                        }

                        var enableNotifications = document.getElementById('enable_notifications');
                        if (enableNotifications) {
                            enableNotifications.addEventListener('click', function(event) {
                                event.preventDefault();
                                pushManagerSubscribe();
                            });

                            if (PushSubscription && 'object' === typeof PushSubscription) {
                            } else {
                                enableNotifications.parentNode.classList.remove('d-none');
                            }
                        }
                    });
                }
            });
        }

        if (window.location.hash) {
            loadRoute(window.location.hash);
        } else {
            if (getCookie('token_signed')) {
                loadRoute('#items/unread');
            } else {
                loadRoute('#login');
            }
        }

        $(document).on('click', '.load-route', function(event) {
            event.preventDefault();

            loadRoute($(this).attr('href'), {page: $(this).data('page'), q: $(this).data('q'), link: $(this)});

            if ($(this).data('close-dialog')) {
                const myModal = Modal.getOrCreateInstance($(this).data('close-dialog'), {});
                myModal.hide();
            }
        });

        $(document).on('click', '.dialog', function(event) {
            event.preventDefault();

            if ($(this).hasClass('action-share') && 'share' in navigator) {
                navigator.share({
                    title: decodeURIComponent($(this).data('title')),
                    url: decodeURIComponent($(this).data('url'))
                });
            } else {
                const myModal = Modal.getOrCreateInstance($(this).data('bs-target'), {});
                myModal.show();
            }
        });

        $('.mdl-grid').on('click', '.item .mdl-card__title h1 a, .item .mdl-card__supporting-text a', function(event) {
            var ref = $(this).parents('.item');

            $(this).attr('target', '_blank');

            if (ref.hasClass('collapse')) {
                event.preventDefault();
                if (ref.hasClass('collapse')) {
                    ref.removeClass('collapse');
                    ref.addClass('expand');
                } else {
                    ref.removeClass('expand');
                    ref.addClass('collapse');
                }
            }

            if (document.querySelector('body').classList.contains('connected') && document.querySelector('body').classList.contains('online')) {
                var action = ref.find('.action-read');
                if (action.hasClass('read')) {
                } else {
                    action.trigger('click');
                }
            }
        });

        $(document).on('click', '.action-toggle', function(event) {
            event.preventDefault();
            if (document.querySelector('body').classList.contains('collapse')) {
                $('body').removeClass('collapse');
            } else {
                $('body').addClass('collapse');
            }
        });

        $(document).on('click', '.action-toggle-unit', function(event) {
            event.preventDefault();
            var ref = $( $(this).attr('href') );
            if (ref.hasClass('collapse')) {
                ref.removeClass('collapse');
                ref.addClass('expand');
            } else {
                ref.removeClass('expand');
                ref.addClass('collapse');
            }
        });

        $(document).on('click', '.action-reload', function(event) {
            event.preventDefault();
            reload();
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

            if (form.hasClass('share-form')) {
                var choice = form.find('input[type="radio"]:checked').val();
                if (choice) {
                    if (choice.indexOf('mailto:') !== -1) {
                        window.location.href = choice;
                    } else {
                        window.open(choice, 'share');
                    }
                }

            } else if (typeof id !== 'undefined' && id.indexOf('form-search-') !== -1) {
                loadRoute(form.attr('action'), {page: 1, q: encodeURIComponent( form.find('input[name="filter[query]"]').val() )});

            } else if (form.data('query')) {
                var headers = new Headers({
                    'Authorization': 'Bearer ' + getCookie('token_signed')
                });

                var body = null;

                if (window.FormData && form.attr('enctype') === 'multipart/form-data') {
                    body = new FormData();
                    var file = document.getElementById('file');
                    if (file.files.length === 1 && window.FileReader) {
                        body.append('file', file.files[0]);
                    }

                } else {
                    headers.append('Content-Type', 'application/x-www-form-urlencoded');
                    body = form.serialize();
                }

                var url = apiUrl + form.data('query');
                if (form.data('query') === '/login') {
                    url = loginUrl;
                }

                fetch(url, {
                    method: form.attr('method'),
                    credentials: 'omit',
                    mode: 'cors',
                    headers: headers,
                    body: body
                }).then(function(response) {
                    if (response.ok && 200 <= response.status && 300 > response.status) {
                        if (form.data('query') === '/feeds/export') {
                            response.text().then(function(jsonResponse) {
                                var blob = new Blob([jsonResponse], {type: 'application/xml;charset=utf-8'});
                                saveAs(blob, form.find('#choice').val() + '-' + getDate() + '.opml');
                            });

                        } else {
                            response.json().then(function(jsonResponse) {
                                /*if (typeof jsonResponse.entry !== 'undefined') {
                                    if (jsonResponse.entry.data.attributes.title) {
                                        setToast({'title': i18next.t(form.attr('method')), 'body': jsonResponse.entry.data.attributes.title});
                                    }
                                }*/
                                if (form.data('query') === '/login') {
                                    setCookie('token_signed', jsonResponse.entry.token_signed, 365);

                                    explainConnection();

                                    setToast({'title': i18next.t('login')});
                                }
                                loadRoute(form.attr('action'));
                            });
                        }

                        if (form.data('close-dialog')) {
                            const myModal = Modal.getOrCreateInstance(form.data('close-dialog'), {});
                            myModal.hide();
                        }
                    } else {
                        handleBadResponse(response);
                    }
                }).catch(function(err) {
                });
            }
        });

        $('.mdl-layout__content').bind('scroll', function() {
            $('main > .mdl-grid').find('.mdl-card').each(function() {
                if ($(this).attr('id')) {
                    var ref = $('#' + $(this).attr('id'));

                    $('main > .mdl-grid .card-selected').removeClass('card-selected');
                    ref.addClass('card-selected');

                    /*if ($(this).hasClass('item')) {
                        var last = $('main > .mdl-grid').find('.item:last').attr('id');
                        if (last === itm_id) {
                            add_items( $('.mdl-navigation').find('li.active').find('a.mdl-navigation__link').attr('href') );
                        }
                    }*/

                    var offset = $(this).offset();
                    if (offset.top + ref.height() - 60 < 0) {
                        if ($(this).hasClass('more')) {
                            actionMore(ref.find('.more'));
                        }

                        if ($(this).hasClass('item') && document.querySelector('body').classList.contains('connected')) {// && itemsDisplay === 'expand'
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
}

function getTranslation(languageFinal) {
    return fetch('app/translations/' + languageFinal + '.json').then(function(response) {
        if (response.ok) {
            return response.json().then(function(json) {
                i18next.init({
                    debug: false,
                    lng: 'en',
                    resources: {
                        en: {
                            translation: json
                        },
                      }
                });

                Handlebars.registerHelper('trans', function(key) {
                    var result = i18next.t(key);
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
                    if (a === b) {
                        return options.fn(this);
                    } else {
                        return options.inverse(this);
                    }
                });
            });
        } else {
            Promise.reject();
        }
    }).catch(function(err) {
    });
}

function getTemplate(key) {
    return Handlebars.compile( $('#' + key).text() );
}

function loadFile(url) {
    return fetch(url).then(function(response) {
        if (response.ok) {
            return response.text().then(function(text) {
                document.querySelector('body').insertAdjacentHTML('afterend', text);
            });
        } else {
            Promise.reject();
        }
    }).catch(function(err) {
    });
}

function explainConnection() {
    if (getCookie('token_signed')) {
        $('body').removeClass('anonymous');
        $('body').addClass('connected');

        $('body').removeClass('not_administrator');
        $('body').addClass('administrator');
    } else {
        $('body').removeClass('connected');
        $('body').addClass('anonymous');

        $('body').removeClass('administrator');
        $('body').addClass('not_administrator');
    }
}

function loadRoute(key, parameters) {
    if (typeof parameters === 'undefined') {
        parameters = {};
    }

    if (typeof parameters.page === 'undefined') {
        parameters.page = false;
    }

    if (typeof parameters.q === 'undefined') {
        parameters.q = false;
    }

    if (typeof parameters.link === 'undefined') {
        parameters.link = false;
    }

    if (typeof parameters.snackbar === 'undefined') {
        parameters.snackbar = true;
    }

    var replaceId = false;

    var parts = key.split('/');
    for(var i in parts) {
        if ($.isNumeric(parts[i])) {
            key = key.replace(parts[i], '{id}');
            replaceId = parts[i];
            break;
        }
    }

    if (key in routes || replaceId) {
        var route = routes[key];
        var url = false;

        if (route.query) {
            url = apiUrl + route.query;
            if (parameters.page) {
                if (url.indexOf('?') !== -1) {
                    url = url + '&page[number]=' + parameters.page;
                } else {
                    url = url + '?page[number]=' + parameters.page;
                }
            }
            if (parameters.q) {
                if (url.indexOf('?') !== -1) {
                    url = url + '&filter[query]=' + parameters.q;
                } else {
                    url = url + '?filter[query]=' + parameters.q;
                }
            }
            if (replaceId) {
                url = url.replace('{id}', replaceId);
                key = key.replace('{id}', replaceId);
            }
        }

        if (route.view) {
            if (!parameters.page || parameters.page === 1) {
                scrollTo('#top');
                document.querySelector('main > .mdl-grid').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            }

            if (key !== '#404' && key !== '#500') {
                if (key !== window.location.hash) {
                    history.pushState({key: key}, null, key);
                    lastHistory = window.location.hash;
                }
            }

            if (route.title) {
                window.document.title = i18next.t(route.title);
            }
        }

        if (!route.query && route.view) {
            var jsonResponse = {};

            var template = getTemplate(route.view);
            document.querySelector('main > .mdl-grid').innerHTML = template(jsonResponse);

        } else if (route.query) {
            fetch(url, {
                method: 'GET',
                credentials: 'omit',
                mode: 'cors',
                headers: new Headers({
                    'Authorization': 'Bearer ' + getCookie('token_signed'),
                    'Content-Type': 'application/json'
                })
        	}).then(function(response) {
                if (response.ok && 200 <= response.status && 300 > response.status) {
                    response.json().then(function(jsonResponse) {
                        jsonResponse.current_key = key;
                        jsonResponse.current_key_markallasread = key.replace('#items', '#items/markallasread');
                        jsonResponse.current_q = parameters.q ? decodeURIComponent(parameters.q) : '';

                        if (route.title) {
                            jsonResponse.current_title = route.title;
                        }

                        if (typeof jsonResponse.unread !== 'undefined') {
                            setBadge(parseInt(jsonResponse.unread));
                        }

                        if (route.view) {
                            let included = {};
                            if (Object.prototype.toString.call( jsonResponse.included ) === '[object Array]') {
                                for (i in jsonResponse.included) {
                                    if (jsonResponse.included.hasOwnProperty(i)) {
                                        var entry = jsonResponse.included[i];
                                        if (typeof included[entry.type] === 'undefined') {
                                            included[entry.type] = [];
                                        }
                                        included[entry.type][entry.id] = entry;
                                    }
                                }
                            }

                            if (typeof route.hightlightIncluded !== 'undefined' && replaceId && typeof included[route.hightlightIncluded][replaceId] !== 'undefined') {
                                jsonResponse.hightlightIncluded = included[route.hightlightIncluded][replaceId];
                                window.document.title = jsonResponse.hightlightIncluded.attributes.title + ' (' + i18next.t(route.hightlightIncluded) + ')';
                            }

                            var renderRouteView = false;

                            if (!parameters.page || parameters.page === 1) {
                                //if (typeof route.hightlightIncluded === 'undefined') {
                                    renderRouteView = true;
                                    var template = getTemplate(route.view);
                                    document.querySelector('main > .mdl-grid').innerHTML = template(jsonResponse);
                                /*} else {
                                    document.querySelector('main > .mdl-grid').innerHTML = '';
                                }*/
                            }

                            var jsonResponsedata = jsonResponse.data;

                            if (Object.prototype.toString.call( jsonResponse.data ) === '[object Object]') {
                                jsonResponsedata = [];
                                jsonResponsedata[0] = jsonResponse.data;
                                window.document.title = jsonResponse.data.attributes.title + ' (' + i18next.t(jsonResponse.data.type) + ')';
                            }

                            if (Object.prototype.toString.call( jsonResponsedata ) === '[object Array]') {
                                var templateUnit = false;
                                if (typeof route.viewUnit === 'string') {
                                    templateUnit = getTemplate(route.viewUnit);
                                } else if (false === renderRouteView) {
                                    templateUnit = getTemplate(route.view);
                                }

                                if (templateUnit) {
                                    for (i in jsonResponsedata) {
                                        if (jsonResponsedata.hasOwnProperty(i)) {
                                            var entry = jsonResponsedata[i];
                                            if (typeof entry.relationships !== 'undefined') {
                                                for (var relationship in entry.relationships) {
                                                    if ('actions' === relationship) {
                                                        if (Object.prototype.toString.call( entry.relationships[relationship]['data'] ) === '[object Array]') {
                                                            for (var j in entry.relationships[relationship]['data']) {
                                                                var include = entry.relationships[relationship]['data'][j];
                                                                entry[included[include.type][include.id]['attributes']['title']] = true;
                                                            }
                                                        }
                                                    } else {
                                                        if (Object.prototype.toString.call( entry.relationships[relationship]['data'] ) === '[object Object]') {
                                                            entry.relationships[relationship]['data']['attributes'] = included[relationship][entry.relationships[relationship]['data']['id']]['attributes'];
                                                        }
                                                        if (Object.prototype.toString.call( entry.relationships[relationship]['data'] ) === '[object Array]') {
                                                            for (var j in entry.relationships[relationship]['data']) {
                                                                var include = entry.relationships[relationship]['data'][j];
                                                                entry.relationships[relationship]['data'][j]['attributes'] = included[include.type][include.id]['attributes'];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            document.querySelector('main > .mdl-grid').innerHTML += templateUnit({'entry': entry});
                                        }
                                    }
                                }

                                if (route.title) {
                                    window.document.title = i18next.t(route.title) + ' [' + jsonResponse.meta.results + ']';
                                }
                                $('.count').text(jsonResponse.meta.results);

                                if (jsonResponse.meta.page_next) {
                                    var template_more = getTemplate('view-more');
                                    document.querySelector('main > .mdl-grid').innerHTML += template_more(jsonResponse);
                                }
                            }

                            if (Object.prototype.toString.call( jsonResponsedata ) === '[object Array]') {
                                $('body').removeClass('no_entries');
                            } else {
                                $('body').addClass('no_entries');
                            }

                            $('main > .mdl-grid').find('.timeago').each(function() {
                                var result = moment( $(this).data('date') ).add(timezone, 'hours');
                                $(this).attr('title', result.format('LLLL'));
                                $(this).text(result.fromNow());
                            });
                        } else {
                            if (parameters.link) {
                                parameters.link.text(i18next.t(jsonResponse.action_reverse));
                                parameters.link.addClass(jsonResponse.action);
                                parameters.link.removeClass(jsonResponse.action_reverse);
                            }
                            if (typeof jsonResponse.entry === 'object' && typeof jsonResponse.action === 'string') {
                                if (parameters.snackbar) {
                                    setToast({'title': i18next.t(jsonResponse.action), 'body': jsonResponse.entry.data.attributes.title});
                                }
                            }
                        }

                        if (route.query === '/logout') {
                            removeCookie('token_signed');
                            $('body').removeClass('connected');
                            $('body').addClass('anonymous');
                            loadRoute('#login');
                            setToast({'title': i18next.t('logout')});
                        }
                    });
                } else {
                    handleBadResponse(response);
                }
            }).catch(function(err) {
            });
        } else {
            setToast({'title': i18next.t('error_404')});
        }
    } else {
        setToast({'title': i18next.t('error_404')});
    }
}

function generateUniqueID(prefix) {
    function chr4() {
      return Math.random().toString(16).slice(-4);
    }

    return prefix + chr4() + chr4() +
      '-' + chr4() +
      '-' + chr4() +
      '-' + chr4() +
      '-' + chr4() + chr4() + chr4();
}

function setToast(content) {
    var id = generateUniqueID('id-');
    var dataReturn = {'id': id, 'title': content.title, 'body': content.body};
    var template = getTemplate('view-toast');
    document.querySelector('.toast-container').innerHTML = template(dataReturn);

    var toastEl = document.getElementById(id);
    if (toastEl) {
        var toast = new Toast(toastEl, {'autohide': true, 'delay': 2500});
        toast.show();
    }
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

function reload() {
    document.location.reload();
}

function itemUp() {
    var item = document.querySelector('.mdl-grid .card-selected');
    if (null !== item) {
        var prev = document.querySelector('#' + item.getAttribute('id')).previousElementSibling;
        if (null !== prev) {
            scrollTo('#' + prev.getAttribute('id'));
        }
    }
}
function itemDown() {
    var itm_id = false;
    var next = false;
    if ($('.mdl-grid .card-selected').length === 0) {
        itm_id = $('.mdl-grid').find('.mdl-card:first').attr('id');
        next = $('#' + itm_id).attr('id');
        $('#' + itm_id).addClass('card-selected');
    } else {
        itm_id = document.querySelector('.mdl-grid .card-selected').getAttribute('id');
        next = $('#' + itm_id).next().attr('id');
    }
    if (next) {
        scrollTo('#' + next);

        if ($('#' + next).hasClass('more')) {
            actionMore($('#' + next).find('.more'));
        }

        if ($('#' + next).hasClass('item') && document.querySelector('body').classList.contains('connected')) {
            actionRead($('#' + next).find('.action-read'));
        }
    }
}

function actionMore(liknActionMore) {
    if (liknActionMore.hasClass('inprogress')) {
    } else {
        liknActionMore.addClass('inprogress');
        liknActionMore.trigger('click');
        //loadRoute(liknActionMore.attr('href'));
    }
}

function actionRead(liknActionRead) {
    if (liknActionRead.hasClass('read')) {
    } else if (liknActionRead.hasClass('unread')) {
    } else if (liknActionRead.hasClass('inprogress')) {
    } else {
        liknActionRead.addClass('inprogress');
        loadRoute(liknActionRead.attr('href'), {link: liknActionRead, snackbar: false});
    }
}

function updateOnlineStatus() {
    if (navigator.onLine) {
        $('body').removeClass('offline');
        $('body').addClass('online');
    } else {
        $('body').removeClass('online');
        $('body').addClass('offline');
    }
}

function getDate() {
    var d = new Date();
    var utc = d.getFullYear() + '-' + addZero(d.getMonth() + 1) + '-' + addZero(d.getDate());
    return utc;

    function addZero(i) {
        if (i < 10) {
            i = '0' + i;
        }
        return i;
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function pushManagerSubscribe() {
    if (serviceWorkerEnabled) {
        navigator.serviceWorker.ready
        .then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.permissionState({userVisibleOnly: true}).then(function(permissionState) {
                    if (permissionState == 'prompt' || permissionState == 'granted') {
                        ServiceWorkerRegistration.pushManager.subscribe({applicationServerKey: urlBase64ToUint8Array(applicationServerKey), userVisibleOnly: true})
                        .then(function(PushSubscription) {
                            if (PushSubscription && 'object' === typeof PushSubscription) {
                                sendSubscribe(PushSubscription);

                                var enableNotifications = document.getElementById('enable_notifications');
                                enableNotifications.parentNode.classList.add('d-none');

                                var disableNotifications = document.getElementById('disable_notifications');
                                disableNotifications.parentNode.classList.remove('d-none');
                            }
                        })
                        .catch(function(error) {
                        });
                    }
                });
            }
        });
    }
}

function pushManagerUnsubscribe() {
    if (serviceWorkerEnabled) {
        navigator.serviceWorker.ready
        .then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.getSubscription()
                .then(function(PushSubscription) {
                    if (PushSubscription && 'object' === typeof PushSubscription) {
                        PushSubscription.unsubscribe()
                        .then(function() {
                            sendUnbscribe(PushSubscription);

                            var disableNotifications = document.getElementById('disable_notifications');
                            disableNotifications.parentNode.classList.add('d-none');

                            var enableNotifications = document.getElementById('enable_notifications');
                            enableNotifications.parentNode.classList.remove('d-none');
                        })
                        .catch(function(error) {
                        });
                    }
                });
            }
        });
    }
}

function sendSubscribe(PushSubscription) {
    if (PushSubscription && 'object' === typeof PushSubscription) {
        var toJSON = PushSubscription.toJSON();

        var body = {
            'endpoint': PushSubscription.endpoint,
            'publicKey': toJSON.keys.p256dh,
            'authenticationSecret': toJSON.keys.auth,
            'contentEncoding': (PushManager.supportedContentEncodings || ['aesgcm'])[0],
        };

        var headers = new Headers({
            'Authorization': 'Bearer ' + getCookie('token_signed'),
            'Content-Type': 'application/json'
        });

        var url = apiUrl + '/push/create';

        fetch(url, {
            method: 'POST',
            credentials: 'omit',
            mode: 'cors',
            headers: headers,
            body: JSON.stringify(body)
        }).then(function(response) {
            if (response.ok && 200 <= response.status && 300 > response.status) {
                setToast({'title': i18next.t('notifications'), 'body': i18next.t('notifications_enabled')});
            } else {
                handleBadResponse(response);
            }
        }).catch(function(err) {
        });
    }
}

function sendUnbscribe(PushSubscription) {
    if (PushSubscription && 'object' === typeof PushSubscription) {
        var body = {
            'endpoint': PushSubscription.endpoint,
        };

        var headers = new Headers({
            'Authorization': 'Bearer ' + getCookie('token_signed'),
            'Content-Type': 'application/json'
        });

        var url = apiUrl + '/push/delete';

        fetch(url, {
            method: 'POST',
            credentials: 'omit',
            mode: 'cors',
            headers: headers,
            body: JSON.stringify(body)
        }).then(function(response) {
            if (response.ok && 200 <= response.status && 300 > response.status) {
                setToast({'title': i18next.t('notifications'), 'body': i18next.t('notifications_disabled')});
            } else {
                handleBadResponse(response);
            }
        }).catch(function(err) {
        });
    }
}

var readyPromises = [];

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
if (language) {
    language = language.substr(0, 2);
}

var hostName = window.location.hostname;
var baseUrl = '//' + hostName;
if (window.location.port) {
    baseUrl += ':' + window.location.port;
}
var loginUrl = baseUrl + '/login';
var apiUrl = baseUrl + '/api';

if (serviceWorkerEnabled) {
    navigator.serviceWorker.register('serviceworker.js').then(function() {
    }).catch(function() {
    });

    navigator.serviceWorker.addEventListener('message', function(MessageEvent) {
        if (MessageEvent.data.type == 'new-version-installed') {
            //setToast({'title': i18next.t('new_version_installed'), 'body': MessageEvent.data.content});
            reload();
        }
    });
}

explainConnection();

var languages = ['en', 'fr'];
var languageFinal = 'en';
if (languages.indexOf(language)) {
    languageFinal = language;
}

readyPromises.push(getTranslation(languageFinal));

for(var i in files) {
    if (files.hasOwnProperty(i)) {
        readyPromises.push(loadFile(files[i]));
    }
}

if (document.attachEvent ? document.readyState === 'complete' : document.readyState !== 'loading') {
    ready();
} else {
    document.addEventListener('DOMContentLoaded', ready);
}

window.addEventListener('beforeinstallprompt', function(BeforeInstallPromptEvent) {
    BeforeInstallPromptEvent.preventDefault();

    var install = document.getElementById('install');

    if (install) {
        install.addEventListener('click', function(event) {
            event.preventDefault();

            BeforeInstallPromptEvent.userChoice
            .then(function(AppBannerPromptResult) {
                setToast({'title': i18next.t('install'), 'body': AppBannerPromptResult.outcome});
            })
            .catch(function(error) {
            });

            BeforeInstallPromptEvent.prompt();
        });

        install.parentNode.classList.remove('d-none');
    }
});

window.addEventListener('appinstalled', function(appinstalled) {
    setToast({'title': i18next.t('install'), 'body': appinstalled});
});

window.addEventListener('online', function() {
    setToast({'title': i18next.t('network'), 'body': 'Online'});
});

window.addEventListener('offline', function() {
    setToast({'title': i18next.t('network'), 'body': 'Offline'});
});

var gKey = false;

document.addEventListener('keyup', function(event) {
    var keycode = event.which || event.keyCode;

    if ($(event.target).parents('form').length === 0) {
        //g
        if (keycode === 71) {
            gKey = true;
        } else {
            gKey = false;
        }
    }
});

document.addEventListener('keydown', function(event) {
    var keycode = event.which || event.keyCode;

    if ($(event.target).parents('form').length === 0) {
        //g then a: go to all items
        if (gKey && keycode === 65) {
            loadRoute('#items/recent');

        //g then u: go to unread items
        } else if (gKey && keycode === 85) {
            loadRoute('#items/unread');

        //g then s: go to starred items
        } else if (gKey && keycode === 83) {
            loadRoute('#items/starred');

        //g then f: go to all feeds
        } else if (gKey && keycode === 70) {
            loadRoute('#feeds/recent');

        //t: back to top
        } else if (keycode === 84) {
            event.preventDefault();
            scrollTo('#top');

        //v: view original from selected item
        } else if (keycode === 86) {
            var href = $('.mdl-grid .card-selected').find('h1').find('a').attr('href');
            var name = $('.mdl-grid .card-selected').attr('id');
            window.open(href, 'window_' + name);

        //m: read / unread selected item
        } else if (keycode === 77 && $('body').hasClass('connected') && $('body').hasClass('online')) {
            if ($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-read').trigger('click');
            }

        //shift + s: share selected item
        } else if (event.shiftKey && keycode === 83) {
            if ($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-share').trigger('click');
            }

        //s: star / unstar selected item
        } else if (keycode === 83 && $('body').hasClass('connected') && $('body').hasClass('online')) {
            if ($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-star').trigger('click');
            }

        } else if (keycode === 65 && $('body').hasClass('connected') && $('body').hasClass('online')) {
            //shift + a: dialog mark all as read
            if (event.shiftKey) {
                const myModal = Modal.getOrCreateInstance('#dialog-mark_all_as_read', {});
                myModal.show();
            //a: add a feed
            } else {
                const myModal = Modal.getOrCreateInstance('#dialog-add_feed', {});
                myModal.show();
            }

        //h or ? : open shortcuts dialog
        } else if(keycode == 72 || keycode == 188) {
            const myModal = Modal.getOrCreateInstance('#dialog-shortcuts', {});
            myModal.show();

        //slash
        } else if (keycode === 191) {
            event.preventDefault();
            if ($('input[name="filter[query]"]').length > 0) {
                $('input[name="filter[query]"]').focus();
            }

        //nothing when meta + k
        } else if (event.metaKey && keycode === 75) {

        //nothing when ctrl + k
        } else if (event.ctrlKey && keycode === 75) {

        //k or p or shift + space: up / previous item
        } else if (keycode === 75 || keycode === 80 || (keycode === 32 && event.shiftKey)) {
            itemUp();

        //j or n or space: down / next item
        } else if (keycode === 74 || keycode === 78|| keycode === 32) {
            itemDown();

        //r: reload
        } else if (keycode === 82) {
            reload();
        }
    }
});
