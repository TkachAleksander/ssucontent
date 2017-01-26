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

// Из строки в массив
function getSubElementsInArray (str){
    var sub_elements = str.split(' | ');
    return sub_elements;
}


// addForm //
// addForm //
// addForm //
// addForm //
// addForm //


// Подтверждение нажатия на кнопку
$('.confirmDelete').on('click', function() {
    return (confirm("Вы подтверждаете удаление?")) ? true : false ;
});

//  Страница newElement проверка активное/неактивное поле после выбора select
function select_labels() {
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
}

$(document).ready(function() {

	// MULTISELECT
    $('.multiselect').multiselect();

    // Страница newElement проверка активное/неактивное поле после выбора select
    $("#select_labels").change(function () {
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
//alert(id_form);
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
                success: function (fields) {
console.log(fields);
                    var sortContainer = $('#sortContainer');
                    sortContainer.empty();
                    $('#old_name_forms').remove();
                    $('#name_forms').after('<input type="hidden" id="old_name_forms" name="old_name_forms" value="' + fields[0].name_forms + '" required>')
                        .empty().val(fields[0].name_forms);
                    $('#date_update_forms').empty().val(fields[0].date_update_forms);
            
                    $('#addNewForm').after('<button type="button" id="btn-cancel-form" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewForm();" style="margin-left:10px"> Отмена </button>' +
                            '<button type="submit" id="btn-edit-form" class="btn btn-sm btn-success btn-padding-0 pull-right" style="margin-left:10px"> Редактировать </button>')
                        .remove();
                    $('#btn-edit-form').attr("data-id-this-form",id_form);

                    fields.forEach(function (field, key, fields) {
                        var checked = (field.required_fields_current == 1) ? "checked=true" : "";
                        sortContainer.append('<tr id="' + field.id_fields + '" >' +
                            '<td>' + field.label_fields + '</td>' +
                            '<td>' + field.labels_sub_elements + '</td>' +
                            '<td class="text-center"><input type="checkbox" class="required" name="required[]" value="' + field.id_fields + '" ' + checked + ' ></td>' +
                            '<td class="text-center"><button id="' + field.id_fields + '" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm" data-id="' + field.id_fields + '"> X </button></td>' +
                            '<td class="id-set-elements" style="display: none;">'+field.id_fields+'</td>'+
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
        var id_fields = $('#sortContainer').sortable("toArray");
        var date_update_forms = $('#date_update_forms').val();

        var required = [];
        var i = 0;
        $('#sortContainer input:checkbox:checked').each(function(){
                required[i++] = $(this).val();
        });

        $.ajax({
            type: "POST",
            url: "addSetFormsElements",
            data: { name_forms:name_forms, id_fields:id_fields, required:required, date_update_forms:date_update_forms },
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
            var id_fields = $(this).attr('id');
            $.ajax({
                url: 'getSetElements',
                data: {id_fields:id_fields},
                type: 'POST',
                dataType: 'JSON',
                beforeSend: function(xhr)
                {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (fields)
                {
                    // console.log(fields);
                    $('#sortContainer').append( '<tr id="' +fields[0].id_fields+ '">'+
                        '<td>' +fields[0].label_fields+ '</td>'+
                        '<td>' +fields[0].labels_sub_elements+ '</td>'+
                        '<td class="text-center"><input type="checkbox" class="required" name="required[]" value="'+fields[0].id_fields+'"></td>'+
                        '<td class="text-center"><button id="'+fields[0].id_fields+'" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm" fields-id="0"> X </button></td>'+
                        '<td class="id-set-elements" style="display: none;">'+fields[0].id_fields+'</td>'+
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
    $('#name_forms,#update_date').val("");
    $('#old_name_forms').remove();
}

// Кнопка отправки отредактированной формы
$('#container').on('click','#btn-edit-form',function () {

    var id_form = $('#btn-edit-form').data('idThisForm');
    var name_forms = $('#name_forms').val();
    var old_name_forms = $('#old_name_forms').val();
    var date_update_forms = $('#date_update_forms').val();
    
    var id_fields = [];
    var required = [];
    $.each($('#sortContainer tr td:last-child'),function(i){
        id_fields[i] = $(this).html();

    });
    $.each($('#sortContainer input:checkbox:checked'),function(i){
        required[i] = $(this).val();
    });

    $.ajax({
        type:"POST",
        url:"/constructor/addEditedNewForm",
        data:{name_forms:name_forms, old_name_forms:old_name_forms, required:required, id_form:id_form, date_update_forms:date_update_forms, id_fields:id_fields},
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (data) {console.log(data);
            alert(data.message);
            if(data.bool) {
                location.reload();
            }
        }
    })
});


// newElement //
// newElement //
// newElement //
// newElement //
// newElement //


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
    var id_fields = $(this).attr('id');
    $.ajax({
        type:"POST",
        url :"/editSetElementFromForm",
        data:{id_fields:id_fields},
        dataType:"JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (data) {
// console.log(data);
            var label_fields = $('#label_fields');

            // Удаляем hidden поля
            $('.old-value-hidden').remove();
            // Вставка Label
            label_fields.val(data.fields[0].label_fields);

            // Вставка hidden поля с id_fields
            label_fields.after('<input class="old-value-hidden" type="hidden" name="id_fields" value="'+data.fields[0].id_fields+'" required>');

            // Вставка значения в multiselect
            $('#select_labels option').removeAttr('selected');
            $('#select_labels [value="'+data.fields[0].id_elements+'"]').prop("selected", true);
            var name = $('#select_labels :selected').text();
            $('.multiselect-selected-text').html(name).parent().attr('title',name);

            // Удаляем старые danger поля
            $('.btn-danger-last').parents('.entry').remove();
            $('#btn-edit','#btn-cancel').remove();

            // Заполняем поля под элементами
            if(data.fields[0].labels_sub_elements){
                var labels_sub_elements = getSubElementsInArray(data.fields[0].labels_sub_elements);

                var controlsForm = $('.controls-form');
                for(var i=labels_sub_elements.length; i>=0; i--) {
                    if(labels_sub_elements[i] != null) {
                        controlsForm.prepend(
                            '<div class="entry input-group col-xs-12">' +
                            '<input class="form-control sub_elements" name="label_sub_elements[' + labels_sub_elements[i] + ']" type="text" value="' + labels_sub_elements[i] + '">' +
                            '<span class="input-group-btn">' +
                            '<button class="btn btn-remove btn-danger btn-danger-last" type="button" data-id="' + labels_sub_elements[i] + '"><span class="glyphicon glyphicon-minus"></span></button>' +
                            '</span>' +
                            '</div>');
                    }
                }
            }
            // Отключаем у success поля проверку на заполнение и неактивность
            $('.sub_elements').attr({'disabled':false, 'required':false});

            // Вставляем кнопку редактировать / отменить
            $('#btn-add').before('<button type="button" id="btn-remove" class="btn btn-sm btn-danger btn-padding-0 pull-right confirmDelete" onclick="removeSetElement('+id_fields+');" style="margin-left:10px" class="confirmDelete"> Удаить </button>'+
                '<button type="button" id="btn-cancel" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewSetElement();" style="margin-left:10px"> Отмена </button>'+
                '<button type="submit" id="btn-edit" class="btn btn-sm btn-success btn-padding-0 pull-right" onclick="editNewSetElement('+id_fields+');" style="margin-left:10px"> Редактировать </button>');
            $('#btn-add').remove();
            // Проверяем на активность поля Список выбора
            select_labels();

/*            // var name_fields = $('#name_fields');
            var label_fields = $('#label_fields');

            // Очищаем hidden поля
            $('#old_name_fields').remove();
            $('#old_label_fields').remove();

            // Вставка Label
            label_fields.val(data.fields[0].label_fields);

            // Вставка Label в hidden поля
            label_fields.after('<input class="old-value-hidden" type="hidden" name="old_label_fields" value="'+data.fields[0].label_fields+'" required>'+
                               '<input class="old-value-hidden" type="hidden" name="old_id_elements" value="'+data.fields[0].id_elements+'" required>');


            // Вставка значения в multiselect
            $('#select_labels option').removeAttr('selected');
            $('#select_labels [value="'+data.fields[0].id_elements+'"]').prop("selected", true);
            var name = $('#select_labels :selected').text();
            $('.multiselect-selected-text').html(name).parent().attr('title',name);

            // Удаляем старые danger поля
            $('.btn-danger-last').parents('.entry').remove();
            $('#btn-edit','#btn-cancel').remove();



            // Заполняем поля под элементами
            if(data.fields[0].labels_sub_elements){
                var labels_sub_elements = getSubElementsInArray(data.fields[0].labels_sub_elements);

                var controlsForm = $('.controls-form');
                for(var i=labels_sub_elements.length; i>=0; i--) {
                    if(labels_sub_elements[i] != null) {
                        controlsForm.prepend(
                            '<div class="entry input-group col-xs-12">' +
                            '<input class="form-control sub_elements" name="label_sub_elements[' + labels_sub_elements[i] + ']" type="text" value="' + labels_sub_elements[i] + '">' +
                            '<span class="input-group-btn">' +
                            '<button class="btn btn-remove btn-danger btn-danger-last" type="button" data-id="' + labels_sub_elements[i] + '"><span class="glyphicon glyphicon-minus"></span></button>' +
                            '</span>' +
                            '</div>');
                    }
                }
            }
            // Отключаем у success поля проверку на заполнение и неактивность
            $('.sub_elements').attr({'disabled':false, 'required':false});

            // Вставляем кнопку редактировать / отменить
            $('#btn-add').before('<button type="button" id="btn-remove" class="btn btn-sm btn-danger btn-padding-0 pull-right confirmDelete" onclick="removeSetElement('+id_fields+');" style="margin-left:10px" class="confirmDelete"> Удаить </button>'+
                                 '<button type="button" id="btn-cancel" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewSetElement();" style="margin-left:10px"> Отмена </button>'+
                                 '<button type="submit" id="btn-edit" class="btn btn-sm btn-success btn-padding-0 pull-right" onclick="editNewSetElement('+id_fields+');" style="margin-left:10px"> Редактировать </button>');
            $('#btn-add').remove();
            // Проверяем на активность поля Список выбора
            select_labels();
            // создаем куки для элементов которые будут скрыты в бд после редактирования
            $.cookie('uninstalled_sub_elements', new Array(), {expires: 1, path:'/'});*/

        }

    });
});

    // Кнопка отмены редактируемого элемента
    function cleanTableNewSetElement(){
        // Очистка Имени и Label
        $('#label_fields').val('');
        // Очищаем hidden поля
        $('.old-value-hidden').remove();
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

    // Кнопка отправки отредактираванного элемента
    function editNewSetElement(id_fields) {
        $('#label_set_elements').after('<input type="hidden" name="id_set_elements" value="'+ id_fields +'">');
        $('form').attr('action', '/addEditedNewSetElement');
    }

    // Удалить элемент
    function removeSetElement(id_fields) {

        $.ajax({
            type: "POST",
            url: "/removeSetElement",
            data: {id_fields: id_fields},
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function () {
                location.reload();
            }
        });
    }


// showForms //
// showForms //
// showForms //
// showForms //
// showForms //

    // Отображение содержимого формы (Просмотр списка форм) constructorForm
    $('.forms-info').on('click', function(){
        var id_forms = $(this).data("id");
        var id_forms_departments = $(this).data("idFormsDepartments");
        var generateString = $(this).data("generatestring")
        var contentForm = $('#content-form'+generateString);
        contentForm.empty();

        if ($(this).hasClass("collapsed")) {
            $.ajax({
                type: "POST",
                url: "getFormInfo",
                data: {id_forms: id_forms, id_forms_departments:id_forms_departments},
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (formsInfo) {

                    formsInfo.forEach(function (value, key, formsInfo) {
                        switchElements(contentForm, formsInfo, key, " ");
                    });
                }

            });
        }
    });

    // Отображение старого содержимого формы homeAdmin
    $('.forms-info-old').on('click', function() {
        var id_forms = $(this).data("id");
        var id_forms_departments = $(this).data("idFormsDepartments");
        var generateString = $(this).data("generatestring")
        var contentForm = $('#content-form-old'+generateString);
        contentForm.empty();

        if ($(this).hasClass("collapsed")) {
            $.ajax({
                type: "POST",
                url: "getFormInfoOld",
                data: {id_forms: id_forms, id_forms_departments:id_forms_departments},
                dataType: "JSON",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
                },
                success: function (formsInfo) {
                    console.log(formsInfo);
                    if (JSON.stringify(formsInfo) == "{}"){
                        contentForm.append('<p class="text-center"><b>Прошлая версия формы отсутствует !</b></p>');
                    } else {
                        formsInfo.forEach(function (value, key, formsInfo) {
                            switchElements(contentForm, formsInfo, key, "disabled");
                        });
                    }
                }
            });
        }
    });

    // Предпросмотр пустых форм
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
                success: function (data) {
                    // $('.input-id-form').val(formsInfo[0].id_forms);
                    var formsInfo = data;
                    console.log(data);
                    formsInfo.forEach(function (value, key, formsInfo) {
                        switchElements(contentForm, formsInfo, key);
                    });
                }

            });
        }
    });

    function switchElements(contentForm, formsInfo, key, disabled) {

        var required;
        if(formsInfo[key].required == true) {
            formsInfo[key].required = "*";
            required = "required";
        } else {
            formsInfo[key].required = "";
            required = "";
        }

        (disabled == 'disabled') ? formsInfo[key].id_fields_forms = " " : formsInfo[key].id_fields_forms;

        formsInfo[key].values_fields_current = (formsInfo[key].values_fields_current == null ) ? "" : formsInfo[key].values_fields_current;

        switch (formsInfo[key].name_elements) {

            case "input(text)":
                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b>');
                contentForm.append('<input type="text" class="form-control" name="' + formsInfo[key].id_fields_forms + '"' + required + ' value="'+formsInfo[key].values_fields_current+'"'+disabled+'><p></p>');
                break;

            case "input(email)":
                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b>');
                contentForm.append('<input type="email" class="form-control" name="' + formsInfo[key].id_fields_forms + '"' + required + ' value="'+formsInfo[key].values_fields_current+'"'+disabled+'><p></p>');
                break;

            case "textarea":
                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b>');
                contentForm.append('<textarea rows="3" class="form-control" name="' + formsInfo[key].id_fields_forms + '" style="resize: none;"' + required +disabled+'>'+formsInfo[key].values_fields_current+'</textarea><p></p>');
                break;

            case "radiobutton":

                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b><br>');
                var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
                var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

                contentForm.append('<input type="hidden" name="' + formsInfo[key].id_fields_forms + '" value="">');
                id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                    var show_empty_checkbox = true;
                    if (formsInfo[key].enum_sub_elements_current != 0) {
                        if (value_sub == formsInfo[key].enum_sub_elements_current) {

                            contentForm.append('<input type="radio" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + required + disabled+' checked > ' + label_sub_elements[key_value] + '</br>');
                            show_empty_checkbox = false;

                        }
                    }
                    if (show_empty_checkbox){
                        contentForm.append('<input type="radio" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + required + disabled+'> ' + label_sub_elements[key_value] + '</br>');
                    }
                });

                contentForm.append('<p></p>');
                break;

            case "checkbox":
                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b><br>');
                var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
                var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

                contentForm.append('<input type="hidden" name="' + formsInfo[key].id_fields_forms + '" value="">');
                id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                    var show_empty_checkbox = true;
                    if (formsInfo[key].enum_sub_elements_current != 0) {
                        if ($.isArray(formsInfo[key].enum_sub_elements_current)) {
                            var arr = formsInfo[key].enum_sub_elements_current;

                            arr.forEach(function(enum_sub_element, key_enum_sub_element, arr){
                                if (id_sub_element[key_value] == enum_sub_element) {

                                    contentForm.append('<input type="checkbox" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + required + disabled+' checked > ' + label_sub_elements[key_value] + '</br>');
                                    show_empty_checkbox = false;

                                }
                            });

                        } else {
                            if (id_sub_element[key_value] == formsInfo[key].enum_sub_elements_current) {

                                contentForm.append('<input type="checkbox" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + required + disabled+' checked > ' + label_sub_elements[key_value] + '</br>');
                                show_empty_checkbox = false;

                            }
                        }
                    }
                    if (show_empty_checkbox){
                        contentForm.append('<input type="checkbox" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + required + disabled+'> ' + label_sub_elements[key_value] + '</br>');
                    }
                });

                contentForm.append('<p></p>');
                break;

            case "select":
                contentForm.append('<b>' + formsInfo[key].required + '' + formsInfo[key].label_fields + '</b>');
                var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
                var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

                contentForm.append('<input type="hidden" name="' + formsInfo[key].id_fields_forms + '" value="">');
                contentForm.append('<select id="select' +key+ '" class="multiselect" name="' + formsInfo[key].id_fields_forms +"[]" + '" style="margin-left: 10px;" ' + required + disabled+'>');

                id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                    var show_empty_checkbox = true;
                    if (formsInfo[key].enum_sub_elements_current != 0) {
                        if (id_sub_element[key_value] == formsInfo[key].enum_sub_elements_current) {

                            $('#select' + key).append('<option value="' + id_sub_element[key_value] + '" selected>' + label_sub_elements[key_value] + '</option>');
                            show_empty_checkbox = false;
                        }
                    }
                    if (show_empty_checkbox){
                        $('#select' + key).append('<option value="' + id_sub_element[key_value] + '">' + label_sub_elements[key_value] + '</option>');
                    }
                });

                contentForm.append('<p></p>');
                break;
        }
    }


// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //


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
        var id_departments = $('#id_departments option:selected').val();
        
        $.ajax({
            type:"POST",
            url:"setTableDisconnectUsers",
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


// departments //
// departments //
// departments //
// departments //
// departments //


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
        success: function (data) {
            alert(data.message);
            if(data.bool) {
                location.reload();
            }
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


// homeAdmin //
// homeAdmin //
// homeAdmin //
// homeAdmin //
// homeAdmin //


// Список форм на проверку

// Кнопка принять
$('.btn-accept-form').on('click', function () {
    var id_forms_departments = $(this).data('idFormsDepartments');

    $.ajax({
        type:"POST",
        url:"acceptForm",
        data:{ id_forms_departments:id_forms_departments },
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

// Кнопка отклонить
$('.btn-reject-form').on('click', function () {
    var id_forms_departments = $(this).data('idFormsDepartments');

    $.ajax({
        type:"POST",
        url:"rejectForm",
        data:{ id_forms_departments:id_forms_departments },
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