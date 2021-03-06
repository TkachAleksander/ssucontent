<?php

namespace App\Http\Controllers;

use App;

use App\Department;
use App\Field;
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
            ->groupBy('f.id_fields')
            ->select(DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field')
            ->get();

        $forms = DB::table('forms')
            ->leftJoin('forms_departments as fd', 'fd.id_forms', '=', 'forms.id_forms')
            ->groupBy('forms.id_forms')
            ->select(DB::raw('group_concat(fd.id_status_checks separator " | ") as id_status_checks'), 'forms.*', 'fd.id_forms_departments')
            ->get();

        foreach ($forms as $key=>$form){
            $status_checks = explode(" | ", $form->id_status_checks);
            foreach($status_checks as $status){
                if($status == '2'){
                    $forms[$key]->id_status_checks = '2';
                }
            }
        }
//dd($fields, $forms);
        return view('constructor.addForm', ['fields' => $fields, 'forms' => $forms]);
    }

    // Кнопка перемещения fields в форму
    public function getSetElements(Request $request)
    {
        $fields = DB::table('fields as f')
            ->where('f.id_fields','=',$request->input('id_fields'))
            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields','=','f.id_fields')
            ->leftJoin('sub_elements_current as sec', 'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->select('f.id_fields','f.label_fields','e.name_elements','sef.id_sub_elements_field','sec.label_sub_elements_current')
            ->get();

            $arr_labels = [];
            if(!empty($fields[0]->label_sub_elements_current)) {
                foreach ($fields as $key => $field) {
                    array_push($arr_labels,$field->label_sub_elements_current); 
                }
                $label_sub_elements_current = implode(" | ", $arr_labels);
                $fields[0]->labels_sub_elements = $label_sub_elements_current;
            } else {
                $fields[0]->labels_sub_elements = "---";
            }
// dd($fields);
        return response()->json($fields);
    }

    // Кнопка добавления новой формы
    public function addNewForm(Request $request)
    {
        $this->validate($request, [
            'name_forms' => 'required|max:255|unique:forms',
            'date_update_forms' => 'required',
            'info_new_form' => 'required'
        ],
            [
                'required' => 'Поле обязательно для заполнения',
                'max' => 'Поле должно содержать максимум :max символов',
                'unique' => 'Форма с таким именем уже существует',
            ]);

        $id_form = DB::table('forms')
            ->insertGetId([
                'name_forms' => $request->input('name_forms'),
                'date_update_forms' => $request->input('date_update_forms')
            ]);
        $id_user = Auth::user()->id;

        $info_new_fields_form = $request->input('info_new_form');

        foreach ($info_new_fields_form as $arr_value){

            // Если это новое поле
            if (isset($arr_value['new_id_fields_forms'])){

                $id_fields = $arr_value['id_fields'];
                $required = (isset($arr_value['required'])) ? 1 : 0;

                // Запиываем новое поле в fields_forms и узнаем его id
                $id_fields_forms = DB::table('fields_forms')
                    ->insertGetID([
                        'id_forms' => $id_form,
                        'id_fields' =>  $id_fields
                    ]);

                // Записываеем новое поле в таблицу fields_forms_current
                DB::table('fields_forms_current')
                    ->insert([
                        'id_fields_forms' => $id_fields_forms,
                        'required_fields_current' => $required
                    ]);

            }
        }

        $status = [
            'class' => 'success',
            'message' => 'Форма успешно добавлена'
        ];

        $this->writeLog($id_user, $id_form, $id_departments = 0, $id_forms_departments = 0, $id_log_action = 5);
        return redirect('/constructor/addForm')->with('status', $status);
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
            ->leftJoin('sub_elements_current as sec', 'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->orderBy('ffc.id_fields_forms_current','asc')
            ->groupBy('sef.id_fields','ff.id_fields_forms')
            ->select(
                'ff.id_fields_forms', 'f.id_fields', 'ffc.required_fields_current', 'forms.name_forms', 'forms.id_forms',
                'forms.date_update_forms', 'f.id_fields', 'f.label_fields', 'e.name_elements', 'sef.id_sub_elements_field',
                DB::raw('group_concat(sec.label_sub_elements_current ORDER BY sec.id_sub_elements_current asc separator " | ") as labels_sub_elements ')
            )
            ->get();

//dd($request->all(),$fields);
        return response()->json($fields);
    }

    // Кнопка удаления формы
    public function removeFormsToServer(Request $request){

        $id_form = $request->input('id_forms');
        $id_user = Auth::user()->id;

        DB::table('forms')
            ->where('id_forms','=',$id_form)
            ->update(['deleted_forms' => 1]);

        $status = [
            'class' => 'success',
            'message' => 'Форма успешно удалена.'
        ];

        $this->writeLog($id_user, $id_form, $id_departments = 0, $id_forms_departments = 0, $id_log_action = 7);
        return redirect('/constructor/addForm')->with('status', $status);
    }

    public function reestablishForm(Request $request){

        $id_form = $request->input('id_forms');
        $id_user = Auth::user()->id;

        DB::table('forms')
            ->where('id_forms','=',$id_form)
            ->update(['deleted_forms' => 0]);

        $status = [
            'class' => 'success',
            'message' => 'Форма успешно восствновлена.'
        ];

        $this->writeLog($id_user, $id_form, $id_departments = 0, $id_forms_departments = 0, $id_log_action = 10);
        return redirect('/constructor/addForm')->with('status', $status);
    }

    // Кнопка редактирования уже сужествуещей формы
    public function addEditedNewForm(Request $request)
    {
//dd($request->all());

        $id_form = $request->input('id_forms');
        $id_user = Auth::user()->id;

        $this->validate($request, [
            'name_forms' => 'required|max:255|unique:forms,name_forms,'.$id_form.',id_forms',
            'date_update_forms' => 'required',
            'info_new_form' => 'required'
        ],
            [
                'required' => 'Поле обязательно для заполнения',
                'max' => 'Поле должно содержать максимум :max символов',
                'unique' => 'Форма с таким именем уже существует',
            ]);

        // Апдейтим имя и дату формы
        DB::table('forms')
            ->where('id_forms','=',$id_form)
            ->update([
                'name_forms' => $request->input('name_forms'),
                'date_update_forms' => $request->input('date_update_forms')
            ]);



        // Узнаем все id_fields_forms для редактируемой таблицы
        $all_id_fields_forms = DB::table('fields_forms')
            ->where('id_forms','=',$id_form)
            ->pluck('id_fields_forms');

        // Удаляем поля для редактируемой таблицы с таблицы fields_forms_current
        foreach ($all_id_fields_forms as $id_fields_form){
            DB::table('fields_forms_current')
                ->where('id_fields_forms','=',$id_fields_form)
                ->delete();
        }

        // Получаем информацию о новы полях формы
        $info_new_fields_form = $request->input('info_new_form');

        foreach ($info_new_fields_form as $arr_value){

            // Если это старое поле 
            if (isset($arr_value['exists_id_fields_forms'])) {

                $id_fields_forms = $arr_value['exists_id_fields_forms'];
                $required = (isset($arr_value['required'])) ? 1 : 0;

                if (!empty($id_fields_forms)) {
                    // Записываеем старое поле в таблицу fields_forms_current
                    DB::table('fields_forms_current')
                        ->insert([
                            'id_fields_forms' => $id_fields_forms,
                            'required_fields_current' => $required
                        ]);
                }
            }
            // Если это новое поле
            if (isset($arr_value['new_id_fields_forms'])){

                $id_fields = $arr_value['id_fields'];
                $required = (isset($arr_value['required'])) ? 1 : 0;

                // Запиываем новое поле в fields_forms и узнаем его id
                $id_fields_forms = DB::table('fields_forms')
                    ->insertGetID([
                        'id_forms' => $id_form,
                        'id_fields' =>  $id_fields
                    ]);

                // Записываеем новое поле в таблицу fields_forms_current
                DB::table('fields_forms_current')
                    ->insert([
                        'id_fields_forms' => $id_fields_forms,
                        'required_fields_current' => $required
                    ]);

            }
        }



        // Берем все id_fields_forms для редактируемой таблицы
        $id_fields_forms = DB::table('fields_forms')
            ->where('id_forms','=',$id_form)
            ->pluck('id_fields_forms');

        // Удаляем его из таблицы fields_forms
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
            // каскадом удалятся значения и с таблицы values_fields_current
            if(!$isset_field_current && !$isset_field_old){
                DB::table('fields_forms')
                    ->where('id_fields_forms','=',$id_field_form)
                    ->delete();
            }
        }

        $status = [
            'class' => 'success',
            'message' => 'Форма успешно отредактирована'
        ];

        $this->writeLog($id_user, $id_form, $id_department = 0, $id_forms_departments = 0, $id_log_action = 6);
        return redirect('/constructor/addForm')->with('status', $status);
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
            ->select('f.id_fields', 'f.label_fields', 'e.id_elements', 'e.name_elements', 'sef.id_sub_elements_field',
                DB::raw('group_concat(sec.label_sub_elements_current separator " | ") as labels_sub_elements'),
                DB::raw('group_concat(sec.id_sub_elements_field separator " | ") as id_sub_elements_from_fields'))
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

        $field = Field::create([
            'id_elements' => $request->input('id_elements'),
            'label_fields' => $request->input('label_fields')
        ]);

        // Проверяем есть ли label_sub_elements
        if (!empty($request->input('label_sub_elements'))) {

            foreach ($request->input('label_sub_elements')as $label_sub_element) {
                // Вставляем id_fields в таблицу sub_elements_fields
                $id_sub_elements_field = DB::table('sub_elements_fields')
                    ->insertGetId([
                        'id_fields' => $field->id
                    ]);
                // Вставляем id_sub_elements_field, label_sub_elements_current в таблицу sub_elements_current
                DB::table('sub_elements_current')
                    ->insert([
                        'id_sub_elements_field' => $id_sub_elements_field,
                        'label_sub_elements_current' => $label_sub_element
                    ]);
            }
        }

        return redirect('/constructor/newElement');
    }

    // Запись отредактированного fields
    public function addEditedNewSetElement(Request $request)
    {
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

            // Получаем массив id_sub_elements_fields которые уже есть в таблице sub_elements_fields
            $id_sub_elements_from_fields = explode(" | ", $request->input('id_sub_elements_from_fields'));

            // Удаляем текущие sub_elements
            foreach ($id_sub_elements_from_fields as $id_sub_elements_field) {
                DB::table('sub_elements_current')
                    ->where('id_sub_elements_field','=',$id_sub_elements_field)
                    ->delete();
            }

            $arr_key =[];
            foreach ($request->input('label_sub_elements') as $label_sub_element) {

                // Если это не массив
                if (!is_array($label_sub_element)) {
                    // И не пустое значение
                    if ($label_sub_element != "") {

                        // Записываем в sub_elements_fields, получаем его id
                        $id_sub_elements_field = DB::table('sub_elements_fields')
                            ->insertGetId([
                                'id_fields' => $request->input('id_fields')
                            ]);

                        // Записываем в sub_elements_current
                        DB::table('sub_elements_current')
                            ->insert([
                                'id_sub_elements_field' => $id_sub_elements_field,
                                'label_sub_elements_current' => $label_sub_element
                            ]);
                    }
                    
                } else {

                    foreach ($label_sub_element as $key => $label) {
                        // Если значение не пустое
                        if ($label != '') {
                            // Записываем в sub_elements_current
                            DB::table('sub_elements_current')
                                ->insert([
                                    'id_sub_elements_field' => $key,
                                    'label_sub_elements_current' => $label
                                ]);
                        }
                        array_push($arr_key,$key);

                    }
                }
            }

            // Находим элементы которых нет в $arr_key
            $arr_for_dell = array_diff($id_sub_elements_from_fields,$arr_key);

            // Удаляем элементы из таблицы sub_elements_fields которых нет sub_elements_current
            foreach ($arr_for_dell as $id_for_dell){
                DB::table('sub_elements_fields')
                    ->where('id_sub_elements_field','=',$id_for_dell)
                    ->delete();
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
        $forms = DB::table('forms')->where('deleted_forms','=',0)->get();
        return view('constructor.showForms', ['forms' => $forms]);
    }

    // Вывод содержимого формы без значений
    public function viewFormEmpty($id_forms)
    {
        $forms_info_empty = DB::table('fields as f')
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$id_forms)
            ->join('forms', 'forms.id_forms','=','ff.id_forms')
            ->join('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->leftJoin('elements as e', 'e.id_elements','=','f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields','=','f.id_fields')
            ->leftJoin('sub_elements_current as sec' ,'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->orderBy('ffc.id_fields_forms_current')
            ->groupBy('f.id_fields')
            ->select('forms.name_forms','f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field', 'ffc.required_fields_current as required',
                DB::raw('group_concat(sec.label_sub_elements_current ORDER BY sec.label_sub_elements_current ASC separator " | ") as labels_sub_elements'),
                DB::raw('group_concat(sec.id_sub_elements_current separator " | ") as id_sub_elements'))
            ->get();

        return view('viewFormEmpty', ['forms_info_empty' => $forms_info_empty]);
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
        $id_departments = $request->input('id_departments');
        $id_forms = $request->input('id_forms');
        $id_user = Auth::user()->id;

        $value = DB::table('forms_departments as fd')
            ->where('fd.id_forms', '=', $id_forms)
            ->where('fd.id_departments', '=', $id_departments)
            ->get();

        if ($value == null) {
            DB::table('forms_departments')
                ->insert([
                    'id_forms' => $id_forms,
                    'id_departments' => $id_departments
                ]);


            $this->writeLog($id_user, $id_forms, $id_departments, $id_forms_departments = 0, $id_log_action = 8);
            return response()->json(['message' => 'Связь успешно добавлена.', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Такая связь уже существует!', 'bool' => false]);
        }

    }

    public function setTableDisconnectUsers(Request $request)
    {
        $id_departments = $request->input('id_departments');
        $id_forms = $request->input('id_forms');
        $id_user = Auth::user()->id;

        $value = DB::table('forms_departments as fd')
            ->where('id_forms', '=', $id_forms)
            ->where('id_departments', '=', $id_departments)
            ->pluck('id_forms_departments');

        if ($value != null) {
            DB::table('forms_departments')->where('id_forms_departments', '=', $value)->delete();

            $this->writeLog($id_user, $id_forms, $id_departments, $id_forms_departments = 0, $id_log_action = 9);
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
        $status = [
            'class' => 'success',
            'message' => 'Отдел успешно добавлен !'
        ];
        return redirect('/constructor/departments')->with('status',$status);
    }

    public function removeDepartments(Request $request){
        $bool = DB::table('forms_departments')->where('id_departments','=',$request->input('id_departments'))->get();

        if($bool == null){
            DB::table('departments')
                ->where('id_departments','=',$request->input('id_departments'))
                ->update(['deleted_departments' => 1]);
            $status = [
                'class' => 'success',
                'message' => 'Отдел успешно удален'
            ];
        } else {
            $status = [
                'class' => 'danger',
                'message' => 'Сначала отвяжите все формы от отдела !'
            ];
        }
        return redirect('/constructor/departments')->with('status',$status);
    }

    public function reestablishDepartments(Request $request){

            DB::table('departments')
                ->where('id_departments','=',$request->input('id_departments'))
                ->update(['deleted_departments' => 0]);
        $status = [
            'class' => 'success',
            'message' => 'Отдел успешно востановлен !'
        ];

        return redirect('/constructor/departments')->with('status',$status);
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

    public function writeLog($id_users, $id_forms, $id_departments, $id_forms_departments, $id_log_action){
        DB::table('log')
            ->insert([
                'id_users' => $id_users,
                'id_forms' => $id_forms,
                'id_departments' => $id_departments,
                'id_forms_departments' => $id_forms_departments,
                'id_log_action' => $id_log_action
            ]);
    }
}

