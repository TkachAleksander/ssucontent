@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    @if (Auth::guest())
                        Пожалуйста, авторизуйтесь для просмотра списка статей.
                    @else
                        Список доступных форм:
                    @endif
                </div>
                    <div class="panel-body">


                        @if (isset($forms))
                        <div class="panel-group" id="accordion">

                        @foreach($forms as $form)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id_forms}}" class="forms-info collapsed" data-id="{{$form->id_forms}}">
                                        {{ $form->name_forms }}
                                        @if($role != null)
                                            <span class="pull-right">{{ $form->surname.' '.$form->name }}</span>
                                        @endif
                                    </a>
                                </h4>
                            </div>
                            <div id="accordion{{$form->id_forms}}" class="panel-collapse collapse ">
                                <div class="panel-body">
                                    <form action="submitFillForm" method="POST">
                                        {{ csrf_field() }}

                                        <div class="row">
                                            <div id="content-form{{$form->id_forms}}" class="col-sm-6">

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <hr>
                                                <input type="submit" class="btn btn-sm btn-primary pull-right" value="Отправить">
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
