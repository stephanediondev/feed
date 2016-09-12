var source = $('#template-feed-cell').text();
Handlebars.registerPartial('cellFeed', source);

var source = $('#template-feed-title').text();
Handlebars.registerPartial('titleFeeds', source);

$('#target-page').on('click', '.test-load-template-global', function(event) {
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
                console.log(data_return.feeds);
                for(i in data_return.feeds) {
                    store.set('feed_' + data_return.feeds[i].id, data_return.feeds[i]);
                }
    
                var source = $('#template-feeds').text();
                var template = Handlebars.compile(source);
                $('#target-page').html(template({feeds: data_return.feeds}));
            }
        },
        type: 'GET',
        url: 'https://localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api/feeds'
    });
});

$('#target-page').on('click', '.test-load-template', function(event) {
    event.preventDefault();
    var source = $('#' + $(this).data('template')).text();
    var template = Handlebars.compile(source);
    $('#target-page').html(template({feed: store.get('feed_' + $(this).data('id'))}));
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
        data: {
        },
        dataType: 'json',
        statusCode: {
            200: function(data_return) {
                if(form.attr('method') == 'DELETE') {
                    store.remove('feed_' + data_return.id);
                }
            }
        },
        type: form.attr('method'),
        url: form.attr('action')
    });
});
