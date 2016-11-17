<!DOCTYPE html>
<html lang="ru">
    <head>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <title>{{ $title }}</title>

        <link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ url('css/project.css') }}">
    
    </head>
    <body>
        <header>
        	@include('header.header')
        </header>
    	
    	<div id="wrapper" class="container">
    		@yield('content')
    	</div>

    	<footer>
    		@include('footer.footer')
    	</footer>
    </body>
</html>