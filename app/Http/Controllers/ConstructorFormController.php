<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
//use Illuminate\Support\Facades\Auth;


class ConstructorFormController extends Controller
{
    const RADIOBUTTON = "radiobutton";
    const CHECKBOX = "checkbox";
    const OPTION = "option";

// addForm
    public function addForm()
    {
        $set_elements = DB::table('set_elements as se')->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->select('se.*', 'se.id as id_set_elements', 'e.*')
            ->orderBy('se.label_set_elements','asc')
            ->get();

        $this->ForeachImplode($set_elements);

        $name_forms = DB::table('forms as f')->where('show', '=', 1)
            ->leftJoin('set_forms_departments as sfd', 'sfd.id_forms', '=', 'f.id')
            ->select('f.id', 'f.name_forms', 'sfd.id_status_checks')->distinct()->get();

        foreach ($name_forms as $name_form) {
            if ($name_form->id_status_checks != 1) {
                $id = $name_form->id;
                foreach ($name_forms as $key => $form) {
                    if ($form->id == $id && $form->id_status_checks == 1) {
                        unset($name_forms[$key]);
                    }
                }
            }
        }

        return view('constructor.addForm', ['set_elements' => $set_elements, 'name_forms' => $name_forms]);
    }

    public function getSetElements(Request $request)
    {
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('idSetElement'))->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->where('version_sub_elements', '=', 1)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;
        }
        return response()->json($set_elements);
    }

    public function addSetFormsElementsToServer(Request $request)
    {

        if (!empty($request->input('name_forms')) && !empty($request->input('queue')) && !empty($request->input('update_date'))) {
            $id_forms = DB::table('forms')->insertGetId(['name_forms' => $request->input('name_forms'), 'update_date' => $request->input('update_date')]);

            foreach ($request->input('queue') as $key => $value) {
                if (!empty($request->input('required'))) {
                    foreach ($request->input('required') as $key => $bool) {
                        if ($bool == $value) {
                            $required = true;
                            break 1;
                        } else {
                            $required = false;
                        }
                    }
                } else {
                    $required = false;
                }
                DB::table('set_forms_elements')->insert([
                    'id_forms' => $id_forms,
                    'id_set_elements' => $value,
                    'required' => $required
                ]);
            }
            return response()->json(['message' => 'Форма успешно добавлена !', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Форма заполнена неверно !', 'bool' => false]);
        }
    }

    public function editForm(Request $request)
    {
        $set_elements = DB::table('set_forms_elements as sfe')
            ->where('sfe.id_forms', '=', $request->input('id_form'))
            ->where('version', '=', 1)
            ->join('forms as f', 'f.id', '=', 'sfe.id_forms')
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->orderBy('sfe.id', 'asc')
            ->select('sfe.id', 'sfe.id_set_elements', 'sfe.required', 'f.name_forms', 'f.update_date', 'se.id_elements', 'se.label_set_elements', 'e.name_elements')
            ->get();

        $this->ForeachImplode($set_elements);

        return response()->json($set_elements);
    }

    public function addEditedNewForm(Request $request)
    {
        // Собираем информацию о элементах формы
        $set_elements = DB::table('forms as f')->where('f.id', '=', $request->input('id_form'))->where('f.show', '=', 1)
            ->join('set_forms_elements as sfe', 'sfe.id_forms', '=', 'f.id')
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->select('f.name_forms', 'f.update_date', 'e.name_elements', 'se.label_set_elements', 'sfe.id_set_elements', 'sfe.required')
            ->get();
        // Дополняем информацию, под элементами элемента
        $this->ForeachImplode($set_elements);

        // Изменение старого имени формы на новое
        $repeat_name_forms = DB::table('forms')->where('name_forms', '=', $request->input('name_forms'))->get();
        // Если нет такого имени в базе ИЛИ найденое имя такое же как и прошлое
        if (empty($repeat_name_forms) || $repeat_name_forms[0]->name_forms == $request->input('old_name_forms')) {

            // Если прошлое имя != новому ИЛИ прошлая дата обновления формы != новой дате обновления
            if ($set_elements[0]->name_forms != $request->input('name_forms') || $set_elements[0]->update_date != $request->input('update_date')) {
                DB::table('forms')->where('id', '=', $request->input('id_form'))
                    ->update([
                        'name_forms' => $request->input('name_forms'),
                        'update_date' => $request->input('update_date')]);
            }

            // Все элементы формы делаем невидемыми, увеличиваем их версию +1
            DB::table('set_forms_elements')->where('id_forms', '=', $request->input('id_form'))
                ->increment('version', 1/*, ['show_set_forms_elements' => false]*/);

            // Удаляем 3ю версию формы
            DB::table('set_forms_elements')->where('id_forms', '=', $request->input('id_form'))
                ->where('version', '>=', 3)->delete();

            $bool = false;
            // Если массив обязательных элементов(required) с номерами очереди элементов(queue) не пуст
            if (!empty($request->input('required'))) {
                // Берем первый номер элемента из очереди
                foreach ($request->input('queue') as $key => $queue) {
                    // Перебираем весь массив обязательных элементов
                    foreach ($request->input('required') as $required) {
                        // Если есть такой - true; если нет - false
                        if ($queue == $required) {
                            $bool = true;
                            break 1;
                        } else {
                            $bool = false;
                        }
                    }
                    // Записываем элемент со значение $bool для required
                    DB::table('set_forms_elements')->insert(['id_forms' => $request->input('id_form'), 'id_set_elements' => $request->input('id_set_elements')[$key], 'required' => $bool]);
                }
            } else {
                // Если массив обязательных элементов(required) пуст все строки записываем с $bool = false
                foreach ($request->input('id_set_elements') as $id_set_elements) {
                    DB::table('set_forms_elements')->insert(['id_forms' => $request->input('id_form'), 'id_set_elements' => $id_set_elements, 'required' => $bool]);
                }
            }
            return response()->json(['message' => 'Форма успешно отредактирована!', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Форма с таким именем уже существует. Пожалуйста измените имя формы.', 'bool' => false]);
        }

    }

    public function removeFormsToServer(Request $request)
    {
        DB::table('forms')->where('id', '=', $request->input('id_forms'))->update(['show' => 0]);
        return response()->json();
    }


// newElement
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

    public function newElement()
    {
        $elements = DB::table('elements')->get();
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
            ->select('set_elements.*', 'set_elements.id as id_set_elements', 'elements.*')
            ->orderBy('set_elements.label_set_elements', 'asc')
            ->get();

        $this->ForeachImplode($set_elements);
        return view('constructor.newElement', ['elements' => $elements, 'set_elements' => $set_elements]);
    }

    public function addNewElementToServer(Request $request)
    {
        $id = DB::table('set_elements')->insertGetId([
            'label_set_elements' => $request->input('label_set_elements'),
            'id_elements' => $request->input('id_elements')
        ]);
        $name_element = DB::table('elements')->where('id', '=', $request->input('id_elements'))->pluck('name_elements');

        if (!empty($request->input('value_sub_elements'))) {

            if ($name_element[0] == self::RADIOBUTTON || $name_element[0] == self::OPTION) {
                foreach ($request->input('value_sub_elements') as $key => $value) {
                    if (!empty($value)) {
                        $value = trim($value, " \t\n\r\0\x0B");
                        DB::table('sub_elements')->insert([
                            'id_set_elements' => $id,
                            'value_sub_elements' => $value
                        ]);
                    }
                }
            }
            if ($name_element[0] == self::CHECKBOX) {
                foreach ($request->input('value_sub_elements') as $key => $value) {
                    if (!empty($value)) {
                        $value = trim($value, " \t\n\r\0\x0B");
                        DB::table('sub_elements')->insert([
                            'id_set_elements' => $id,
                            'value_sub_elements' => $value
                        ]);
                    }
                }
            }
        }
        return redirect('/constructor/newElement');
    }

    public function editSetElementFromForm(Request $request)
    {
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->get();

        $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $request->input('id_set_elements'))
//            ->where('show', '=', 1)
                ->where('version_sub_elements','=',1)
            ->select('id', 'value_sub_elements')->orderBy('id', 'desc')->get();
        return response()->json(['set_elements' => $set_elements, 'sub_elements' => $sub_elements]);
    }

    public function addEditedNewSetElement(Request $request)
    {
        // Если такого элемента нет - обновляем страницу
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->get();
//        dd($request->all(),$set_elements);
        if ($request->input('old_label_set_elements') != $request->input('label_set_elements') || $request->input('old_id_elements') != $request->input('id_elements')) {
            DB::table('set_elements')->where('id', $request->input('id_set_elements'))
                ->update(['label_set_elements' => $request->input('label_set_elements'),
                    'id_elements' => $request->input('id_elements')]);
        }

        if ($set_elements != null) {
            $value_new_sub_elements = $request->value_sub_elements; // значения подэлементов
//            $uninstalled_sub_elements = $_COOKIE['uninstalled_sub_elements']; // список полей на удаление

            if ($value_new_sub_elements != null) {
                DB::table('sub_elements')->where('id_set_elements','=', $set_elements[0]->id)->increment('version_sub_elements', 1);

                foreach ($value_new_sub_elements as $key_new_element => $value_new_sub_element) {
//                    dd($value_new_sub_elements,$set_elements[0]->id,$uninstalled_sub_elements);
                    if (!empty($value_new_sub_element)) {

                        DB::table('sub_elements')->insert(['id_set_elements' => $set_elements[0]->id, 'value_sub_elements' => $value_new_sub_element]);
                    }
                }
                DB::table('sub_elements')->where('version_sub_elements', '>=', 3)->delete();

            }
        }
//        setcookie("uninstalled_sub_elements", "", time() - 3600);
        return redirect('/constructor/newElement');
    }

    public function removeSetElement(Request $request)
    {
        DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->delete();
        return response()->json();
    }


// showForms
    public function FormInfo(Request $request, $version)
    {
        // Для полей которые имеют value
        $form_info = DB::table('set_forms_elements as sfe')->where('id_forms', '=', $request->input('id_forms'))
            ->where('sfe.version', '=', $version)
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->leftJoin('values_forms as vf', 'vf.id_set_forms_elements', '=', 'sfe.id')
            ->where('vf.version_values_forms', '=', $version)
            ->where('vf.id_departments', '=', $request->input('id_departments'))
            ->orderBy('sfe.id', 'asc')
            ->select('sfe.id as id_set_forms_elements','sfe.id_set_elements', 'sfe.width', 'sfe.required', 'sfe.id_forms', 'se.label_set_elements', 'e.name_elements', 'vf.id_departments', 'vf.values_forms', 'vf.checked_sub_elements')
            ->get();
//        dd($form_info);
        // Поля без value
        if ($form_info == null) {
            $form_info = DB::table('set_forms_elements as sfe')->where('id_forms', '=', $request->input('id_forms'))
                ->where('sfe.version', '=', $version)
                ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                ->join('elements as e', 'e.id', '=', 'se.id_elements')
                ->leftJoin('values_forms as vf', 'vf.id_set_forms_elements', '=', 'sfe.id')
                ->orderBy('sfe.id', 'asc')
                ->select('sfe.id as id_set_forms_elements','sfe.id_set_elements', 'sfe.width', 'sfe.required', 'sfe.id_forms', 'se.label_set_elements', 'e.name_elements', 'vf.id_departments', 'vf.values_forms', 'vf.checked_sub_elements')
                ->get();
        }
        $this->ForeachImplode($form_info);

        return $form_info;
    }

    public function ForeachImplode($arr)
    {
        foreach ($arr as $key => $set_element) {

            $id_set_element = $set_element->id_set_elements;

            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)->where('version_sub_elements','=',1)
                /*->where('show', '=', 1)*/->select('id','value_sub_elements')->get();

            if (!empty($sub_elements)) {
                $values = [];
                $id = [];
                if ($set_element->name_elements == self::RADIOBUTTON || $set_element->name_elements == self::OPTION) {
                    foreach ($sub_elements as $key_sub => $value) {
                        $values[$key_sub] = $value->value_sub_elements;
                        $id[$key_sub] = $value->id;
                    }
                }
                if ($set_element->name_elements == self::CHECKBOX) {
                    foreach ($sub_elements as $key_sub => $value) {
                        $values[$key_sub] = $value->value_sub_elements;
                        $id[$key_sub] = $value->id;
                    }
                }
            } else {
                $values = [];
                $id = [];
            }
            $value_sub_elements = implode(" | ", $values);

            $arr[$key]->value_sub_elements = $value_sub_elements;
            $arr[$key]->id_sub_elements = $id;
        }
        return $arr;
    }
//???????????????????????????????????????????????????????????????????????????????????
//    public function getSubElements(Request $request)
//    {
////dd($request->all());
//        if (is_array($request->input('id_sub_elements'))) {
//            $sub_elements = preg_split(' | ', $request->input('id_sub_elements'));
////            dd($sub_elements);
//            foreach ($sub_elements as $key => $sub_element) {
//                $value_sub_elements[$key] = DB::table('sub_elements')->where('id', '=', $sub_element)->pluck('value_sub_elements');
//            }
//        } else {
//            $sub_elements = $request->input('id_sub_elements');
//            $value_sub_elements = DB::table('sub_elements')->where('id', '=', $sub_elements)->pluck('value_sub_elements');
//        }
//
////        dd(preg_split(' | ', $request->input('id_sub_elements')));
//        return response()->json($value_sub_elements);
//    }

    public function showForms()
    {
        $forms = DB::table('forms')->get();
        return view('constructor.showForms', ['forms' => $forms]);
    }

    public function getFormInfo(Request $request)
    {
        $form_info = $this->FormInfo($request, 1);
//        dd($request->all());
//        dd($form_info);
        return response()->json($form_info);
    }

    public function getFormInfoOld(Request $request)
    {
        $form_info = $this->FormInfo($request, 2);
        return response()->json($form_info);
    }

    public function getFormInfoAll(Request $request)
    {
        $form_info = $this->FormInfoAll($request, 1);
        return response()->json($form_info);
    }

    public function FormInfoAll(Request $request, $version)
    {
        $form_info = DB::table('set_forms_elements as sfe')->where('id_forms', '=', $request->input('id_forms'))
            ->where('sfe.version', '=', $version)
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->orderBy('sfe.id', 'asc')
            ->select('sfe.id_set_elements', 'sfe.width', 'sfe.required', 'sfe.id_forms', 'se.label_set_elements', 'e.name_elements')
            ->get();
        $this->ForeachImplode($form_info);

        return $form_info;
    }


// formsConnectUsers
    public function formsConnectUsers()
    {
        $forms = DB::table('forms')->get();
        $departments = DB::table('departments')->get();
        $connects = DB::table('set_forms_departments as sfd')->join('forms as f', 'f.id', '=', 'sfd.id_forms')
            ->join('departments as d', 'd.id', '=', 'sfd.id_departments')
            ->select('d.*', 'f.name_forms')
            ->orderBy('d.name_departments', 'asc')
            ->get();
        return view('constructor.formsConnectUsers', ['forms' => $forms, 'departments' => $departments, 'connects' => $connects]);
    }

    public function getTableConnectUsers(Request $request)
    {
        $departments = [];
        if ($request->input('id_forms') == '*' && $request->input('id_departments') == '*') {
            $departments = DB::table('set_forms_departments as sfd')
                ->join('forms as f', 'f.id', '=', 'sfd.id_forms')
                ->join('departments as d', 'd.id', '=', 'sfd.id_departments')
                ->select('d.*', 'f.name_forms')
                ->orderBy('d.name_departments', 'asc')
                ->get();
        }
        if ($request->input('id_forms') == '*' && $request->input('id_departments') != '*') {
            $departments = DB::table('set_forms_departments as sfd')
                ->where('sfd.id_departments', '=', $request->input('id_departments'))
                ->join('forms as f', 'f.id', '=', 'sfd.id_forms')
                ->join('departments as d', 'd.id', '=', 'sfd.id_departments')
                ->select('d.*', 'f.name_forms')
                ->orderBy('d.name_departments', 'asc')
                ->get();
        }
        if ($request->input('id_forms') != '*' && $request->input('id_departments') == '*') {
            $departments = DB::table('set_forms_departments as sfd')
                ->where('sfd.id_forms', '=', $request->input('id_forms'))
                ->join('forms as f', 'f.id', '=', 'sfd.id_forms')
                ->join('departments as d', 'd.id', '=', 'sfd.id_departments')
                ->select('d.*', 'f.name_forms')
                ->orderBy('d.name_departments', 'asc')
                ->get();
        }
        if ($request->input('id_forms') != '*' && $request->input('id_departments') != '*') {
            $departments = DB::table('set_forms_departments as sfd')
                ->where('sfd.id_forms', '=', $request->input('id_forms'))
                ->where('sfd.id_departments', '=', $request->input('id_departments'))
                ->join('forms as f', 'f.id', '=', 'sfd.id_forms')
                ->join('departments as d', 'd.id', '=', 'sfd.id_departments')
                ->select('d.*', 'f.name_forms')
                ->orderBy(/*f.name_forms*/
                    'd.name_departments', 'asc')
                ->get();
        }
        return response()->json($departments);
    }

    public function setTableConnectUsers(Request $request)
    {
        $value = DB::table('set_forms_departments as sfd')
            ->where('sfd.id_forms', '=', $request->input('id_forms'))
            ->where('sfd.id_departments', '=', $request->input('id_departments'))
            ->get();

        if ($value == null) {
            DB::table('set_forms_departments')->insert(['id_forms' => $request->input('id_forms'),
                'id_departments' => $request->input('id_departments')]);
            return response()->json(['message' => 'Связь успешно добавлена.', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Такая связь уже существует!', 'bool' => false]);
        }

    }

    public function setTableDisconnectUsers(Request $request)
    {
        $value = DB::table('set_forms_departments as sfd')->where('id_forms', '=', $request->input('id_forms'))
            ->where('id_departments', '=', $request->input('id_departments'))
            ->pluck('id');
//dd($request->all(),$value != null);
        if ($value != null) {
            DB::table('set_forms_departments')->where('id', '=', $value)->delete();
            return response()->json(['message' => 'Связь успешно разорвана.', 'bool' => true]);
        } else {
            return response()->json(['message' => 'Такой связи не существует!', 'bool' => false]);
        }
    }




//departments
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
        $bool = DB::table('set_forms_departments')->where('id_departments','=',$request->input('id_departments'))->get();

        if($bool == null){
            DB::table('departments')->where('id','=',$request->input('id_departments'))->delete();
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

        DB::table('departments')->where('id','=',$request->input('id_departments'))->update(['name_departments' => $request->input('name_departments')]);

        return redirect('/constructor/departments');
    }

}

