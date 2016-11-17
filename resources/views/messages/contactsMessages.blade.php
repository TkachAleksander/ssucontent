@extends('layouts.app')
@section('content')

<div class="container" style="padding: 15px;">
    <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default">
            <div class="panel-heading">
                    Выберете получателя:
            </div>
            
            <div class="panel-body">
                <p class="text-center"></p>
             
                @if (isset($users))
                <div class="row">
                    <div class="col-sm-offset-1 col-sm-4">
                    <table class="list-users table table-bordered">
                    @foreach ($users as $user)
                    	<tr>
                            <td class="text-center">
                                <a href="/showMessages/{{$user->name}}">{{$user->name}}</a>
                            </td>
                        </tr>
                    @endforeach
                    </table>
                    </div>
                </div>             
                @endif

            </div>

        </div>
    </div>
</div>

@endsection