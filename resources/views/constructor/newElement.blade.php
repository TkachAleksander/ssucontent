@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active'); $('#tab2').addClass('active') </script>

                    <div class="panel-body">

                        <form action="/constructor/addNewElement" method="POST">
                            <div class="row">
                                <div class="col-sm-5">
                                    <p><div class="text-center"> Создать эллемент </div></p>
                                    <table class="table-padding">
                                        {{ csrf_field() }}
                                        <tr>
                                            <td> Имя элемента </td>
                                            <td>
                                                <input id="name_set_elements" class="form-control" type="text" name="name_set_elements" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Заголовок (lable) </td>
                                            <td>
                                                <input id="label_set_elements" class="form-control" type="text" name="label_set_elements" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Тип элемента </td>
                                            <td>
                                                <select id="select_labels" class="multiselect" name="id_elements">
                                                    @foreach($elements as $element)
                                                        <option value="{{ $element->id }}">{{ $element->name_elements }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Список выбора </td>
                                            <td>
                                                <div class="control-group" id="fields">
                                                    <div class="controls">
                                                        <div class="controls-form" role="form" autocomplete="off">
                                                            <div class="entry input-group col-xs-12">
                                                                <input class="form-control sub_elements" name="value_sub_elements[]" type="text" required disabled/><span class="input-group-btn"><button class="btn btn-success btn-add btn-success-last" type="button"><span class="glyphicon glyphicon-plus"></span></button></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <button type="submit" id="btn-add" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                                <div class="col-sm-7">
                                    <p><div class="text-center">Список элементов</div></p>
                                    <table class="table table-striped table-bordered table-constructorForm table-padding">

                                        @foreach($set_elements as $set_element)
                                            <tr>
                                                <td>{{ $set_element->name_set_elements }}</td>
                                                <td>{{ $set_element->label_set_elements }}</td>
                                                <td>{{ $set_element->name_elements }}</td>
                                                <td>{{ ($set_element->value_sub_elements != null) ? $set_element->value_sub_elements : "---" }}</td>
                                                <td><button type="button" id="{{ $set_element->id_set_elements }}" class="editElementFromForm btn btn-sm btn-default btn-padding-0 pull-right"> Редактировать </button></td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>

    @endsection
