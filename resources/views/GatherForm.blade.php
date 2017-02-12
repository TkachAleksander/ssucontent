@foreach($forms_info as $key_fi => $form_info)
<?php
        $star = ($form_info->required) ? '* ' : '';
        $label = '<label class="'.$form_info->name_elements.'"><span class="red">'.$star.'</span><strong>'.$form_info->label_fields.'</strong></label>';

        $id_fields_forms = $form_info->id_fields_forms;
        $value = (!empty($form_info->values_fields)) ? $form_info->values_fields : '';

        $labels_sub_elements = (!empty($form_info->labels_sub_elements)) ? explode(' | ', $form_info->labels_sub_elements) : '';
        $id_sub_elements = (!empty($form_info->id_sub_elements)) ? explode(' | ', $form_info->id_sub_elements) : '';

        if (isset($forms_info[$key_fi]->enum_sub_elements)){
            foreach ($forms_info[$key_fi]->enum_sub_elements as $key_ese => $enum_sub_element){
                $id_selected[$key_ese] = $enum_sub_element;
            }
        }

        $required = ($form_info->required) ? 'required' : '';
        $control_group = ($required != '') ? 'control-group' : '';

        $wrapper_top = '<div class="'.$control_group.' group" >'.$label.'<div class="controls'.$ver.'">';
        $wrapper_down = '<p class="help-block"></p></div></div>';

        $disabled = ($ver == 'old') ? 'disabled' : '';

        switch ($form_info->name_elements){

            case 'input(text)':
                echo $wrapper_top;
                echo '<input type="text" class="form-control" name="'.$id_fields_forms.'" value="'.$value.'" '.$disabled.' '.$required.'></input>';
                echo $wrapper_down;
                break;

            case 'input(email)':
                echo $wrapper_top;
                echo '<input type="email" class="form-control" name="'.$id_fields_forms.'" value="'.$value.'" '.$disabled.' '.$required.'></input>';
                echo $wrapper_down;
                break;

            case 'textarea':
                echo $wrapper_top;
                echo '<textarea class="form-control textarea-resize-none" name="'.$id_fields_forms.'" '.$disabled.' '.$required.'>'.$value.'</textarea>';
                echo $wrapper_down;
                break;

            case 'checkbox':
                echo $wrapper_top;
                    if (isset($id_selected) && isset($id_sub_elements[0])){
                        $checked = '';
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_elements[0]){
                                $checked = 'checked';
                            }
                        }

                    echo '<label class="checkbox"><input type="checkbox" name="'.$id_fields_forms.'[]" value="'.$id_sub_elements[0].'"'.$checked.' '.$disabled.' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="Поле обязательно к заполнению"> '.$labels_sub_elements[0].'</label>';
                    array_shift($id_sub_elements);
                    }

                    foreach ($id_sub_elements as $key_ise => $id_sub_element){
                        $checked = '';
                        if (isset($id_selected)){
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_element){
                                $checked = 'checked';
                            }
                        }
                            }
                        echo '<label class="checkbox"><input type="checkbox" name="'.$id_fields_forms.'[]" value="'.$id_sub_element.'"'.$checked.' '.$disabled.'> '.$labels_sub_elements[$key_ise+1].'</label>';
                    }
                echo $wrapper_down;
                break;

            case 'radio':
                echo $wrapper_top;
                if (isset($id_selected) && isset($id_sub_elements[0])){
                    $checked = '';
                    foreach ($id_selected as $id_select){
                        if ($id_select == $id_sub_elements[0]){
                            $checked = 'checked';
                        }
                    }

                    echo '<label class="radio"><input type="radio" name="'.$id_fields_forms.'[]" value="'.$id_sub_elements[0].'"'.$checked.' '.$disabled.' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="Поле обязательно к заполнению"> '.$labels_sub_elements[0].'</label> ';
                    array_shift($id_sub_elements);
                }
                    foreach ($id_sub_elements as $key_ise => $id_sub_element){
                        $checked = '';
                        if (!empty($id_selected)){
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_element){
                                $checked = 'checked';
                            }
                        }
                            }
                        echo '<label class="radio"><input type="radio" name="'.$id_fields_forms.'[]" value="'.$id_sub_element.'"'.$checked.' '.$disabled.'> '.$labels_sub_elements[$key_ise+1].'</label> ';
                    }
                echo $wrapper_down;
                break;

            case 'select':
                echo $wrapper_top;
                echo '<select name="'.$id_fields_forms.'[]" class="margin-left-10 multiselect" '.$disabled.'>';
                    foreach ($id_sub_elements as $key_ise => $id_sub_element){
                        $checked = '';
                        if (isset($id_selected)){
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_element){
                                $checked = 'selected';
                            }
                        }
                            }
                        echo '<option '.$checked.' value="'.$id_sub_element.'"> '.$labels_sub_elements[$key_ise].'</option>';
                    }
                echo '</select>';
                echo $wrapper_down;
                break;
        }

?>
@endforeach
