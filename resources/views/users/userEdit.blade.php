@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                @if (session()->has('status'))
                    <div class="alert alert-{{ session('status.class') }} text-center">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        {{ session('status.message') }}
                    </div>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Данные пользователя
                        <span class="pull-right">
                            <a href="/registration" > Назад </a>
                        </span>
                    </div>
                    <div class="panel-body">

                        <form action="{{ url('/updateEditUsers/'.$user->id) }}" method="POST" class="form-horizontal" role="form">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('surname') ? ' has-error' : '' }}">
                                <label for="surname" class="col-md-4 control-label">Фамилия</label>

                                <div class="col-md-6">
                                    <input id="surname" type="text" class="form-control" name="surname"
                                           value="{{ $user->surname }} ">

                                    @if ($errors->has('surname'))
                                        <span class="help-block">
                                            {{ $errors->first('surname') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Имя</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ $user->name }}">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('middle_name') ? ' has-error' : '' }}">
                                <label for="middle_name" class="col-md-4 control-label">Отчество</label>

                                <div class="col-md-6">
                                    <input id="middle_name" type="text" class="form-control" name="middle_name"
                                           value="{{ $user->middle_name }}">

                                    @if ($errors->has('middle_name'))
                                        <span class="help-block">
                                            {{ $errors->first('middle_name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail адрес</label>

                                <div class="col-md-6">
                                    @if ($errors->has('email'))
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">
                                        <span class="help-block">
                                            {{ $errors->first('email') }}
                                        </span>
                                    @else
                                        <input id="email" type="email" class="form-control" name="email" value="{{ $user->email }}">
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="пароль" class="col-md-4 control-label">Пароль</label>

                                <div class="col-md-6">
                                    <input id="password" type="text" class="form-control" name="password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            {{ $errors->first('password') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label for="password-confirm" class="col-md-4 control-label">Подтвердите пароль</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="text" class="form-control"
                                           name="password_confirmation">

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                            {{ $errors->first('password_confirmation') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('id_roles') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Роль</label>

                                <div class="col-md-6">
                                    <select class="multiselect" name="id_roles">
                                        @foreach($roles as $role)
                                            {{ $selected = ($role->id_roles == $user->id_roles) ? 'selected' : '' }}
                                            <option value="{{ $role->id_roles }}" {{ $selected }}>{{ $role->name_roles }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('id_departments') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Отдел</label>

                                <div class="col-md-6">
                                    <select class="multiselect" name="id_departments">
                                        @foreach($departments as $department)
                                            {{ $selected = ($department->id_departments == $user->id_departments) ? 'selected' : '' }}
                                            <option value="{{ $department->id_departments }}" {{ $selected }}>{{ $department->name_departments }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-user"></i> Сохранить
                                    </button>
                                    <button type="button" class="btn btn-default generateNewPassword"> Сгенерировать
                                        пароль
                                    </button>
                                    <script>
                                        $('.generateNewPassword').on('click', function () {
                                            $('#password, #password-confirm').val(str_rand(10));
                                        });
                                    </script>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
