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
    public function index(){

        if (!Auth::guest()){
            $role = DB::table('users')
                ->where('name', '=', Auth::user()->name)
                ->join('roles', 'roles.id', '=', 'users.id_roles')
                ->select('name_roles')
                ->get();
            $id_users = Auth::user()->id;

            if ($role[0]->name_roles == self::ADMINISTRATOR)
            {
                $forms =  DB::table('set_forms_users as sfu')
                    ->join('users as u', 'u.id','=','sfu.id_users')
                    ->join('forms as f', 'f.id','=','sfu.id_forms')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->select('u.surname','u.name','u.middle_name','sfu.id_forms','f.name_forms')
                    ->get();
                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                }

            } else {
                $role[0]->name_roles = null; // Не выводит имя пользователя в списке доступных форм (home)

                $forms =  DB::table('set_forms_users as sfu')->where('sfu.id_users','=',$id_users)
                    ->join('users as u', 'u.id','=','sfu.id_users')
                    ->join('forms as f', 'f.id','=','sfu.id_forms')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->select('u.surname','u.name','u.middle_name','sfu.id_forms','f.name_forms')
                    ->get();

                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                }
            }
            return view('home',['forms' => $forms, 'role' => $role[0]->name_roles]);
        } else {
            return view('home');
        }
    }
}
