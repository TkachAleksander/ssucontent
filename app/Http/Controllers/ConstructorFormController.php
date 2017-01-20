<?php

namespace App\Http\Controllers;

use App;

use App\Department;
use App\Models\Field;
use App\Models\Fields_form;
use App\Models\Fields_forms_current;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

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
            ->select('ff.id_fields_forms', 'f.id_fields', 'ffc.required_fields_current', 'forms.name_forms', 'forms.date_update_forms', 'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();

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
        // Получаем все старые формы
        $all_old_forms = DB::table('forms')->get();

        // Если новая форма имееи такое же имя и не ее ид формы
        // Обрываем выполнение метода и ыдаем алерт с ошибкой
        foreach($all_old_forms as $old_form){
            if($old_form->name_forms == $request->input('name_forms') && $old_form->id_forms != $request->input('id_form')){
                return response()->json(['message' => 'Форма с таким именем уже существует. Пожалуйста измените имя формы.', 'bool' => false]);
            }
        }
        // Апдейтим имя и дату формы
        DB::table('forms')->where('id_forms','=',$request->input('id_form'))->update(['name_forms' => $request->input('name_forms'), 'date_update_forms' => $request->input('date_update_forms')]);

        // Если есть новые fields добавляем в таблицу fields_forms
        // Узнаем id_fields_forms которые нужно удалить из таблицы fields_forms_current
        foreach($request->input('id_fields') as $key=>$id_field){
            Fields_form::firstOrCreate( array('id_forms' => $request->input('id_form'), 'id_fields' => $id_field));
            $id_fields_forms = DB::table('fields_forms')->where('id_forms','=',$request->input('id_form'))->where('id_fields','=',$id_field)->value('id_fields_forms');

            // Узнаем required
            $require = false;
            if (!empty($request->input('required'))) {
                foreach ($request->input('required') as $required) {
                    if ($required == $id_field) {
                        $require = true;
                    }
                }
            }

            // Удаляем старое поле из fields_forms_current, записываем новое
            Fields_forms_current::where('id_fields_forms','=',$id_fields_forms)->delete();
            Fields_forms_current::create([
                'id_fields_forms' => $id_fields_forms,
                'required_fields_current' => $require,
                'queue_form_fields_current' => $request->input('queue')[$key]
            ]);
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
        $elements = DB::table('elements')->get();
        $fields = DB::table('fields as f')
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->groupBy('f.id_fields'/*,'sef.id_sub_elements_field'*/)
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
            ->groupBy('f.id_fields'/*,'sef.id_sub_elements_field'*/)
            ->select(DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),'f.id_fields', 'f.label_fields', 'e.id_elements', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();

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


    // Запись отредактированого fields
    public function addEditedNewSetElement(Request $request)
    {
        // Проверяем изменились ли Label или Id_elements если да то апдейтим их
        if($request->input('label_fields') != $request->input('old_label_fields') || $request->input('old_id_elements') != $request->input('id_elements')){
            DB::table('fields')->where('id_elements','=',$request->input('old_id_elements'))
                ->where('label_fields','=',$request->input('old_label_fields'))
                ->update(['label_fields'=>$request->input('label_fields'), 'id_elements'=>$request->input('id_elements')]);
        }

        // Получаем id_sub_elements_field
        $id_sub_elements_field = DB::table('fields as f')->where('f.id_elements','=',$request->input('old_id_elements'))
            ->where('f.label_fields','=',$request->input('old_label_fields'))
            ->join('sub_elements_fields as sef', 'sef.id_fields','=','f.id_fields')->value('id_sub_elements_field');

        // Если есть id_sub_elements_field удаляем из таблицы sub_elements_current старые данные и записываем новые label sub_elements
        if ($id_sub_elements_field != null) {
            DB::table('sub_elements_current')->where('id_sub_elements_field','=', $id_sub_elements_field)->delete();

            if ($request->input('label_sub_elements') != null ) {
                foreach ($request->input('label_sub_elements') as $label_sub_element) {
                    if (!empty($label_sub_element)) {
                        DB::table('sub_elements_current')->insert(['id_sub_elements_field' => $id_sub_elements_field, 'label_sub_elements_current' => $label_sub_element]);
                    }
                }
            }
        }
//dd($request->all(), $id_sub_elements_field,$request->input('label_sub_elements'),$request->input('label_sub_elements') != null);
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

    // Вывод пустого содержимого формы
    public function getFormInfoAll(Request $request)
    {
//        $form_name = DB::table('forms')->where('id_forms','=',$request->input('id_forms'))->value('name_forms');
        $form_info = DB::table('fields as f')
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$request->input('id_forms'))
            ->join('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->leftJoin('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->groupBy('f.id_fields')
            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field', 'ffc.required_fields_current as required', DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'), DB::raw('group_concat(sec.id_sub_elements_current separator " | ") as id_sub_elements'))
            ->get();

        return response()->json(/*['forms_info' => */$form_info/*, 'forms_name' => $form_name]*/);
    }












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
                'max' => 'Поледолжно содержать максимум :max символов',
                'unique' => 'Такой отдел уже существует',
            ]);

        DB::table('departments')->where('id_departments','=',$request->input('id_departments'))->update(['name_departments' => $request->input('name_departments')]);

        return redirect('/constructor/departments');
    }

}

