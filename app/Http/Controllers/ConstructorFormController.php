<?php

namespace App\Http\Controllers;

use App;

use App\Department;
use App\Field;
use App\Fields_form;
use App\Fields_forms_current;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Illuminate\Support\Facades\Auth;

class ConstructorFormController extends Controller
{
    const RADIOBUTTON = "radiobutton";
    const CHECKBOX = "checkbox";
    const OPTION = "option";


    // addForm //
    // addForm //
    // addForm //
    // addForm //
    // addForm //


    // Вывод страницы addForm
    public function addForm()
    {
        $fields = DB::table('fields as f')
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->groupBy('f.id_fields'/*,'sef.id_sub_elements_field'*/)
            ->select(DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();

        $forms = DB::table('forms_departments as fd')
            ->leftJoin('forms', 'forms.id_forms', '=', 'fd.id_forms')
            ->groupBy('fd.id_forms')
            ->select(DB::raw('group_concat(fd.id_status_checks separator " | ") as id_status_checks'), 'forms.id_forms', 'forms.name_forms', 'fd.id_forms_departments')
            ->get();

        foreach ($forms as $key=>$form){
            $status_checks = explode(" | ", $form->id_status_checks);
            foreach($status_checks as $status){
                if($status == '2'){
                    $forms[$key]->id_status_checks = '2';
                }
            }
        }

        return view('constructor.addForm', ['fields' => $fields, 'forms' => $forms]);
    }

    // Кнопка перемещения fields в форму
    public function getSetElements(Request $request)
    {
        $fields = DB::table('fields as f')
            ->where('f.id_fields','=',$request->input('id_fields'))
            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields','=','f.id_fields')
            ->select('f.id_fields','f.label_fields','e.name_elements','sef.id_sub_elements_field')
            ->get();

            if ($fields[0]->id_sub_elements_field != null) {
                $label_sub_elements = DB::table('sub_elements_current as sec')
                    ->where('id_sub_elements_field', '=', $fields[0]->id_sub_elements_field)
                    ->pluck('label_sub_elements_current');
                $label_sub_elements_current = implode(" | ", $label_sub_elements);
                $fields[0]->labels_sub_elements = $label_sub_elements_current;
            } else {
                $fields[0]->labels_sub_elements = "---";
            }

        return response()->json($fields);
    }

    // Кнопка добавления новой формы
    public function addSetFormsElementsToServer(Request $request)
    {
        if ($request->input('name_forms') != null && $request->input('id_fields') != null && $request->input('date_update_forms') != null) {
            $all_old_forms = DB::table('forms')->get();

            // Если новая форма имееи такое же имя и не такой ид формы
            // Обрываем выполнение метода и выводим алерт с ошибкой
            foreach ($all_old_forms as $old_form) {
                if ($old_form->name_forms == $request->input('name_forms') && $old_form->id_forms != $request->input('id_form')) {
                    return response()->json(['message' => 'Форма с таким именем уже существует. Пожалуйста измените имя формы.', 'bool' => false]);
                }
            }

            $this->validate($request, [
                'name_forms' => 'required|max:255|unique:forms',
                'date_update_forms' => 'required'
            ],
                [
                    'required' => 'Поле обязательно для заполнения',
                    'max' => 'Поле должно содержать максимум :max символов',
                    'unique' => 'Такая форма уже существует',
                ]);
            $id_forms = DB::table('forms')->insertGetId(['name_forms' => $request->input('name_forms'), 'date_update_forms' => $request->input('date_update_forms')]);

            foreach ($request->input('id_fields') as $key => $id_field) {

                // Узнаем required
                $require = false;
                if (!empty($request->input('required'))) {
                    foreach ($request->input('required') as $required) {
                        if ($required == $id_field) {
                            $require = true;
                        }
                    }
                }
                $id_fields_forms = DB::table('fields_forms')->insertGetId([
                    'id_forms' => $id_forms,
                    'id_fields' => $id_field
                ]);
                Fields_forms_current::create([
                    'id_fields_forms' => $id_fields_forms,
                    'required_fields_current' => $require
                ]);
            }
            return response()->json(['message' => 'Форма успешно добавлена !', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Форма заполнена неверно !', 'bool' => false]);
        }
    }

    // Кнопка добавления формы на редактирование
    public function editForm(Request $request)
    {
        $fields = DB::table('fields_forms as ff')
            ->where('ff.id_forms','=',$request->input('id_form'))
            ->join('fields as f', 'f.id_fields','=','ff.id_fields')
            ->join('forms', 'forms.id_forms','=','ff.id_forms')
            ->join('elements as e', 'e.id_elements','=','f.id_elements')
            ->join('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields','=','f.id_fields')
            ->orderBy('ffc.id_fields_forms_current')
            ->select('ff.id_fields_forms', 'f.id_fields', 'ffc.required_fields_current', 'forms.name_forms', 'forms.date_update_forms', 'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();
//dd($request->all(),$fields);
        foreach ($fields as $key => $field) {
            if ($field->id_sub_elements_field != null) {
                $label_sub_elements = DB::table('sub_elements_current as sec')
                    ->where('id_sub_elements_field', '=', $field->id_sub_elements_field)
                    ->pluck('label_sub_elements_current');
                $label_sub_elements_current = implode(" | ", $label_sub_elements);
                $fields[$key]->labels_sub_elements = $label_sub_elements_current;
            } else {
                $fields[$key]->labels_sub_elements = "---";
            }
        }

        return response()->json($fields);
    }

    // Кнопка удаления формы
    public function removeFormsToServer(Request $request){

        DB::table('forms')->where('id_forms','=',$request->input('id_forms'))->delete();

        return response()->json();
    }

    // Кнопка редактирования уже сужествуещей формы
    public function addEditedNewForm(Request $request)
    {
//dd($request->all());
        $id_form = $request->input('id_form');


        // Получаем все старые формы
        $all_old_forms = DB::table('forms')->get();

        // Если новая форма имееи такое же имя и не ее ид формы
        // Обрываем выполнение метода и выдаем алерт с ошибкой
        foreach($all_old_forms as $old_form){

            if($old_form->name_forms == $request->input('name_forms') && $old_form->id_forms != $request->input('id_form')){
                return response()->json(['message' => 'Форма с таким именем уже существует. Пожалуйста измените имя формы.', 'bool' => false]);
            }
        }
        // Апдейтим имя и дату формы
        DB::table('forms')->where('id_forms','=',$request->input('id_form'))->update(['name_forms' => $request->input('name_forms'), 'date_update_forms' => $request->input('date_update_forms')]);

        // Узнаем id_fields_forms которые нужно удалить из таблицы fields_forms_current
        $id_fields_forms = DB::table('fields_forms')
            ->where('id_forms','=', $id_form)
            ->pluck('id_fields_forms');


        // Удаляем старые поля из current
        foreach ($id_fields_forms as $id_field_form){
            DB::table('fields_forms_current')
                ->where('id_fields_forms','=', $id_field_form)
                ->delete();
        }


        foreach ($request->input('id_fields') as $id_field){
            // Добавляем fields_forms поля к редактируемой фрме если таких раньше не было
            $isset = DB::table('fields_forms')
                ->where('id_forms','=', $id_form)
                ->where('id_fields','=', $id_field)
                ->get();

            if(!$isset){
                DB::table('fields_forms')
                    ->insert([
                        'id_forms' => $id_form,
                        'id_fields' => $id_field
                    ]);
            }

            // Узнаем required поля
            $required = 0;
            if(!empty($request->input('required'))) {
                foreach ($request->input('required') as $r) {
                    if ($r == $id_field) {
                        $required = 1;
                    }
                }
            }

            // Берем id_fields_forms нового поля для current
            $id_fields_forms = DB::table('fields_forms')
                ->where('id_forms','=', $id_form)
                ->where('id_fields','=', $id_field)
                ->value('id_fields_forms');

            // Добавляем новое поле
            DB::table('fields_forms_current')
                ->insert([
                    'id_fields_forms' => $id_fields_forms,
                    'required_fields_current' => $required,
                    'queue_form_fields_current' => 100
                ]);

        }

        // Берем все id_fields_forms для редактируемой таблицы
        $id_fields_forms = DB::table('fields_forms')
            ->where('id_forms','=',$id_form)
            ->pluck('id_fields_forms');


        // удаляем его из таблтцы fields_forms
        foreach ($id_fields_forms as $id_field_form){

            // Ищем id_fields_forms в таблице fields_forms_current
            $isset_field_current = DB::table('fields_forms_current')
                ->where('id_fields_forms','=', $id_field_form)
                ->get();

            // Ищем id_fields_forms в таблице fields_forms_old
            $isset_field_old = DB::table('fields_forms_old')
                ->where('id_fields_forms','=', $id_field_form)
                ->get();

            // Если отсутствует в обоих таблицах удаляем id_fields_forms из таблицы fields_forms
            if(!$isset_field_current && !$isset_field_old){
                DB::table('fields_forms')
                    ->where('id_fields_forms','=',$id_field_form)
                    ->delete();
            }

            // Если отсутствует fields_forms_current удаляем id_fields_forms из таблицы id_values_fields_current
            if(!$isset_field_current){
                DB::table('values_fields_current')
                    ->where('id_fields_forms','=',$id_field_form)
                    ->delete();
            }

        }

        return response()->json(['message' => 'Форма успешно отредактирована!', 'bool' => true]);
    }


    // newElement //
    // newElement //
    // newElement //
    // newElement //
    // newElement //


    public function generateString($length = 8)
    {
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    // Вывод всех fields и elements для multiselect
    public function newElement()
    {
        $elements = DB::table('elements')
            ->orderBy('id_elements')
            ->get();
        $fields = DB::table('fields as f')
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->groupBy('f.id_fields')
            ->select(DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();
        
        return view('constructor.newElement', ['elements' => $elements, 'fields' => $fields]);
    }

    // Кнопка редактирования fields
    public function editSetElementFromForm(Request $request)
    {
        $fields = DB::table('fields as f')->where('f.id_fields','=',$request->input('id_fields'))
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->groupBy('f.id_fields')
            ->select(DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),'f.id_fields', 'f.label_fields', 'e.id_elements', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();

//dd($fields);
        return response()->json(['fields' => $fields]);
    }

    // Добавление нового fields
    public function addNewElementToServer(Request $request)
    {
        $this->validate($request, [
            'label_fields' => 'required|max:255|unique:fields',
        ],
            [
                'required' => 'Поле обязательно для заполнения',
                'max' => 'Поледолжно содержать максимум :max символов',
                'unique' => 'Такоe поле уже существует',
            ]);

        Field::create([
            'id_elements' => $request->input('id_elements'),
            'label_fields' => $request->input('label_fields')
        ]);

        $id_field = DB::table('fields')->max('id_fields');

        // Проверяем есть ли label_sub_elements
        if (!empty($request->input('label_sub_elements'))) {

            // Если есть записываем в таблицу sub_elements_fields: id_fields узнаем id под которым он записан
            $id_sub_elements_field = DB::table('sub_elements_fields')->insertGetId(['id_fields' => $id_field]);

            // Записываем label_sub_elements с полученым id_sub_elements_field в таблицу sub_elements_current
            foreach ($request->input('label_sub_elements') as $label_sub_element){
                DB::table('sub_elements_current')->insert(['id_sub_elements_field' => $id_sub_elements_field, 'label_sub_elements_current' => $label_sub_element]);
            }
        }

        return redirect('/constructor/newElement');
    }


    // Запись отредактированного fields
    public function addEditedNewSetElement(Request $request)
    {
//dd($request->all());
        // Ни одна форма в составе которой есть редактируемое поле не должна иметь статус 2 (проверяется администратором)
        $list_forms = DB::table('fields as f')
            ->where('f.id_fields','=',$request->input('id_edit_fields'))
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->join('forms_departments as fd', 'fd.id_forms','=','ff.id_forms')
            ->join('forms', 'forms.id_forms','=','fd.id_forms')
            ->join('departments as d', 'd.id_departments','=','fd.id_departments')
            ->where('fd.id_status_checks','=',2)
            ->select('f.label_fields','forms.name_forms')
            ->distinct()
            ->get();
        // Получаем old_id_elements и old_label_fields
        $old_value = DB::table('fields')
            ->where('id_fields','=',$request->input('id_fields'))
            ->select('id_elements','label_fields')
            ->get();

//dd($list_forms,$old_value,$id_sub_elements_field);

        // Вывод списка форм какие нужно принять\отклонить
        if(!empty($list_forms)){
            $message = 'Вы не можете редактировать '.$list_forms[0]->label_fields.' пока в вашем "Списке форм на проверку" есть формы которые содержат его. Примите или отклоните: ';
            foreach ($list_forms as $key_list => $form){
                $message .= $form->name_forms." ";
            }
            $message .= " для всех отделов.";
            $this->validate($request, [
                'list_forms_departments' => 'required',
            ],
                [
                    'required' => $message,
                ]);
            return redirect('/constructor/newElement');
        } else {

            // Для все форм/отделов изменяемое поле current записываем в таблицу sub_elements_old
            $list_departments = DB::table('fields as f')
                ->where('f.id_fields', '=', $request->input('id_edit_fields'))
                ->leftJoin('fields_forms as ff', 'ff.id_fields', '=', 'f.id_fields')
                ->leftJoin('forms_departments as fd', 'fd.id_forms', '=', 'ff.id_forms')
                ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
                ->leftJoin('sub_elements_current as sec', 'sec.id_sub_elements_field', '=', 'sef.id_sub_elements_field')
                ->select('fd.id_forms_departments', 'ff.id_fields', 'sef.id_sub_elements_field', 'ff.id_fields_forms', 'sec.label_sub_elements_current')
                ->get();

//dd($list_departments, $list_departments[0]->id_sub_elements_field );
            // Если поле имеет sub_elements записываем их
            if (!empty($list_departments) && $list_departments[0]->id_sub_elements_field != null) {
                // Удаляем старые sub_elements
                foreach ($list_departments as $department) {
                    DB::table('sub_elements_old')
                        ->where('id_sub_elements_field', '=', $department->id_sub_elements_field)
                        ->where('id_fields_forms', '=', $department->id_fields_forms)
                        ->where('id_forms_departments', '=', $department->id_forms_departments)
                        ->delete();
                }
                // Добавляем новые sub_elements в sub_elements_old
                foreach ($list_departments as $department) {
                    DB::table('sub_elements_old')->insert([
                        'id_sub_elements_field' => $department->id_sub_elements_field,
                        'id_fields_forms' => $department->id_fields_forms,
                        'id_forms_departments' => $department->id_forms_departments,
                        'label_sub_elements_old' => $department->label_sub_elements_current
                    ]);
                }
            }

            // Добавляем старое имя и ид элемента в таблицу fields
            DB::table('fields as f')
                ->where('f.id_fields', '=', $request->input('id_fields'))
                ->update([
                    'id_elements_old' => $old_value[0]->id_elements,
                    'label_fields_old' => $old_value[0]->label_fields,
                    'id_elements' => $request->input('id_elements'),
                    'label_fields' => $request->input('label_fields')
                ]);

//dd($request->all());
            if ($request->input('id_elements') >= 3) {

                // Получаем id_sub_elements_field
                $id_sub_elements_field = DB::table('fields as f')
                    ->where('f.id_fields', '=', $request->input('id_fields'))
                    ->join('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
                    ->value('id_sub_elements_field');

                // Если есть id_sub_elements_field удаляем из таблицы sub_elements_current старые данные
                // и записываем новые label sub_elements
                if ($id_sub_elements_field != null) {
                    DB::table('sub_elements_current')->where('id_sub_elements_field', '=', $id_sub_elements_field)->delete();

                    if ($request->input('label_sub_elements') != null) {
                        foreach ($request->input('label_sub_elements') as $label_sub_element) {
                            if (!empty($label_sub_element)) {
                                DB::table('sub_elements_current')
                                    ->insert([
                                        'id_sub_elements_field' => $id_sub_elements_field,
                                        'label_sub_elements_current' => $label_sub_element
                                    ]);
                            }
                        }
                    }
                } else {

                    // Записываем id_fields если такого нет в sub_elements_fields
                    $id_sub_elements_field = DB::table('sub_elements_fields')
                        ->where('id_fields','=',$request->input('id_fields'))
                        ->value('id_sub_elements_field');

                    if (empty($id_sub_elements_field)){
                        $id_sub_elements_field = DB::table('sub_elements_fields')
                            ->insertGetId([
                                'id_fields' => $request->input('id_fields')
                            ]);
                    }
//                    dd($request->input('id_fields'),$id_sub_elements_field);

//                    // Узнаем $id_sub_elements_field записанного id_fields
//                    $id_sub_elements_field = DB::table('sub_elements_fields as sef')
//                        ->where('sef.id_fields', '=', $request->input('id_fields'))
//                        ->value('id_sub_elements_field');

                    // Если есть $label_sub_element записываем в sub_elements_current
                    if ($request->input('label_sub_elements') != null) {
                        foreach ($request->input('label_sub_elements') as $label_sub_element) {
                            if (!empty($label_sub_element)) {
                                DB::table('sub_elements_current')
                                    ->insert([
                                        'id_sub_elements_field' => $id_sub_elements_field,
                                        'label_sub_elements_current' => $label_sub_element
                                    ]);
                            }
                        }
                    }
                }
            } else {
                $value = DB::table('fields_forms')
                    ->where('id_fields','=',$request->input('id_fields'))
                    ->value('id_fields_forms');

                if (empty($value)){
                    DB::table('sub_elements_fields')
                        ->where('id_fields','=',$request->input('id_fields'))
                        ->delete();
                }
            }
        }
        return redirect('/constructor/newElement');
    }

    // Кнопка удаления fields
    public function removeSetElement(Request $request)
    {
        DB::table('fields')->where('id_fields', '=', $request->input('id_fields'))->delete();
        return response()->json();
    }


    // showForms //
    // showForms //
    // showForms //
    // showForms //
    // showForms //


    // Вывод списка форм на страницу
    public function showForms()
    {
        $forms = DB::table('forms')->get();
        return view('constructor.showForms', ['forms' => $forms]);
    }

    // Вывод содержимого формы без значений
    public function getFormInfoAll(Request $request)
    {
        $form_info = DB::table('fields as f')
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$request->input('id_forms'))
            ->join('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->orderBy('ffc.id_fields_forms_current')
            ->groupBy('f.id_fields')
            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field', 'ffc.required_fields_current as required', DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'), DB::raw('group_concat(sec.id_sub_elements_current separator " | ") as id_sub_elements'))
            ->get();

        return response()->json($form_info);
    }

    // Вывод содержимого формы со значениями
    public function getFormInfo(Request $request)
    {
        // Узнаем список полей формы
        $form_infos = DB::table('fields as f')
            ->leftJoin('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$request->input('id_forms'))
            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec', 'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->leftJoin('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->orderBY('ffc.id_fields_forms_current')
            ->groupBy('f.id_fields')
            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field',
                DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),
                DB::raw('group_concat(sec.id_sub_elements_current separator " | ") as id_sub_elements'))
            ->get();

        // Узнаем id_forms_departments
        $id_forms_departments = DB::table('forms_departments')
            ->where('id_forms','=',$request->input('id_forms'))
            ->where('id_departments','=', Auth::user()->id_departments)
            ->value('id_forms_departments');

//dd($form_infos, $id_forms_departments);

        // Для каждого поля и массива $form_infos ищем значения
        foreach ($form_infos as $key => $form_info) {

            // Выбираем значения (values_fields_current,enum_sub_elements_current) из таблицы values_fields_current
            $values = DB::table('values_fields_current')
                ->where('id_fields_forms', '=', $form_info->id_fields_forms)
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->select('values_fields_current', 'enum_sub_elements_current')
                ->get();
//dd($form_infos, $values);

            // Если значения есть
            if(!empty($values)) {
                // Добавляем в массив $form_infos values_fields_current
                $form_infos[$key]->values_fields_current = $values[0]->values_fields_current;

                // enum_sub_elements_current добавляем массивом
                foreach ($values as $key_value => $value) {
                    $form_infos[$key]->enum_sub_elements_current[$key_value] = $values[$key_value]->enum_sub_elements_current;
                }
            // Если значений нет
            } else {
                // Выбираем значения (values_fields_old,enum_sub_elements_old) из таблицы values_fields_old
                $values = DB::table('values_fields_old')
                    ->where('id_fields_forms', '=', $form_info->id_fields_forms)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->select('values_fields_old', 'enum_sub_elements_old')
                    ->get();

                // Если значения есть
                if(!empty($values)) {
                    // Добавляем в массив $form_infos values_fields_current
                    $form_infos[$key]->values_fields_current = $values[0]->values_fields_old;

                    // enum_sub_elements_current добавляем массивом
                    foreach ($values as $key_value => $value) {
                        $form_infos[$key]->enum_sub_elements_current[$key_value] = $values[$key_value]->enum_sub_elements_old;
                    }
                }
            }
        }
//dd($form_infos);
        return response()->json($form_infos);
    }

//    public function getFormInfoOld(Request $request)
//    {
//        $form_infos = DB::table('fields as f')
//            ->leftJoin('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
//            ->where('ff.id_forms','=',$request->input('id_forms'))
//            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
//            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
//            ->leftJoin('sub_elements_old as seo' ,'seo.id_sub_elements_field','=','sef.id_sub_elements_field')
//            ->groupBy('f.id_fields')
//            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field',
//                DB::raw('group_concat(seo.label_sub_elements_old separator " | ") as labels_sub_elements'),
//                DB::raw('group_concat(seo.id_sub_elements_old separator " | ") as id_sub_elements'))
//            ->get();
////dd();
//        foreach ($form_infos as $key_forms_infos => $form_info){
////dd($form_info->labels_sub_elements);
//            if ($form_info->labels_sub_elements == null || $form_info->id_sub_elements == null){
//                $sub_elements_current = DB::table('sub_elements_current')
//                    ->where('id_sub_elements_field','=',$form_info->id_sub_elements_field)
//                    ->select(
//                        DB::raw('group_concat(label_sub_elements_current separator " | ") as labels_sub_elements'),
//                        DB::raw('group_concat(id_sub_elements_current separator " | ") as id_sub_elements')
//                    )
//                    ->get();
//                $form_infos[$key_forms_infos]->labels_sub_elements = $sub_elements_current[0]->labels_sub_elements;
//                $form_infos[$key_forms_infos]->id_sub_elements = $sub_elements_current[0]->id_sub_elements;
//            }
//        }
////dd($form_infos);
//        $id_forms_departments = DB::table('forms_departments')
//            ->where('id_forms','=',$request->input('id_forms'))
//            ->where('id_departments','=', Auth::user()->id_departments)
//            ->value('id_forms_departments');
//
//        if (!empty($form_infos)) {
//            foreach ($form_infos as $key => $form_info) {
//                $values = DB::table('values_fields_old')
//                    ->where('id_fields_forms', '=', $form_info->id_fields_forms)
//                    ->where('id_forms_departments', '=', $id_forms_departments)
//                    ->select('values_fields_old', 'enum_sub_elements_old')
//                    ->get();
//
//                if (!empty($values)) {
//                    $form_infos[$key]->values_fields_current = $values[0]->values_fields_old;
//                    if (is_array($form_infos)) {
//                        foreach ($values as $key_value => $value) {
//                            $form_infos[$key]->enum_sub_elements_current[$key_value] = $values[$key_value]->enum_sub_elements_old;
//                        }
//                    } else {
//                        $form_infos[$key]->enum_sub_elements_current = $values[0]->enum_sub_elements_old;
//                    }
//                } else {
//                    if (!empty($values)) {
//                        $values = DB::table('values_fields_current')
//                            ->where('id_fields_forms', '=', $form_info->id_fields_forms)
//                            ->where('id_forms_departments', '=', $id_forms_departments)
//                            ->select('values_fields_current', 'enum_sub_elements_current')
//                            ->get();
//                        $form_infos[$key]->values_fields_current = $values[0]->values_fields_current;
//                        if (is_array($form_infos)) {
//                            foreach ($values as $key_value => $value) {
//                                $form_infos[$key]->enum_sub_elements_current[$key_value] = $values[$key_value]->enum_sub_elements_current;
//                            }
//                        } else {
//                            $form_infos[$key]->enum_sub_elements_current = $values[0]->enum_sub_elements_current;
//                        }
//                    }
//                }
//
//            }
//        } else {
//            $form_infos = null;
//        }
////dd($form_infos);
//        return response()->json($form_infos);
//    }










    // formsConnectUsers //
    // formsConnectUsers //
    // formsConnectUsers //
    // formsConnectUsers //
    // formsConnectUsers //


    public function formsConnectUsers()
    {
        $forms = DB::table('forms')->get();
        $departments = DB::table('departments')->get();
        $connects = DB::table('forms_departments as fd')->join('forms', 'forms.id_forms', '=', 'fd.id_forms')
            ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
            ->select('d.*', 'forms.name_forms')
            ->orderBy('d.name_departments', 'asc')
            ->get();
        return view('constructor.formsConnectUsers', ['forms' => $forms, 'departments' => $departments, 'connects' => $connects]);
    }

    public function getTableConnectUsers(Request $request)
    {
        $departments = [];
        if ($request->input('id_forms') == '*' && $request->input('id_departments') == '*') {
            $departments = DB::table('forms_departments as fd');
        }
        if ($request->input('id_forms') == '*' && $request->input('id_departments') != '*') {
            $departments = DB::table('forms_departments as fd')
                ->where('fd.id_departments', '=', $request->input('id_departments'));
        }
        if ($request->input('id_forms') != '*' && $request->input('id_departments') == '*') {
            $departments = DB::table('forms_departments as fd')
                ->where('fd.id_forms', '=', $request->input('id_forms'));
        }
        if ($request->input('id_forms') != '*' && $request->input('id_departments') != '*') {
            $departments = DB::table('forms_departments as fd')
                ->where('fd.id_forms', '=', $request->input('id_forms'))
                ->where('fd.id_departments', '=', $request->input('id_departments'));
        }
        $departments = $departments
            ->join('forms', 'forms.id_forms', '=', 'fd.id_forms')
            ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
            ->select('d.*', 'forms.name_forms')
            ->orderBy('d.name_departments', 'asc')
            ->get();

        return response()->json($departments);
    }

    public function setTableConnectUsers(Request $request)
    {
        $value = DB::table('forms_departments as fd')
            ->where('fd.id_forms', '=', $request->input('id_forms'))
            ->where('fd.id_departments', '=', $request->input('id_departments'))
            ->get();

        if ($value == null) {
            DB::table('forms_departments')
                ->insert([
                    'id_forms' => $request->input('id_forms'),
                    'id_departments' => $request->input('id_departments')
                ]);
            return response()->json(['message' => 'Связь успешно добавлена.', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Такая связь уже существует!', 'bool' => false]);
        }

    }

    public function setTableDisconnectUsers(Request $request)
    {
        $value = DB::table('forms_departments as fd')
            ->where('id_forms', '=', $request->input('id_forms'))
            ->where('id_departments', '=', $request->input('id_departments'))
            ->pluck('id_forms_departments');

        if ($value != null) {
            DB::table('forms_departments')->where('id_forms_departments', '=', $value)->delete();
            return response()->json(['message' => 'Связь успешно разорвана.', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Такой связи не существует!', 'bool' => false]);
        }
    }


    // departments //
    // departments //
    // departments //
    // departments //
    // departments //
    
    
    public function getAllDepartments(){
        $departments = DB::table('departments')->get();
        return view('constructor/departments', ['departments' => $departments]);
    }

    public function setDepartments(Request $request){
        $this->validate($request, [
            'name_departments' => 'required|max:300|unique:departments',
        ],
            [
                'required' => 'Поле обязательно для заполнения',
                'max' => 'Поледолжно содержать максимум :max символов',
                'unique' => 'Такой отдел уже существует',
            ]);

        Department::create([
            'name_departments' => $request->input('name_departments')
        ]);
        return redirect('/constructor/departments');
    }

    public function removeDepartments(Request $request){
        $bool = DB::table('forms_departments')->where('id_departments','=',$request->input('id_departments'))->get();

        if($bool == null){
            DB::table('departments')->where('id_departments','=',$request->input('id_departments'))->delete();
            $message = "Отдел успешно удален !";
            $bool = true;
        } else {
            $message = "Сначала отвяжите все формы от отдела !";
            $bool = false;
        }
        return response()->json(['message'=>$message,'bool'=>$bool]);
    }

    public function editDepartments(Request $request){
        $this->validate($request, [
            'name_departments' => 'required|max:300|unique:departments',
        ],
            [
                'required' => 'Поле обязательно для заполнения',
                'max' => 'Поле должно содержать максимум :max символов',
                'unique' => 'Такой отдел уже существует',
            ]);

        DB::table('departments')
            ->where('id_departments','=',$request->input('id_departments'))
            ->update([
                'name_departments' => $request->input('name_departments')
            ]);

        return redirect('/constructor/departments');
    }
}

