@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row">


        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading panel-heading-constructorForm">
                    <ul class="nav nav-tabs nav-tabs-constructorForm" role="tablist">
                        <li role="presentation" class="active"><a href="#page1" aria-controls="page1" role="tab" data-toggle="tab">Собрать форму</a></li>
                        <li role="presentation"><a href="#page2" aria-controls="page2" role="tab" data-toggle="tab">Дбавить элемент</a></li>
                        <li role="presentation"><a href="#page3" aria-controls="page3" role="tab" data-toggle="tab">Просмотр форм</a></li>
                    </ul>
                </div>



                <div class="panel-body">
                    <div  class="row">                 
                    
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="page1">
                            <div class="row"> 
                                <div class="col-sm-12">
                                    <div class="col-sm-6">
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
                                    <table class="table table-bordered table-constructorForm">
                                    @foreach($set_elements as $set_element)
                                        <tr>
                                            <td><button id="{{ $set_element->id_set_elements }}" class="addElementInForm btn btn-sm btn-warning btn-padding-0"> < </buttton></td>
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
                        <div role="tabpanel" class="tab-pane" id="page2">
                            <div class="row">
                                <form action="/addNewElement" method="POST">
                                    <div class="col-sm-4">
                                        <table class=" table-padding">
                                        {{ csrf_field() }}
                                            <tr>
                                                <td> Имя элемента </td>
                                                <td> 
                                                    <input class="form-control" type="text" name="name_set_elements" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td> Заголовок (lable) </td>
                                                <td> 
                                                    <input class="form-control" type="text" name="label_set_elements" required>
                                                </td>
                                            </tr>                                
                                            <tr>
                                                <td> Тип элемента </td>
                                                <td>
                                                    <select class="multiselect select_labels" name="id_elements">
                                                        @foreach($elements as $element)
                                                            <option value="{{ $element->id }}">{{ $element->name_elements }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>       
                                            <tr>
                                                <td> Список выбора "|"</td>
                                                <td> 
                                                    <textarea class="form-control textarea_sub_elements" name="value_sub_elements" disabled></textarea>
                                                </td>
                                            </tr>          
                                            <tr>
                                                <td colspan="2">
                                                    <button type="submit" class="btn btn-sm btn-primary btn-padding-0 pull-right"> Добавить </button>
                                                </td>
                                            </tr>                                                
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                </div>
            </div>
        </div>            

    </div>
</div>

@endsection
