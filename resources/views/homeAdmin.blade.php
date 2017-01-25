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
                            {{--{{dd($forms)}}--}}
                        <div class="panel-group" id="accordion">
                        @foreach($forms as $form)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#{{$form->generateString}}" class="forms-info forms-info-old collapsed" data-id="{{$form->id_forms}}" data-id-forms-departments="{{$form->id_forms_departments}}" data-generatestring="{{$form->generateString}}">
                                        {{ $form->name_forms }}
                                        <span class="pull-right">{{ $form->name_departments }}</span>
                                    </a>
                                </h4>
                            </div>
                            <div id="{{$form->generateString}}" class="panel-collapse collapse">
                                <div class="panel-body">
                                        <input type="hidden" class="input-id-form" name="id_form" value="">

                                        <div class="row">
                                            <div id="content-form-old{{$form->generateString}}" class="col-sm-6">

                                            </div>
                                            <form action="submitFillForm" method="POST">
                                                {{ csrf_field() }}
                                                <div id="content-form{{$form->generateString}}" class="col-sm-6" style="border-left: 1px solid #eee;">

                                                </div>
                                            </form>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <hr>
                                                <input type="button" class="btn btn-sm btn-danger btn-reject-form" data-id-forms-departments="{{$form->id_forms_departments}}" value="Отклонить">
                                                <input type="button" class="btn btn-sm btn-success btn-accept-form pull-right" data-id-forms-departments="{{$form->id_forms_departments}}" value="Притнять">
                                            </div>
                                        </div>
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
