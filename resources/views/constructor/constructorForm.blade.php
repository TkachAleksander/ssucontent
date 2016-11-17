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
                        <li role="presentation"><a href="#page3" aria-controls="page3" role="tab" data-toggle="tab">Просмотр списка форм</a></li>                      
                    </ul>
                </div>

                <div class="panel-body">                  
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
                            
                                <form action="/addNewElement" method="POST">
                                    <div class="col-sm-4">
                                        <p><div class="text-center"> Создать эллемент </div></p>
                                        <table class="table-padding">
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
                                    <div class="col-sm-offset-1 col-sm-6">
                                        <p><div class="text-center">Список элементов</div></p>
                                        <table class="table table-bordered table-constructorForm table-padding">
    
                                        @foreach($set_elements as $set_element)
                                            <tr>
                                                <td>{{ $set_element->id_set_elements }}</td>
                                                <td>{{ $set_element->name_set_elements }}</td>
                                                <td>{{ $set_element->name_elements }}</td>
                                                <td>{{ $set_element->value_sub_elements }}</td>
                                                <td><button id="{{ $set_element->id_set_elements }}" class="addElementInForm btn btn-sm btn-default btn-padding-0 pull-right"> Удалить </buttton></td>
                                            </tr>
                                        @endforeach
                                        </table>
                                    </div>
                                </form>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="page3">

                            <div class="col-sm-12">
                                @foreach($forms_names as $f_name)
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"> 
                                            <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$f_name->id}}" class="forms-info" data-id="{{$f_name->id}}">
                                                {{ $f_name->name_forms }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="accordion{{$f_name->id}}" class="panel-collapse collapse ">
                                        <div class="panel-body">
                                            <div class="col-sm-6">
                                                <?php
                                                    // switch ($f_info->name_elements) {
                                                    //     case "dopdown":
                                                    //         echo "dropdown </br>";
                                                    //         break;
                                                    //     case "checkbox":
                                                    //         echo "checkbox </br>";
                                                    //         break;                                                            
                                                        
                                                    //     default:
                                                    //         # code...
                                                    //         break;
                                                    // }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>            

    </div>
</div>

@endsection
