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
            loadRoute('#items/unread');

        //g then p
        } else if(g_key && keycode == 80) {
            loadRoute('#items/priority');

        //g then shift + s
        } else if(g_key && event.shiftKey && keycode == 83) {
            loadRoute('#items/shared');

        //g then s
        } else if(g_key && keycode == 83) {
            loadRoute('#items/starred');

        //g then v
        } else if(g_key && keycode == 86) {
            click_navigation($('#load-video-items'));

        //g then g
        } else if(g_key && keycode == 71) {
            loadRoute('#items/geolocation');

        //shift + 1
        } else if(event.shiftKey && keycode == 49) {
            event.preventDefault();
            if(items_mode == 'unread_only') {
                $('#hdrbtn_mode').html('<i class="material-icons md-24">visibility</i>');
                items_mode = 'read_and_unread';
                $.cookie('items_mode', items_mode, { expires: 30, path: '/' });
                load_items( $('.mdl-navigation').find('li.active').find('a.mdl-navigation__link').attr('href') );
            }

        //shift + 2
        } else if(event.shiftKey && keycode == 50) {
            event.preventDefault();
            if(items_mode == 'read_and_unread') {
                $('#hdrbtn_mode').html('<i class="material-icons md-24">visibility_off</i>');
                items_mode = 'unread_only';
                $.cookie('items_mode', items_mode, { expires: 30, path: '/' });
                load_items( $('.mdl-navigation').find('li.active').find('a.mdl-navigation__link').attr('href') );
            }

        //shift + f
        } else if(event.shiftKey && keycode == 70) {
            event.preventDefault();
            fullscreen();

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
            var href = $('.mdl-grid .item-selected').find('h1').find('a').attr('href');
            var name = $('.mdl-grid .item-selected').attr('id');
            window.open(href, 'window_' + name);

        //m
        } else if(keycode == 77) {
            if($('.mdl-grid .item-selected').length > 0) {
                item_read($('.mdl-grid .item-selected').find('.history'));
            }

        //shift + s
        } else if(event.shiftKey && keycode == 83) {
            if($('.mdl-grid .item-selected').length > 0) {
                item_share($('.mdl-grid .item-selected').find('.share'));
            }

        //s
        } else if(keycode == 83) {
            if($('.mdl-grid .item-selected').length > 0) {
                item_star($('.mdl-grid .item-selected').find('.star'));
            }

        ///
        } else if(keycode == 58) {
            event.preventDefault();
            //$('#search_items').focus().select();

        //h or ?
        } else if(keycode == 72 || keycode == 188) {
            //modal_show($('#link_shortcuts').attr('href'));

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
                //modal_show($('#items_read').attr('href'));
            //a
            } else {
                window.location.href = base_url + 'subscriptions/create';
            }

        //nothing when meta + k
        } else if(event.metaKey && keycode == 75) {

        //nothing when ctrl + k
        } else if(event.ctrlKey && keycode == 75) {

        //shift + x
        } else if(event.shiftKey && keycode == 88) {
            var ref = $('.mdl-navigation').find('li.active').find('.folder');
            if(ref.length > 0) {
                toggle_folder(ref);
            }

        //shift + p
        } else if(event.shiftKey && keycode == 80) {
            navigation_up();

        //shift + n
        } else if(event.shiftKey && keycode == 78) {
            navigation_down();

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
