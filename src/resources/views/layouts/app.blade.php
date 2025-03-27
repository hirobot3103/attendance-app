<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>勤怠アプリ　@yield('subtitle')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/reset.css') }}" />
    @yield('css')
  </head>
  <body>
    @yield('header-contents')
    @yield('main-contents')
  </body>
</html>
