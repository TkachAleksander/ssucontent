 
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

    // Страница newElement активное/неактивное поле 
    $(".select_labels").change(function(){
        $val = $('.select_labels :selected').val();
        if($val == 1 || $val == 2 || $val == 3){
            $('.textarea_sub_elements').val('');
            $('.textarea_sub_elements').attr('disabled', true);
        } else
            $('.textarea_sub_elements').attr('disabled', false);
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

    // Кнопка
    $('#getArray').on('click', function() {
        var name_forms = $('#name_forms').val();
        var queue = $('#sortContainer').sortable("toArray");

        $.ajax({
            type: "POST",
            url: "/addSetFormsElements",
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
    // Отображение содержимого формы (Просмотр списка форм constructorForm)

    $('.forms-info').on('click', function(){
        $id_forms = $(this).data("id");
        alert($id_forms);
    });

    $('.dellElementFromForm').on('click',function() {
        $id = $(this).attr('id');
        alert($id);
    });

