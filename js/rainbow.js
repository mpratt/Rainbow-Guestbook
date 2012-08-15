/**
 * Rainbow.js
 *
 * @package This file is part of the Rainbow Guestbook
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){

    /**
     * Instead of defining the underscore.js templates in the HTML inside a <script type="text/template">,
     * I prefer to store them right here in an object.
     */
    var RainbowTemplates = {
        singleBubbleList : '<a class="bubble" href="#thread/<%= id %>" style="color:#<%= textColor %>;background-color:#<%= bgColor %>;">\
                                <%= text %> <p class="replies"><%= replies %> Respuestas (<%= date %>) </p>\
                            </a>',
        singleBubbleColorList : '<a class="bubble" href="#color/<%= bgColor %>" style="width: 10px; height: 10px; float: left; line-height: 10px; color:#<%= textColor %>;background-color:#<%= bgColor %>;">   </a>',
        singleBubbleThreadList : '<div class="bubble" style="color:#<%= textColor %>;background-color:#<%= bgColor %>;">\
                                    <%= text %> <p class="replies">(<%= date %>)</p>\
                                  </div>',
        loading        : '<div class="bubble" style="background-color: #FFF;">Cargando .......</div>',
        noResults      : '<div class="bubble" style="background-color:#<%= bgColor %>;">No se encontraron resultados</div>',
        goBackButton   : '<button name="go-back" class="btn btn-large btn-success">Volver al indice</button>',
        newMessage     : '<button id="new-message" class="btn btn-large btn-primary">Escribir Mensaje Nuevo</button>',
        bookmarkMenu   : '<button name="bookmark" class="btn btn-large btn-warning" rel=""></button>',
        deleteMessage  : '<button name="delete" class="btn btn-large btn-danger" rel="">Borrar</button>',
        newMessageForm : '<form id="new-message-form" action="" method="POST">\
                            <textarea id="new-message-content" name="message"></textarea>\
                            <input type="submit" class="btn btn-large btn-inverse" value="Enviar Mensaje">\
                         </form>'
    };

    /**
     * Markdown to HTML Engine.
     * https://github.com/coreyti/showdown
     */
    var markDown = new Showdown.converter();

    /**
     * Lets start with a Backbone model for each message.
     */
    var Message = Backbone.Model.extend({
        parse: function(response){
            if (!response)
                return {};

            var attributes = {};
            attributes.id      = response.id || 0;
            attributes.date    = response.date || '';
            attributes.text    = response.text || '';
            attributes.bgColor = response.color || 'FFFFFF';
            attributes.textColor = this._getContrast(attributes.bgColor);
            attributes.replies = response.replies || 0;
            attributes.error   = response.error || '';

            var hash = window.location.hash;
            if (attributes.id > 0 && hash.indexOf('#thread') > -1)
                attributes.text = this._htmlize(attributes.text);

            return attributes;
        },
        _htmlize: function(text){
            text = markDown.makeHtml(text);
            var regex = /https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_-]+)([a-zA-Z&=;_+0-9*#-]*?)/;
            return text.replace(regex, '<iframe width="520" height="345" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>');
        },
        _getContrast: function(bgcolor){
            var red   = parseInt(bgcolor.substr(0, 2), 16);
            var green = parseInt(bgcolor.substr(2, 2), 16);
            var blue  = parseInt(bgcolor.substr(4, 6), 16);
            var brightness = parseInt(red + green + blue/3);

            if (brightness > 300)
                return '000000';

            return 'FFFFFF';
        }
    });

    /**
     * A Collection of messages
     */
    var MessageList = Backbone.Collection.extend({
        options: {html: false},
        model: Message,
        url : 'api',
        initialize : function(models, options) {
            this.options.html = options.html || false;

            var path = options.path || '';
            if (path.length < 0)
                throw 'Message List must contain a parameter called path!';

            this.url += '/' + path + '/';
        }
    });

    /**
     * The view responsable of showing a collection of messages
     */
    var MessageListView = Backbone.View.extend({
        el: $('#content'),
        initialize: function(options){
            $(this.el).empty().append(RainbowTemplates.loading);
            this.template = _.template(options.template);
            this.collection = new MessageList({}, {path: options.path});
            this.collection.bind('reset', this.render, this);
            this.collection.fetch();
        },
        render: function(){
            var self = this;
            $(this.el).empty();
            if (this.collection.models.length > 0) {
                this.collection.each(function(item) {
                    $(self.el).append(self.template(item.toJSON()));
                });
            }
            else {
                $(this.el).append(_.template(RainbowTemplates.noResults,{bgColor: myColor}));
            }
        }
    });

    /**
     * The view that has the responsability to draw the submenu.
     * It also changes the contents of the menu based on the context of the action.
     */
    var subMenuView = Backbone.View.extend({
        el: $('#context-nav-up'),
        initialize: function(){
            $(this.el).empty();
            _.bindAll(this, 'render');
            $(window).bind('hashchange', this.populateMenu);
            this.render();
        },

        events: { 'submit #new-message-form' : 'submitNewMessage',
                  'click #new-message' : 'showMessageForm',
                  'click button[name="go-back"]' : 'goBack',
                  'click button[name="bookmark"]' : 'bookmarkActions',
                  'click button[name="delete"]' : 'deleteMessage'},
        render: function(){
            $(this.el).append(RainbowTemplates.goBackButton);
            $(this.el).append(RainbowTemplates.newMessage);
            $(this.el).append(RainbowTemplates.bookmarkMenu);
            $(this.el).append(RainbowTemplates.deleteMessage);
            $(this.el).append(RainbowTemplates.newMessageForm);
            this.populateMenu();
        },
        populateMenu: function(){
            var hash = window.location.hash;
            if (hash.indexOf('#thread/') > -1) {
                var parentId = parseInt(hash.substring(hash.indexOf('/') + 1));

                $('#new-message').text('Responder este Mensaje');
                $('#new-message').removeClass('btn-primary');
                $('#new-message-form').attr('action', 'api/reply/' + parentId);

                $('button[name="go-back"]').show();
                $('button[name="bookmark"]').show();
                $('button[name="delete"]').attr('rel', parentId).show();

                if (document.cookie.indexOf('i%3A' + parentId + '%3B') != -1) {
                    $('button[name="bookmark"]').text('Borrar de mis favoritos');
                    $('button[name="bookmark"]').attr('rel', 'api/bookmark/delete/' + parentId);
                }
                else {
                    $('button[name="bookmark"]').text('Agregar a mis favoritos');
                    $('button[name="bookmark"]').attr('rel', 'api/bookmark/add/' + parentId);
                }
            }
            else {

                $('#new-message').text('Escribir nuevo Mensaje');

                if (!$('#new-message').hasClass('btn-primary'))
                    $('#new-message').addClass('btn-primary');

                $('#new-message-form').attr('action', 'api/new/');

                $('button[name="bookmark"]').hide();
                $('button[name="delete"]').attr('rel', '0').hide();
                $('button[name="go-back"]').hide();
            }
        },
        submitNewMessage: function() {
            var text = $.trim($('#new-message-content').val()) || '';
            var action = $('#new-message-form').attr('action') || '';

            if (text.length > 5 && action.length > 0)
            {
                var message = new Message({message: text, token: token});
                message.url = action;
                message.save({}, {success: function(model, response){
                    if (response.id && response.id > 0)
                    {
                        window.location.hash = 'thread/' + response.id;
                        window.location.reload();
                    }
                }});
            }

            $('#new-message-content').val('');
            $('#new-message-form').hide();
            return false;
        },
        showMessageForm: function(){ $('#new-message-form').toggle(); },
        bookmarkActions: function(ev) {
            var url = $(ev.target).attr('rel') || null;
            if (url)
            {
                $.get(url, function(response) {
                    if (response.status)
                        window.location.reload();
                    else
                        alert('Error!');
                });
            }
        },
        goBack: function (){
            var ref = document.referrer;
            if (ref && ref.match('/^' + url + '/') && !ref.match('/#thread\//'))
                window.location = url;
            else
                window.location.hash = '#';
        },
        deleteMessage: function(ev) {
            var id = $(ev.target).attr('rel') || null;
            var password = prompt('Escribe la contraseña para borrar: ', '');
            if (id && password)
            {
                $.post('api/delete/' + id, {pass: password}, function(response){
                    if (response.status)
                        window.location.hash = '#';
                    else
                        alert('Error!');
                });
            }
        }
    });

    /**
     * The Router. It triggers different actions based on the url
     */
    var AppRouter = Backbone.Router.extend({
        routes: {
            '': 'getAll',
            'new': 'getAll',
            'recent': 'getRecent',
            'fav': 'getFavorites',
            'colors': 'getColors',
            'mine': 'getMine',
            'thread/:id': 'showThread',
            'color/:color' : 'showColor'
        },
        getAll: function() {
            new MessageListView({path: 'getAll', template: RainbowTemplates.singleBubbleList});
        },
        getRecent: function() {
            new MessageListView({path: 'getModified', template: RainbowTemplates.singleBubbleList});
        },
        getFavorites: function() {
            new MessageListView({path: 'getBookmarks', template: RainbowTemplates.singleBubbleList});
        },
        getColors: function() {
            new MessageListView({path: 'getColors', template: RainbowTemplates.singleBubbleColorList});
        },
        showColor: function(color) {
            new MessageListView({path: 'getColor/' + color, template: RainbowTemplates.singleBubbleList});
        },
        showThread: function(id) {
            new MessageListView({path: 'getThread/' + id, template: RainbowTemplates.singleBubbleThreadList});
        },
        getMine: function(){
            this.showColor(myColor);
        },
        defaultRouter: function(){
            this.getAll();
        }
    });

    /**
     * Kickstart the app. Draw the SubMenu and get the actions
     */
    new subMenuView();
    new AppRouter();
    Backbone.history.start();
});
