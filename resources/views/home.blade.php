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
                        {{--<a href="/newArticle" class="pull-right"> Добавить статью </a>--}}
                    @endif
                </div>
                    <div class="panel-body">
                  

                        @if (isset($forms))
                        <div class="panel-group" id="accordion">

                        @foreach($forms as $form)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"> 
                                    <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{$form->id_forms}}">
                                        {{ $form->name_forms }}
                                        @if($role != null)
                                            <span class="pull-right">{{ $form->surname.' '.$form->name }}</span>
                                        @endif
                                    </a>
                                </h4>
                            </div>
                            <div id="accordion{{$form->id_forms}}" class="panel-collapse collapse ">
                                <div class="panel-body">
                                    {{--<table class="table table-bordered">--}}
                                        {{--<tr>--}}
                                            {{--<td> <b>Заголовок</b> </td>--}}
                                            {{--<td><a href="/showArticle/{{$article->id}}">{{ $article->name_articles }}</a></td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>  --}}
                                            {{--<td> <b>Контент</b> </td>--}}
                                            {{--<td>  </td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>  --}}
                                            {{--<td> <b>Создана</b> </td>--}}
                                            {{--<td>{{ $article->created_at }}</td>--}}
                                        {{--</tr>   --}}
                                        {{--<tr>  --}}
                                            {{--<td> <b>Отредактирована</b> </td>--}}
                                            {{--<td> --}}
                                            {{--@if ($article->updated_at == null) --}}
                                                {{--{{ "---" }}--}}
                                            {{--@else --}}
                                                {{--{{ $article->updated_at }}--}}
                                            {{--@endif--}}
                                            {{--</td>--}}
                                        {{--</tr>                                        --}}
                                        {{--<tr>  --}}
                                            {{--<td> <b>Автор</b> </td>--}}
                                            {{--<td>{{ $article->surname.' '.$article->name }}</td>--}}
                                        {{--</tr>                                      --}}
                                        {{--<tr>--}}
                                        {{--<td></td>--}}
                                            {{--<td>--}}
                                                {{--<a href="/editArticle/{{$form->id_forms}}" class="btn btn-sm btn-primary"> Редактировать </a>--}}
                                                {{--<a href="/removeArticle/{{$form->id_forms}}" class="btn btn-sm btn-danger pull-right confirmDelete"> Удалить </a>--}}
                                            {{--</td>--}}
                                        {{--</tr>--}}
                                    {{--</table>--}}
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
