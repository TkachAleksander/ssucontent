<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

//define("RADIOBUTTON", "radiobutton");
//define("CHECKBOX", 'checkbox');
class ConstructorFormController extends Controller
{
    const RADIOBUTTON = "radiobutton";
    const CHECKBOX = "checkbox";
    const OPTION = "option";

// addForm
    public function addForm(){
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
            ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
            ->orderBy('set_elements.name_set_elements', 'asc')
            ->get();

        $this->FOREACH_IMPLODE($set_elements);

        $name_forms = DB::table('forms')->where('show','=',1)->get();

        return view('constructor.addForm', ['set_elements' => $set_elements, 'name_forms' => $name_forms ]);
    }

    public function getSetElements(Request $request){
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('idSetElement'))->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->where('show','=',1)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;
        }
        return response()->json($set_elements);
    }

    public function addSetFormsElementsToServer(Request $request){

        if ( !empty($request->input('name_forms')) && !empty($request->input('queue')) && !empty($request->input('update_date')) ){
            $id_forms = DB::table('forms')->insertGetId([ 'name_forms' => $request->input('name_forms'), 'update_date' => $request->input('update_date') ]);

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
            return response()->json(['message'=>'Форма успешно добавлена !', 'bool'=>true]);
        } else {
            return response()->json(['message'=>'Форма заполнена неверно !', 'bool'=>false]);
        }
    }

    public function addEditedNewForm(Request $request)
    {
        // Собираем информацию о элементах формы
        $set_elements = DB::table('forms as f')->where('f.id', '=', $request->input('id_form'))->where('f.show', '=', 1)
            ->join('set_forms_elements as sfe', 'sfe.id_forms', '=', 'f.id')
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->select('f.name_forms','f.update_date', 'e.name_elements', 'se.name_set_elements', 'se.label_set_elements', 'sfe.id_set_elements', 'sfe.required')
            ->get();
        // Дополняем информацию под элементами
        $this->FOREACH_IMPLODE($set_elements);

        // Изменение старого имени формы на новое
        $repeat_name_forms = DB::table('forms')->where('name_forms','=',$request->input('name_forms'))->get();
        if (empty($repeat_name_forms) || $repeat_name_forms[0]->name_forms == $request->input('old_name_forms')) {

            if ($set_elements[0]->name_forms != $request->input('name_forms') || $set_elements[0]->name_forms != $request->input('update_date')) {
                DB::table('forms')->where('id', '=', $request->input('id_form'))
                    ->update([
                        'name_forms' => $request->input('name_forms'),
                        'update_date' => $request->input('update_date')]);
            }

            $bool = false;
            DB::table('set_forms_elements')->where('id_forms', '=', $request->input('id_form'))->delete();
            if (!empty($request->input('required'))) {
                foreach ($request->input('queue') as $id_set_elements) {
                    foreach ($request->input('required') as $required) {
                        if ($id_set_elements == $required) {
                            $bool = true;
                            break 1;
                        } else {
                            $bool = false;
                        }
                    }
                    DB::table('set_forms_elements')->insert(['id_forms' => $request->input('id_form'), 'id_set_elements' => $id_set_elements, 'required' => $bool]);
                }
            } else {
                foreach ($request->input('queue') as $id_set_elements) {
                    DB::table('set_forms_elements')->insert(['id_forms' => $request->input('id_form'), 'id_set_elements' => $id_set_elements, 'required' => $bool]);
                }
            }
            return response()->json(['message'=>'Форма успешно отредактирована!','bool'=>true]);
        } else {
            return response()->json(['message' => 'Форма с таким именем уже существует. Пожалуйста измените имя формы.', 'bool' => false]);
        }

    }

    public function removeFormsToServer(Request $request){
        DB::table('forms')->where('id','=',$request->input('id_forms'))->update(['show' => 0]);
        return response()->json();
    }

    public function editForm(Request $request){
        $set_elements = DB::table('set_forms_elements as sfe')->where('sfe.id_forms','=',$request->input('id_form'))
            ->join('forms as f', 'f.id','=','sfe.id_forms')
            ->join('set_elements as se', 'se.id','=','sfe.id_set_elements')
            ->join('elements as e', 'e.id','=','se.id_elements')
            ->orderBy('sfe.id','asc')
            ->select('sfe.id','sfe.id_set_elements', 'sfe.required','f.name_forms', 'f.update_date', 'se.id_elements','se.name_set_elements','se.label_set_elements','e.name_elements')
            ->get();

        $this->FOREACH_IMPLODE($set_elements);

        return response()->json($set_elements);
    }








