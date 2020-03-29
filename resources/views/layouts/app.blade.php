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
        api_url = "{{ config('app.url') }}/api/";
        function spin() {
            document.getElementsByClassName('refresh-button')[0].classList.add('spin');
        }
    </script>
</script>
</head>

<body class="bg-grey-darkest">
    <div class="container px-0 sm:px-8 mx-auto pb-8 bg-grey-darkest">
        <div class="section" id="app">
            @yield('contents')
        </div>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>
