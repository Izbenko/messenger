$.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
        return null;
    } else {
        return decodeURI(results[1]) || 0;
    }
}

function message(text) {
    jQuery('#chat-result').append(text);
}

jQuery(document).ready(function ($) {
    var socket = new WebSocket('ws://messenger:8090/server.php')

    socket.onopen = function () {
        //message('<div>Соединение установлено</div>');
    };

    socket.onerror = function (error) {
        message("<div>Ошибка при соединении " + (error.message ? error.message : '') + '</div>');
    };

    socket.onclose = function () {
        //message('<div>Соединение закрыто</div>');
    };

    socket.onmessage = function (event) {
        var data = JSON.parse(event.data);

        if (data.is_group) {
            var id = $.urlParam('group_id');
        } else {
            var id = $.urlParam('id')
        }

        if (data.from_id != id && data.to_id != id) {
            return false;
        }

        message("<div>" + data.message + "</div>");
    }

    $("#chat").on('submit', function () {
        var message = {
            message: $("#message").val(),
            user: $("#user").val(),
            avatar: $("#avatar").val(),
            date: $("#date").val(),
            to_id: $("#to_id").val(),
            from_id: $("#from_id").val(),
            is_group: $("#is_group").val(),
            get_id: $.urlParam('id'),
            get_group_id: $.urlParam('group_id'),
        };

        if (message.message == '') {
            return false;
        }

        socket.send(JSON.stringify(message));
        $('#chat')[0].reset();
        return false;
    });
});

$('.menu').click(function () {
    $('.overlay').fadeIn();
});

$('.close-popup').click(function () {
    $('.overlay').fadeOut();
});

$('.add_user').click(function () {
    $('.add').fadeIn();
});

$('.close-popup-add').click(function () {
    $('.add').fadeOut();
});


$('.edit').click(function () {
    $('.msg-edit').fadeIn();
});

$('.close-edit').click(function () {
    $('.edit-menu').fadeOut();
});

$('.close-msg-edit').click(function () {
    $('.msg-edit').fadeOut();
});

$('.msg').contextmenu(function () {
    $('.edit-menu').fadeIn();
    var id = $(this).children('span').attr("value");
    var text = $(this).children('span').text();
    var group = $('.group').attr("value");
    $('#edit-message').attr("placeholder", text);
    $('#edit-id').attr("value", id);
    $('#edit-group').attr("value", group);
    $('#delete-id').attr("value", id);
    $('#delete-group').attr("value", group);
});