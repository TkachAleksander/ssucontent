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
    const REJECT_FORM = 4;
    const SUCCESS_FORM = 3;


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


    public function index()
    {

        if (!Auth::guest()) {
            $role = DB::table('users')
                ->where('name', '=', Auth::user()->name)
                ->join('roles', 'roles.id_roles', '=', 'users.id_roles')
                ->select('roles.name_roles')
                ->get();
            $id_departments = DB::table('users')
                ->where('name', '=', Auth::user()->name)
                ->pluck('id_departments');

            if ($role[0]->name_roles == self::ADMINISTRATOR) {
                $forms = DB::table('forms_departments as fd')
                    ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
                    ->join('forms as f', 'f.id_forms', '=', 'fd.id_forms')
                    ->join('status_checks as sc', 'sc.id_status_checks', '=', 'fd.id_status_checks')
//                    ->where('f.show', '=', self::SHOW_FORMS)
                    ->where('fd.id_status_checks', '=', 2)
                    ->select('d.name_departments', 'fd.id_forms_departments', 'fd.id_departments', 'fd.id_forms', 'f.name_forms')
                    ->get();
                
                foreach ($forms as $key => $form) {
                    $forms[$key]->info = DB::table('fields_forms as ff')->where('ff.id_forms', '=', $form->id_forms)
                        ->join('fields as f', 'f.id_fields', '=', 'ff.id_fields_forms')
                        ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
                        ->select('f.label_fields','e.name_elements')
                        ->get();
                    $forms[$key]->generateString = $this->generateString();
                }
                return view('homeAdmin', ['forms' => $forms, 'role' => $role[0]->name_roles]);
            } else {
                $role[0]->name_roles = null; // Не выводит имя пользователя в списке доступных форм (home)

                $forms = DB::table('forms_departments as fd')->where('fd.id_departments', '=', $id_departments)
                    ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
                    ->join('forms as f', 'f.id_forms', '=', 'fd.id_forms')
                    ->join('status_checks as sc', 'sc.id_status_checks', '=', 'fd.id_status_checks')
//                    ->where('f.show', '=', self::SHOW_FORMS)
                    ->where('fd.id_status_checks', '!=', 2)
                    ->select('d.name_departments', 'fd.id_departments', 'fd.id_forms', 'sc.name_status_checks', 'sc.id_status_checks', 'sc.status_color', 'f.name_forms')
                    ->orderBy('fd.id_departments', 'asc')
                    ->get();

                foreach ($forms as $key => $form) {
                    $forms[$key]->info = DB::table('fields_forms as ff')->where('ff.id_forms', '=', $form->id_forms)
                        ->join('fields as f', 'f.id_fields', '=', 'ff.id_fields_forms')
                        ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
                        ->select('f.label_fields','e.name_elements')
                        ->get();
                    $forms[$key]->generateString = $this->generateString();
                }
            }
            return view('homeUser', ['forms' => $forms, 'role' => $role[0]->name_roles, 'id_departments' => Auth::user()->id_departments]);
        } else {
            return view('homeUser');
        }
    }

    public function submitFillForm(Request $request)
    {
//        dd($request->all());

        $arrValues = 0;
        foreach ($request->all() as $id_set_forms_elements => $values) {
            // Отсеиваем (1)_token и (2)id_forms
            if ($arrValues++ >= 2) {
                // Если есть значения для этой формы +1 к версии
                DB::table('values_forms')->where('id_set_forms_elements', '=', $id_set_forms_elements)->where('id_departments', '=', Auth::user()->id_departments)->increment('version_values_forms', 1);
                // Если элемент содержит массив
                if (is_array($values)) {
//                    dd($values);
                    // Собираем label_sub_elements в массив
                    foreach ($values as $key => $id_sub_elements) {
                        $temp_label_sub_elements = DB::table('sub_elements')->where('id','=',$id_sub_elements)->pluck('value_sub_elements');
                        $l[$key] = $temp_label_sub_elements[0];
                    }
//                    dd($label_sub_elements);
                    $id_sub_elements = implode(' | ',$values);
                    $label_sub_elements = implode(' | ',$l);

                    DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements, 'id_departments' => Auth::user()->id_departments, 'values_forms' => $id_sub_elements, 'checked_sub_elements' => $label_sub_elements]);
//                    dd($values,$label_sub_elements);
                } else {
                    // Если значение строка записываем как есть $value
                    DB::table('values_forms')->insert(['id_set_forms_elements' => $id_set_forms_elements, 'id_departments' => Auth::user()->id_departments, 'values_forms' => $values]);
                }
                // Удяляем 3ю версию данных
                DB::table('values_forms')->where('version_values_forms', '>=', 3)->where('id_departments', '=', Auth::user()->id_departments)->delete();
            }
        }
        // Ставим статус формы - праверяется администратором
        DB::table('set_forms_departments')->where('id_forms', '=', $request->input('id_forms'))->where('id_departments', '=', Auth::user()->id_departments)
            ->update(['id_status_checks' => 2]);
//        dd($request->all(),$id_set_forms_elements);
        return redirect('/');
    }

    public function rejectForm(Request $request)
    {
        if (DB::table('set_forms_departments')->where('id', '=', $request->input('id_set_forms_departments'))->update(['id_status_checks' => self::REJECT_FORM])) {
            $bool = true;
            $message = "Форма успешно отклонена !";
        } else {
            $bool = false;
            $message = "Форма не найдена !";
        }
        return response()->json(['message' => $message, 'bool' => $bool]);
    }

    public function acceptForm(Request $request)
    {
        if (DB::table('set_forms_departments')->where('id', '=', $request->input('id_set_forms_departments'))->update(['id_status_checks' => self::SUCCESS_FORM])) {
            $bool = true;
            $message = "Форма успешно принята !";
        } else {
            $bool = false;
            $message = "Форма не найдена !";
        }
        return response()->json(['message' => $message, 'bool' => $bool]);
    }













    const SHOW = 1;

    public function test(){
        // вывод пустой новой таблицы c данными и подэлементами
        $current_form = DB::table('fields_forms as ff')
            ->where('ff.id_forms','=',2)
            ->join('fields as f', 'f.id_fields','=','ff.id_fields')
            ->leftJoin('sub_elements_current as sec', 'sec.id_fields','=','f.id_fields')
            ->join('forms_fields_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')

            ->join('elements as e', 'e.id_elements','=','f.id_elements')
            ->get();
        $old_form = DB::table('fields_forms as ff')
            ->where('ff.id_forms','=',2)
            ->join('fields as f', 'f.id_fields','=','ff.id_fields')
            ->leftJoin('sub_elements_old as seo', 'seo.id_fields','=','f.id_fields')
            ->join('forms_fields_old as ffo', 'ffo.id_fields_forms','=','ff.id_fields_forms')
            ->where('ffo.id_forms_departments','=',3)
            ->join('elements as e', 'e.id_elements','=','f.id_elements')
            ->get();
            dd($current_form, $old_form);
    }








}
