@extends('layouts.app')
@section('content')
<div class="container panel" style="padding: 15px;">
	
    <form class="form-horizontal" action="/sendMessages" method="POST">
	{{ csrf_field() }}
    <div class="form-group">

        <input type="hidden" class="form-control text-center" id="inputSender" name="sender" value="{{   Auth::user()->name }}" readonly required>
	    <input type="hidden" class="form-control text-center" id="inputRecipient" name="recipient" value="{{   $name }}" readonly required>
  
	  	<div class="row">
       	    <div class="col-sm-offset-1 col-lg-10"><p><textarea class="form-control" rows="5" name="  message_content" placeholder="Текст письма" required></textarea></p></div>
       	</div>

       	<div class="row">
       		<div class="col-sm-offset-1 col-sm-10">
       			<button class="btn btn-primary pull-right" type="submit">Отправить</button>
       		</div>
       	</div>

    </div>     
    </form>


	<table class="table">
        @foreach($messages as $message)
        <tr>
        	<td>
        		{{$message->message_content}}
        	</td>
        </tr>
        <tr>
        	<td style="background-color: #f2f2f2;">
        		<span class="pull-right">{{$message->sender}}</span>
        	</td>
        </tr>
        @endforeach
	</table>

</div>
@endsection