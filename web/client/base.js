var apiUrl = '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api';
var connectionToken = 'McWGdJO39hX+wsqLxFv/oTt6YoK+14oNOo4r4DocqjVO4t4pWX933Z85vwVTVsgZKww=';

var routes = [];
routes['#login'] = {template: 'template-login', display: 'now'};
routes['#404'] = {template: 'template-404', display: 'now'};
routes['#feeds'] = {template: 'template-feeds', title: 'Feeds', display: 'yopla', store: true, path: '/feeds'};
routes['#folders'] = {template: 'template-folders', title: 'Folders', display: 'yopla', store: true, path: '/folders'};
routes['#items/unread'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?unread=1'};
routes['#items/shared'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?shared=1'};
routes['#items/starred'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?starred=1'};

var source = $('#template-feed-cell').text();
Handlebars.registerPartial('cardFeed', source);

var source = $('#template-folder-cell').text();
Handlebars.registerPartial('cardFolder', source);

var source = $('#template-item-cell').text();
Handlebars.registerPartial('cardItem', source);

var source = $('#template-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

var source = $('#template-folder-title').text();
Handlebars.registerPartial('titleFolders', source);

function loadRoute(key) {
    $('#target-page').html('<div class="mdl-spinner mdl-js-spinner is-active"></div>');componentHandler.upgradeDom('MaterialSpinner', 'mdl-spinner');

    if(key in routes) {
        var route = routes[key];

        window.location.hash = key;
        window.document.title = route.title;//TODO: get first h1 in card

        if(route.display == 'now') {
            var source = $('#' + route.template).text();
            var template = Handlebars.compile(source);
            $('#target-page').html(template());
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
                    200: function(data_return) {
                        console.log(data_return);
                        if(Object.prototype.toString.call( data_return.entries ) === '[object Array]' && typeof route.store == 'boolean' && route.store) {
                            for(var i in data_return.entries) {
                                store.set(data_return.entity + '_' + data_return.entries[i].id, data_return.entries[i]);
                            }
                        }

                        var source = $('#' + route.template).text();
                        var template = Handlebars.compile(source);
                        $('#target-page').html(template(data_return));
                    }
                },
                type: 'GET',
                url: apiUrl + route.path
            });
        }
    } else {
        loadRoute('#404');
    }
}

$(document).ready(function() {
    if(window.location.hash) {
        loadRoute(window.location.hash);
    } else {
        loadRoute('#feeds');
    }

    $(document).on('click', '.test-load-template-global', function(event) {
        loadRoute($(this).attr('href'));
    });

    $('#target-page').on('click', '.test-load-template', function(event) {
        event.preventDefault();

        var source = $($(this).attr('href')).text();
        var template = Handlebars.compile(source);
        $('#target-page').html(template(store.get($(this).data('entry'))));
    });

    $('#target-page').on('submit', 'form', function(event) {
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
                    var snackbarContainer = document.querySelector('.mdl-snackbar');
                    var data = {message: form.attr('method') + ' ' + data_return.entry.title};
                    snackbarContainer.MaterialSnackbar.showSnackbar(data);

                    if(form.attr('method') == 'DELETE') {
                        store.remove(data_return.entity + '_' + data_return.id);
                    }
                    if(form.attr('method') == 'PUT') {
                        store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                    }
                    if(form.attr('method') == 'POST') {
                        store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                    }
                    loadRoute(form.attr('action'));
                }
            },
            type: form.attr('method'),
            url: apiUrl + form.data('path')
        });
    });
});
