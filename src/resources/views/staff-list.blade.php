@extends('layouts.app')

@section('subtitle','スタッフ一覧画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/staff-list.css') }}" />
@endsection

@section('header-contents')
  <x-header-auth></x-header-auth>
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