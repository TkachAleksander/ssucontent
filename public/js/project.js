/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {

    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape...
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            // Replace server-side written pluses with spaces.
            // If we can't decode the cookie, ignore it, it's unusable.
            // If we can't parse the cookie, ignore it, it's unusable.
            s = decodeURIComponent(s.replace(pluses, ' '));
            return config.json ? JSON.parse(s) : s;
        } catch(e) {}
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function (key, value, options) {

        // Write

        if (value !== undefined && !$.isFunction(value)) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setTime(+t + days * 864e+5);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // Read

        var result = key ? undefined : {};

        // To prevent the for loop in the first place assign an empty array
        // in case there are no cookies at all. Also prevents odd result when
        // calling $.cookie().
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = parts.join('=');

            if (key && key === name) {
                // If second argument (value) is a function it's a converter...
                result = read(cookie, value);
                break;
            }

            // Prevent storing a cookie that we couldn't decode.
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
        if ($.cookie(key) === undefined) {
            return false;
        }

        // Must not alter options, thus extending a fresh object...
        $.cookie(key, '', $.extend({}, options, { expires: -1 }));
        return !$.cookie(key);
    };

}));
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////






// Подтверждение нажатия на кнопку
$('.confirmDelete').on('click', function() {

    if (confirm("Вы подтверждаете удаление?")) {
        return true;
    } else {
        return false;
    }
});

