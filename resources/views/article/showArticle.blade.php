@extends('layouts.app')
@section('content')
	<div class="container">
		<h1> {{$article->title}} </h1>
		<p> {{$article->content}} </p>
		<hr/>
		<p><b>Автор: </b> {{$article->author}} <span class="pull-right"><b>Создана: </b> {{$article->created_at}} </span></p>
	</div>
@endsection