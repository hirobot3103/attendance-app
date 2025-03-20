@extends('layouts.app')

@section('subtitle','管理者ログイン画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
@endsection

@section('header-contents')
    <header class="page-header">
      <div class="page-logo">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="ロゴ COACHTECH" />
      </div>
      <div class="page-logo"></div>
      <nav class="page-menu"></nav>
    </header>
@endsection

@section('main-contents')
    <main class="contents">
      <div class="contents-area">
        <p class="sell-title">管理者ログイン</p>
        <form class="sell-form" action="{{ route('admin.login') }}" method="POST">
          @csrf
          <section class="item-detail-area">
            <p class="iteme-name-title">メールアドレス</p>
            <input type="text" class="item-name" name="email" value="{{ old('email') }}"/>
            @if ($errors->has('email'))
              @foreach($errors->get('email') as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
            <p class="iteme-name-title">パスワード</p>
            <input type="password" class="item-prace" name="password" />
          </section>
          <button class="item-post-btn" type="submit">ログインする</button>
        </form>
      </div>
    </main>
@endsection