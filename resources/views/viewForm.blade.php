@extends('layouts.app')

@section('content')

    <script type="text/javascript" src="{{ url('js/switchForm.js') }}"></script>

    <div class="container">
        <div class="row">
            <div class="col-sm-12 ">

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
                        {{$name_forms}}
                        <span class="pull-right"><a href="{{url('/')}}"> Вернуться к списку </a></span>
                    </div>

                    <div class="panel-body">
                        <div id="content-form-old" class="col-sm-6"></div>

                        <form action={{$action}} method="POST">
                            {{ csrf_field() }}

                            <input type="hidden" name="id_forms_departments" value="{{$id_forms_departments}}">
                            <input type="hidden" name="id_forms" value="{{$id_forms}}">
                            <input type="hidden" name="updated_at" value="{{$updated_at}}">

                            <div id="content-form-current" class="col-sm-6" style="border-left: 1px solid #eee;"></div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <hr>
                                </div>
                            </div>

                        @if ($admin)
                                <input type="submit"
                                       class="btn btn-sm btn-success btn-accept-form pull-right confirmRequired"
                                       value="Притнять">
                        </form>


                        <form action="/rejectForm" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" class="duplicate-message" name="message">
                            <input type="hidden" name="id_forms_departments" value="{{$id_forms_departments}}">
                            <input type="submit" class="btn btn-sm btn-danger btn-reject-form" value="Отклонить">
                        </form>

                        @else
                            @if($id_status_checks != 2 )
                                <input type="submit" class="btn btn-sm btn-primary pull-right confirmRequired"
                                       value="Отправить на проверку">
                            @else
                                <span class="pull-left"> Нашли ошибку ? - Отправте повторно !  (В поле текст сообщения укажите изменения внесенные в форму) </span>
                                <input type="hidden" class="duplicate-message" name="message">
                                <input type="submit" class="btn btn-sm btn-primary pull-right confirmRequired"
                                       value="Отправить повторно">
                            @endif
                        </form>
                        @endif

                    </div>
                </div>

            </div>

            @if ($id_status_checks == 2)
            <div class="col-sm-12">
                <form action="/sendMessage" method="POST">
                    {{ csrf_field() }}
                    <div class="thumbnail shadow">
                        <div class="caption">
                            <div class="form-group">

                                <label for="comment">Текст сообщения:</label>
                                <textarea class="form-control message-textarea model-text" name="message"
                                          rows="3" {{$required}}></textarea>
                                <input type="hidden" name="id_forms_departments" value="{{$id_forms_departments}}">
                                @if ($admin)
                                    <button type="submit" class="btn btn-sm btn-primary"> Отправить</button>
                                @endif

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            @if (!empty($messages))
                @foreach($messages as $message)
                    <div class="col-sm-12">
                        <div class="card shadow">
                            <div class="row">
                                <div class="col-sm-12">
                                    @if(!$message->is_read && Auth::user()->id != $message->id)
                                        <div class="new-message pull-right">
                                            <div class="new-message-text"> new </div>
                                        </div>
                                    @endif

                                    <strong>{{$message->surname." ".$message->name." ".$message->middle_name}}</strong>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <hr>
                                    <p>{!! $message->message  !!}</p>
                                    {{$message->created_at}}
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
    <script>
        formsInfo({{$id_forms .",".$id_forms_departments}});
        formsInfoOld({{$id_forms .",".$id_forms_departments}});
    </script>
@endsection
