<!DOCTYPE html>
<html lang="ru">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="token" content="{{ csrf_token() }}">
    <meta name="yandex-verification" content="8c911b1b9e11ff6f" />
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <link onload="if(media!='all') media='all'" type="text/css" rel="stylesheet" href="{{ Asset('/css/app.css') }}">
    <link rel="canonical" href="{{ url(Request::url()) }}" />

    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.5/leaflet.css" />
    <script src="http://cdn.leafletjs.com/leaflet-0.5/leaflet.js"></script>

    <script src="https://api-maps.yandex.ru/2.1/?apikey={{env('YA_API_KEY', 'c4c6869e-8add-414b-b516-52b1dfccb7db')}}&lang=ru_RU" type="text/javascript"></script>
    <title>{{ setting('site.title') }}</title>

    <style lang="css">
        :root {
            --color-one: #3e444c;
            --color-two: #de6d59;
            --color-third: #cbcbcb;

            --text-color-white: #ededed;
            --text-color-black: #000000;
        }
    </style>
</head>
<body id="body">
    <div id="app">
        @yield('content')
    </div>
</body>
<script async src="{{ Asset('/js/app.js') }}"></script>
</html>
