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

            if ($role[0]->name_roles == self::ADMINISTRATOR) {
                $forms = DB::table('forms_departments as fd')
                    ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
                    ->join('forms as f', 'f.id_forms', '=', 'fd.id_forms')
                    ->join('status_checks as sc', 'sc.id_status_checks', '=', 'fd.id_status_checks')
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

                $forms = DB::table('forms_departments as fd')->where('fd.id_departments', '=', Auth::user()->id_departments)
                    ->join('departments as d', 'd.id_departments', '=', 'fd.id_departments')
                    ->join('forms as f', 'f.id_forms', '=', 'fd.id_forms')
                    ->join('status_checks as sc', 'sc.id_status_checks', '=', 'fd.id_status_checks')
                    ->where('fd.id_status_checks', '!=', 2)
                    ->select('d.name_departments', 'fd.id_forms_departments', 'fd.id_departments', 'fd.id_forms', 'sc.name_status_checks', 'sc.id_status_checks', 'sc.status_color', 'f.name_forms')
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



    // UserHome кнопка отправить форму на проверку
    public function submitFillForm(Request $request)
    {
//        dd($request->all());

        $arrValues = 0;

        DB::table('values_fields_current')
            ->where('id_forms_departments','=',$request->input('id_forms_departments'))
            ->delete();

        // Получаем $id_fields_forms с ключей, $values с значений
        foreach ($request->all() as $id_fields_forms => $values) {

            // Пропускаем (1)_token (2)id_forms (3)id_forms_departments
            if ($arrValues++ >= 3) {

                // Если новые значения пришли строкой
                if (!is_array($values)) {

                    // запись значений в values_fields_current
                    DB::table('values_fields_current')
                        ->insert([
                            'id_fields_forms' => $id_fields_forms,
                            'id_forms_departments' => $request->input('id_forms_departments'),
                            'values_fields_current' => (!empty($values)) ? $values : 0 ,
                            'enum_sub_elements_current' => 0
                        ]);

                // Если новые значения пришли массивом
                } else {
                    foreach ($values as $value) {
                        // запись перебором, id_sub_elements в enum_sub_elements_current
                        DB::table('values_fields_current')
                            ->insert([
                                'id_fields_forms' => $id_fields_forms,
                                'id_forms_departments' => $request->input('id_forms_departments'),
                                'values_fields_current' => 0 ,
                                'enum_sub_elements_current' => $value
                            ]);

                    }
                }
            }
        }

        // Ставим статус формы - праверяется администратором
        DB::table('forms_departments')
            ->where('id_forms_departments','=',$request->input('id_forms_departments'))
            ->update(['id_status_checks' => 2]);

//        dd($request->all(),$id_set_forms_elements);
        return redirect('/');
    }



    // adminHome Принять/Отклонить форму

    public function rejectForm(Request $request)
    {
        if (DB::table('forms_departments')->where('id_forms_departments', '=', $request->input('id_forms_departments'))->update(['id_status_checks' => self::REJECT_FORM])) {
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
        // Изменяем статус формы для данного отдела на принята
        if (DB::table('forms_departments')->where('id_forms_departments', '=', $request->input('id_forms_departments'))->update(['id_status_checks' => self::SUCCESS_FORM])) {
            $bool = true;
            $message = "Форма успешно принята !";
        } else {
            $bool = false;
            $message = "Форма не найдена !";
        }

        // Узнаем label_fields, id_fields_forms, id_fields
        $fields_info = DB::table('values_fields_current as vfc')
            ->where('vfc.id_forms_departments', '=', $request->input('id_forms_departments'))
            ->join('fields_forms as ff', 'ff.id_fields_forms','=','vfc.id_fields_forms')
            ->join('fields as f', 'f.id_fields','=','ff.id_fields')
            ->orderBy('ff.id_fields_forms','asc')
            ->select('f.label_fields', 'ff.id_fields_forms','ff.id_fields')
            ->distinct()
            ->get();

        foreach ($fields_info as  $field_info) {
            // Удаляем поля старой формы
            DB::table('fields_forms_old')
                ->where('id_forms_departments','=',$request->input('id_forms_departments'))
                ->where('id_fields_forms','=',$field_info->id_fields_forms)
                ->delete();

            // Удаляем значения для полей старой формы
            DB::table('values_fields_old')
                ->where('id_forms_departments','=',$request->input('id_forms_departments'))
                ->where('id_fields_forms','=',$field_info->id_fields_forms)
                ->delete();

            // Удаляем значения sub_elements для полей старой формы
            DB::table('sub_elements_old')
                ->where('id_forms_departments','=',$request->input('id_forms_departments'))
                ->where('id_fields_forms','=',$field_info->id_fields_forms)
                ->delete();
//var_dump($field_info);
        }

        foreach ($fields_info as  $field_info) {

            // Узнаем sub_elements_current
            $sub_elements_current = DB::table('sub_elements_fields as sef')
                ->where('sef.id_fields','=',$field_info->id_fields)
                ->join('sub_elements_current as sec', 'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
                ->select('sec.id_sub_elements_field','sec.label_sub_elements_current')
                ->get();

            // Узнаем required
            $required = DB::table('fields_forms_current')
                ->where('id_fields_forms','=', $field_info->id_fields_forms)
                ->value('required_fields_current');

            // Вставляем новые поля для старой формы
            DB::table('fields_forms_old')
                ->insert([
                    'id_fields_forms' => $field_info->id_fields_forms,
                    'id_forms_departments' => $request->input('id_forms_departments'),
                    'required_fields_old' => $required,
                    'label_fields_old' => $field_info->label_fields
                ]);

            // Удаляем sub_elements для старой формы
                DB::table('sub_elements_old')
                    ->where('id_fields_forms','=',$field_info->id_fields_forms)
                    ->where('id_forms_departments','=',$request->input('id_forms_departments'))
                    ->delete();

            // Узнаем values_fields_current, enum_sub_elements_current из таблицы values_fields_current
            $values_fields_current = DB::table('values_fields_current')
                ->where('id_fields_forms','=',$field_info->id_fields_forms)
                ->value('values_fields_current');

            $label_sub_elements_current = DB::table('values_fields_current as vfc')
                ->where('id_fields_forms','=',$field_info->id_fields_forms)
                ->join('sub_elements_current as sec', 'sec.id_sub_elements_current','=','vfc.enum_sub_elements_current')
                ->value('label_sub_elements_current');

            if (!empty($sub_elements_current)){

                // Вставляем новые sub_elements для старой формы
                foreach ($sub_elements_current as $sub_element) {
//var_dump($label_sub_elements_current,$sub_element->label_sub_elements_current);
                    $id_sub_elements_old = DB::table('sub_elements_old')
                        ->insertGetID([
                            'id_sub_elements_field' => $sub_element->id_sub_elements_field,
                            'id_fields_forms' => $field_info->id_fields_forms,
                            'id_forms_departments' => $request->input('id_forms_departments'),
                            'label_sub_elements_old' => $sub_element->label_sub_elements_current
                        ]);
                        if ($label_sub_elements_current == $sub_element->label_sub_elements_current) {

                            // Вставляем новые значения для полей старой формы
                            DB::table('values_fields_old')
                                ->insert([
                                    'id_fields_forms' => $field_info->id_fields_forms,
                                    'id_forms_departments' => $request->input('id_forms_departments'),
                                    'values_fields_old' => 0,
                                    'enum_sub_elements_old' => $id_sub_elements_old
                                ]);
                        }
                }
            } else {
                if (!empty($values_fields_current)) {
                    // Вставляем новые значения для полей старой формы
                    DB::table('values_fields_old')
                        ->insert([
                            'id_fields_forms' => $field_info->id_fields_forms,
                            'id_forms_departments' => $request->input('id_forms_departments'),
                            'values_fields_old' => $values_fields_current,
                            'enum_sub_elements_old' => 0
                        ]);
                }
            }





        }

        return response()->json(['message' => $message, 'bool' => $bool]);
    }


}
