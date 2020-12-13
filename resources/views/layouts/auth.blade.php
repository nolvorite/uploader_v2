<!DOCTYPE html>
<html lang="en" class="guest-pages">

<head>
    @include('partials.head')
    <style type="text/css">
        html.guest-pages{
            background:#fff url('{{ URL::to('') }}/guest_wallpape.jpg');
            background-size: cover;
        }
    </style>
</head>

<body class="guest-pages" >

    
    <div id="spanner">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    <div class="scroll-to-top"
         style="display: none;">
        <i class="fa fa-arrow-up"></i>
    </div>

    @include('partials.javascripts')

    <script type="text/javascript">
        isLoggedIn = false;
    </script>

</body>
</html>