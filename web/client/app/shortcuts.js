var g_key = false;

$(document).bind('keyup', function(event) {
    var keycode = event.which || event.keyCode;

    if($(event.target).parents('form').length === 0) {
        //g
        if(keycode == 71) {
            g_key = true;
        } else {
            g_key = false;
        }
    }
});

$(document).bind('keydown', function(event) {
    var keycode = event.which || event.keyCode;

    if($(event.target).parents('form').length === 0) {
        //g then a
        if(g_key && keycode == 65) {
            loadRoute('#items/all');

        //g then u
        } else if(g_key && keycode == 85) {
            loadRoute('#items/unread');

        //g then s
        } else if(g_key && keycode == 83) {
            loadRoute('#items/starred');

        //1
        } else if(keycode == 49) {
            event.preventDefault();
            if(items_display == 'expand') {
                items_collapse();
            }

        //2
        } else if(keycode == 50) {
            event.preventDefault();
            if(items_display == 'collapse') {
                items_expand();
            }

        //v
        } else if(keycode == 86) {
            var href = $('.mdl-grid .card-selected').find('h1').find('a').attr('href');
            var name = $('.mdl-grid .card-selected').attr('id');
            window.open(href, 'window_' + name);

        //m
        } else if(keycode == 77) {
            if($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-read').trigger('click');
            }

        //shift + s
        } else if(event.shiftKey && keycode == 83) {
            if($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-share').trigger('click');
            }

        //s
        } else if(keycode == 83) {
            if($('.mdl-grid .card-selected').length > 0) {
                $('.mdl-grid .card-selected').find('.action-star').trigger('click');
            }

        //o or enter
        } else if(keycode == 79 || keycode == 13) {
            if($('.mdl-grid .item-selected').length > 0) {
                ref = $('.mdl-grid .item-selected');
                if(ref.hasClass('collapse')) {
                    item_expand(ref.find('.expand'));
                } else {
                    item_collapse(ref);
                }
            }

        } else if(keycode == 65) {
            //shift + a
            if(event.shiftKey) {
                if($('#dialog-mark_all_as_read').length > 0) {
                    $('#dialog-mark_all_as_read').trigger('click');
                }
            //a
            } else {
                loadRoute('#feed/create');
            }

        //nothing when meta + k
        } else if(event.metaKey && keycode == 75) {

        //nothing when ctrl + k
        } else if(event.ctrlKey && keycode == 75) {

        //k or p or shift + space
        } else if(keycode == 75 || keycode == 80 || (keycode == 32 && event.shiftKey)) {
            item_up();

        //j or n or space
        } else if(keycode == 74 || keycode == 78|| keycode == 32) {
            item_down();

        //r
        } else if(keycode == 82) {
            if(window.location.hash) {
                loadRoute(window.location.hash);
            }
        }
    }
});
