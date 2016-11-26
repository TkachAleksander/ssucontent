<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

class ConstructorFormController extends Controller
{
    public function addForm(){
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
            ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
            ->orderBy('set_elements.name_set_elements', 'asc')
            ->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id_set_elements;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->where('show', '=', 1)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;
        }

        return view('constructor.addForm', ['set_elements' => $set_elements]);
    }

    public function getSetElements(Request $request){
        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('idSetElement'))->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;
        }
        return response()->json($set_elements);
    }

    public function addSetFormsElementsToServer(Request $request){
        if (!empty($request->input('name_forms'))){
            $id_forms = DB::table('forms')->insertGetId([ 'name_forms' => $request->input('name_forms') ]);
            foreach ($request->input('queue') as $key => $value) {
                DB::table('set_forms_elements')->insert([ 'id_forms' => $id_forms,
                    'id_set_elements' => $value
                ]);
            }
            return "Форма успешно добавлена !";
        } else {
            return "Форма заполнена неверно !";
        }
    }





    public function newElement(){
        $elements = DB::table('elements')->get();
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
            ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
            ->orderBy('set_elements.name_set_elements', 'asc')
            ->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id_set_elements;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->where('show', '=', 1)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;
        }

        return view('constructor.newElement', ['elements' => $elements,'set_elements' => $set_elements]);
    }
    
    public function addNewElementToServer(Request $request){
        $id = DB::table('set_elements')->insertGetId([
            'name_set_elements' => $request->input('name_set_elements'),
            'label_set_elements' => $request->input('label_set_elements'),
            'id_elements' => $request->input('id_elements')
        ]);
        if(!empty($request->input('value_sub_elements'))) {
            foreach ($request->input('value_sub_elements') as $key => $value) {
                if (!empty($value)) {
                    $value = trim ($value ," \t\n\r\0\x0B");
                    DB::table('sub_elements')->insert([
                        'id_set_elements' => $id,
                        'value_sub_elements' => $value
                    ]);
                }
            }
        }
        return redirect('/constructor/newElement');
    }

    public function editElementFromForm(Request $request){

        $set_elements = DB::table('set_elements')->where('id', '=', $request->input('id_set_elements'))->get();

        $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $request->input('id_set_elements'))->select('id','value_sub_elements')->orderBy('id','desc')->get();
        return response()->json(['set_elements' => $set_elements, 'sub_elements' => $sub_elements]);
    }

    public function addEditedNewElement(Request $request) {
        $set_elements = DB::table('set_elements')->where('name_set_elements', '=', $request->input('name_set_elements'))
            ->where('label_set_elements', '=', $request->input('label_set_elements'))
            ->get();

        if($set_elements != []){
            $value_new_sub_elements = $request->value_sub_elements;
            $uninstalled_sub_elements = $_COOKIE['uninstalled_sub_elements'];

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
        return redirect('/constructor/newElement');
    }





    public function showForms(){
        $forms = DB::table('forms')->get();
        return view('constructor.showForms', ['forms' => $forms]);
    }

    public function getFormInfo(Request $request){
        $form_info = DB::table('set_forms_elements as sfe')->where('id_forms', '=', $request->input('id_forms'))
            ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
            ->join('elements as e', 'e.id', '=', 'se.id_elements')
            ->select('sfe.id_set_elements', 'sfe.width', 'se.name_set_elements', 'se.label_set_elements', 'e.name_elements')
            ->get();
        foreach ($form_info as $key => $set_element) {
            $id_set_element = $set_element->id_set_elements;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $form_info[$key]->value_sub_elements = $value_sub_elements;
        }

        return response()->json($form_info);
    }





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
        $users = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                                                    ->join('users as u', 'u.id', '=', 'sfu.id_users')
                                                    ->select('u.surname', 'u.name', 'u.middle_name')
                                                    ->orderBy('u.surname', 'asc')
                                                    ->get();
        return response()->json($users);
    }

    public function setTableConnectUsers(Request $request){
        $value = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                                                    ->where('id_users', '=', $request->input('id_users'))
                                                    ->get();
        if($value == null) {
            DB::table('set_forms_users')->insert(['id_forms' => $request->input('id_forms'),
                'id_users' => $request->input('id_users')]);
            $value = "Связь успешно добавлена.";
        } else {
            $value = "Такая связь уже существует !";
        }
        return response()->json($value);

    }

    public function setTableDisconnectUsers(Request $request){
        $value = DB::table('set_forms_users as sfu')->where('id_forms', '=', $request->input('id_forms'))
                                                    ->where('id_users', '=', $request->input('id_users'))
                                                    ->pluck('id');

        if($value != null) {
            DB::table('set_forms_users')->where('id', '=', $value)->delete();
            $value = "Связь успешно разорвана.";
        } else {
            $value = "Такой связи не существует !";
        }
        return response()->json($value);
    }
}
