var AppRouter = Backbone.Router.extend({
    routes: {
        "posts/:id": "getPost",
        "items/unread": "getItemsUnread",
        "": "defaultRoute" 
        // Backbone will try to match the route above first
    }
});

// Instantiate the router
var app_router = new AppRouter();

var FeedModel = Backbone.Model.extend({
    url : function() {
      // Important! It's got to know where to send its REST calls. 
      // In this case, POST to '/donuts' and PUT to '/donuts/:id'
      return this.id ? 'test.php/' + this.id : 'test.php'; 
    },

    // Default attributes for the todo item.
    defaults: function() {
        return {
            title: "",
        };
    }
});

var ItemModel = Backbone.Model.extend({
    url : function() {
      // Important! It's got to know where to send its REST calls. 
      // In this case, POST to '/donuts' and PUT to '/donuts/:id'
      return this.id ? 'test.php/' + this.id : 'test.php'; 
    },

    // Default attributes for the todo item.
    defaults: function() {
        return {
            title: "",
        };
    }
});

var FeedsCollection = Backbone.Collection.extend({
    model: FeedModel,
    //url: 'feeds.php',
    url: '../app_dev.php/api/feeds',
    parse: function(data) {
        console.log(data);
        return data.feeds;
    }
});

var feeds = new FeedsCollection();

feeds.on('add', function(feed) {
    console.log(feed.get("id") + " / " + feed.get("title") + "!");

    store.set('feed_' + feed.get('id'), feed);

    var view = new TodoView({model: feed});
    $("#todo-list").append(view.render().el);
});

var ItemsCollection = Backbone.Collection.extend({
    model: ItemModel,
    url: '../app_dev.php/api/items',
    parse: function(data) {
        console.log(data);
        return data.items;
    }
});

var items = new ItemsCollection();

items.on('add', function(item) {
    console.log(item.get("id") + " / " + item.get("title") + "!");

    //store.set('item_' + item.get('id'), item);

    var view = new ItemView({model: item});
    $("#todo-list").append(view.render().el);
});

// The DOM element for a todo item...
var TodoView = Backbone.View.extend({
    tagName: "div",
    className: "mdl-card mdl-shadow--2dp mdl-color--white mdl-cell mdl-cell--3-col",
    template: _.template($('#cell-feed').html()),
    // Re-render the titles of the todo item.
    render: function() {
        console.log('TodoView render');
        this.$el.html(this.template(this.model.toJSON()));
        return this;
    }
});

var ItemView = Backbone.View.extend({
    tagName: "div",
    className: "mdl-card mdl-shadow--2dp mdl-color--white mdl-cell mdl-cell--12-col",
    template: Handlebars.compile( $('#cell-item').html() ),
    // Re-render the titles of the todo item.
    render: function() {
        console.log('ItemView render');
        this.$el.html(this.template(this.model.toJSON()));
        return this;
    }
});

app_router.on('route:getItemsUnread', function () {
    items.fetch({data: {yo: 'test'}, headers: {yo: 'test'}});
});

app_router.on('route:getPost', function (id) {
    // Note the variable in the route definition being passed in here
    console.log( "Get post number " + id );
    feed.save({headers: {oo: 'tagada'}});
});

app_router.on('route:defaultRoute', function () {
    console.log( 'home' ); 
});

Backbone.history.start({pushState: false, root: base_dir});

//feeds.fetch({data: {yo: 'test'}, headers: {yo: 'test'}});

$(document).ready(function() {
    $('a').each( function( index, link ){
      $(link).click( function( event ){
        event.preventDefault();
        app_router.navigate( $(this).attr( "href" ), {trigger: true} );
      });
    });
});
