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
var Rainbow = {
    // Sets things up
    init : function () {
        var t = this;

        $('#create').click(function () {
            t.createForm();
            return false;
        });

        $('#new').click(function () {
            t.list('actions.php?do=getAll&token=' + token);

            $('#header ul li').removeClass('current');
            $(this).parent('li').addClass('current');
            return false;
        });

        $('#modified').click(function () {
            t.list('actions.php?do=getActive&token=' + token);

            $('#header ul li').removeClass('current');
            $(this).parent('li').addClass('current');
            return false;
        });

        $('#favorite').click(function () {
            t.list('actions.php?do=getFavorite&token=' + token);

            $('#header ul li').removeClass('current');
            $(this).parent('li').addClass('current');
            return false;
        });

        $('#mine').click(function () {
            t.viewColor(myColor);

            $('#header ul li').removeClass('current');
            $(this).parent('li').addClass('current');
            return false;
        });

        $('#help').click(function () {
            $(this).nextAll('div').toggle();

            $('#header ul li').removeClass('current');
            $(this).parent('li').addClass('current');
            return false;
        });

        $('#help').mouseover(function () { $('#help').click(); });
        $('#help').mouseout(function () { $(this).nextAll('div').hide(); });

        $('#avatar').css({'background-color' : '#' + myColor,
                          'width' : '25px',
                          'text-align' : 'center'});

        // Trigger the action we want to do
        if (request.view)
            t.viewReplies('actions.php?do=view&token=' + token + '&id=' + request.view, request.view, true);
        else if (request.color)
            t.viewColor(request.color);
        else if (request.modified == '')
            $('#modified').click();
        else if (request.favorite == '')
            $('#favorite').click();
        else if (request.mine == '')
            $('#mine').click();
        else
            $('#new').click();
    },

    // Reads all the messages from a url
    list : function(url) {
        var t = this;
        $('#bubbles').empty();
        $.ajax({url: url,
            method:   'GET',
            dataType: 'json',
            beforeSend:  function() { $('#avatar').html('<img src="fashion/loader.gif" alt="' + rainbowLang.loading + '" />'); },
            success: function(response) {
                $('#avatar').empty();
                if ($.isEmptyObject(response))
                {
                    $('<div/>', {'class' : 'anounce-bubbles', 'text' : rainbowLang.empty}).appendTo('#bubbles');
                    return false;
                }

                $.each(response, function(index, value) {
                    t.draw(value.id, value.text, value.replies, value.date, value.color);
                });
            }
        });
    },

    // Fetches and draws all the replies from an id
    viewReplies: function(viewUrl, id, fatalError) {
        var t = this;
        $('.inline-reply').remove();
        $('#create-form').remove();
        $.ajax({url: viewUrl,
            method:   'GET',
            dataType: 'json',
            beforeSend: function() { $('#avatar').html('<img src="fashion/loader.gif" alt="' + rainbowLang.loading + '" /> '); },
            success: function(response) {
                $('#avatar').empty();

                if (fatalError && $.isEmptyObject(response))
                {
                    $('<div/>', {'class' : 'anounce-bubbles', 'text' : rainbowLang.empty}).appendTo('#bubbles');
                    return false;
                }

                var container = $('<div/>', {'class' : 'inline-reply'}).appendTo('#bubbles');
                $('#bubble-' + id).after(container);

                if (!$.isEmptyObject(response)) {
                    $.each(response, function(index, value) {
                        var reply = $('<div/>', {'class' : 'bubble'});
                        reply.css({'background-color' : '#' + value.color});
                        reply.html(t.autoEmbed(value.text));
                        reply.appendTo(container).fadeIn(1000);
                    });
                }

                var form = t.createReplyForm(id);
                form.appendTo(container);
            }
        });
    },

    // shows messages that were sent by a color
    viewColor : function (color) {
        $('#bubbles').empty();
        $.ajax({url: 'actions.php?do=getColor&token=' + token + '&color=' + color,
                method:   'GET',
                dataType: 'json',
                beforeSend:  function() { $('#avatar').html('<img src="fashion/loader.gif" alt="' + rainbowLang.loading + '" /> '); },
                success: function(response) {
                    $('#avatar').empty();
                    if ($.isEmptyObject(response))
                    {
                        $('<div/>', {'class' : 'anounce-bubbles', 'text' : rainbowLang.empty}).appendTo('#bubbles');
                        return false;
                    }

                    $.each(response, function(index, value) {
                        var newBubble = $('<div/>', {'class' : 'bubble', 'text' : value.text, 'id' : 'bubble-' + value.id});
                        newBubble.css({'background-color' : '#' + color,
                                       'text-align' : 'left',
                                       'opacity' : 0.7});

                        newBubble.appendTo('#bubbles');
                    });

                }
        });
    },

    // Creates a reply Form to a message
    createReplyForm : function (parentId) {
        var formContainer = $('<div/>', {'class' : 'bubble'});
        formContainer.css({'background-color' : '#B3AAAA'});

        var form = $('<form />', {'action' : 'actions.php?do=reply&token=' + token, 'method' : 'post'});
        var textInput = $('<textarea />', {'name' : 'reply', 'type' : 'text', 'value' : ''});

        textInput.keyup(function() {
            if ($(this).val().length > 1000){
                $(this).val($(this).val().substr(0, 1000));
            }
        });

        var idInput = $('<input />', {'name' : 'id', 'type' : 'hidden', 'value' : parentId});
        var submitButton = $('<input />', {'type' : 'submit', 'value' : rainbowLang.reply});
        var clearfix = $('<p />', {'class' : 'clearfix'});

        textInput.appendTo(form);
        idInput.appendTo(form);
        submitButton.appendTo(form);
        clearfix.appendTo(form);

        var viewLink = $('<a />', {'href' : 'index.php?view=' + parentId, 'text' : rainbowLang.view});
        var deleteLink = $('<a />', {'href' : 'actions.php?do=delete&token=' + token + '&id=' + parentId, 'text' : rainbowLang.deleteShow});
        deleteLink.click(function(){ return confirm(rainbowLang.deleteConfirm); });

        if (document.cookie.indexOf('i%3A' + parentId + '%3B') != -1) {
            var favorite = $('<a />', {'href' : 'actions.php?do=favorite&token=' + token + '&subaction=remove&id=' + parentId,
                                       'text' : rainbowLang.removeFavorite});
        }
        else {
            var favorite = $('<a />', {'href' : 'actions.php?do=favorite&token=' + token + '&subaction=add&id=' + parentId,
                                       'text' : rainbowLang.addFavorite});
        }

        favorite.click(function(){
            $(this).remove();
        });

        viewLink.appendTo(form);
        $('<span />', {'text' : '  |  '}).appendTo(form);
        favorite.appendTo(form);
        $('<span />', {'text' : '  |  '}).appendTo(form);
        deleteLink.appendTo(form);
        form.appendTo(formContainer);

        return formContainer;
    },

    // Creates a form for new messages
    createForm : function () {
        $('#create-form').remove();
        $('.inline-reply').remove();
        var formContainer = $('<div/>', {'class' : 'bubble', 'id' : 'create-form'});
        formContainer.css({'background-color' : '#B3AAAA'});

        var form = $('<form />', {'action' : 'actions.php?do=create&token=' + token, 'method' : 'post'});
        var textInput = $('<textarea />', {'name' : 'quote', 'type' : 'text', 'value' : rainbowLang.createDesc});
        textInput.focus(function() { $(this).val(''); });

        textInput.keyup(function() {
            if ($(this).val().length > 1000){
                $(this).val($(this).val().substr(0, 1000));
            }
        });

        var submitButton = $('<input />', {'type' : 'submit', 'value' : rainbowLang.create});
        var clearfix = $('<p />', {'class' : 'clearfix'});

        textInput.appendTo(form);
        submitButton.appendTo(form);
        clearfix.appendTo(form);
        form.appendTo(formContainer);
        formContainer.prependTo('#bubbles');
    },

    // This is the main mehtod that draws messages
    draw : function (id, text, replies, date, color) {
        var t = this;
        var repliesCounter =  $('<div/>', {'text' : replies + ' ' + rainbowLang.replies, 'class' : 'reply-counter'});
        var textLink  = $('<a />', {'text' : text, 'href' : 'index.php?view=' + id});
        var newBubble = $('<div/>', {'class' : 'bubble', 'id' : 'bubble-' + id});
        newBubble.css({'background-color' : '#' + color,
                       'cursor' : 'pointer',
                       'text-align' : 'left',
                       'opacity' : 0.7});

        newBubble.click(function(){
            $(this).prependTo('#bubbles');

            $('iframe').each(function(){
                var src = $(this).attr('src');
                $(this).replaceWith(src);
            });

            $(this).html(t.autoEmbed($(this).html()));
            t.viewReplies('actions.php?do=view&token=' + token + '&id=' + id + '&shift=1', id, false);
            $('html,body').animate({scrollTop: $('#bubble-' + id).offset().top},'slow');
            return false;
        });

        repliesCounter.appendTo(newBubble);
        textLink.appendTo(newBubble);
        newBubble.appendTo('#bubbles').fadeIn(1000);
    },

    // Embeds videos
    autoEmbed : function(text) {
        var re = /https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube\.com\S*[^\w\-\s])([\w\-]{11})(?=[^\w\-]|$)(?![?=&+%\w]*(?:['"][^<>]*>|<\/a>))[?=&+%\w]*/ig;
        return text.replace(re, '<br /><iframe width="560" height="349" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe><br />');
    }
};

$(function() { Rainbow.init(); });