@extends('layouts.app')

@section('subtitle','勤怠一覧画面(管理者用)')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-admin-list.css') }}" />
@endsection

@section('header-contents')
  <x-header-auth></x-header-auth>
@endsection  

@section('main-contents')
<main class="contents">
  <section class="contents__lists-area">
    <div class="attendance-title">{{ $navLinkDate['year'] }}年{{ $navLinkDate['month'] }}月{{ $navLinkDate['day'] }}日({{ $navLinkDate['dayname'] }})の勤怠</div>
    <form id="nav_header" class="attendance-month" action="{{ route('admin.attendant-serch') }}" method="POST">
      @csrf
      <button class="attendance-month__prev" name="day_prev">
        <span>&larr;</span>前日
      </button>
      <label for="day__current" class="attendance-month__label">
        <input
          type="date"
          class="attendance-month__current"
          name="day__current"
          id="day__current"
          value="{{ $navLinkDate['baseDay'] }}"
        />
      </label>
      <button class="attendance-month__next" name="day_next">
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
        @foreach( $dispAttendanceDatas as $dayData )
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

            $tidDate = $navLinkDate['baseDay'];
          @endphp
        <tr>
          <td>{{ $dayData['name'] }}</td>
          <td>{{ $dayData['clock_in'] }}</td>
          <td>{{ $dayData['clock_out'] }}</td>
          <td>{{ $defRest }}</td>
          <td>{{ $defTotal }}</td>
          <td class="attendance-list__detail">
            <form action="/admin/attendance/staff/detail/{{ $dayData['id'] }}" method="POST" class="admin-list-btn">
              @csrf
              <button type="submit">詳細</button>
              <input type="hidden" name="tid" value={{ $tidDate }}>
              <input type="hidden" name="uid" value={{ $dayData['user_id'] }}>

            </form>
          </td>
          {{-- <td class="attendance-list__detail">
            <a href="/admin/attendance/staff/detail/{{ $dayData['id'] }}?tid={{ $tidDate }}&uid={{ $dayData['user_id'] }}">詳細</a>
          </td> --}}
        </tr>
        @endforeach
      </tbody>
    </table>
  </section>
</main>
<script>
  async function sendData(data) {
    document.forms["nav_header"].submit();
  }
  const send = document.querySelector("#day__current");
  send.addEventListener("change", sendData);
</script>
@endsection