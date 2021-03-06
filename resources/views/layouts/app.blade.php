<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta id="token" name="token" content="{{ csrf_token() }}">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/tcal.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/project.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/font-awesome.min.css') }}">

    <!-- MULTISELECT -->
    <link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-multiselect.css') }}">
    <script type="text/javascript" src="{{ url('js/jquery-3.1.1.min.js') }}"></script>

</head>

<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <a class="navbar-brand" href="{{ url('/') }}">
                    SumDU
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Авторизоваться</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="user dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                               {{ Auth::user()->surname.' '.Auth::user()->name.' '.Auth::user()->middle_name }}
                               <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/') }}"><i class="fa fa-btn fa-file "></i> Список форм </a></li>
                                @if (Auth::user()->id_roles == 1)
                                    <li><a href="{{ url('/doneForm') }}"><i class="fa fa-btn fa-chevron-down"></i> Обработанные формы </a></li>
                                    <li><a href="{{ url('/constructor/addForm') }}"><i class="fa fa-btn fa-cog"></i> Конструктор форм </a></li>
                                    <li><a href="{{ url('/registration') }}"><i class="fa fa-btn fa-user"></i> Управление пользователями </a></li>
                                @endif
                                <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i> Выйти </a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- JavaScripts -->
    <script type="text/javascript" src="{{ url('js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/bootstrap-multiselect.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/jquery-ui.js') }}" ></script>
    <script type="text/javascript" src="{{ url('js/project.js') }}"></script>

</body>
</html>