$(document).ready(function() {

	// MULTISELECT
    $('.multiselect').multiselect();

    // Страница newElement проверка активное/неактивное поле после выбора select
    $("#select_labels").change(function(){
        $val = $('#select_labels :selected').val();
        if($val == 1 || $val == 2 || $val == 3){
            $('.sub_elements').val('');
            $('.sub_elements').attr('disabled', true);
        } else
            $('.sub_elements').attr('disabled', false);
    });



    // Перетаскивание элементов
    // Не дает элементам внутри tr съехать
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    // Перетаскивание
    $("#sortContainer ").sortable({
        helper: fixHelper,
        placeholder: 'emptySpace',
        axis: "y",
        containment: "table"
    }).disableSelection();

    // Кнопка добавления формы на сервер
    $('#getArray').on('click', function() {
        var name_forms = $('#name_forms').val();
        var queue = $('#sortContainer').sortable("toArray");

        $.ajax({
            type: "POST",
            url: "addSetFormsElements",
            data: { name_forms:name_forms, queue:queue },
            datatype: "JSON",
            beforeSend: function (xhr){
                xhr.setRequestHeader( 'X-CSRF-TOKEN', $('#token').attr('content'));
            },
            success: function(message){
                alert(message);
            }
        });

    });

    // Добаление элементов в область перетаскивания
    $('.addElementInForm').on('click', function(){
            var idSetElement = $(this).attr('id');

            $.ajax({
                url: 'getSetElements',
                data: {idSetElement:idSetElement},
                type: 'POST',
                datatype: 'JSON',
                beforeSend: function(xhr)
                {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (data)
                {
                    $('#sortContainer').append( '<tr id="' +data[0].id+ '">'+
                                                   '<td>' +data[0].name_set_elements+ '</td>'+
                                                   '<td>' + data[0].value_sub_elements+ '</td>'+
                                                   '<td><button id="'+data[0].id+'" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm"> X </button></td>'+
                                                '</tr>'
                                                );
                }
            });
    });

});




// Добавление строки для подэлементов вкладка Добавить элемент
$(document).on('click', '.btn-add', function(e)
{
    e.preventDefault();

    var controlForm = $('.controls div:first'),
        currentEntry = $(this).parents('.entry:first'),
        newEntry = $(currentEntry.clone()).appendTo(controlForm);

    newEntry.find('input').val('');
    controlForm.find('.entry:not(:last) .btn-add')
        .removeClass('btn-add').addClass('btn-remove')
        .removeClass('btn-success','btn-success-last').addClass('btn-danger','btn-danger-last')
        .attr('data-id', 0) // для элементов которые добавил пользователь и удаляет
        .html('<span class="glyphicon glyphicon-minus"></span>');
}).on('click', '.btn-remove', function(e)
{
    $(this).parents('.entry:first').remove();
    // записываем элементы которые скроются
    var id = $(this).attr('data-id');
    if (id != 0) {
        // не пишем первую запятую
        if ($.cookie('uninstalled_sub_elements') != Array()) {
            id += "," + $.cookie('uninstalled_sub_elements');
        }
        $.cookie('uninstalled_sub_elements', id);
    }
    e.preventDefault();
    return false;
});

// Редактирование элементов
$('.editElementFromForm').on('click',function() {
    var id_set_elements = $(this).attr('id');
    $.ajax({
        type:"POST",
        url :"/editElementFromForm",
        data:{id_set_elements:id_set_elements},
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (value) {

            // Вставка Имени и Label
            $('#name_set_elements').val(value.set_elements[0].name_set_elements);
            $('#label_set_elements').val(value.set_elements[0].label_set_elements);

            // Вставка значения в multiselect
            $('#select_labels option').removeAttr('selected');
            $('#select_labels [value="'+value.set_elements[0].id_elements+'"]').prop("selected", true);
            var name = $('#select_labels :selected').text();
            $('.multiselect-selected-text').html(name).parent().attr('title',name);

            // Удаляем старые danger поля
            $('.btn-danger-last').parents('.entry').remove();
            $('#btn-edit','#btn-cancel').remove();

            // Заполняем поля под элементами
            var controlsForm = $('.controls-form');
            for(var i=0; i<value.sub_elements.length; i++) {
                controlsForm.prepend(
                    '<div class="entry input-group col-xs-12">'+
                    '<input class="form-control sub_elements" name="value_sub_elements['+value.sub_elements[i].id+']" type="text" value="'+value.sub_elements[i].value_sub_elements+'">'+
                    '<span class="input-group-btn">'+
                    '<button class="btn btn-remove btn-danger btn-danger-last" type="button" data-id="'+value.sub_elements[i].id+'"><span class="glyphicon glyphicon-minus"></span></button>'+
                    '</span>'+
                    '</div>');
            }
            // Отключаем у success поля проверку на заполнение и неактивность
            $('.sub_elements').attr({'disabled':false, 'required':false});

            // Вставляем кнопку редактировать / отменить
            $('#btn-add').before('<button type="button" id="btn-cancel" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewSetElement();" style="margin-left:15px"> Отмена </button>'+
                                 '<button type="submit" id="btn-edit" class="btn btn-sm btn-success btn-padding-0 pull-right" onclick="editNewSetElement();" style="margin-left:15px"> Редактировать </button>')
            $('#btn-add').remove();

            // создаем куки для элементов которые будут скрыты в бд после редактирования
            $.cookie('uninstalled_sub_elements', new Array(), {expires: 1, path:'/'});

        }

    });
});

     function cleanTableNewSetElement(){
         // Очистка Имени и Label
         $('#name_set_elements').val('');
         $('#label_set_elements').val('');

         // Очистка значения в multiselect
         $('#select_labels option').removeAttr('selected');
         $('#select_labels [value="1"]').prop("selected", true);
         $('.multiselect-selected-text').html('input(text)').parent().attr('title','input(text)');

         // Удаляем старые danger поля
         $('.btn-danger-last').parents('.entry').remove();

         // Включаем у success поля проверку на заполнение и неактивность
         $('.sub_elements').attr({'disabled':true, 'required':true});

         // Вставляем кнопку добавить, удаляем кнопки редактировать и отменить
         $('#btn-cancel').before('<button type="submit" id="btn-add" class="btn btn-sm btn-primary btn-padding-0 pull-right" style="margin-left:15px"> Добавить </button>');
         $('#btn-edit').remove();
         $('#btn-cancel').remove();

         // удаляем куки
         $.cookie('uninstalled_sub_elements', new Array(), {expires: -1, path:'/'});
     }

     function editNewSetElement() {
         $('form').attr('action', '/addEditedNewElement');
     }

    // Отображение содержимого формы (Просмотр списка форм constructorForm)
    $('.forms-info').on('click', function(){
        var id_forms = $(this).data("id");
        var contentForm = $('#content-form'+id_forms);
        contentForm.empty();

        if ($(this).hasClass("collapsed")) {
            $.ajax({
                type: "POST",
                url: "getFormInfo",
                data: {id_forms: id_forms},
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (formsInfo) {
                    formsInfo.forEach(function (value, key, formsInfo) {

                        switch (formsInfo[key].name_elements) {
                            case "checkbox":
                                contentForm.append('<p><b>' + formsInfo[key].label_set_elements + '</b></p>');
                                var sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                                sub_elements.forEach(function (value, key, sub_elements) {
                                    contentForm.append('<input type="checkbox" name="' + sub_elements[key] + '" value="' + sub_elements[key] + '"> ' + sub_elements[key] + '</br>');
                                });
                                break;
                            
                            case "option":
                                sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                                contentForm.append('<p><select id="select' + key + '" class="multiselect">');
                                var id = key;
                                sub_elements.forEach(function (value, key, sub_elements) {
                                    $('#select' + id).append('<option value="' + key + '">' + sub_elements[key] + '</option>');
                                });
                                contentForm.append('</select></p>');
                                break;
                        }
                    });
                }

            });
        }
    });

    // Из строки в массив
    function getSubElementsInArray (str){
        var sub_elements = str.split(' | ');
        return sub_elements;
    }



    // Вкладка Форма/Пользователь отображение уже существующих связей
    $("#id_forms").change(function(){
        var id_forms = $('#id_forms option:selected').val();
        var name_forms = $('#id_forms option:selected').text();
        $.ajax({
            type:"POST",
            url:"getTableConnectUsers",
            data:{ id_forms:id_forms },
            dataType:"JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (users) {
                var table = $('#table-forms-connect-users tr:first');
                $('.new_tr').empty();
                users.forEach( function(user, key, users){
                    table.after('<tr class="new_tr">'+
                                    '<td>'+name_forms+'</td>'+
                                    '<td>'+users[key].surname+' '+users[key].name+' '+users[key].middle_name+'</td>'+
                                '</tr>');
                });
            }
        });
    });

    // Кнопка добаления связей
    $('#btn-forms-connect-users').on('click', function(){
        var id_forms = $('#id_forms option:selected').val();
        var id_users = $('#id_users option:selected').val();

        $.ajax({
            type:"POST",
            url:"setTableConnectUsers",
            data:{ id_forms:id_forms, id_users:id_users },
            dataType:"JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (value) {
                alert(value);
                if(value == 'Связь успешно добавлена.')
                    location.reload();
            }
        });
    });

// Кнопка удаления связей
$('#btn-forms-disconnect-users').on('click', function(){
    var id_forms = $('#id_forms option:selected').val();
    var id_users = $('#id_users option:selected').val();
    // alert (id_forms+id_users);
    $.ajax({
        type:"POST",
        url:"setTableDisconnectUsers",
        data:{ id_forms:id_forms, id_users:id_users },
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (value) {
            alert(value);
            if(value == 'Связь успешно разорвана.')
                location.reload();
        }
    });
});




















