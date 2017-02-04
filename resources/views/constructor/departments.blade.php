@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active');$('#tab5').addClass('active') </script>

                    <div class="panel-body">

                        <form action="{{ url('constructor/setDepartments') }}" method="POST">
                            {{csrf_field()}}
                            <div class="col-sm-9 form-group{{ $errors->has('name_departments') ? ' has-error' : '' }}">
                                <input id="name_departments" type="text" class="form-control" name="name_departments" value="" placeholder="Имя нового отдела">
                                @if ($errors->has('name_departments'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name_departments') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-sm-3 form-group">
                                <input type="submit" id="btn-add-departments" class="btn btn-sm btn-primary " value="Добавить">
                            </div>
                        </form>

                        <div class="col-sm-10 form-group">
                            <table class="table table-bordered table-padding">
                                @foreach($departments as $department)
                                    @if($department->id_departments)
                                        <tr>
                                            <td>{{$department->name_departments}}</td>
                                            <td style="width: 187px;">
                                                <input type="button" class="btn btn-sm btn-warning btn-padding-0 btn-edit-departments" data-id-departments="{{$department->id_departments}}" data-name-departments="{{$department->name_departments}}" value="Редактировать">
                                                <input type="button" class="btn btn-sm btn-danger btn-padding-0 btn-remove-departments confirmDelete" data-id-departments="{{$department->id_departments}}" value="Удалить">
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection
