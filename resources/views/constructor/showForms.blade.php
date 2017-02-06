@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">


            <div class="col-md-12">
                <div class="panel panel-default shadow">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active'); $('#tab3').addClass('active') </script>

                    <div class="panel-body">
                        <div class="col-sm-12">

                            {{--@foreach($forms as $form)--}}
                                {{--<div class="panel panel-default shadow">--}}
                                    {{--<div class="panel-heading">--}}
                                        {{--<h4 class="panel-title">--}}
                                            {{--<a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id_forms}}" class="forms-info-all collapsed" data-id="{{$form->id_forms}}">--}}
                                                {{--{{ $form->name_forms }}--}}
                                            {{--</a>--}}
                                        {{--</h4>--}}
                                    {{--</div>--}}
                                    {{--<div id="accordion{{$form->id_forms}}" class="panel-collapse collapse ">--}}
                                        {{--<div class="panel-body">--}}
                                            {{--<div id="content-form-current{{$form->id_forms}}" class="col-sm-6">--}}

                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--@endforeach--}}
                            <div class="row">
                                @if (!empty($forms))
                                    @foreach($forms as $form)
                                        <div class="col-sm-12">
                                            <div class="thumbnail shadow">

                                                <div class="caption">
                                                    <label><b>{{ $form->name_forms }}</b></label>
                                                    <dl class="dl-horizontal">
                                                        <a href="{{ url('/viewFormEmpty/'.$form->id_forms) }}" class="btn btn-primary forms-btn pull-right" role="button">Подробнее</a>
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
            </div>

        </div>
    </div>
    <script type="text/javascript" src="{{ url('js/switchForm.js') }}"></script>

@endsection
