@extends('layouts.app')

@section('subtitle','勤怠一覧画面(管理者用)')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-admin-list.css') }}" />
@endsection


@section('header-contents')  
    <header class="page-header">
      <div class="page-logo">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="ロゴ COACHTECH" />
      </div>
      <div class="page-logo"></div>
      <nav class="page-menu">
        <ul>
          <li><a href="">勤怠一覧</a></li>
          <li><a href="">スタッフ一覧</a></li>
          <li><a href="">申請一覧</a></li>
          @if (Auth::guard('admin')->check())
            <li>
              <form action="{{ route('admin.logout') }}" method="post">
                  @csrf
                  <button type="submit" class="page-menu-btn">ログアウト</button>
              </form>
            </li>
          @else
            <li><a href="{{ route('admin.login') }}">ログイン</a></li>
          @endif        
        </ul>
      </nav>
    </header>
@endsection

@section('main-contents')
<main class="contents">
  <section class="contents__lists-area">
    <div class="attendance-title">XXXX年XX月XX日の勤怠</div>
    <form class="attendance-month">
      <button class="attendance-month__prev" name="month_prev">
        <span>&larr;</span>前日
      </button>
      <label for="month__current" class="attendance-month__label">
        <input
          type="date"
          class="attendance-month__current"
          name="month__current"
          id="month__current"
        />
      </label>
      <button class="attendance-month__next" name="month_next">
        翌日<span>&rarr;</span>
      </button>
    </form>
    <table class="attendance-list">
      <thead>
        <tr>
          <th>名前</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>testman</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
        <tr>
          <td>testman2</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
        <tr>
          <td>testman3</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
        <tr>
          <td>testman4</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
        <tr>
          <td>testman5</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
        <tr>
          <td>testman6</td>
          <td>09:00</td>
          <td>18:00</td>
          <td>1:00</td>
          <td>8:00</td>
          <td class="attendance-list__detail"><a href="">詳細</a></td>
        </tr>
      </tbody>
    </table>
    <form action="" class="csv-form">
      <button type="submit" class="form-btn">CSV出力</button>
    </form>
  </section>
</main>
@endsection