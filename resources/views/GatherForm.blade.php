@foreach($forms_info as $key_fi => $form_info)
<?php
        $required = ($form_info->required) ? '* ' : '';
        $label = '<strong><span class="red">'.$required.'</span>'.$form_info->label_fields.'</strong><br>';

        $id_fields_forms = $form_info->id_fields_forms;
        $value = (!empty($form_info->values_fields)) ? $form_info->values_fields : '';

        $labels_sub_elements = (!empty($form_info->labels_sub_elements)) ? explode(' | ', $form_info->labels_sub_elements) : '';
        $id_sub_elements = (!empty($form_info->id_sub_elements)) ? explode(' | ', $form_info->id_sub_elements) : '';

        if (isset($forms_info[$key_fi]->enum_sub_elements)){
            foreach ($forms_info[$key_fi]->enum_sub_elements as $key_ese => $enum_sub_element){
                $id_selected[$key_ese] = $enum_sub_element;
            }
        }

        $group = '<div class="'.$ver.'_group'.$id_fields_forms.' group">';
        $groupend = '</div>';

        $disabled = ($ver == 'old') ? 'disabled' : '';

        switch ($form_info->name_elements){
            case 'input(text)':
                echo $group;
                echo $label;
                echo '<input type="text" class="form-control" name="'.$id_fields_forms.'" value="'.$value.'" '.$disabled.'></input>';
                echo $groupend;
                break;

            case 'input(email)':
                echo $group;
                echo $label;
                echo '<input type="email" class="form-control" name="'.$id_fields_forms.'" value="'.$value.'" '.$disabled.'></input>';
                echo $groupend;
                break;

            case 'textarea':
                echo $group;
                echo $label;
                echo '<textarea class="form-control textarea-resize-none" name="'.$id_fields_forms.'" '.$disabled.'>'.$value.'</textarea>';
                echo $groupend;
                break;

            case 'checkbox':
                echo $group;
                echo $label;
                    foreach ($id_sub_elements as $key_ise => $id_sub_element){
                        $checked = '';
                        if (isset($id_selected)){
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_element){
                                $checked = 'checked';
                            }
                        }
                            }
                        echo '<label><input type="checkbox" name="'.$id_fields_forms.'[]" value="'.$id_sub_element.'"'.$checked.' '.$disabled.'> '.$labels_sub_elements[$key_ise].'</label><br> ';
                    }
                echo $groupend;
                break;

            case 'radiobutton':
                echo $group;
                echo $label;
                    foreach ($id_sub_elements as $key_ise => $id_sub_element){
                        $checked = '';
                        if (!empty($id_selected)){
                        foreach ($id_selected as $id_select){
                            if ($id_select == $id_sub_element){
                                $checked = 'checked';
                            }
                        }
                            }
                        echo '<label><input type="radio" name="'.$id_fields_forms.'[]" value="'.$id_sub_element.'"'.$checked.' '.$disabled.'> '.$labels_sub_elements[$key_ise].'</label><br> ';
                    }
                echo $groupend;
                break;

            case 'select':
                echo $group;
                echo $label.'<select name="'.$id_fields_forms.'[]" class="margin-left-10 multiselect" '.$disabled.'>';
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
                echo $groupend;
                break;
        }

?>
@endforeach
