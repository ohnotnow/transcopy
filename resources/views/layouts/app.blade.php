<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Transcopy</title>

    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    <script>
        function spin() {
            document.getElementsByClassName('refresh-button')[0].classList.add('spin');
        }
    </script>
</script>
</head>

<body>
    <div class="container px-4 mx-auto pb-8">
        <div class="section" id="app">
            @yield('contents')
        </div>
    </div>
</body>
<script src="/js/app.js"></script>
</html>
