{{--@extends('layouts.app')--}}
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-sm-6 ">
                <div class="row">

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

                    @if (!empty($reject_forms))
                        @foreach($reject_forms as $form)
                            <div class="col-sm-offset-1 col-sm-10">
                                <div class="thumbnail shadow">

                                    <div class="caption">
                                        <label><b>{{ $form->name_forms }}</b></label>
                                        <hr>
                                        <dl class="dl-horizontal">
                                            <dt>Статус:</dt><dd><span style="color: {{$form->status_color}}; font-size: 13px">{{ $form->name_status_checks }}</span><br>{{$form->updated_at}} </dd>
                                            <dt>Отдел:</dt><dd>{{$form->name_departments}}</dd>
                                            <dt>Отправитель:</dt><dd>{{$form->surname." ".$form->name." ".$form->middle_name}}</dd>
                                            {{--<dt>Дата заполнения:</dt><dd>{{$form->updated_at}}</dd>--}}
                                            <dt>Дата обновления:</dt><dd>{{$form->date_update_forms}}</dd>

                                            <a href="{{ url('/viewDoneForm/'.$form->id_forms_departments) }}" class="btn btn-primary forms-btn pull-right" role="button">Подробнее</a>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>

            <div class="col-sm-6 ">
                <div class="row">

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

                    @if (!empty($accept_forms))
                        @foreach($accept_forms as $form)
                            <div class="col-sm-offset-1 col-sm-10">
                                <div class="thumbnail shadow">

                                    <div class="caption">
                                        <label><b>{{ $form->name_forms }}</b></label>
                                        <hr>
                                        <dl class="dl-horizontal">
                                            <dt>Статус:</dt><dd><span style="color: {{$form->status_color}}; font-size: 13px">{{ $form->name_status_checks }}</span><br>{{$form->updated_at}}</dd>
                                            <dt>Отдел:</dt><dd>{{$form->name_departments}}</dd>
                                            <dt>Отправитель:</dt><dd>{{$form->surname." ".$form->name." ".$form->middle_name}}</dd>
                                            {{--<dt>Дата заполнения:</dt><dd>{{$form->updated_at}}</dd>--}}
                                            <dt>Дата обновления:</dt><dd>{{$form->date_update_forms}}</dd>

                                            <a href="{{ url('/viewDoneForm/'.$form->id_forms_departments) }}" class="btn btn-primary forms-btn pull-right" role="button">Подробнее</a>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection
