@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                @if (session()->has('status'))
                    <div class="alert alert-{{ session('status.class') }} text-center">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        {{ session('status.message') }}
                    </div>
                @endif

                <div class="panel panel-default shadow">

                    {{--Вывод меню вкладок--}}
                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active');$('#tab1').addClass('active') </script>

                    @if(isset($message))
                        <script> alert("{{$message}}")</script>
                    @endif

                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#accordionListForms" class="collapsed" >
                                        Список существующих форм
                                    </a>
                                </h4>
                            </div>
                            <div id="accordionListForms" class="panel-collapse collapse in" aria-expanded="true">
                                <div class="panel-body showAllForms">
                                    <table class="table table-padding table-striped">
                                    @foreach($forms as $form)
                                         <tr>
                                             <td>
                                                 {{$form->name_forms}}
                                                  <span class="pull-right">
                                                      <button type="button" class="btn btn-sm btn-success btn-padding-0 editForms"  data-id-form="{{$form->id_forms}}" data-status-checks="{{$form->id_status_checks}}"> Редактировать </button>
                                                      @if ($form->deleted_forms == 1)
                                                        <form action="reestablishForm" method="POST" class="display-inline-block">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="id_forms" value="{{$form->id_forms}}">
                                                            <button type="submit" class="btn btn-sm btn-warning btn-padding-0" style="width: 63px;"> Вернуть </button>
                                                        </form>
                                                      @else
                                                          <form action="removeForms" method="POST" class="display-inline-block">
                                                              {{ csrf_field() }}
                                                              <input type="hidden" name="id_forms" value="{{$form->id_forms}}">
                                                              <button type="submit" class="btn btn-sm btn-danger btn-padding-0 confirmDelete"> Удалить </button>
                                                          </form>
                                                      @endif
                                                  </span>
                                             </td>
                                         </tr>
                                    @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>

                        <form action="addNewForm" method="POST">
                            {{ csrf_field() }}
                            <div class="col-sm-12">
                                <div class="col-sm-6">
                                    <input type="text" id="name_forms" class="form-control" name="name_forms" placeholder="Имя  формы" required>
                                    @if ($errors->has('name_forms'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name_forms') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div id="container" class="col-sm-12">

                                    <p><div class="text-center">Порядок элементов в форме</div></p>
                                    <table class="table table-bordered table-padding table-sort">

                                        <tr class="active">
                                            <th class="text-center"> Имя элемента </th>
                                            <th class="text-center"> Под элементы </th>
                                            <th class="text-center"> * </th>
                                            <th class="text-center"> Удалить </th>
                                        </tr>

                                        <tbody id="sortContainer"></tbody>
                                        @if ($errors->has('info_new_form'))
                                            <tr id="help-block">
                                                <td colspan="4">
                                                    <span class="help-block">
                                                    <strong>{{ $errors->first('info_new_form') }}</strong>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>

                                    <input id="date_update_forms" type="text" name="date_update_forms" class="tcal" value="" placeholder=" Дата обновления формы" required/>
                                    @if ($errors->has('date_update_forms'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('date_update_forms') }}</strong>
                                        </span>
                                    @endif
                                    <button type="submit" id="addNewForm" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>

                                </div>
                                <div class="col-sm-12" style="margin-top:20px;">
                                    * - поле станет обязательным к заполнению если checkbox активен
                                </div>

                        </div>
                        </form>

                        <div class="col-sm-6">
                            <p><div class="text-center">Список возможных элементов</div></p>
                            <table class="table table-bordered table-constructorForm table-padding">

                                @foreach($fields as $field)
                                    <tr>
                                        <td><button id="{{ $field->id_fields }}" class="addElementInForm btn btn-sm btn-warning btn-padding-0"> < </button></td>
                                        <td data-label-fields="{{ $field->label_fields }}">{{ $field->label_fields }}</td>
                                        <td>{{ $field->name_elements }}</td>
                                        <td class="text-center">
                                            @if(!empty($field->labels_sub_elements))
                                                {{$field->labels_sub_elements}}
                                            @else
                                                {{"---"}}
                                            @endif
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


    <script type="text/javascript" src="{{ url('js/tcal.js') }}"></script>

@endsection