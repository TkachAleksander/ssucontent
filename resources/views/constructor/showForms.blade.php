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

                            @foreach($forms as $f_name)
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$f_name->id}}" class="forms-info collapsed" data-id="{{$f_name->id}}">
                                                {{ $f_name->name_forms }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="accordion{{$f_name->id}}" class="panel-collapse collapse ">
                                        <div class="panel-body">
                                            <div id="content-form{{$f_name->id}}" class="col-sm-6">

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
