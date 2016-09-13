var routes = [];
routes['#login'] = {template: 'template-login', display: 'now'};
routes['#feeds'] = {template: 'template-feeds', display: 'yopla'};

var source = $('#template-feed-cell').text();
Handlebars.registerPartial('cellFeed', source);

var source = $('#template-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

$('#target-page').on('click', '.test-load-template-global', function(event) {
    var route = routes[$(this).attr('href')];

    if(route.display == 'now') {
        var source = $('#' + route.template).text();
        var template = Handlebars.compile(source);
        $('#target-page').html(template());
    } else {
        $.ajax({
            headers: {
                'X-CONNECTION-TOKEN': 'McWGdJO39hX+wsqLxFv/oTt6YoK+14oNOo4r4DocqjVO4t4pWX933Z85vwVTVsgZKww='
            },
            async: true,
            cache: false,
            data: {
            },
            dataType: 'json',
            statusCode: {
                200: function(data_return) {
                    console.log(data_return);
                    for(i in data_return.entries) {
                        store.set(data_return.class + '_' + data_return.entries[i].id, data_return.entries[i]);
                    }

                    var source = $('#' + route.template).text();
                    var template = Handlebars.compile(source);
                    $('#target-page').html(template({entries: data_return.entries}));
                }
            },
            type: 'GET',
            url: '//localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api/feeds'
        });
    }
});

$('#target-page').on('click', '.test-load-template', function(event) {
    event.preventDefault();
    var source = $('#' + $(this).data('template')).text();
    var template = Handlebars.compile(source);
    $('#target-page').html(template({feed: store.get('Feed_' + $(this).data('id'))}));
});

$('#target-page').on('submit', 'form', function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
        headers: {
            'X-CONNECTION-TOKEN': 'McWGdJO39hX+wsqLxFv/oTt6YoK+14oNOo4r4DocqjVO4t4pWX933Z85vwVTVsgZKww='
        },
        async: true,
        cache: false,
        data: form.serialize(),
        dataType: 'json',
        statusCode: {
            200: function(data_return) {
                if(form.attr('method') == 'DELETE') {
                    store.remove(data_return.class + '_' + data_return.id);
                }
                if(form.attr('method') == 'PUT') {
                    store.set(data_return.class + '_' + data_return.entry.id, data_return.entry);
                }
                if(form.attr('method') == 'POST') {
                    store.set(data_return.class + '_' + data_return.entry.id, data_return.entry);
                }
            }
        },
        type: form.attr('method'),
        url: form.attr('action')
    });
});
