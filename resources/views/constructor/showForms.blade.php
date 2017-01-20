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
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id_forms}}" class="forms-info-all collapsed" data-id="{{$form->id_forms}}">
                                                {{ $form->name_forms }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="accordion{{$form->id_forms}}" class="panel-collapse collapse ">
                                        <div class="panel-body">
                                            <div id="content-form{{$form->id_forms}}" class="col-sm-6">

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
