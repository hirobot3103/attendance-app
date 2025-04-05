@extends('layouts.app')

@section('subtitle','申請一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/request-list.css') }}" />
@endsection

@section('header-contents')
  <x-header-user></x-header-user>
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

            @php
              if ($requestName['name']) {
                $name = $requestName['name'];
              }
            @endphp

            @foreach ($requestDates as $date)
              @php
                $stat = "承認済み";
                if ($date['status'] <> 15 ) {
                  $stat = "承認待ち";
                }

                $clockin = date('Y/m/d', strtotime( $date['clock_in'] ));
                $reqDate = date('Y/m/d', strtotime( $date['created_at'] ));
                $descript = $date['descript'];

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