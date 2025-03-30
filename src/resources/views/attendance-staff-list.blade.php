@extends('layouts.app')

@section('subtitle','スタッフ別勤怠一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-staff-list.css') }}" />
@endsection

<x-header-auth>
</x-header-auth>

@section('main-contents')
  <main class="contents">
    <section class="contents__lists-area">
      <div class="attendance-title">{{ $dispAttendanceDatas[0]['user_name'] }}さんの勤怠</div>
      <form id="nav_header" class="attendance-month" action="/admin/attendance/staff/{{ $dispAttendanceDatas[0]['target_id'] }}" method="POST">
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
        <input type="hidden" name="select_user_id" value={{ $dispAttendanceDatas[0]['target_id'] }}">
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
                  <form action="/admin/attendance/staff/detail/{{ $dayData['id'] }}" method="POST" class="admin-list-btn">
                    @csrf
                    <button type="submit">詳細</button>
                  </form>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
      <form action="" class="csv-form">
        <button type="submit" class="form-btn">CSV出力</button>
      </form>  
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
