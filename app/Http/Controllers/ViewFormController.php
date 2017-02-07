<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class ViewFormController extends Controller
{
    const SHOW_FORMS = 1;
    const CHECKOUT_FORM = 2;
    const SUCCESS_FORM = 3;
    const REJECT_FORM = 4;

    const ADMINISTRATOR = 1;
    
    
    public function viewForm($id_forms_departments) {

        $forms_departments = DB::table('forms_departments')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->select('updated_at', 'id_forms', 'id_status_checks')
            ->get();

        if (empty($forms_departments)){
            $status = [
                "class" => "danger",
                "message" => "Форма не стоит на проверке"
            ];
            return redirect('/')->with("status", $status);
        }

        if (Auth::user()->id_roles == self::ADMINISTRATOR) {
            if ($forms_departments[0]->id_status_checks != self::CHECKOUT_FORM){
                $status = [
                    "class" => "danger",
                    "message" => "Форма не стоит на проверке"
                ];
                return redirect('/')->with("status", $status);
            } else {
                $action = "/acceptForm";
                $admin = true;
                $required = "required";
            }
        } else {
            if ($forms_departments[0]->id_status_checks != 2 ){
                $action = "/submitFillForm";
            } else {
                $action = "/submitFillFormRepeatedly";
            }

            $admin = false;
            $required = "";
        }

        $name_forms = DB::table('forms')
            ->where('id_forms', '=', $forms_departments[0]->id_forms)
            ->value('name_forms');

        $messages = DB::table('messages as m')
            ->where('m.id_forms_departments', '=', $id_forms_departments)
            ->join('users as u', 'u.id', '=', 'm.id')
            ->select('m.*', 'u.surname', 'u.name', 'u.middle_name')
            ->orderBy('created_at', 'desc')
            ->get();





        // Проверяем есть ли поля в sub_elements_old
        $forms_info_old = DB::table('fields as f')
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$forms_departments[0]->id_forms)
            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('fields_forms_old as ffo', 'ffo.id_fields_forms','=','ff.id_fields_forms')
            ->where('ffo.id_forms_departments','=', $id_forms_departments)
            ->leftJoin('sub_elements_old as seo' ,'seo.id_fields_forms','=','ff.id_fields_forms')
            ->orderBy('ffo.id_fields_forms_old', 'asc')
            ->groupBy('ff.id_fields_forms')
            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'seo.id_forms_departments', 'ffo.required_fields_old as required',
                DB::raw('group_concat(seo.label_sub_elements_old ORDER BY seo.label_sub_elements_old ASC separator " | ") as labels_sub_elements'),
                DB::raw('group_concat(seo.id_sub_elements_field separator " | ") as id_sub_elements'))
            ->get();

        if (!empty($forms_info_old)){
            // Для каждого поля и массива $form_infos ищем значения
            foreach ($forms_info_old as $key => $form_info) {

                // Выбираем значения из таблицы values_fields_old
                $values = DB::table('values_fields_old')
                    ->where('id_fields_forms', '=', $form_info->id_fields_forms)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->select('values_fields_old', 'enum_sub_elements_old', 'id_forms_departments')
                    ->get();
                // Если значения есть
                if (!empty($values)) {
                    // Добавляем в массив $form_infos values_fields_current
                    $forms_info_old[$key]->values_fields = $values[0]->values_fields_old;

                    // enum_sub_elements_current добавляем массивом
                    foreach ($values as $key_value => $value) {
                        if ($values[$key_value]->enum_sub_elements_old != 0) {
                            $forms_info_old[$key]->enum_sub_elements[$key_value] = $values[$key_value]->enum_sub_elements_old;
                        }
                    }
                }
            }
        } else {
            $form_infos = null;
        }





        $forms_info_new = DB::table('fields as f')
            ->join('fields_forms as ff', 'ff.id_fields','=','f.id_fields')
            ->where('ff.id_forms','=',$forms_departments[0]->id_forms)
            ->join('elements as e', 'e.id_elements', '=', 'f.id_elements')
            ->leftJoin('sub_elements_fields as sef', 'sef.id_fields', '=', 'f.id_fields')
            ->leftJoin('sub_elements_current as sec', 'sec.id_sub_elements_field','=','sef.id_sub_elements_field')
            ->join('fields_forms_current as ffc', 'ffc.id_fields_forms','=','ff.id_fields_forms')
            ->orderBy('ffc.id_fields_forms_current','asc')
            ->groupBy('ff.id_fields_forms')
            ->select('f.id_fields', 'f.label_fields', 'ff.id_fields_forms', 'e.name_elements', 'sef.id_sub_elements_field','ffc.required_fields_current as required',
                DB::raw('group_concat(sec.label_sub_elements_current ORDER BY sec.label_sub_elements_current ASC separator " | ") as labels_sub_elements'),
                DB::raw('group_concat(sef.id_sub_elements_field separator " | ") as id_sub_elements'))
            ->get();

        // Для каждого поля и массива $forms_info ищем значения
        foreach ($forms_info_new as $key => $form_info) {

            // Выбираем значения (values_fields_current,enum_sub_elements_current) из таблицы values_fields_current
            $values = DB::table('values_fields_current')
                ->where('id_fields_forms', '=', $form_info->id_fields_forms)
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->select('values_fields_current', 'enum_sub_elements_current','id_forms_departments')
                ->get();

            // Если значения есть
            if(!empty($values)) {
                // Добавляем в массив $forms_info values_fields_current
                $forms_info_new[$key]->values_fields = $values[0]->values_fields_current;

                // enum_sub_elements_current добавляем массивом
                foreach ($values as $key_value => $value) {
                    if ($values[$key_value]->enum_sub_elements_current != 0) {
                        $forms_info_new[$key]->enum_sub_elements[$key_value] = $values[$key_value]->enum_sub_elements_current;
                    }
                }
                // Если значений нет
            } else {
                // Выбираем значения (values_fields_old,enum_sub_elements_old) из таблицы values_fields_old
                $values = DB::table('values_fields_old')
                    ->where('id_fields_forms', '=', $form_info->id_fields_forms)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->select('values_fields_old', 'enum_sub_elements_old')
                    ->get();

                // Если значения есть
                if(!empty($values)) {
                    // Добавляем в массив $forms_info values_fields_current
                    $forms_info_new[$key]->values_fields = $values[0]->values_fields_old;

                    // enum_sub_elements_current добавляем массивом
                    foreach ($values as $key_value => $value) {
                       if ($values[$key_value]->enum_sub_elements_old != 0) {
                           $forms_info_new[$key]->enum_sub_elements[$key_value] = $values[$key_value]->enum_sub_elements_old;
                       }
                    }
                }
            }
        }


        return view('viewForm', [
            'id_forms' => $forms_departments[0]->id_forms,
            'id_forms_departments' => $id_forms_departments,
            'id_status_checks' => $forms_departments[0]->id_status_checks,
            'name_forms' => $name_forms,
            'updated_at' => $forms_departments[0]->updated_at,
            'action' => $action,
            'admin' => $admin,
            'required' => $required,
            'messages' => $messages,
            'forms_info_new' => $forms_info_new,
            'forms_info_old' => $forms_info_old
        ]);

    }

    // UserHome кнопка отправить форму на проверку
    public function submitFillForm(Request $request)
    {

        $id_forms_departments = $request->input('id_forms_departments');
        $id_user = Auth::user()->id;
        $id_forms = DB::table('forms_departments')
            ->where('id_forms_departments','=',$id_forms_departments)
            ->value('id_forms');

        // Удаляем старые значения current
        DB::table('values_fields_current')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->delete();

        // Получаем: $id_fields_forms с ключей, $values с значений
        foreach ($request->all() as $id_fields_forms => $values) {

            // Пропускаем (1)_token (2)id_forms (3)id_forms_departments
            if (is_int($id_fields_forms)) {

                // Если новые значения пришли строкой
                if (!is_array($values)) {

                    // запись значений в values_fields_current
                    DB::table('values_fields_current')
                        ->insert([
                            'id_fields_forms' => $id_fields_forms,
                            'id_forms_departments' => $id_forms_departments,
                            'values_fields_current' => (!empty($values)) ? $values : 0,
                            'enum_sub_elements_current' => 0
                        ]);


                    // Если новые значения пришли массивом
                } else {
                    foreach ($values as $value) {
                        // запись перебором, id_sub_elements в enum_sub_elements_current
                        DB::table('values_fields_current')
                            ->insert([
                                'id_fields_forms' => $id_fields_forms,
                                'id_forms_departments' => $id_forms_departments,
                                'values_fields_current' => 0,
                                'enum_sub_elements_current' => $value
                            ]);

                    }
                }
            }
        }

        // Ставим статус формы - праверяется
        DB::table('forms_departments')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->update(['id_status_checks' => 1]);
        DB::table('forms_departments')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->update([
                'id_status_checks' => self::CHECKOUT_FORM,
                'id_users' => $id_user
            ]);

        // Отмечаем сообщения для данной формы-депарьамента прочитанными
        DB::table('messages')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->where('id','!=',$id_user)
            ->update(['is_read' => 1]);

        $this->writeLog($id_user ,$id_forms, 1);

        $status = [
            'class' => 'success',
            'message' => 'Форма успешно отправлена на проверку'
        ];

        return redirect('/')->with('status', $status);
    }

    public function submitFillFormRepeatedly(Request $request) {

        $id_forms_departments = $request->input('id_forms_departments');
        $id_user = Auth::user()->id;
        $id_forms = DB::table('forms_departments')
            ->where('id_forms_departments','=',$id_forms_departments)
            ->value('id_forms');

        // Если сообщение не пустое
        if (!empty($request->input('message'))) {

            // Удаляем старые значения current
            DB::table('values_fields_current')
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->delete();

            // Получаем: $id_fields_forms с ключей, $values с значений
            foreach ($request->all() as $id_fields_forms => $values) {

                // Пропускаем (1)_token (2)id_forms (3)id_forms_departments
                if (is_int($id_fields_forms)) {

                    // Если новые значения пришли строкой
                    if (!is_array($values)) {

                        // запись значений в values_fields_current
                        DB::table('values_fields_current')
                            ->insert([
                                'id_fields_forms' => $id_fields_forms,
                                'id_forms_departments' => $id_forms_departments,
                                'values_fields_current' => (!empty($values)) ? $values : 0,
                                'enum_sub_elements_current' => 0
                            ]);


                        // Если новые значения пришли массивом
                    } else {
                        foreach ($values as $value) {
                            // запись перебором, id_sub_elements в enum_sub_elements_current
                            DB::table('values_fields_current')
                                ->insert([
                                    'id_fields_forms' => $id_fields_forms,
                                    'id_forms_departments' => $id_forms_departments,
                                    'values_fields_current' => 0,
                                    'enum_sub_elements_current' => $value
                                ]);
                        }
                    }
                }
            }

            // Ставим статус формы - праверяется
            DB::table('forms_departments')
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->update(['id_status_checks' => 1]);
            DB::table('forms_departments')
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->update([
                    'id_status_checks' => self::CHECKOUT_FORM,
                    'id_users' => $id_user
                ]);

            // Помечаем все сообщения для id_forms_departments как прочитанные
            DB::table('messages')
                ->where('id_forms_departments','=',$id_forms_departments)
                ->update(['is_read' => 1]);

            // Записываем новое сообщение
            DB::table('messages')
                ->insert([
                    'id' => Auth::user()->id,
                    'id_forms_departments' => $id_forms_departments,
                    'message' => '<b><i>Форма была отправлена повторно:</i></b><br>'.$request->input('message')
                ]);

            $status = [
                'class' => 'success',
                'message' => 'Данные в форме успешно обновлены'
            ];

            $this->writeLog($id_user ,$id_forms, 2);

            return redirect('/viewForm/'.$id_forms_departments)->with('status', $status);

        } else {

            $status = [
                'class' => 'danger',
                'message' => 'Поле "Текст сообщения" должно содержать описание изменений внесенных в форму !'
            ];
            return redirect('/viewForm/'.$id_forms_departments)->with('status', $status);
        }
    }

    public function rejectForm(Request $request)
    {

        $id_forms_departments = $request->input('id_forms_departments');
        $id_user = Auth::user()->id;
        $id_forms = DB::table('forms_departments')
            ->where('id_forms_departments','=',$id_forms_departments)
            ->value('id_forms');

        if(empty($request->input('message'))) {
            $status = [
                "class" => "danger",
                "message" => "Причина отклонения формы должна быть указана в сообщении !"
            ];
            return redirect('/viewForm/'.$id_forms_departments)->with("status", $status);
        } else {

            // Помечаем все сообщения для id_forms_departments как прочитанные
            DB::table('messages')
                ->where('id_forms_departments','=',$id_forms_departments)
                ->update(['is_read' => 1]);

            // Записываем новое сообщение
            DB::table('messages')
                ->insert([
                    'id' => $id_user,
                    'id_forms_departments' => $id_forms_departments,
                    'message' => $request->input('message')
                ]);


            // Ставим статус формы - отменена
            DB::table('forms_departments')
                ->where('id_forms_departments','=',$id_forms_departments)
                ->update([
                    'id_status_checks' => self::REJECT_FORM
                ]);

            $status = [
                "class" => "success",
                "message" => "Форма отклонена"
            ];

            $this->writeLog($id_user ,$id_forms, 4);

            return redirect('/')->with("status", $status);
        }


    }


    public function acceptForm(Request $request)
    {
//      dd($request->all());

        $id_forms = $request->input('id_forms');
        $id_forms_departments = $request->input('id_forms_departments');
        $id_user = Auth::user()->id;

        $updated_at = DB::table('forms_departments')
            ->where('id_forms_departments', '=', $id_forms_departments)
            ->value('updated_at');

        if ($updated_at != $request->input('updated_at')) {
            $status = [
                "class" => "danger",
                "message" => "Загружена более новая версия формы"
            ];
            return redirect('viewForm/' . $id_forms_departments)->with("status", $status);
        } else {

            // Изменяем статус формы для данного отдела на принята
            DB::table('forms_departments')
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->update(['id_status_checks' => self::SUCCESS_FORM]);

            // Узнаем все id_fields_forms для принятой формы
            $id_fields_forms = DB::table('fields_forms as ff')
                ->where('ff.id_forms', '=', $id_forms)
                ->join('fields_forms_current as ffc', 'ffc.id_fields_forms', '=', 'ff.id_fields_forms')
                ->orderBy('ffc.id_fields_forms_current', 'asc')
                ->pluck('ff.id_fields_forms');

            // Удаляем старые значения с таблиц fields_forms_old, value_fields_current, sub_elements_old
            foreach ($id_fields_forms as $id_field_form) {
                DB::table('fields_forms_old')
                    ->where('id_fields_forms', '=', $id_field_form)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->delete();
                DB::table('values_fields_old')
                    ->where('id_fields_forms', '=', $id_field_form)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->delete();
                DB::table('sub_elements_old')
                    ->where('id_fields_forms', '=', $id_field_form)
                    ->where('id_forms_departments', '=', $id_forms_departments)
                    ->delete();
                DB::table('values_fields_current')
                    ->where('id_fields_forms', '=', $id_field_form)
                    ->delete();
            }

            // Получаем: $id_fields_forms с ключей, $values с значений
            foreach ($request->all() as $key_id_fields_forms => $values) {

                // Пропускаем (1)_token (2)id_forms (3)id_forms_departments
                if (is_int($key_id_fields_forms)) {

                    // Если новые значения пришли строкой
                    if (!is_array($values)) {

                        // запись значений в values_fields_current
                        DB::table('values_fields_current')
                            ->insert([
                                'id_fields_forms' => $key_id_fields_forms,
                                'id_forms_departments' => $id_forms_departments,
                                'values_fields_current' => (!empty($values)) ? $values : 0,
                                'enum_sub_elements_current' => 0
                            ]);

                        // Если новые значения пришли массивом
                    } else {
                        foreach ($values as $value) {
                            // запись перебором, id_sub_elements в enum_sub_elements_current
                            DB::table('values_fields_current')
                                ->insert([
                                    'id_fields_forms' => $key_id_fields_forms,
                                    'id_forms_departments' => $id_forms_departments,
                                    'values_fields_current' => 0,
                                    'enum_sub_elements_current' => $value
                                ]);
                        }
                    }
                }
            }

            foreach ($id_fields_forms as $id_field_form) {

                // Переносим данные из таблицы fields_forms_current в таблицу fields_forms_old
                $fields_forms_current = DB::table('fields_forms_current as ffc')
                    ->where('ffc.id_fields_forms', '=', $id_field_form)
                    ->join('fields_forms as ff', 'ff.id_fields_forms', '=', 'ffc.id_fields_forms')
                    ->join('fields as f', 'f.id_fields', '=', 'ff.id_fields')
                    ->select('f.label_fields', 'ffc.required_fields_current', 'f.id_fields')
                    ->get();
                DB::table('fields_forms_old')
                    ->insert([
                        'id_fields_forms' => $id_field_form,
                        'id_forms_departments' => $id_forms_departments,
                        'required_fields_old' => $fields_forms_current[0]->required_fields_current,
                        'label_fields_old' => $fields_forms_current[0]->label_fields
                    ]);

                // Переносим данные из таблицы values_fields_current в таблицу values_fields_old
                $values_fields_current = DB::table('values_fields_current as vfc')
                    ->where('vfc.id_fields_forms', '=', $id_field_form)
                    ->where('vfc.id_forms_departments', '=', $id_forms_departments)
                    ->select('vfc.values_fields_current', 'vfc.enum_sub_elements_current', 'vfc.id_fields_forms')
                    ->get();

                if (is_array($values_fields_current)) {
                    foreach ($values_fields_current as $value_field_current) {
                        DB::table('values_fields_old')
                            ->insert([
                                'id_fields_forms' => $id_field_form,
                                'id_forms_departments' => $id_forms_departments,
                                'values_fields_old' => $value_field_current->values_fields_current,
                                'enum_sub_elements_old' => $value_field_current->enum_sub_elements_current
                            ]);
                    }
                } else {
                    DB::table('values_fields_old')
                        ->insert([
                            'id_fields_forms' => $id_field_form,
                            'id_forms_departments' => $id_forms_departments,
                            'values_fields_old' => $values_fields_current[0]->values_fields_current,
                            'enum_sub_elements_old' => $values_fields_current[0]->enum_sub_elements_current
                        ]);
                }

                // Переносим данные из таблицы sub_elements_current в таблицу sub_elements_old
                $sub_elements_info = DB::table('fields_forms as ff')
                    ->where('ff.id_fields_forms', '=', $id_field_form)
                    ->join('sub_elements_fields as sef', 'sef.id_fields', '=', 'ff.id_fields')
                    ->join('sub_elements_current as sec', 'sec.id_sub_elements_field', '=', 'sef.id_sub_elements_field')
                    ->select('sec.id_sub_elements_field', 'sec.label_sub_elements_current', 'ff.id_fields_forms')
                    ->get();
                if (!empty($sub_elements_info)) {
                    foreach ($sub_elements_info as $sub_element) {
                        DB::table('sub_elements_old')
                            ->insert([
                                'id_sub_elements_field' => $sub_element->id_sub_elements_field,
                                'id_fields_forms' => $sub_element->id_fields_forms,
                                'id_forms_departments' => $id_forms_departments,
                                'label_sub_elements_old' => $sub_element->label_sub_elements_current
                            ]);
                    }
                }
            }

            // Отмечаем сообщения для данной формы-депарьамента прочитанными
            DB::table('messages')
                ->where('id_forms_departments', '=', $id_forms_departments)
                ->where('id','!=',$id_user)
                ->update(['is_read' => 1]);

            $this->writeLog($id_user ,$id_forms, 3);

            $status = [
                "class" => "success",
                "message" => "Форма успешно принятя"
            ];
            return redirect('/')->with("status", $status);
        }
    }

    public function writeLog($id_users, $id_forms, $id_log_action){
        DB::table('log')
            ->insert([
                'id_users' => $id_users,
                'id_forms' => $id_forms,
                'id_log_action' => $id_log_action
            ]);
    }
}
