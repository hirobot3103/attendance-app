@extends('layouts.app')

@section('subtitle','スタッフ一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/staff-list.css') }}" />
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
        <li><a href="{{ route('admin.stafflist') }}">スタッフ一覧</a></li>
        <li><a href="">申請一覧</a></li>
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
      <div class="attendance-title">スタッフ一覧</div>
      <table class="attendance-list">
        <thead>
          <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @if( !empty($userDates ))
            @foreach ($userDates as $data)
              <tr>
                <td>{{ $data['name'] }}</td>
                <td>{{ $data['email'] }}</td>
                <td class="attendance-list__detail"><a href="/admin/attendance/staff/{{ $data['id'] }}">詳細</a></td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </section>
  </main>
@endsection