// newElement
    public function generateString($length = 8){
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }
    public function newElement(){
        $elements = DB::table('elements')->get();
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
            ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
            ->orderBy('set_elements.name_set_elements', 'asc')
            ->get();

        $this->FOREACH_IMPLODE($set_elements);
        $name_set_elements = $this->generateString();
        return view('constructor.newElement', ['elements' => $elements,'set_elements' => $set_elements,'name_set_elements' => $name_set_elements]);
    }
    
    public function addNewElementToServer(Request $request){
        $id = DB::table('set_elements')->insertGetId([
            'name_set_elements' => $request->input('name_set_elements'),
            'label_set_elements' => $request->input('label_set_elements'),
            'id_elements' => $request->input('id_elements')
        ]);
        $name_element = DB::table('elements')->where('id','=',$request->input('id_elements'))->pluck('name_elements');

        if(!empty($request->input('value_sub_elements'))) {

            if($name_element[0] == self::RADIOBUTTON || $name_element[0] == self::OPTION) {
                foreach ($request->input('value_sub_elements') as $key => $value) {
                    if (!empty($value)) {
                        $value = trim($value, " \t\n\r\0\x0B");
                        DB::table('sub_elements')->insert([
                            'id_set_elements' => $id,
                            'name_sub_elements' => $request->input('name_set_elements'),
                            'value_sub_elements' => $value
                        ]);
                    }
                }
            }
            if($name_element[0] == self::CHECKBOX) {
                foreach ($request->input('value_sub_elements') as $key => $value) {
                    if (!empty($value)) {
                        $value = trim($value, " \t\n\r\0\x0B");
                        DB::table('sub_elements')->insert([
                            'id_set_elements' => $id,
                            'name_sub_elements' => $this->generateString(),
                            'value_sub_elements' => $value
                        ]);
                    }
                }
            }
        }
        return redirect('/constructor/newElement');
    }

    public function editSetElementFromForm(Request $request){
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->get();

        $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $request->input('id_set_elements'))
            ->where('show','=',1)
            ->select('id','value_sub_elements')->orderBy('id','desc')->get();
        return response()->json(['set_elements' => $set_elements, 'sub_elements' => $sub_elements]);
    }

    public function addEditedNewSetElement(Request $request) {

        // Если такого элемента нет - обновляем страницу
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->get();

        // Перезаписываем Name Label если были изменения
        if($request->input('old_name_set_elements') != $request->input('name_set_elements')) {
            DB::table('set_elements')->where('name_set_elements', $request->input('old_name_set_elements'))
                ->where('id','=',$request->input('id_set_elements'))
                ->update(['name_set_elements' => $request->input('name_set_elements')]);
        }
        if($request->input('old_label_set_elements') != $request->input('label_set_elements')) {
            DB::table('set_elements')->where('label_set_elements', $request->input('old_label_set_elements'))
                ->where('id','=',$request->input('id_set_elements'))
                ->update(['label_set_elements' => $request->input('label_set_elements')]);
        }


        if($set_elements != []){
            $value_new_sub_elements = $request->value_sub_elements; // значения под элементов
            $uninstalled_sub_elements = $_COOKIE['uninstalled_sub_elements']; // список полей на удаление

            // Замена старых значений или добавление новых под элементов
            foreach ($value_new_sub_elements as $key_new_element => $value_new_sub_element){
                if($value_new_sub_element != null) {
                    $old_value = DB::table('sub_elements')->where('id_set_elements', '=', $set_elements[0]->id)->where('id', '=', $key_new_element)->get();
                    if ($old_value != []) {
                        DB::table('sub_elements')->where('id_set_elements', '=', $set_elements[0]->id)
                            ->where('id', '=', $key_new_element)
                            ->update(['value_sub_elements' => $value_new_sub_elements[$key_new_element]]);
                    } else {
                        DB::table('sub_elements')->insert(['id_set_elements' => $set_elements[0]->id,
                            'value_sub_elements' => $value_new_sub_elements[$key_new_element]
                        ]);
                    }
                }
            }
            // Из строки с id скрываемых под элементов делаем массив
            $uninstalled_sub_elements = explode(",", $uninstalled_sub_elements);

            // Скрываем под элементы по их id
            foreach($uninstalled_sub_elements as $key => $id_sub_element ){
                DB::table('sub_elements')->where('id', '=', $id_sub_element)->update(['show' => 0]);
            }
        }
        setcookie ("uninstalled_sub_elements", "", time() - 3600);
        return redirect('/constructor/newElement');
    }

    public function removeSetElement(Request $request){
        DB::table('set_elements')->where('id','=',$request->input('id_set_elements'))->delete();
        return response()->json();
    }




