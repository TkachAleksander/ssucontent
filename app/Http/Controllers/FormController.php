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
                    ->join('status_checks as sc', 'sc.id','=','sfu.id_status_checks')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->where('sfu.id_status_checks','=',2)
                    ->select('u.surname','u.name','u.middle_name','sfu.id_forms','f.name_forms')
                    ->get();
                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                }
                return view('homeAdmin',['forms' => $forms, 'role' => $role[0]->name_roles]);
            } else {
                $role[0]->name_roles = null; // Не выводит имя пользователя в списке доступных форм (home)

                $forms =  DB::table('set_forms_users as sfu')->where('sfu.id_users','=',$id_users)
                    ->join('users as u', 'u.id','=','sfu.id_users')
                    ->join('forms as f', 'f.id','=','sfu.id_forms')
                    ->join('status_checks as sc', 'sc.id','=','sfu.id_status_checks')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->select('u.surname','u.name','u.middle_name','sfu.id_forms','sc.name_status_checks','sc.border_color','f.name_forms')
                    ->orderBy('sfu.id','asc')
                    ->get();

                foreach ($forms as $key=>$form) {
                    $forms[$key]->info = DB::table('set_forms_elements as sfe')->where('sfe.id_forms', '=', $form->id_forms)
                        ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
                        ->join('elements as e', 'e.id', '=', 'se.id_elements')
                        ->select('se.name_set_elements', 'se.label_set_elements', 'sfe.width', 'e.name_elements')
                        ->get();
                }
            }
            return view('homeUser',['forms' => $forms, 'role' => $role[0]->name_roles]);
        } else {
            return view('homeUser');
        }
    }

    public function submitFillForm(Request $request){
        $id_set_forms_elements = DB::table('set_forms_elements')->where('id_forms','=',$request->input('id_form'))->pluck('id');

        $row  = $i = 0;
        foreach ($request->all() as $key=>$value){
            if ($i++ >= 2) {
                DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements[$row++], 'value' => $value]);
            }
        }

        DB::table('set_forms_users')->where('id_users','=',Auth::user()->id)->where('id_forms','=',$request->input('id_form'))
        ->update(['id_status_checks'=>2]);

        redirect('/homeUser');
    }
}
