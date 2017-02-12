@extends('layouts.app')
@section('content')
    <script src="{{url('js/jqBootstrapValidation.js')}}"></script>
    <script> $(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(); } ); </script>

    <div class="container">
        <div class="row">
            <div class="col-sm-offset-3 col-sm-6">

                @if (session()->has('status'))
                    <div class="alert alert-{{ session('status.class') }} text-center">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        {{ session('status.message') }}
                    </div>
                @endif

                <div class="panel panel-default shadow">
                    <div class="panel-heading">
                        {{ $forms_info_empty[0]->name_forms }}
                    </div>

                    <div class="panel-body">

                            <div class="">
                                <?php $ver = ""; $forms_info = $forms_info_empty;?>
                                @include('gatherForm')
                            </div>
                        <span class="pull-right"><a class="btn btn-sm btn-primary" href="{{url('/constructor/showForms')}}"> Назад </a></span>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
