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
            $('#todo-list').append(template({feeds: data_return.feeds}));
        }
    },
    type: 'GET',
    url: 'https://localhost/projects/readerself-symfony/readerself-symfony/web/app_dev.php/api/feeds'
});

$('#todo-list').on('click', '#test-delete', function(event) {
    event.preventDefault();
    var source = $('#template-feed-delete').text();
    var template = Handlebars.compile(source);
    $('#todo-list').append(template({feed: store.get('feed_' + $(this).data('id'))}));
});

$('#todo-list').on('submit', 'form', function(event) {
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
                console.log(data_return);
            }
        },
        type: $(this).attr('method'),
        url: $(this).attr('action')
    });
});