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
    return (confirm("Вы подтверждаете удаление?")) ? true : false ;
});

$(document).ready(function() {

	// MULTISELECT
    $('.multiselect').multiselect();

    // Страница newElement проверка активное/неактивное поле после выбора select
    $("#select_labels").change(function(){
        var sub_elements = $('.sub_elements');

        var val = $('#select_labels :selected').val();
        switch (val) {
            case '4':
            case '5':
            case '6':
                sub_elements.attr('disabled', false);
                break;
            default:
                sub_elements.attr('disabled', true);
        }
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
        containment: ".table-sort"
    }).disableSelection();
});

    // Удалить форму(скрыть) constructorForm
    $('.removeForms').on('click', function() {
        var id_forms = $(this).data("idForm");
        $.ajax({
            type:"POST",
            url:"/constructor/removeForms",
            data:{id_forms:id_forms},
            dataType:"JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function () {
                location.reload();
            }
        })
    });

    // Выбрать форму для редактирования
    $('.editForms').on('click', function () {
        var id_form = $(this).data("idForm");
        var status_checks = $(this).data("statusChecks");

        if (status_checks == '2'){
            alert("Сначала примите или откланите форму !");
        } else {
            $.ajax({
                type: "POST",
                url: "/constructor/editForm",
                data: {id_form: id_form},
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (set_elements) {
                    var sortContainer = $('#sortContainer');
                    sortContainer.empty();
                    $('#name_forms').after('<input type="hidden" id="old_name_forms" name="old_name_forms" value="' + set_elements[0].name_forms + '" required>')
                        .empty().val(set_elements[0].name_forms);
                    $('#update_date').empty().val(set_elements[0].update_date);

                    $('#addNewForm').after('<button type="button" id="btn-cancel-form" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewForm();" style="margin-left:10px"> Отмена </button>' +
                            '<button type="submit" id="btn-edit-form" class="btn btn-sm btn-success btn-padding-0 pull-right" data-id-form="' + id_form + '" style="margin-left:10px"> Редактировать </button>')
                        .remove();

                    set_elements.forEach(function (set_element, key, set_elements) {

                        set_element.value_sub_elements = (set_element.value_sub_elements == "") ? "---" : set_element.value_sub_elements;
                        var checked = (set_element.required == 1) ? "checked=true" : "";
                        sortContainer.append('<tr id="' + set_element.id + '" >' +
                            '<td>' + set_element.label_set_elements + '</td>' +
                            '<td>' + set_element.value_sub_elements + '</td>' +
                            '<td class="text-center"><input type="checkbox" class="required" name="required[]" value="' + set_element.id + '" ' + checked + ' ></td>' +
                            '<td class="text-center"><button id="' + set_element.id + '" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm" data-id="' + set_element.id + '"> X </button></td>' +
                            '<td class="id-set-elements" style="display: none;">'+set_element.id_set_elements+'</td>'+
                            '</tr>'
                        );

                    });

                }
            })
        }

    });

    // Кнопка добавления формы на сервер
    $('#container').on('click','#addNewForm', function() {
        var name_forms = $('#name_forms').val();
        var queue = $('#sortContainer').sortable("toArray");
        var update_date = $('#update_date').val();

        var required = [];
        var i = 0;
        $('#sortContainer input:checkbox:checked').each(function(){
                required[i++] = $(this).val();
        });

        $.ajax({
            type: "POST",
            url: "addSetFormsElements",
            data: { name_forms:name_forms, queue:queue, required:required, update_date:update_date },
            dataType:"JSON",
            beforeSend: function (xhr){
                xhr.setRequestHeader( 'X-CSRF-TOKEN', $('#token').attr('content'));
            },
            success: function(data){
                alert(data.message);
                if(data.bool) {
                    location.reload();
                }
            }
        });

    });

    // Добаление элементов в область перетаскивания
    $('.table-constructorForm').on('click','.addElementInForm', function(){
            var idSetElement = $(this).attr('id');

            $.ajax({
                url: 'getSetElements',
                data: {idSetElement:idSetElement},
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(xhr)
                {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (data)
                {
                    data[0].value_sub_elements =(data[0].value_sub_elements == "") ? "---" : data[0].value_sub_elements;
                    $('#sortContainer').append( '<tr id="' +data[0].id+ '">'+
                        '<td>' +data[0].label_set_elements+ '</td>'+
                        '<td>' +data[0].value_sub_elements+ '</td>'+
                        '<td class="text-center"><input type="checkbox" class="required" name="required[]" value="'+data[0].id+'"></td>'+
                        '<td class="text-center"><button id="'+data[0].id+'" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm" data-id="0"> X </button></td>'+
                        '<td class="id-set-elements" style="display: none;">'+data[0].id+'</td>'+
                        '</tr>');
                }
            });
    });



// Стереть элементы с таблицы сбора формы
$('#sortContainer').on('click','.dellElementFromForm', function(){
    var id = $(this).attr('id');
    $(this).parents('tr').remove();
});

// Кнопка отмены редактируемой формы
function cleanTableNewForm() {
    $('#btn-edit-form').after('<button id="addNewForm" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>');
    $('#btn-edit-form, #btn-cancel-form').remove();
    $('#sortContainer').empty();
    $('#name_form,#update_date').val("");
    $('#old_name_forms').remove();
}

// Кнопка отправки отредактированной формы
$('#container').on('click','#btn-edit-form',function () {
    var id_form = $(this).data('idForm');
    var name_forms = $('#name_forms').val();
    var old_name_forms = $('#old_name_forms').val();
    var queue = $('#sortContainer').sortable("toArray");
    var update_date = $('#update_date').val();
    
    var id_set_elements = [];
    var required = [];
    $.each($('#sortContainer tr td:last-child'),function(i){
        id_set_elements[i] = $(this).html();

    });
    $.each($('#sortContainer input:checkbox:checked'),function(i){
        required[i] = $(this).val();
    });
console.log('queue: '+queue+' required: '+required+' set: '+id_set_elements);
    $.ajax({
        type:"POST",
        url:"/constructor/addEditedNewForm",
        data:{name_forms:name_forms, old_name_forms:old_name_forms, queue:queue, required:required, id_form:id_form, update_date:update_date, id_set_elements:id_set_elements},
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (data) {
            alert(data.message);
            if(data.bool) {
                location.reload();
            }
        }
    })
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
        $.cookie('uninstalled_sub_elements', id, {expires: 1, path:'/'});
    }
    e.preventDefault();
    return false;
});

