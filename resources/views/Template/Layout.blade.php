<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda online') }}</title>

    <!-- Styles -->


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-beta.40/css/uikit.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-beta.40/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-beta.40/js/uikit-icons.min.js"></script>

</head>
<body>

<div class="uk-offcanvas-content">


    <nav class="uk-navbar-container">
        <div class="uk-container">
            <div uk-navbar="" class="uk-navbar">
                <div class="uk-navbar-left">

                    <ul class="uk-navbar-nav">
                        <li class="uk-active"><a class="uk-navbar-toggle" href="#" uk-toggle="target: #offcanvas-nav">
                                <span uk-navbar-toggle-icon></span> <span class="uk-margin-small-left">Menu</span>
                            </a></li>
                    </ul>

                </div>
                <div class="uk-navbar-right">
                    <ul class="uk-navbar-nav">
                        @guest
                            <li><a href="{{ route('login') }}">Ingresar</a></li>
                            <li><a href="{{ route('register') }}">Registrarse</a></li>
                            @else
                                <li>
                                    <a href="#">{{ Auth::user()->name }}</a>
                                    <div class="uk-navbar-dropdown">
                                        <ul class="uk-nav uk-navbar-dropdown-nav">
                                            <li><a class="dropdown-item" href="{{ route('logout') }}"
                                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                                    Logout
                                                </a>
                                            </li>
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                        </ul>
                                    </div>
                                </li>
                                @endguest
                    </ul>
                </div>
            </div>
        </div>
    </nav>


    <div class="uk-offcanvas-content">

        <div id="offcanvas-nav" uk-offcanvas="overlay: true">

            <div class="uk-offcanvas-bar">
                <h3>ADMIN</h3>
                <button class="uk-offcanvas-close" type="button" uk-close></button>
                <ul class="uk-nav uk-nav-default">
                    <li>
                        <a href="{!! route('products.index') !!}">
                            <span class="uk-margin-small-right" uk-icon="icon: file-edit"></span>
                            Productos
                        </a>
                    </li>
                    <li>
                        <a href="{!! route('category.index') !!}">
                            <span class="uk-margin-small-right" uk-icon="icon: tag"></span>
                            Categorias
                        </a>
                    </li>
                    <li>
                        <a href="{!! route('purchases.index') !!}">
                            <span class="uk-margin-small-right" uk-icon="icon: cart"></span>
                            Ordenes de compra
                        </a>
                    </li>
                    <li>
                        <a href="{!! route('cellars.index') !!}">
                            <span class="uk-margin-small-right" uk-icon="icon: album"></span>
                            Inventario
                        </a>
                    </li>
                    <li>
                        <a href="{!! route('admin.index') !!}">
                            <span class="uk-margin-small-right" uk-icon="icon: users"></span>
                            Clientes
                        </a>
                    </li>
                    <hr class="uk-divider-icon">
                    <li>
                        <a href="#">
                            <span class="uk-margin-small-right" uk-icon="icon: cog"></span>
                            Configurar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <div class="uk-margin">
        @yield('content')
    </div>
</div>


</body>
</html>
