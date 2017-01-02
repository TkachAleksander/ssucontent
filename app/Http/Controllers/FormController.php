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
                    ->select('d.name_departments'/*,'sfd.id'*/,'sfd.id_forms','f.name_forms')
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

                $forms =  DB::table('set_forms_departments as sfd')->where('sfd.id_departments','=',$id_departments)
                    ->join('departments as d', 'd.id','=','sfd.id_departments')
                    ->join('forms as f', 'f.id','=','sfd.id_forms')
                    ->join('status_checks as sc', 'sc.id','=','sfd.id_status_checks')
                    ->where('f.show','=', self::SHOW_FORMS)
                    ->where('sfd.id_status_checks','!=',2)
                    ->select('d.name_departments'/*,'sfd.id'*/,'sfd.id_forms','sc.name_status_checks','sc.id as id_status_checks','sc.border_color','f.name_forms')
                    ->orderBy('sfd.id','asc')
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
        $id_set_forms_elements = DB::table('set_forms_elements')->where('id_forms','=',$request->input('id_forms'))
            ->where('version', '=', 1)->pluck('id');
        $row  = $i = 0;
        foreach ($request->all() as $key=>$value){
            if ($i++ >= 2) {
                DB::table('values_forms')->where('id_set_forms_elements','=',$id_set_forms_elements[$row])->increment('version_values_forms',1);
                DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements[$row++], 'values_forms' => $value]);
                DB::table('values_forms')->where('version_values_forms','>=', 3)->delete();
            }
        }

        DB::table('set_forms_departments')->where('id_departments','=',Auth::user()->id_departments)->where('id_forms','=',$request->input('id_forms'))
        ->update(['id_status_checks'=>2]);

        return redirect('/');
    }
}
