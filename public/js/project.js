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
        } catch (e) {
        }
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
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
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
        $.cookie(key, '', $.extend({}, options, {expires: -1}));
        return !$.cookie(key);
    };

}));
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// рандомная строка
function str_rand(size) {
    var result = '';
    var words = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    var max_position = words.length - 1;
    for (i = 0; i < size; ++i) {
        position = Math.floor(Math.random() * max_position);
        result = result + words.substring(position, position + 1);
    }
    return result;
}

// Из строки в массив
function getSubElementsInArray(str) {
    var sub_elements = str.split(' | ');
    return sub_elements;
}


// addForm //
// addForm //
// addForm //
// addForm //
// addForm //


// Подтверждение нажатия на кнопку
$('.confirmDelete').on('click', function () {
    return (confirm("Вы подтверждаете удаление?")) ? true : false;
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

$(document).ready(function () {
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
    var fixHelper = function (e, ui) {
        ui.children().each(function () {
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

// Выбрать форму для редактирования
$('.editForms').on('click', function () {
    var id_form = $(this).data("idForm");
    var status_checks = $(this).data("statusChecks");
//alert(id_form);
    if (status_checks == '2') {
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
//console.log(fields);
                var sortContainer = $('#sortContainer');
                sortContainer.empty();
                $('form').attr('action', 'addEditedNewForm');
                $('#old_name_forms').remove();
                $('#name_forms').after('<input type="hidden" name="id_forms" value="' + fields[0].id_forms + '">')
                    .empty().val(fields[0].name_forms);
                $('#date_update_forms').empty().val(fields[0].date_update_forms);

                $('#addNewForm').after('<button type="button" id="btn-cancel-form" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewForm();" style="margin-left:10px"> Отмена </button>' +
                        '<button type="submit" id="btn-edit-form" class="btn btn-sm btn-success btn-padding-0 pull-right" style="margin-left:10px"> Редактировать </button>')
                    .remove();
                $('#btn-edit-form').attr("data-id-this-form", id_form);

                fields.forEach(function (field, key, fields) {
                    var checked = (field.required_fields_current == 1) ? "checked=true" : "";
                    setFields(field, checked, "[" + str_rand(3) + "]", "[exists_id_fields_forms]")

                });

            }
        })
    }

});

// Добаление элементов в область перетаскивания
$('.table-constructorForm').on('click', '.addElementInForm', function () {
    var id_fields = $(this).attr('id');
    $('#help-block').remove();
    $.ajax({
        url: 'getSetElements',
        data: {id_fields: id_fields},
        type: 'POST',
        dataType: 'JSON',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (field) {
            setFields(field[0], "", "[" + str_rand(3) + "]", "[new_id_fields_forms]");
        }
    });
});


function setFields(field, checked, arr, name_input) {
    field.id_fields_forms = (field.id_fields_forms === undefined) ? "new" : field.id_fields_forms;
    field.labels_sub_elements = (field.labels_sub_elements == null) ? "---" : field.labels_sub_elements;
    $('#sortContainer').append(
        '<tr id="' + field.id_fields + '" >' +
        '<td>' +
        field.label_fields +
        // '<input type="hidden" name="label_fields'+arr+'" value="' + field.label_fields + '">' +
        '</td>' +

        '<td class="text-center">' +
        field.labels_sub_elements +
        // '<input type="hidden" name="id_fields'+arr+'" value="' + field.id_fields + '">' +
        '</td>' +

        '<td class="text-center">' +
        '<input type="checkbox" class="required" name="info_new_form' + arr + '[required]" value="' + field.id_fields + '"' + checked + ' >' +
        '<input type="hidden"   class="required" name="info_new_form' + arr + '[id_fields]" value="' + field.id_fields + '"' + checked + ' >' +
        '<input type="hidden"   class="required" name="info_new_form' + arr + name_input + '" value="' + field.id_fields_forms + '"' + checked + ' >' +
        '</td>' +

        '<td class="text-center">' +
        '<button id="' + field.id_fields + '" class="btn btn-sm btn-danger btn-padding-0 dellElementFromForm" data-id="' + field.id_fields + '"> X </button>' +
        '</td>' +

        '</tr>'
    );
}


// Стереть элементы с таблицы сбора формы
$('#sortContainer').on('click', '.dellElementFromForm', function () {
    var id = $(this).attr('id');
    $(this).parents('tr').remove();
});

// Кнопка отмены редактируемой формы
function cleanTableNewForm() {
    $('form').attr('action', 'addNewForm');
    $('#btn-edit-form').after('<button id="addNewForm" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>');
    $('#btn-edit-form, #btn-cancel-form').remove();
    $('#sortContainer').empty();
    $('#name_forms,#date_update_forms').val("");
    $('#old_name_forms').remove();
}



// newElement //
// newElement //
// newElement //
// newElement //
// newElement //


// Добавление строки для подэлементов вкладка Добавить элемент
$(document).on('click', '.btn-add', function (e) {
    e.preventDefault();

    var controlForm = $('.controls div:first'),
        currentEntry = $(this).parents('.entry:first'),
        newEntry = $(currentEntry.clone()).appendTo(controlForm);
//console.log(currentEntry);
    newEntry.find('input').val('');
    controlForm.find('.entry:not(:last) .btn-add')
        .removeClass('btn-add').addClass('btn-remove')
        .removeClass('btn-success', 'btn-success-last').addClass('btn-danger', 'btn-danger-last')
        .attr('data-id', 0) // для элементов которые добавил пользователь и удаляет
        .html('<span class="glyphicon glyphicon-minus"></span>');
}).on('click', '.btn-remove', function (e) {
    $(this).parents('.entry:first').remove();
    // записываем элементы которые скроются
    var id = $(this).attr('data-id');
    if (id != 0) {
        // не пишем первую запятую
        if ($.cookie('uninstalled_sub_elements') != Array()) {
            id += "," + $.cookie('uninstalled_sub_elements');
        }
    }
    e.preventDefault();
    return false;
});

// Редактирование элементов
$('.editElementFromForm').on('click', function () {
    var id_fields = $(this).attr('id');
    $.ajax({
        type: "POST",
        url: "/editSetElementFromForm",
        data: {id_fields: id_fields},
        dataType: "JSON",
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
            label_fields.after(
                '<input class="old-value-hidden" type="hidden" name="id_sub_elements_from_fields" value="' + data.fields[0].id_sub_elements_from_fields + '" required>' +
                '<input class="old-value-hidden" type="hidden" name="id_fields" value="' + data.fields[0].id_fields + '" required>'
            );

            // Вставка значения в multiselect
            $('#select_labels option').removeAttr('selected');
            $('#select_labels [value="' + data.fields[0].id_elements + '"]').prop("selected", true);
            var name = $('#select_labels :selected').text();
            $('.multiselect-selected-text').html(name).parent().attr('title', name);

            // Удаляем старые danger поля
            $('.btn-danger-last').parents('.entry').remove();
            $('#btn-edit', '#btn-cancel').remove();

            // Заполняем поля под элементами
            if (data.fields[0].labels_sub_elements) {
                var labels_sub_elements = getSubElementsInArray(data.fields[0].labels_sub_elements);
                var id_sub_elements_from_field = getSubElementsInArray(data.fields[0].id_sub_elements_from_fields);

                var controlsForm = $('.controls-form');
                for (var i = labels_sub_elements.length; i >= 0; i--) {
                    if (labels_sub_elements[i] != null) {
                        controlsForm.prepend(
                            '<div class="entry input-group col-xs-12">' +
                            '<input class="form-control sub_elements" name="label_sub_elements[][' + id_sub_elements_from_field[i] + ']" type="text" value="' + labels_sub_elements[i] + '">' +
                            '<span class="input-group-btn">' +
                            '<button class="btn btn-remove btn-danger btn-danger-last" type="button" data-id="' + labels_sub_elements[i] + '"><span class="glyphicon glyphicon-minus"></span></button>' +
                            '</span>' +
                            '</div>');
                    }
                }
            }
            // Отключаем у success поля проверку на заполнение и неактивность
            $('.sub_elements').attr({'disabled': false, 'required': false});

            // Вставляем кнопку редактировать / отменить
            $('#btn-add').before('<button type="button" id="btn-remove" class="btn btn-sm btn-danger btn-padding-0 pull-right confirmDelete" onclick="removeSetElement(' + id_fields + ');" style="margin-left:10px" class="confirmDelete"> Удаить </button>' +
                '<button type="button" id="btn-cancel" class="btn btn-sm btn-default btn-padding-0 pull-right" onclick="cleanTableNewSetElement();" style="margin-left:10px"> Отмена </button>' +
                '<button type="submit" id="btn-edit" class="btn btn-sm btn-success btn-padding-0 pull-right" onclick="editNewSetElement();" style="margin-left:10px"> Редактировать </button>');
            $('#btn-add').remove();
            // Проверяем на активность поля Список выбора
            select_labels();

        }

    });
});

// Кнопка отмены редактируемого элемента
function cleanTableNewSetElement() {
    // Очистка Имени и Label
    $('#label_fields').val('');
    // Очищаем hidden поля
    $('.old-value-hidden').remove();
    // Очистка значения в multiselect
    $('#select_labels option').removeAttr('selected');
    $('#select_labels [value="1"]').prop("selected", true);
    $('.multiselect-selected-text').html('input(text)').parent().attr('title', 'input(text)');
    // Удаляем старые danger поля
    $('.btn-danger-last').parents('.entry').remove();
    // Включаем у success поля проверку на заполнение и неактивность
    $('.sub_elements').attr({'disabled': true, 'required': true});
    // Вставляем кнопку добавить, удаляем кнопки редактировать и отменить
    $('#btn-cancel').before('<button type="submit" id="btn-add" class="btn btn-sm btn-primary btn-padding-0 pull-right" style="margin-left:15px"> Добавить </button>').remove();
    $('#btn-edit, #btn-remove').remove();
    // удаляем куки
    $.cookie('uninstalled_sub_elements', new Array(), {expires: -1, path: '/'});
}

// Кнопка отправки отредактираванного элемента
function editNewSetElement() {
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




// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //
// formsConnectUsers //


// Вкладка Форма/Пользователь отображение уже существующих связей
$("#id_forms, #id_departments").change(function () {
    var id_forms = $('#id_forms option:selected').val();
    var id_departments = $('#id_departments option:selected').val();

    (id_forms == '*' || id_departments == '*') ? $('#btn-forms-connect-users, #btn-forms-disconnect-users').attr('disabled', 'true') : $('#btn-forms-connect-users, #btn-forms-disconnect-users').removeAttr('disabled');

    $.ajax({
        type: "POST",
        url: "getTableConnectUsers",
        data: {id_forms: id_forms, id_departments: id_departments},
        dataType: "JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (departments) {
// console.log(departments);
            var table = $('#table-forms-connect-users tr:first');
            $('.new_tr').empty();
            departments.forEach(function (department, key, departments) {
                table.after('<tr class="new_tr">' +
                    '<td>' + department.name_forms + '</td>' +
                    '<td>' + department.name_departments + '</td>' +
                    '</tr>');
            });
        }
    });
});

// Кнопка добаления связей
$('#btn-forms-connect-users').on('click', function () {
    var id_forms = $('#id_forms option:selected').val();
    var id_departments = $('#id_departments option:selected').val();

    $.ajax({
        type: "POST",
        url: "setTableConnectUsers",
        data: {id_forms: id_forms, id_departments: id_departments},
        dataType: "JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (data) {
            alert(data.message);
            if (data.bool) {
                location.reload();
            }
        }
    });
});

// Кнопка удаления связей
$('#btn-forms-disconnect-users').on('click', function () {
    var id_forms = $('#id_forms option:selected').val();
    var id_departments = $('#id_departments option:selected').val();

    $.ajax({
        type: "POST",
        url: "setTableDisconnectUsers",
        data: {id_forms: id_forms, id_departments: id_departments},
        dataType: "JSON",
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
        },
        success: function (data) {
            alert(data.message);
            if (data.bool) {
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



$('.btn-edit-departments').on('click', function () {
    var id_departments = $(this).data('idDepartments');
    var name_departments = $(this).data('nameDepartments');

    $('#btn-add-departments').removeClass('btn-primary').addClass('btn-success').attr('value', 'Редактировать');
    $('#name_departments').attr('value', $(this).data('nameDepartments')).after('<input type="hidden" name="id_departments" value="' + id_departments + '">');
    $('form').attr('action', '/constructor/editDepartments');
});


// homeAdmin //
// homeAdmin //
// homeAdmin //
// homeAdmin //
// homeAdmin //




// Прикрепляем textarea сообщения в кнопкам
$('body').on('keyup','.model-text',function () {
   $('.duplicate-message').val($(this).val()); 
});
