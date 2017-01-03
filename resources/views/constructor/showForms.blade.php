@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">


            <div class="col-md-12">
                <div class="panel panel-default">

                    @include('constructor.constructorTabs')
                    <script defer> $('ul[role=tablist]').removeClass('active'); $('#tab3').addClass('active') </script>

                    <div class="panel-body">
                        <div class="col-sm-12">

                            @foreach($forms as $form)
                                <div class="panel panel-default">
                                    <?php $style = ($form->show == 0) ? "style=background-color:#fff1ab" : '' ;?>
                                    <div class="panel-heading" {{ $style }}>
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id}}" class="forms-info-all collapsed" data-id="{{$form->id}}" {{--data-id-departments="{{$form->id_departments}}" data-generatestring="{{$form->generateString}}"--}}>
                                                {{ $form->name_forms }}
                                                <span class="pull-right">
                                                     <?php $show = ($form->show == 0) ? "[скрыта]" : "[видна]" ;?>
                                                    {{ $show }}
                                                </span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="accordion{{$form->id}}" class="panel-collapse collapse ">
                                        <div class="panel-body">
                                            <div id="content-form{{$form->id}}" class="col-sm-6">

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

@endsection
