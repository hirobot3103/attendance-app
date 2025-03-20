@extends('layouts.app')

@section('subtitle','会員登録画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/reg.css') }}" />
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
    <p class="sell-title">会員登録</p>
    <form class="sell-form" action="/register" method="POST">
      @csrf
      <section class="item-detail-area">
        <p class="iteme-name-title">名前</p>
        <input type="text" class="item-name" name="name" />
        <p class="iteme-name-title">メールアドレス</p>
        <input type="text" class="item-name" name="email" />
        <p class="iteme-name-title">パスワード</p>
        <input type="text" class="item-name" name="password" />
        <p class="iteme-name-title">確認用パスワード</p>
        <input type="text" class="item-prace" name="password_confirmation" />
      </section>
      <button class="item-post-btn" type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}" class="register-link">ログインはこちら</a>
  </div>
</main>
@endsection
