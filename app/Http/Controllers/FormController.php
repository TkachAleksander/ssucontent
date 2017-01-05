<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class FormController extends Controller
{
    const SHOW_FORMS = 1;
    const ADMINISTRATOR = 'administrator';

    
    public function generateString($length = 8){
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }
    
    
    public function index(){

        if (!Auth::guest()){
            $role = DB::table('users')
                ->where('name', '=', Auth::user()->name)
                ->join('roles', 'roles.id', '=', 'users.id_roles')
                ->select('name_roles')
                ->get();
            $id_departments = DB::table('users')
                ->where('name', '=', Auth::user()->name)
                ->pluck('id_departments');
            $id_users = Auth::user()->id;

            if ($role[0]->name_roles == self::ADMINISTRATOR)
            {
                $forms =  DB::table('set_forms_departments as sfd')
                    ->join('departments as d', 'd.id','=','sfd.id_departments')
                    ->join('forms as f', 'f.id','=','sfd.id_forms')
                    ->join('status_checks as sc', 'sc.id','=','sfd.id_status_checks')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->where('sfd.id_status_checks','=',2)
                    ->select('d.name_departments','sfd.id_departments','sfd.id_forms','f.name_forms')
                    ->get();
                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                    $forms[$key]->generateString = $this->generateString();
                }
                return view('homeAdmin',['forms' => $forms, 'role' => $role[0]->name_roles]);
            } else {
                $role[0]->name_roles = null; // Не выводит имя пользователя в списке доступных форм (home)

                $forms =  DB::table('set_forms_departments as sfd')->where('sfd.id_departments','=',$id_departments)
                    ->join('departments as d', 'd.id','=','sfd.id_departments')
                    ->join('forms as f', 'f.id','=','sfd.id_forms')
                    ->join('status_checks as sc', 'sc.id','=','sfd.id_status_checks')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->where('sfd.id_status_checks','!=',2)
                    ->select('d.name_departments','sfd.id_departments','sfd.id_forms','sc.name_status_checks','sc.id as id_status_checks','sc.border_color','f.name_forms')
                    ->orderBy('sfd.id','asc')
                    ->get();

                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                    $forms[$key]->generateString = $this->generateString();
                }
            }
            return view('homeUser',['forms' => $forms, 'role' => $role[0]->name_roles, 'id_departments' => Auth::user()->id_departments]);
        } else {
            return view('homeUser');
        }
    }

    public function submitFillForm(Request $request){
//        dd($request->all());
        // Выбираем все id_set_forms_elements в заполняемой форме
        $id_set_forms_elements = DB::table('set_forms_elements')->where('id_forms','=',$request->input('id_forms'))
            ->where('version', '=', 1)->pluck('id');
//        $row  = $i = 0;
        $arrValues = 0;
        $row_id_set_forms_elements = 0;

        foreach ($request->all() as $key=>$value){
            // Отсеиваем (1)_token и (2)id_forms
            if ($arrValues++ >= 2) {
                // Если есть значения для этой формы +1 к версии
                DB::table('values_forms')->where('id_set_forms_elements','=',$id_set_forms_elements[$row_id_set_forms_elements])->where('id_departments','=',Auth::user()->id_departments)->increment('version_values_forms',1);
                // Если элемент содержит массив
                if (is_array($value)){
                    // Записываем по очереди его элементы(id_sub_elements) с одинаковым id_set_forms_elements
                    foreach ($value as $id_sub_elements){
//                        dd($id_sub_elements);
                        DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements[$row_id_set_forms_elements],'id_departments' => Auth::user()->id_departments, 'values_forms' => $id_sub_elements]);
                    }
                } else {
                    // Если значение строка берем следующтй id_set_forms_elements
                    DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements[$row_id_set_forms_elements], 'id_departments' => Auth::user()->id_departments, 'values_forms' => $value]);
                }
                $row_id_set_forms_elements++;
                // Удяляем 3ю версию данных
                DB::table('values_forms')->where('version_values_forms', '>=', 3)->where('id_departments', '=', Auth::user()->id_departments)->delete();
            }
        }
        // Ставим статус формы на праверяется администратором
        DB::table('set_forms_departments')->where('id_forms','=',$request->input('id_forms'))->where('id_departments','=',Auth::user()->id_departments)
        ->update(['id_status_checks'=>2]);
//        dd($request->all(),$id_set_forms_elements);
        return redirect('/');
    }
}
