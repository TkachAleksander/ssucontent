@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12 ">
            <div class="panel panel-default">
                <div class="panel-heading">
                    @if (Auth::guest())
                        Пожалуйста, авторизуйтесь для просмотра списка статей.
                    @else
                            Список форм на проверку:
                    @endif
                </div>
                    <div class="panel-body">


                        @if (isset($forms))
                        <div class="panel-group" id="accordion">

                        @foreach($forms as $form)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id_forms}}" class="forms-info forms-info-old collapsed" data-id="{{$form->id_forms}}">
                                        {{ $form->name_forms }}
                                        <span class="pull-right">{{ $form->surname.' '.$form->name }}</span>
                                    </a>
                                </h4>
                            </div>
                            <div id="accordion{{$form->id_forms}}" class="panel-collapse collapse ">
                                <div class="panel-body">
                                    <form action="submitFillForm" method="POST">
                                        {{ csrf_field() }}

                                        <input type="hidden" class="input-id-form" name="id_form" value="">

                                        <div class="row">
                                            <div id="content-form-old{{$form->id_forms}}" class="col-sm-6">

                                            </div>
                                            <div id="content-form{{$form->id_forms}}" class="col-sm-6" style="border-left: 1px solid #eee;" >

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <hr>
                                                <input type="button" class="btn btn-sm btn-danger" value="Отклонить">
                                                <input type="button" class="btn btn-sm btn-success pull-right" value="Притнять">
                                            </div>
                                        </div>


                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        </div>
                        @endif  
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
