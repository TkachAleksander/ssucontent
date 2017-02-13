@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">
                <div class="panel-heading">Регистрация</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/registration') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('surname') ? ' has-error' : '' }}">
                            <label for="surname" class="col-md-4 control-label">Фамилия</label>

                            <div class="col-md-6">
                                <input id="surname" type="text" class="form-control" name="surname" value="{{ old('dd') }}">

                                @if ($errors->has('surname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('surname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Имя</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('dd') }}">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('middle_name') ? ' has-error' : '' }}">
                            <label for="middle_name" class="col-md-4 control-label">Отчество</label>

                            <div class="col-md-6">
                                <input id="middle_name" type="text" class="form-control" name="middle_name" value="{{ old('dd') }}">

                                @if ($errors->has('middle_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('middle_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail адрес</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="пароль" class="col-md-4 control-label">Пароль</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="col-md-4 control-label">Подтвердите пароль</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('id_roles') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Роль</label>

                            <div class="col-md-6">
                                <select class="multiselect" name="id_roles">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id_roles }}">{{ $role->name_roles }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('id_departments') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Отдел</label>

                            <div class="col-md-6">
                                <select class="multiselect" name="id_departments">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id_departments }}">{{ $department->name_departments }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i> Зарегистрироваться
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Удаление пользователя</div>
                <div class="panel-body">
                    <div class="row">

                        <div class="col-sm-6">
                            <table class="table table-bordered table-padding table-striped">
                                <tr>
                                    <th class="text-center">Список администраторов</th>
                                </tr>
                                @foreach($administrators as $administrator)
                                    <tr>
                                        <td>
                                            {{$administrator->surname." ".$administrator->name." ".$administrator->middle_name}}
                                            <span class="pull-right">
                                                <a href="/editUsers/{{$administrator->id}}" type="submit" class="btn btn-sm btn-default forms-btn"> Edit </a>
                                                <a href="/removeUser/{{$administrator->id}}" type="submit" class="btn btn-sm btn-danger forms-btn confirmDelete"> Del </a>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>

                        <div class="col-sm-6">
                            <table class="table table-bordered table-padding .table-striped">
                                <tr>
                                    <th class="text-center">Список пользователей</th>
                                </tr>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        {{$user->surname." ".$user->name." ".$user->middle_name}}
                                        <span class="pull-right">
                                            <a href="/editUsers/{{$user->id}}" type="submit" class="btn btn-sm btn-default forms-btn"> Edit </a>
                                            <a href="/removeUser/{{$user->id}}" type="submit" class="btn btn-sm btn-danger forms-btn confirmDelete"> Del </a>
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>

                    </div>
                </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
