<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class MessageController extends Controller
{
    public function sendMessage(Request $request){

        DB::table('messages')
            ->insert([
                'id' => Auth::user()->id,
                'id_forms_departments' => $request->input('id_forms_departments'),
                'message' => $request->input('message')
            ]);
        
        return redirect('/viewForm/'.$request->input('id_forms_departments'));
    }

}
