@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active');$('#tab4').addClass('active') </script>

                    <div class="panel-body">

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-4">
                                    <select id="id_forms" class="multiselect" name="id_forms">
                                        @foreach($forms as $form)
                                            <option value="{{ $form->id }}">{{ $form->name_forms }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <select id="id_users" class="multiselect" name="id_users">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{$user->surname." ".$user->name." ".$user->middle_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                <div class="col-sm-2">
                                    <button id="btn-forms-connect-users" type="button" class="btn btn-sm btn-primary"> Связать </button>
                                    <button id="btn-forms-disconnect-users" type="button" class="btn btn-sm btn-danger"> Удалить связь </button>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-8">
                                    <p></p>
                                    <table id="table-forms-connect-users" class="table table-striped table-bordered table-padding">
                                        <tr>
                                            <th> Имя формы </th>
                                            <th> Имя пользователя </th>
                                        </tr>
                                        @foreach ($connects as $connect)
                                            <tr class="new_tr">
                                                <td>{{ $connect->name_forms }}</td>
                                                <td>{{ $connect->surname." ".$connect->name." ".$connect->middle_name }}</td>
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
