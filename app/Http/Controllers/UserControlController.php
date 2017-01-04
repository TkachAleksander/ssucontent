<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use app\User;
use Validator;

class UserControlController extends Controller
{
	public function index()
	{
        $users = DB::table('users')->join('roles', 'roles.id','=','users.id_roles')
                                   ->where('name_roles', '!=', 'administrator')->get();
        $administrators = DB::table('users')->join('roles', 'roles.id','=','users.id_roles')
                                            ->where('name_roles', '=', 'administrator')->get();
        $departments = DB::table('users as u')->join('departments as d', 'd.id','=','u.id_departments')->get();
        $roles = DB::table('roles')->get();
		return view('userControl', ['users' => $users, 'roles' => $roles, 'administrators' => $administrators, 'departments' => $departments]);
	}

    public function registration(Request $request)
    {
    	 $this->validate($request, [
            'name' => 'required|max:80|unique:users',
            'surname' => 'required|max:80|unique:users',
            'middle_name' => 'required|max:80|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ],
        [
            'required' => 'Поле :attribute обязательно для заполнения',
            'max' => 'Поле :attribute должно содержать максимум :max символов',
            'min' => 'Поле :attribute должно содержать минимум :min символов',
            'unique' => 'Такой :attribute уже существует',
            'confirmed' => 'Пароли не совпадают',
        ]);

        User::create([
            'surname' => $request->input('surname'),
            'name' => $request->input('name'),
            'middle_name' => $request->input('middle_name'),
            'email' => $request->input('email'),
            'password' => bcrypt( $request->input('password') ),
            'id_roles' => $request->input('id_roles'),
            'id_departments' => $request->input('id_departments')
        ]);

        return redirect('/registration');
    }

    public function removeUser($id)
    {
        DB::table('users')->where('id',$id)->delete();
        return redirect('userControl');
    }

}
