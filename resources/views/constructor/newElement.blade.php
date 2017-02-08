@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default shadow">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active'); $('#tab2').addClass('active') </script>

                    <div class="panel-body">
                        @if ($errors->has('list_forms_departments'))
                            <span class="help-block">
                                <strong>{{ $errors->first('list_forms_departments') }}</strong>
                            </span>
                        @endif

                        <form action="/constructor/addNewElement" method="POST">
                            <div class="row">
                                <div class="col-sm-5">
                                    <p><div class="text-center"> Создать эллемент </div></p>
                                    <table class="table-padding">
                                        {{ csrf_field() }}
                                        <tr>
                                            <td> Заголовок (lable) </td>
                                            <td>
                                                <input id="label_fields" class="form-control" type="text" name="label_fields" required>
                                                @if ($errors->has('label_fields'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('label_fields') }}</strong>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Тип элемента </td>
                                            <td>
                                                <select id="select_labels" class="multiselect" name="id_elements">
                                                    @foreach($elements as $element)
                                                        <option value="{{ $element->id_elements }}">{{ $element->name_elements }}</option>
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
                                                                <input class="form-control sub_elements" name="label_sub_elements[]" type="text" required disabled/><span class="input-group-btn"><button class="btn btn-success btn-add btn-success-last" type="button"><span class="glyphicon glyphicon-plus"></span></button></span>
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

                                        @foreach($fields as $field)
                                            <tr>
                                                <td>{{ $field->label_fields }}</td>
                                                <td>{{ $field->name_elements }}</td>
                                                {{--<td {{(empty($field->labels_sub_elements)) ? "class=text-center" : ""}}>--}}
                                                <td class="text-center">
                                                    @if(!empty($field->labels_sub_elements))
                                                        {{$field->labels_sub_elements}}
                                                    @else
                                                        {{"---"}}
                                                    @endif
                                                </td>
                                                <td><button type="button" id="{{ $field->id_fields }}" class="editElementFromForm btn btn-sm btn-default btn-padding-0 pull-right"> Редактировать </button></td>
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
