@extends('layouts.app')

@section('subtitle','勤怠登録画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/top.css') }}" />
@endsection

@section('header-contents')
  <header class="page-header">
    <div class="page-logo">
      <img src="./assets/img/logo.svg" alt="ロゴ COACHTECH" />
    </div>
    <div class="page-logo"></div>
    <nav class="page-menu">
      <ul>
        <li><a href="/attendance">勤怠</a></li>
        <li><a href="/attendance/list">勤怠一覧</a></li>
        <li><a href="/">申請</a></li>
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
  @if ($errors->any())
  @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
  @endforeach  
@endif
<main class="contents">
  <section class="contents__lists-area">
    <div class="attendance-status">勤務外</div>
    <div class="attendance-date" id="attendance-date">2023年6月1日(木)</div>
    <div class="attendance-time" id="attendance-time">08:00</div>
    <form action="" class="attendance-btn__area">
      <button type="submit" class="attendance-btn">出&nbsp;勤</button>
      <button type="submit" class="rest-btn">休憩入</button>
    </form>
    <p class="attendance-massage">お疲れ様でした。</p>
  </section>
</main>

<script>
  function clock() {
    var twoDigit = function (num) {
      var digit;
      if (num < 10) {
        digit = "0" + num;
      } else {
        digit = num;
      }
      return digit;
    };
    var weeks = new Array("日", "月", "火", "水", "木", "金", "土");

    // JSTが取得できない場合に備えて、手計算
    var now = new Date(
      Date.now() + (new Date().getTimezoneOffset() + 9 * 60) * 60 * 1000
    );
    var year = now.getFullYear();
    var month = twoDigit(now.getMonth() + 1);
    var day = twoDigit(now.getDate());
    var week = weeks[now.getDay()];
    var hour = twoDigit(now.getHours());
    var minute = twoDigit(now.getMinutes());
    var second = twoDigit(now.getSeconds());

    document.getElementById("attendance-date").textContent =
      year + "/" + month + "/" + day + " (" + week + ")";
    document.getElementById("attendance-time").textContent =
      hour + ":" + minute;
  }
  setInterval(clock, 1000);
</script>
@endsection
