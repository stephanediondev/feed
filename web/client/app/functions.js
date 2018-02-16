function getMomentLocale(languageFinal) {
    return fetch('vendor/moment/locale/' + languageFinal + '.js').then(function(response) {
    }).catch(function(err) {
    });
}

function getTranslation(languageFinal) {
    return fetch('app/translations/' + languageFinal + '.json').then(function(response) {
        if(response.ok) {
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
        if(response.ok) {
            return response.text().then(function(text) {
                document.querySelector('body').innerHTML += text;
            });
        } else {
            Promise.reject();
        }
    }).catch(function(err) {
    });
}

function explainConnection(connection) {
    if(typeof connection === 'undefined' || null === connection) {
        connection = {id: false, token: false, member: {id: false, administrator: false, member: false}};

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
            if(response.ok) {
                response.json().then(function(dataReturn) {
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
                });
            }
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
                window.document.title = i18next.t(route.title);
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
                            var badge = 0;
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
                                    window.document.title = dataReturn.entry.title + ' (' + i18next.t(dataReturn.entry_entity) + ')';
                                }
                            }

                            if(!parameters.page || parameters.page === 1) {
                                var template = getTemplate(route.view);
                                document.querySelector('main > .mdl-grid').innerHTML = template(dataReturn);
                            }

                            if(Object.prototype.toString.call( dataReturn.entries ) === '[object Array]' && typeof route.viewUnit === 'string') {
                                var templateUnit = getTemplate(route.viewUnit);

                                for(i in dataReturn.entries) {
                                    if(dataReturn.entries.hasOwnProperty(i)) {
                                        document.querySelector('main > .mdl-grid').innerHTML += templateUnit({connectionData: connectionData, entry: dataReturn.entries[i]});
                                    }
                                }

                                if(route.title) {
                                    window.document.title = i18next.t(route.title) + ' (' + dataReturn.entries_total + ')';
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
                                parameters.link.text(i18next.t(dataReturn.action_reverse));
                                parameters.link.addClass(dataReturn.action);
                                parameters.link.removeClass(dataReturn.action_reverse);
                            }
                            if(typeof dataReturn.entry === 'object' && typeof dataReturn.action === 'string') {
                                if(parameters.snackbar) {
                                    setSnackbar(i18next.t(dataReturn.action) + ' ' + dataReturn.entry.title);
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
                            setSnackbar(i18next.t('logout'));
                        }
                    });
                }
                if(403 === response.status) {
                    localStorage.removeItem('connection');
                    $('body').removeClass('connected');
                    $('body').addClass('anonymous');
                    loadRoute('#login');
                    setSnackbar(i18next.t('logout'));
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
