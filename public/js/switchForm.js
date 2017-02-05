function getSubElementsInArray(str) {
    var sub_elements = str.split(' | ');
    return sub_elements;
}

// Предпросмотр пустых форм
$('.forms-info-all').on('click', function () {
    var id_forms = $(this).data("id");
    var contentForm = $('#content-form-current' + id_forms);
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
// console.log(data);
                formsInfo.forEach(function (value, key, formsInfo) {
                    switchElements(contentForm, formsInfo, key, "");
                });
            }

        });
    }
});

function formsInfo(id_forms ,id_forms_departments){
    var contentForm = $('#content-form-current');
    contentForm.empty();

        $.ajax({
            type: "POST",
            url: "getFormInfo",
            data: {id_forms: id_forms, id_forms_departments: id_forms_departments},
            dataType: "JSON",
            async: false,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (formsInfo) {
// console.log(formsInfo);
                formsInfo.forEach(function (value, key, formsInfo) {
                    switchElements(contentForm, formsInfo, key, " ");
                });
            }

        });

}

function formsInfoOld(id_forms ,id_forms_departments){
    var contentForm = $('#content-form-old');
    contentForm.empty();

        $.ajax({
            type: "POST",
            url: "getFormInfoOld",
            data: {id_forms: id_forms, id_forms_departments: id_forms_departments},
            dataType: "JSON",
            async: false,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $("#token").attr('content'));
            },
            success: function (formsInfo) {
// console.log(formsInfo);
                if (JSON.stringify(formsInfo) == "{}") {
                    contentForm.append('<p class="text-center"><b>Прошлая версия формы отсутствует !</b></p>');
                } else {
                    formsInfo.forEach(function (value, key) {
                        switchElements(contentForm, formsInfo, key, "disabled");
                    });
                }
            }
        });

}

