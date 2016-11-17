<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class MessangerController extends Controller
{
    public function index()
    {
    	$users = DB::table('users')->where('name','!=', Auth::user()->name)->get();
    	return view('messages.contactsMessages', ['users' => $users]);
    }

    public function showMessages($name)
    {
    	$messages = DB::table('messages')
    					->where(function($table) use($name){
    						$table->where('sender', Auth::user()->name);
    						$table->where('recipient', $name);

    					})
    					->orWhere(function($table) use($name){
    						$table->where('recipient', Auth::user()->name);
    						$table->where('sender', $name);

    					})
    					->select('message_content','created_at','sender')
    					->orderBy('id', 'desc')
    					->get();
    	return view('messages.sendMessages',['messages' => $messages, 'name' => $name]);
    }

    public function sendMessages(Request $request)
    {
    	DB::table('messages')->insert([
    		'sender' => $request->input('sender'),
    		'recipient' => $request->input('recipient'),
    		'message_content' => $request->input('message_content')
    		]);
    	return redirect('/showMessages/'.$request->input('recipient'));
    }
}
