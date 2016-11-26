@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active');$('#tab1').addClass('active') </script>

                    <div class="panel-body">
                        <div class="row">

                            <div class="col-sm-12">
                                <div class="col-sm-4">
                                    <input type="text" id="name_forms" class="form-control" name="name_forms" placeholder="Имя  формы" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div id="container" class="col-sm-12">

                                    <p><div class="text-center">Порядок элементов в форме</div></p>
                                    <table class="table table-bordered">

                                        <tr>
                                            <th class="text-center"> Имя элемента </th>
                                            <th class="text-center"> Под элементы </th>
                                            <th class="text-center"> Удалить </th>
                                        </tr>
                                        <tbody id="sortContainer"></tbody>

                                    </table>
                                    <button id="getArray" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>

                                </div>
                            </div>

                            <div class="col-sm-6">
                                <p><div class="text-center">Список возможных элементов</div></p>
                                <table class="table table-bordered table-constructorForm table-padding">

                                    @foreach($set_elements as $set_element)
                                        <tr>
                                            <td><button id="{{ $set_element->id_set_elements }}" class="addElementInForm btn btn-sm btn-warning btn-padding-0"> < </button></td>
                                            <td>{{ $set_element->id_set_elements }}</td>
                                            <td>{{ $set_element->name_set_elements }}</td>
                                            <td>{{ $set_element->name_elements }}</td>
                                            <td>{{ $set_element->value_sub_elements }}</td>
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

@endsection