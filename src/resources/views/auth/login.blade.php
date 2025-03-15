@extends('layouts.app')

@section('subtitle','ログイン画面')

@section('css')
    <link rel="stylesheet" href="./assets/css/login.css" />
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
        <p class="sell-title">ログイン</p>
        <form class="sell-form" action="">
          <section class="item-detail-area">
            <p class="iteme-name-title">メールアドレス</p>
            <input type="text" class="item-name" name="item-user" />
            <p class="iteme-name-title">パスワード</p>
            <input type="text" class="item-prace" name="item-pass" />
          </section>
          <button class="item-post-btn" type="submit">ログインする</button>
        </form>
        <a href="" class="register-link">会員登録はこちら</a>
      </div>
    </main>
@endsection