// showForms
    public function FORM_INFO (Request $request){
        $form_info = DB::table('set_forms_elements as sfe')->where('id_forms', '=', $request->input('id_forms'))
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->orderBy('sfe.id','asc')
            ->select('sfe.id_set_elements', 'sfe.width', 'sfe.required', 'se.name_set_elements', 'se.label_set_elements', 'e.name_elements')
            ->get();

        $this->FOREACH_IMPLODE($form_info);

        return $form_info;
    }

    public function FOREACH_IMPLODE($arr){
        foreach ($arr as $key => $set_element) {
            
            $id_set_element = $set_element->id_set_elements;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->where('show','=',1)->select('name_sub_elements','value_sub_elements')->get();

            if(!empty($sub_elements)){
                $names = [];
                $values = [];
                if($set_element->name_elements == self::RADIOBUTTON || $set_element->name_elements == self::OPTION) {
                    foreach ($sub_elements as $key_sub => $value) {
                        $values[$key_sub] = $value->value_sub_elements;
                    }
                    $names = $sub_elements[0]->name_sub_elements;
                }
                if($set_element->name_elements == self::CHECKBOX) {
                    foreach ($sub_elements as $key_sub => $value) {
                        $values[$key_sub] = $value->value_sub_elements;
                        $names[$key_sub] = $value->name_sub_elements;
                    }
                }
            } else {
                $values = [];
                $names = [];
            }
            $value_sub_elements = implode(" | ", $values);

            $arr[$key]->value_sub_elements = $value_sub_elements;
            $arr[$key]->name_sub_elements = $names;
        }
        return $arr;
    }

    public function showForms(){
        $forms = DB::table('forms')->get();
        return view('constructor.showForms', ['forms' => $forms]);
    }

    public function getFormInfo(Request $request){
        $form_info = $this->FORM_INFO($request);
        return response()->json($form_info);
    }





// formsConnectUsers
    public  function formsConnectUsers(){
        $forms = DB::table('forms')->get();
        $users = DB::table('users')->join('roles', 'roles.id', '=', 'users.id_roles')
            ->where('name_roles', '!=', 'administrator')
            ->select('users.*')
            ->get();
        $connects = DB::table('set_forms_users as sfu')->join('forms as f', 'f.id', '=', 'sfu.id_forms')
                                                       ->join('users as u', 'u.id', '=', 'sfu.id_users')
                                                       ->select('u.surname', 'u.name', 'u.middle_name', 'f.name_forms')
                                                       ->orderBy('f.name_forms', 'asc')
                                                       ->get();
        return view('constructor.formsConnectUsers', ['forms' => $forms, 'users' => $users, 'connects' => $connects]);
    }

    public function getTableConnectUsers(Request $request){
        if($request->input('id_forms') == '*'){
            $users = DB::table('set_forms_users as sfu')
                ->join('forms as f', 'f.id', '=', 'sfu.id_forms')
                ->join('users as u', 'u.id', '=', 'sfu.id_users')
                ->select('u.surname', 'u.name', 'u.middle_name', 'f.name_forms')
                ->orderBy('u.surname', 'asc')
                ->get();
        } else {
            $users = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                ->join('forms as f', 'f.id', '=', 'sfu.id_forms')
                ->join('users as u', 'u.id', '=', 'sfu.id_users')
                ->select('u.surname', 'u.name', 'u.middle_name', 'f.name_forms')
                ->orderBy('u.surname', 'asc')
                ->get();
        }
        return response()->json($users);
    }

    public function setTableConnectUsers(Request $request){
        $value = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                                                    ->where('id_users', '=', $request->input('id_users'))
                                                    ->get();
        if($value == null) {
            DB::table('set_forms_users')->insert(['id_forms' => $request->input('id_forms'),
                'id_users' => $request->input('id_users')]);
            return response()->json(['message'=>'Связь успешно добавлена.', 'bool'=>true]);
        } else {
            return response()->json(['message'=>'Такая связь уже существует!', 'bool'=>false]);
        }

    }

    public function setTableDisconnectUsers(Request $request){
        $value = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                                                    ->where('id_users', '=', $request->input('id_users'))
                                                    ->pluck('id');

        if($value != null) {
            DB::table('set_forms_users')->where('id', '=', $value)->delete();
            return response()->json(['message'=>'Связь успешно разорвана.', 'bool'=>true]);
        } else {
            return response()->json(['message'=>'Такой связи не существует!', 'bool'=>false]);
        }
    }
}
