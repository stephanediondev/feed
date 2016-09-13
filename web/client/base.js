var apiUrl = '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api';
var connectionToken = 'McWGdJO39hX+wsqLxFv/oTt6YoK+14oNOo4r4DocqjVO4t4pWX933Z85vwVTVsgZKww=';

var routes = [];
routes['#login'] = {template: 'template-login', display: 'now'};
routes['#404'] = {template: 'template-404', display: 'now'};
routes['#feeds'] = {template: 'template-feeds', title: 'Feeds', display: 'yopla', store: true, path: '/feeds'};
routes['#items/unread'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?unread=1'};
routes['#items/shared'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?shared=1'};
routes['#items/starred'] = {template: 'template-items', display: 'yopla', store: false, path: '/items?starred=1'};

var source = $('#template-feed-cell').text();
Handlebars.registerPartial('cardFeed', source);

var source = $('#template-item-cell').text();
Handlebars.registerPartial('cardItem', source);

var source = $('#template-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

function loadRoute(key) {
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
                        if(typeof data_return.entries == 'array' && typeof route.store == 'boolean' && route.store) {
                            for(i in data_return.entries) {
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
        $('#target-page').html(template({feed: store.get($(this).data('entry'))}));
    });

    $('#target-page').on('submit', 'form', function(event) {
        var form = $(this);
        event.preventDefault();
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
                    if(form.attr('method') == 'DELETE') {
                        store.remove(data_return.entity + '_' + data_return.id);
                    }
                    if(form.attr('method') == 'PUT') {
                        store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                    }
                    if(form.attr('method') == 'POST') {
                        store.set(data_return.entity + '_' + data_return.entry.id, data_return.entry);
                    }
                }
            },
            type: form.attr('method'),
            url: form.attr('action')
        });
    });
});
