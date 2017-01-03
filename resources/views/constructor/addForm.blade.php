@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">

                    {{--Вывод меню вкладок--}}
                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active');$('#tab1').addClass('active') </script>


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
                                    @foreach($name_forms as $name)
                                         <tr>
                                             <td>
                                                 {{$name->name_forms}}
                                                  <span class="pull-right">
                                                      <button type="button" id="btn-reject-form" class="btn btn-sm btn-success btn-padding-0 editForms"  data-id-form="{{$name->id}}" data-status-checks="{{$name->id_status_checks}}"> Редактировать </button>
                                                      <button type="button" id="btn-accept-form" class="btn btn-sm btn-danger btn-padding-0 removeForms confirmDelete" data-id-form="{{$name->id}}"> Удалить </button>
                                                  </span>
                                             </td>
                                         </tr>
                                    @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div class="col-sm-12">
                            <div class="col-sm-6">
                                <input type="text" id="name_forms" class="form-control" name="name_forms" placeholder="Имя  формы" required>
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

                                </table>
                                <input id="update_date" type="text" name="date" class="tcal" value="" placeholder=" Дата обновления формы" required/>
                                <button id="addNewForm" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>

                            </div>
                            <div class="col-sm-12" style="margin-top:20px;">
                                * - поле станет обязательным к заполнению если checkbox активен
                            </div>

                        </div>

                        <div class="col-sm-6">
                            <p><div class="text-center">Список возможных элементов</div></p>
                            <table class="table table-bordered table-constructorForm table-padding">

                                @foreach($set_elements as $set_element)
                                    <tr>
                                        <td><button id="{{ $set_element->id_set_elements }}" class="addElementInForm btn btn-sm btn-warning btn-padding-0"> < </button></td>
                                        <td>{{ $set_element->label_set_elements }}</td>
                                        <td>{{ $set_element->name_elements }}</td>
                                        <td>{{ ($set_element->value_sub_elements != null) ? $set_element->value_sub_elements : "---"}}</td>
                                    </tr>
                                @endforeach

                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection