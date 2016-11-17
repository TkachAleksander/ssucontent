<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

class ConstructorFormController extends Controller
{
    public function constructorForm()
    {
        $elements = DB::table('elements')->get();
        $set_elements = DB::table('set_elements')->join('elements', 'elements.id', '=', 'set_elements.id_elements')
                                                 ->select('set_elements.*','set_elements.id as id_set_elements', 'elements.*')
                                                 ->get();
                                       
        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id_set_elements;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                                                     ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;       
        }
        
        $forms_names = DB::table('forms')->get();

        // $forms_info = DB::table('set_forms_elements as sfe')->join('forms', 'forms.id', '=', 'sfe.id_forms')
        //                                                     ->join('set_elements as se', 'se.id', '=', 'sfe.id_set_elements')
        //                                                     ->join('elements as e', 'e.id', '=', 'se.id_elements')
        //                                                     ->select('forms.name_forms','forms.id','se.name_set_elements','se.label_set_elements','e.name_elements')
        //                                                     ->get();
        //                                                     dd ($forms_info);
        return view('constructor.constructorForm', ['elements' => $elements, 
                                                    'set_elements' => $set_elements, 
                                                    'forms_names' => $forms_names,
                                                    /*'forms_info' => $forms_info */]);    	
    }

    public function getSetElements()
    { 
        $set_elements = DB::table('set_elements')->where('id', '=', $_POST['idSetElement'])->get();

        foreach ($set_elements as $key => $set_element) {
            $id_set_element = $set_element->id;
            $sub_elements = DB::table('sub_elements')->where('id_set_elements', '=', $id_set_element)
                                                     ->pluck('value_sub_elements');
            $value_sub_elements = implode(" | ", $sub_elements);
            $set_elements[$key]->value_sub_elements = $value_sub_elements;       
        }
        return response()->json($set_elements);      
    }

    public function newElement()
    {
        $elements = DB::table('elements')->get();
        return view('forms.newElement', ['elements' => $elements]);
    }

    public function addNewElementToServer(Request $request)
    {
        $id = DB::table('set_elements')->insertGetId([
                                          'name_set_elements' => $request->input('name_set_elements'),
                                          'label_set_elements' => $request->input('label_set_elements'),
                                          'id_elements' => $request->input('id_elements')
                                        ]);
        $values = explode(" | ", $request->input('value_sub_elements'));
        foreach ($values as $key => $value) {
            DB::table('sub_elements')->insert([
                                        'id_set_elements' => $id,
                                        'value_sub_elements' => $value
                                    ]);
        }
        return redirect('/constructorForm');
    }

    public function addSetFormsElementsToServer(Request $request)
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
}