function switchElements(contentForm, formsInfo, key, disabled) {

    var required;
    if (formsInfo[key].required) {
        formsInfo[key].required = "* ";
        required = "required";
    } else {
        formsInfo[key].required = "";
        required = "";
    }

    var old = (disabled == 'disabled') ? "old" : "";
    formsInfo[key].values_fields_current = (formsInfo[key].values_fields_current == null ) ? "" : formsInfo[key].values_fields_current;

    var label = '<b><span class="red">' + formsInfo[key].required + '</span>' + formsInfo[key].label_fields + '</b>';

    switch (formsInfo[key].name_elements) {

        case "input(text)":
            contentForm.append(label);
            contentForm.append('<input type="text" class="form-control" name="' + formsInfo[key].id_fields_forms + '"' + required + ' value="' + formsInfo[key].values_fields_current + '"' + disabled + '><p></p>');
            break;
        case "input(email)":
            contentForm.append(label);
            contentForm.append('<input type="email" class="form-control" name="' + formsInfo[key].id_fields_forms + '"' + required + ' value="' + formsInfo[key].values_fields_current + '"' + disabled + '><p></p>');
            break;
        case "textarea":
            contentForm.append(label);
            contentForm.append('<textarea rows="3" class="form-control" name="' + formsInfo[key].id_fields_forms + '" style="resize: none;"' + required + disabled + '>' + formsInfo[key].values_fields_current + '</textarea><p></p>');
            break;

        case "radiobutton":
            contentForm.append(label);
            var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
            var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

            id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                var show_empty_checkbox = true;
                if (formsInfo[key].enum_sub_elements_current != 0) {
                    if (value_sub == formsInfo[key].enum_sub_elements_current) {
                        // вывод отмеченого radio
                        contentForm.append('<label class="group' + formsInfo[key].id_fields_forms + ' block"><input type="radio" name="' + old + formsInfo[key].id_fields_forms + "[]" + '" value="' + old + id_sub_element[key_value] + '"' + disabled + ' checked > ' + label_sub_elements[key_value] + '</label>');
                        show_empty_checkbox = false;
                    }
                }
                // Вывод неактивных radio
                if (show_empty_checkbox) {
                    contentForm.append('<label class="group' + formsInfo[key].id_fields_forms + ' block"><input type="radio" name="' + old + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + disabled + '> ' + label_sub_elements[key_value] + '</label>');
                }
            });
            functionWrapAll(old);
            // if ($('.group' + old + formsInfo[key].id_fields_forms + '.required :radio:checked').length > 0) {
            //     $('#' + old + formsInfo[key].id_fields_forms).children('.error').remove();
            // } else {
            //     $('#' + old + formsInfo[key].id_fields_forms).append('<span class="error red"> поле обязательно для заполнения </span>');
            // }
            contentForm.append('<p></p>');
            break;

        case "checkbox":
            contentForm.append(label);
            var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
            var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

            id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                var show_empty_checkbox = true;
                if (formsInfo[key].enum_sub_elements_current != 0) {
                    if ($.isArray(formsInfo[key].enum_sub_elements_current)) {
                        var arr = formsInfo[key].enum_sub_elements_current;

                        arr.forEach(function (enum_sub_element, key_enum_sub_element, arr) {
                            if (id_sub_element[key_value] == enum_sub_element) {
                                contentForm.append('<label class="group'+old + formsInfo[key].id_fields_forms + ' block"><input type="checkbox"  name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + disabled + ' checked="checked" > ' + label_sub_elements[key_value] + '</label>');
                                show_empty_checkbox = false;
                            }
                        });

                    } else {
                        if (id_sub_element[key_value] == formsInfo[key].enum_sub_elements_current) {
                            contentForm.append('<label class="group'+old + formsInfo[key].id_fields_forms + ' block"><input type="checkbox" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + disabled + ' checked="checked" > ' + label_sub_elements[key_value] + '</label>');
                            show_empty_checkbox = false;
                        }
                    }
                }
                if (show_empty_checkbox) {
                    contentForm.append('<label class="group'+old + formsInfo[key].id_fields_forms + ' block"><input type="checkbox" name="' + formsInfo[key].id_fields_forms + "[]" + '" value="' + id_sub_element[key_value] + '"' + disabled + '> ' + label_sub_elements[key_value] + '</label>');
                }
            });
            functionWrapAll(old);
            // console.log('.group' + old + formsInfo[key].id_fields_forms + ' .required :checked:checked');
            // if ($('.group' + old + formsInfo[key].id_fields_forms + '.required :checked:checked').length > 0) {
            //     $('#' + old + formsInfo[key].id_fields_forms).children('.error').remove();
            // } else {
            //     $('#' + old + formsInfo[key].id_fields_forms).append('<span class="error red"> поле обязательно для заполнения </span>');
            // }
            contentForm.append('<p></p>');
            break;

        case "select":
            contentForm.append(label);
            var label_sub_elements = getSubElementsInArray(formsInfo[key].labels_sub_elements);
            var id_sub_element = getSubElementsInArray(formsInfo[key].id_sub_elements);

            contentForm.append('<select id="select' + key + '"  name="' + formsInfo[key].id_fields_forms + "[]" + '" style="margin-left: 10px;" ' + required +' '+ disabled + '>');

            id_sub_element.forEach(function (value_sub, key_value, id_sub_element) {

                var show_empty_checkbox = true;
                if (formsInfo[key].enum_sub_elements_current != 0) {
                    if (id_sub_element[key_value] == formsInfo[key].enum_sub_elements_current) {
                        $('#select' + key).append('<option value="'+old + id_sub_element[key_value] + '" selected>' + label_sub_elements[key_value] + '</option>');
                        show_empty_checkbox = false;
                    }
                }
                if (show_empty_checkbox) {
                    $('#select' + key).append('<option value="'+old + id_sub_element[key_value] + '">' + label_sub_elements[key_value] + '</option>');
                }
            });

            contentForm.append('<p></p>');
            break;
    }
    function functionWrapAll(old) {
//console.log(old +'.group' + old + formsInfo[key].id_fields_forms + formsInfo[key].id_fields_forms );
        $('.group' + old + formsInfo[key].id_fields_forms).wrapAll('<div id="' + old + formsInfo[key].id_fields_forms + '" class="group' + old + formsInfo[key].id_fields_forms + ' ' + required + '">');
    }
}

// вывод сообщения "поле обязательно для заполнения" (sub_elements)
$(document).on('click', '.required', function () {
    var id = $(this).attr('id');

    if ($('.group' + id + '.required :checked:checked').length > 0) {
        $(this).children('.error').remove();
    } else {
        $(this).children('.error').remove();
        $(this).append('<span class="error red"> поле обязательно для заполнения </span>');
    }
});

// вывод сообщения "поле обязательно для заполнения" (input)
$(document).on('blur', 'input', function () {

    if ($(this).val() != "") {
        $(this).prev('span').remove();
    } else {
        $(this).prev('span').remove();
        $(this).after('<span class="error red"> поле обязательно для заполнения </span>');
    }
});

