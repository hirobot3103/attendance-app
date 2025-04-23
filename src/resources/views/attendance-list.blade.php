{{-- 一般ユーザー用勤怠一覧画面 --}}
@extends('layouts.app')

@section('subtitle','勤怠一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-list.css') }}" />
@endsection

@section('header-contents')
  <x-header-user></x-header-user>
@endsection  

@section('main-contents')
  @php

    // 未来の日付へはリンクを作らない
    $todayDate      = new DateTime( date("Y-m") );
    $todayDateDay   = new DateTime( date("Y-m-d") );

    $displayDate = new DateTime( date($navLinkDate['baseMonth']) );

    $lockLink = 0;       // ヘッダーナビ部分
    $lockDairyLink = 0;  // 日付毎のデータ欄
    if($todayDate == $displayDate) {
      $lockLink = 1;
    } else {
      $lockLink = 0;
    }
  @endphp
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
          @if ($lockLink == 1)
            <p class="attendance-month__next" name="">
              翌月<span>&rarr;</span>
            </p>
          @else
            <button class="attendance-month__next" name="month_next">
              翌月<span>&rarr;</span>
            </button>
          @endif
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
                  <td class="attendance-list__detail">
                    @if($lockDairyLink == 1)
                      <p>詳細</p>
                    @else
                      <a href="/attendance/{{ $dayData['id'] }}?tid={{ $tidDate }}">詳細</a>      
                    @endif
                  </td>
                </tr>
                @php
                  // 今日の日付と表示用の日付が一致したら、それ以降「詳細」リンクをつけない
                  $tidDateDay = new DateTime( date($tidDate) );
                  if($todayDateDay == $tidDateDay) {
                    $lockDairyLink = 1;
                  }
                @endphp
      
              @endforeach
            @endif
          </tbody>
        </table>
      </section>
    </main>
    <p>attendance-list.blade.php</p>
    <script>
      async function sendData(data) {
        document.forms["nav_header"].submit();
      }
      const send = document.querySelector("#month__current");
      send.addEventListener("change", sendData);
    </script>
@endsection