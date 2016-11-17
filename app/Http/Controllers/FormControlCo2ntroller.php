<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use DB;

class FormControlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function formControl()
    {
        $forms = DB::table('forms')->get();

        $type_elements = DB::table('type_elements')->get();
        $labels = DB::table('labels')->get();
        $elements = DB::table('elements')
                      ->leftJoin('labels', 'labels.id', '=', 'elements.id_labels')
                      ->select('elements.id as id_elements', 'elements.*', 'labels.*')
                      ->get();

        $select_options = DB::table('elements')
                            ->leftJoin('labels', 'labels.id', '=', 'elements.id_labels')
                            ->whereIn('name_elements', ['radiobutton','checkbox'])
                            ->get();

        $sub_elements = DB::table('sub_elements')
                          ->leftJoin('elements', 'elements.id', '=', 'sub_elements.id_elements')
                          ->leftJoin('labels', 'labels.id', '=', 'sub_elements.id_labels')
                          ->whereIn('name_elements', ['radiobutton','checkbox'])
                          ->select('sub_elements.id as id_sub_elements','sub_elements.*', 'name_elements', 'labels.*')
                          ->get();

        return view('forms.formControl', [
                                          'forms' => $forms,
                                          'type_elements' => $type_elements,
                                          'labels' => $labels,
                                          'elements' => $elements,
                                          'select_options' => $select_options,
                                          'sub_elements' => $sub_elements
                                          ]);
    }

    public function addElementToServer(Request $request)
    {
        DB::table('elements')->insert([ 'name_elements' => $request->input('name_elements'), 'id_labels' =>  $request->input('id_labels')]);
        return redirect('/formControl');
    }

    public function addTypeElementToServer(Request $request)
    {
        DB::table('type_elements')->insert([ 'name_type_elements' => $request->input('name_type_elements') ]);
        return redirect('/formControl');
    }

    public function addLabelElementToServer(Request $request)
    {
        DB::table('labels')->insert([ 'name_labels' => $request->input('name_labels') ]);
        return redirect('/formControl');
    }

    public function addSubElementToServer(Request $request)
    {
        DB::table('sub_elements')->insert([
                                           'id_elements' => $request->input('id_elements'),
                                           'id_labels' => $request->input('id_labels'),
                                           'name_sub_elements' => $request->input('name_sub_elements'),
                                           'value_sub_elements' => $request->input('value_sub_elements')
                                         ]);
        return redirect('/formControl');
    }

    public function addFormToServer(Request $request)
    {
        DB::table('forms')->insert(['name_forms' => $request->input('name_form')]);
        return redirect('/formControl');
    }    




    public function removeElementToServer($id)
    {
        DB::table('elements')->where('id', $id)->delete();
        return redirect('/formControl');
    }

    public function removeSubElementToServer($id)
    {
        DB::table('sub_elements')->where('id', $id)->delete();
        return redirect('/formControl');        
    }

    public function removeTypeElementToServer($id)
    {
        DB::table('type_elements')->where('id', $id)->delete();
        return redirect('/formControl');        
    }

    public function removeLableElementToServer($id)
    {
        DB::table('labels')->where('id', $id)->delete();
        return redirect('/formControl');        
    }
 
    public function removeFormToServer($id)
    {
        DB::table('forms')->where('id', $id)->delete();
        return redirect('/formControl');
    }




    public function newElement()
    {
        $elements = DB::table('elements')->get();
        return view('forms.newElement', ['elements' => $elements]);
    }

    public function addNewElementToServer(Request $request)
    {
        DB::table('set_elements')->insert([
                                          'name_set_elements' => $request->input('name_set_elements'),
                                          'label_set_elements' => $request->input('label_set_elements'),
                                          'id_elements' => $request->input('id_elements'),
                                          'value_sub_elements' => $request->input('value_sub_elements') ? $request->input('value_sub_elements') : "NULL"
                                        ]);
        return redirect('/newElement');
    }




    public function newForm()
    {
        $set_elements = DB::table('set_elements')
                          ->join('elements', 'elements.id', '=', 'set_elements.id_elements')
                          ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
                          ->get();
        $forms = DB::table('forms')->get();
        return view('forms.newForm', ['set_elements' => $set_elements, 'forms' => $forms]);
    }

    public function getSetElements()
    { 
        $elements = DB::table('set_elements')->where('id', '=', $_POST['idSetElement'])->get();
        return response()->json($elements);      
    }

    public function addSetFormsElements(Request $request)
    {
        if (!empty($request->input('name_forms'))){
            $id = DB::table('forms')->insertGetId([ 'name_forms' => $request->input('name_forms') ]);
            foreach ($request->input('queue') as $key => $value) {
                DB::table('set_forms_elements')->insert([ 'id_forms' => $id,
                                                          'id_set_elements' => $value
                                                         ]);
            }
            return "Форма успешно добавлена !";
        } else {
            return "Форма заполнена неверно !";
        }
    }























    public function setForm()
    {
        $users = DB::table('users')->get();
        $forms = DB::table('forms')->get();
        $elements = DB::table('elements')->get();

        return view('forms.setForm', [
                                      'users' => $users, 
                                      'forms' => $forms, 
                                      'elements' => $elements
                                      ]);
    }

    public function addSetFormToServer(Request $request)
    {
        $all = $request->all();
        foreach ($all['id_element'] as $key => $value) 
        {    
            DB::table('queue_elements_in_forms')->insert([
                                        'id_user' => $request->input('id_user'),
                                        'id_forms' => $request->input('id_form'),
                                        'id_elements' => $value,  
                                        'queue' => $all['queue'][$key],
                                        'width' => $all['width'][$key]
                                        ]);
        }

        return redirect ('/setForm');
    }    
}
