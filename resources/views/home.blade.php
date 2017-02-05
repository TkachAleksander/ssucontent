{{--@extends('layouts.app')--}}
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-sm-12 ">

                    @if (Auth::guest())
                        <div class="row">
                            <div class="col-sm-offset-1 col-sm-10">
                                <div class="alert alert-danger text-center">
                                    Пожалуйста, авторизуйтесь для просмотра списка форм.
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (session()->has('status'))
                         <div class="row">
                             <div class="col-sm-offset-1 col-sm-10">
                                 <div class="alert alert-{{ session('status.class') }} text-center">
                                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                         <span aria-hidden="true">&times;</span>
                                     </button>
                                     {{ session('status.message') }}
                                 </div>
                             </div>
                         </div>
                    @endif


                <div class="row">
                    @if (!empty($forms))
                        @foreach($forms as $form)
                            <div class="col-sm-offset-1 col-sm-10">
                                <div class="thumbnail shadow">

                                    <div class="caption">
                                        <label><b>{{ $form->name_forms }}</b></label>
                                        <hr>
                                        <dl class="dl-horizontal">
                                            @if (Auth::user()->id_roles == 1)
                                                <dt>Отдел:</dt><dd>{{$form->name_departments}}</dd>
                                                <dt>Отправитель:</dt><dd>{{$form->surname." ".$form->name." ".$form->middle_name}}</dd>
                                                <dt>Дата заполнения:</dt><dd>{{$form->updated_at}}</dd>
                                                <dt>Дата обновления:</dt><dd>{{$form->date_update_forms}}</dd>
                                            @else
                                                <dt>Статус:</dt><dd><span style="color: {{$form->status_color}}; font-size: 13px">{{ $form->name_status_checks }}</span></dd>
                                                <dt>Дата заполнения:</dt><dd>{{$form->updated_at}}</dd>
                                                <dt>Дата обновления:</dt><dd>{{$form->date_update_forms}}</dd>
                                            @endif
                                            <a href="{{ url('/viewForm/'.$form->id_forms_departments) }}" class="btn btn-primary forms-btn pull-right" role="button">Подробнее</a>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    @else
                        @if(!Auth::guest())
                            <div class="row">
                                <div class="col-sm-offset-1 col-sm-10">
                                    <div class="alert alert-success text-center">
                                        Список форм пуст !
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>

@endsection
