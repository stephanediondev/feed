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

var hostName = window.location.hostname;
var apiUrl = '//' + hostName;
if(window.location.port) {
    apiUrl += ':' + window.location.port;
}
apiUrl += window.location.pathname;

apiUrl = apiUrl.replace('index.html', '');
if(hostName.indexOf('local') !== -1) {
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
    readyPromises.push(getMomentLocale(languageFinal));
}

readyPromises.push(getTranslation(languageFinal));

for(var i in files) {
    if(files.hasOwnProperty(i)) {
        readyPromises.push(loadFile(files[i]));
    }
}

$(document).ready(function() {
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

        var templateAside = getTemplate('view-aside');
        document.querySelector('.mdl-layout__drawer').innerHTML = templateAside();

        setPositions();

        window.addEventListener('resize', function() {
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
