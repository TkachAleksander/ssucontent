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
                                        <option value="*"> Все формы </option>
                                        @foreach($forms as $form)
                                            <option value="{{ $form->id_forms }}">{{ $form->name_forms }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <select id="id_departments" class="multiselect" name="id_departments">
                                        <option value="*"> Все отделы </option>
                                        @foreach($departments as $department)
                                            @if($department->id_departments)
                                                <option value="{{ $department->id_departments }}">{{$department->name_departments}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                <div class="col-sm-2">
                                    <button id="btn-forms-connect-users" type="button" class="btn btn-sm btn-primary" disabled> Связать </button>
                                    <button id="btn-forms-disconnect-users" type="button" class="btn btn-sm btn-danger" disabled> Удалить связь </button>
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
                                                <td>{{ $connect->name_departments }}</td>
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
