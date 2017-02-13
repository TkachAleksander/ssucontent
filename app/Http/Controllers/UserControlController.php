<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use app\User;
use Validator;
use Illuminate\Support\Facades\Crypt;

class UserControlController extends Controller
{
	public function index()
	{
        $users = DB::table('users')
            ->join('roles', 'roles.id_roles','=','users.id_roles')
            ->where('name_roles', '!=', 'administrator')->get();
        $administrators = DB::table('users')
            ->join('roles', 'roles.id_roles','=','users.id_roles')
            ->where('name_roles', '=', 'administrator')->get();
        $departments = DB::table('departments as d')
            ->get();
        $roles = DB::table('roles')
            ->get();
		return view('users.userControl', ['users' => $users, 'roles' => $roles, 'administrators' => $administrators, 'departments' => $departments]);
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
        DB::table('users')->where('id',$id)->update(['deleted_users' => 1]);
        return redirect('registration');
    }

    public function editUsers($id){

        $user = DB::table('users')
            ->where('id','=',$id)
            ->select('id','id_departments','id_roles','name','surname','middle_name','email')
            ->get();
        $departments = DB::table('departments as d')->get();
        $roles = DB::table('roles')->get();

        return view('users.userEdit',[
            'user' => $user[0],
            'departments' => $departments,
            'roles' => $roles
        ]);
    }

    public function updateEditUsers($id_users,Request $request)
    {

        $this->validate($request, [
            'name' => 'required|max:80',
            'surname' => 'required|max:80',
            'middle_name' => 'required|max:80',
            'email' => 'required|email|max:255|unique:users,email,' . $id_users,
            'password' => 'min:6|confirmed',
        ],
            [
                'required' => 'Поле :attribute обязательно для заполнения',
                'max' => 'Поле :attribute должно содержать максимум :max символов',
                'min' => 'Поле :attribute должно содержать минимум :min символов',
                'unique' => 'Такой :attribute уже существует',
                'confirmed' => 'Пароли не совпадают',
            ], [
                'id_users' => $id_users
            ]);

        if ($request->input('password') != '') {

            $password = bcrypt($request->input('password'));
            User::findOrFail($id_users)->update([
                'surname' => $request->input('surname'),
                'name' => $request->input('name'),
                'middle_name' => $request->input('middle_name'),
                'email' => $request->input('email'),
                'password' => $password,
                'id_roles' => $request->input('id_roles'),
                'id_departments' => $request->input('id_departments')
            ]);

                /* высылаем новый пароль на почту */
        } else {
            User::findOrFail($id_users)->update([
                'surname' => $request->input('surname'),
                'name' => $request->input('name'),
                'middle_name' => $request->input('middle_name'),
                'email' => $request->input('email'),
                'id_roles' => $request->input('id_roles'),
                'id_departments' => $request->input('id_departments')
            ]);
        }

        $status = [
            'class' => 'success',
            'message' => 'Данные успешно обновлены'
        ];
        return redirect('/editUsers/'.$id_users)->with('status',$status);
    }
}
