@extends('layouts.app')

@section('subtitle','申請一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-request-list.css') }}" />
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
        <div class="attendance-title">申請一覧</div>
        <div class="request-link">
            <a href="/stamp_correction_request/list/15"><span>承認待ち</span></a>
            <a href="/stamp_correction_request/list/11">承認済み</a>
        </div>
        <hr />
        <table class="attendance-list">
          <thead>
            <tr>
              <th>状態</th>
              <th>名前</th>
              <th>対象日時</th>
              <th>申請理由</th>
              <th>申請日時</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($requestDates as $date)
              @php
                if ($date['stat'] <> 15 ) {
                  $stat = "承認待ち";
                }

                $clockin = date('Y/m/d', strtotime( $date['clock_in'] ));
                $reqDate = date('Y/m/d', strtotime( $date['created_at'] ));
                $descript = $date['descript'];

                foreach( $requestName as $regName) {
                  if( $date['user_id'] == $regName['id']){
                    $name = $regName['name'];
                    break;
                  }
                }

              @endphp
              <tr>
                <td>{{ $stat }}</td>
                <td>{{ $name }}</td>
                <td>{{ $clockin }}</td>
                <td>{{ $descript }}</td>
                <td>{{ $reqDate }}</td>
                <td class="attendance-list__detail"><a href="/stamp_correction_request/{{ $date['id'] }}">詳細</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </section>
    </main>
@endsection