// Редактирование элементов
$('.editElementFromForm').on('click',function() {
    var id_set_elements = $(this).attr('id');
    $.ajax({
        type:"POST",
        url :"/editSetElementFromForm",
        data:{id_set_elements:id_set_elements},
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (value) {
            var name_set_elements = $('#name_set_elements');
            var label_set_elements = $('#label_set_elements');

            // Очищаем hidden поля
            $('#old_name_set_elements').remove();
            $('#old_label_set_elements').remove();
            $('#id_set_elements').remove();

            // Вставка Имени и Label
            name_set_elements.val(value.set_elements[0].name_set_elements);
            label_set_elements.val(value.set_elements[0].label_set_elements);

            // Вставка Имени и Label в hidden поля
            name_set_elements.after('<input id="old_name_set_elements" type="hidden" name="old_name_set_elements" value="'+value.set_elements[0].name_set_elements+'" required>'+
                '<input id="id_set_elements" type="hidden" name="id_set_elements" value="'+value.set_elements[0].id+'" required>');
            label_set_elements.after('<input id="old_label_set_elements" type="hidden" name="old_label_set_elements" value="'+value.set_elements[0].label_set_elements+'" required>');

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
            $('#btn-add').before('<button type="button" id="btn-remove" class="btn btn-sm btn-danger btn-padding-0 pull-right" onclick="removeSetElement();" style="margin-left:10px" class="confirmDelete"> Удаить </button>'+
                                 '<button type="button" id="btn-cancel" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewSetElement();" style="margin-left:10px"> Отмена </button>'+
                                 '<button type="submit" id="btn-edit" class="btn btn-sm btn-success btn-padding-0 pull-right" onclick="editNewSetElement();" style="margin-left:10px"> Редактировать </button>');
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

         // Очищаем hidden поля
         $('#old_name_set_elements').remove();
         $('#old_label_set_elements').remove();
         $('#id_set_elements').remove();

         // Очистка значения в multiselect
         $('#select_labels option').removeAttr('selected');
         $('#select_labels [value="1"]').prop("selected", true);
         $('.multiselect-selected-text').html('input(text)').parent().attr('title','input(text)');

         // Удаляем старые danger поля
         $('.btn-danger-last').parents('.entry').remove();

         // Включаем у success поля проверку на заполнение и неактивность
         $('.sub_elements').attr({'disabled':true, 'required':true});

         // Вставляем кнопку добавить, удаляем кнопки редактировать и отменить
         $('#btn-cancel').before('<button type="submit" id="btn-add" class="btn btn-sm btn-primary btn-padding-0 pull-right" style="margin-left:15px"> Добавить </button>').remove();
         $('#btn-edit, #btn-remove').remove();

         // удаляем куки
         $.cookie('uninstalled_sub_elements', new Array(), {expires: -1, path:'/'});
     }

     function editNewSetElement() {
         $('form').attr('action', '/addEditedNewSetElement');
     }

    function removeSetElement() {
        var id_set_element = $('#id_set_elements').val();
        console.log(id_set_element);
        $.ajax({
            type: "POST",
            url: "/removeSetElement",
            data: {id_set_elements: id_set_element},
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function () {
                location.reload();
            }
        });
    }






    // Отображение содержимого формы (Просмотр списка форм) constructorForm
    $('.forms-info').on('click', function(){
        var id_forms = $(this).data("id");
        var id_departments = $(this).data("idDepartments");
        var generateString = $(this).data("generatestring")
        var contentForm = $('#content-form'+generateString);
        contentForm.empty();

    if ($(this).hasClass("collapsed")) {
        $.ajax({
            type: "POST",
            url: "getFormInfo",
            data: {id_forms: id_forms, id_departments:id_departments},
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (formsInfo) {
                /* $('.input-id-form').val(formsInfo[0].id_forms);*/console.log(formsInfo);
                var required;
                formsInfo.forEach(function (value, key, formsInfo) {
                    if(formsInfo[key].required == true) {
                        formsInfo[key].required = "*";
                        required = "required";
                    } else {
                        formsInfo[key].required = "";
                        required = "";
                    }

                    formsInfo[key].values_forms = (formsInfo[key].values_forms == null) ? "" : formsInfo[key].values_forms;
                    console.log(formsInfo[key].name_elements);
                    switch (formsInfo[key].name_elements) {

                        case "input(text)":

                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="text" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value="'+formsInfo[key].values_forms+'"><p></p>');
                            break;

                        case "input(email)":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="email" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value="'+formsInfo[key].values_forms+'"><p></p>');
                            break;

                        case "textarea":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<textarea rows="3" class="form-control" name="' + formsInfo[key].name_set_elements + '" style="resize: none;"' + required + '>'+formsInfo[key].values_forms+'</textarea><p></p>');
                            break;

                        case "radiobutton":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            var sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="radio" name="' + formsInfo[key].name_set_elements + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "checkbox":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            var name_sub_elements = formsInfo[key].name_sub_elements;
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="checkbox" name="' + name_sub_elements[key_sub] + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "option":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            contentForm.append('<select id="select' +key+ '" class="multiselect" name="' +formsInfo[key].name_set_elements+ '" style="margin-left: 10px;" ' + required + '>');
                            var id = key;
                            sub_elements.forEach(function (value, key, sub_elements) {
                                $('#select' + id).append('<option value="' +sub_elements[key]+ '">' + sub_elements[key] + '</option>');
                            });
                            contentForm.append('</select>');
                            contentForm.append('<p></p>');
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


// Отображение старого содержимого формы homeAdmin
$('.forms-info-old').on('click', function() {
    var id_forms = $(this).data("id");
    var id_departments = $(this).data("idDepartments");
    var generateString = $(this).data("generatestring")
    var contentForm = $('#content-form-old'+generateString);
    contentForm.empty();

    if ($(this).hasClass("collapsed")) {
        $.ajax({
            type: "POST",
            url: "getFormInfoOld",
            data: {id_forms: id_forms, id_departments:id_departments},
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (formsInfo) {

                if (formsInfo.length == 0){
                    contentForm.append('<p class="text-center">Форма подана первый раз</p>');
                }
                
                var required;
                formsInfo.forEach(function (value, key, formsInfo) {
                    if(formsInfo[key].required == true) {
                        formsInfo[key].required = "*";
                        required = "required";
                    } else {
                        formsInfo[key].required = "";
                        required = "";
                    }

                    formsInfo[key].values_forms = (formsInfo[key].values_forms == null) ? "" : formsInfo[key].values_forms;

                    switch (formsInfo[key].name_elements) {

                        case "input(text)":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="text" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value="'+formsInfo[key].values_forms+'"><p></p>');
                            break;

                        case "input(email)":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="email" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value="'+formsInfo[key].values_forms+'"><p></p>');
                            break;

                        case "textarea":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<textarea rows="3" class="form-control" name="' + formsInfo[key].name_set_elements + '" style="resize: none;"' + required + '>'+formsInfo[key].values_forms+'</textarea><p></p>');
                            break;

                        case "radiobutton":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            var sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="radio" name="' + formsInfo[key].name_set_elements + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "checkbox":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            var name_sub_elements = formsInfo[key].name_sub_elements;
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="checkbox" name="' + name_sub_elements[key_sub] + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "option":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            contentForm.append('<select id="select' +key+ '" class="multiselect" name="' +formsInfo[key].name_set_elements+ '" style="margin-left: 10px;" ' + required + '>');
                            var id = key;
                            sub_elements.forEach(function (value, key, sub_elements) {
                                $('#select' + id).append('<option value="' +sub_elements[key]+ '">' + sub_elements[key] + '</option>');
                            });
                            contentForm.append('</select>');
                            contentForm.append('<p></p>');
                            break;
                    }

                });
            }
        });
    }
});



$('.forms-info-all').on('click', function(){
    var id_forms = $(this).data("id");
    var contentForm = $('#content-form'+id_forms);
    contentForm.empty();

    if ($(this).hasClass("collapsed")) {
        $.ajax({
            type: "POST",
            url: "getFormInfoAll",
            data: {id_forms: id_forms},
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (formsInfo) {
                $('.input-id-form').val(formsInfo[0].id_forms);
                var required;
                formsInfo.forEach(function (value, key, formsInfo) {
                    if(formsInfo[key].required == true) {
                        formsInfo[key].required = "*";
                        required = "required";
                    } else {
                        formsInfo[key].required = "";
                        required = "";
                    }

                    switch (formsInfo[key].name_elements) {

                        case "input(text)":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="text" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value=""><p></p>');
                            break;

                        case "input(email)":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<input type="email" class="form-control" name="' + formsInfo[key].name_set_elements + '"' + required + ' value=""><p></p>');
                            break;

                        case "textarea":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            contentForm.append('<textarea rows="3" class="form-control" name="' + formsInfo[key].name_set_elements + '" style="resize: none;"' + required + '></textarea><p></p>');
                            break;

                        case "radiobutton":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            var sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="radio" name="' + formsInfo[key].name_set_elements + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "checkbox":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b><br>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            var name_sub_elements = formsInfo[key].name_sub_elements;
                            sub_elements.forEach(function (value, key_sub, sub_elements) {
                                contentForm.append('<input type="checkbox" name="' + name_sub_elements[key_sub] + '" value="' + sub_elements[key_sub] + '"' + required + '> ' + sub_elements[key_sub] + '</br>');
                            });
                            contentForm.append('<p></p>');
                            break;

                        case "option":
                            contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_set_elements + '</b>');
                            sub_elements = getSubElementsInArray(formsInfo[key].value_sub_elements);
                            contentForm.append('<select id="select' +key+ '" class="multiselect" name="' +formsInfo[key].name_set_elements+ '" style="margin-left: 10px;" ' + required + '>');
                            var id = key;
                            sub_elements.forEach(function (value, key, sub_elements) {
                                $('#select' + id).append('<option value="' +sub_elements[key]+ '">' + sub_elements[key] + '</option>');
                            });
                            contentForm.append('</select>');
                            contentForm.append('<p></p>');
                            break;
                    }

                });
            }

        });
    }
});




    // Вкладка Форма/Пользователь отображение уже существующих связей
    $("#id_forms, #id_departments").change(function(){
        var id_forms = $('#id_forms option:selected').val();
        var id_departments = $('#id_departments option:selected').val();
        
        (id_forms == '*' || id_departments == '*') ? $('#btn-forms-connect-users, #btn-forms-disconnect-users').attr('disabled','true') : $('#btn-forms-connect-users, #btn-forms-disconnect-users').removeAttr('disabled');

        $.ajax({
            type:"POST",
            url:"getTableConnectUsers",
            data:{ id_forms:id_forms, id_departments:id_departments },
            dataType:"JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (departments) {
                console.log(departments);
                var table = $('#table-forms-connect-users tr:first');
                $('.new_tr').empty();
                departments.forEach( function(department, key, departments){
                    table.after('<tr class="new_tr">'+
                                    '<td>'+department.name_forms+'</td>'+
                                    '<td>'+department.name_departments+'</td>'+
                                '</tr>');
                });
            }
        });
    });

    // Кнопка добаления связей
    $('#btn-forms-connect-users').on('click', function(){
        var id_forms = $('#id_forms option:selected').val();
        var id_departments = $('#id_departments option:selected').val();

        $.ajax({
            type:"POST",
            url:"setTableConnectUsers",
            data:{ id_forms:id_forms, id_departments:id_departments },
            dataType:"JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (data) {
                alert(data.message);
                if(data.bool) {
                    location.reload();
                }
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
            success: function (data) {
                alert(data.message);
                if(data.bool) {
                    location.reload();
                }
            }
        });
    });





$('.btn-remove-departments').on('click', function () {
    var id_departments = $(this).data('idDepartments');

    $.ajax({
        type:"POST",
        url:"removeDepartments",
        data:{ id_departments:id_departments },
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function () {
            location.reload();
        }
    });
});

$('.btn-edit-departments').on('click', function () {
    var id_departments = $(this).data('idDepartments');
    var name_departments = $(this).data('nameDepartments');

    $('#btn-add-departments').removeClass('btn-primary').addClass('btn-success').attr('value','Редактировать');
    $('#name_departments').attr('value',$(this).data('nameDepartments')).after('<input type="hidden" name="id_departments" value="'+id_departments+'">');
    $('form').attr('action', '/constructor/editDepartments');
});






// Список форм на проверку 
// Кнопка принять 

$('#btn-accept-form').on('click', function () {

});














