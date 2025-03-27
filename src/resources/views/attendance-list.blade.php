@extends('layouts.app')

@section('subtitle','勤怠一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-list.css') }}" />
@endsection

@section('header-contents')  
  <header class="page-header">
    <div class="page-logo">
      <img src="{{ asset('assets/img/logo.svg') }}" alt="ロゴ COACHTECH" />
    </div>
    <div class="page-logo"></div>
    <nav class="page-menu">
      <ul>
        <li><a href="{{ route('user.dashboard') }}">勤怠</a></li>
        <li><a href="{{ route('user.attendant-list') }}">勤怠一覧</a></li>
        <li><a href="{{ route('user.attendant-req') }}">申請</a></li>
        @if (Auth::guard('web')->check())
          <li>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="page-menu-btn">ログアウト</button>
            </form>
          </li>
        @else
          <li><a href="{{ route('login') }}">ログイン</a></li>
        @endif
      </ul>
    </nav>
  </header>
@endsection

@section('main-contents')
    <main class="contents">
      <section class="contents__lists-area">
        <div class="attendance-title">勤怠一覧</div>
        <form id="nav_header" class="attendance-month" action="{{ route('user.attendant-serch') }}" method="POST">
          @csrf
          <button class="attendance-month__prev" name="month_prev">
            <span>&larr;</span>前月
          </button>
          <label for="month__current" class="attendance-month__label">
            <input
              type="month"
              class="attendance-month__current"
              name="month__current"
              id="month__current"
              value="{{ $navLinkDate['baseMonth'] }}"
            />
          </label>
          <button class="attendance-month__next" name="month_next">
            翌月<span>&rarr;</span>
          </button>
        </form>
        <table class="attendance-list">
          <thead>
            <tr>
              <th>日付</th>
              <th>出勤</th>
              <th>退勤</th>
              <th>休憩</th>
              <th>合計</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @if (!empty($dispAttendanceDatas))
              @foreach ( $dispAttendanceDatas as $dayData )
                @php
                  $defRest = '';
                  $defTotal = '';

                  $retDiff = '0:00';
                  $hours = floor((int)$dayData['def_rest'] / 60);
                  $remainingMinutes = (int)$dayData['def_rest'] % 60;
                  if($remainingMinutes < 10){
                    $retDiff ="{$hours}:0{$remainingMinutes}";                  
                  } else {
                    $retDiff ="{$hours}:{$remainingMinutes}";                                    
                  }
                  $defRest = $retDiff;

                  $retDiff = '0:00';
                  $hours = floor((int)$dayData['total_attendance'] / 60);
                  $remainingMinutes = (int)$dayData['total_attendance'] % 60;
                  if($remainingMinutes < 10){
                    $retDiff ="{$hours}:0{$remainingMinutes}";                  
                  } else {
                    $retDiff ="{$hours}:{$remainingMinutes}";                                    
                  }
                  $defTotal = $retDiff;

                  $tidDate = $navLinkDate['baseMonth'] . '-' . substr($dayData['date'], 3, 2);
                @endphp
                <tr>
                  <td>{{ $dayData['date'] }}</td>
                  <td>{{ $dayData['clock_in'] }}</td>
                  <td>{{ $dayData['clock_out'] }}</td>
                  <td>{{ $defRest }}</td>
                  <td>{{ $defTotal }}</td>
                  <td class="attendance-list__detail"><a href="/attendance/{{ $dayData['id'] }}?tid={{ $tidDate }}">詳細</a></td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </section>
    </main>
    <script>
      async function sendData(data) {
        document.forms["nav_header"].submit();
      }
      const send = document.querySelector("#month__current");
      send.addEventListener("change", sendData);
    </script>
@endsection