@extends('layouts.app')

@section('subtitle','管理者ログイン画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
@endsection

@section('header-contents')
  <x-header-nolink></x-header-nolink>
@endsection

@section('main-contents')
    <main class="contents">
      <div class="contents-area">
        <p class="sell-title">管理者ログイン</p>
        <form class="sell-form" action="{{ route('admin.login') }}" method="POST">
          @csrf
          <section class="item-detail-area">
            <p class="iteme-name-title">メールアドレス</p>
            <input class="item-name" type="text" name="email" value="{{ old('email') }}"/>
            @if ($errors->has('email'))
              @foreach($errors->get('email') as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
            <p class="iteme-name-title">パスワード</p>
            <input class="item-prace" type="password" name="password" />
            @if ($errors->has('email'))
              @foreach($errors->get('password') as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
          </section>
          <button class="item-post-btn" type="submit">ログインする</button>
        </form>
      </div>
    </main>
@endsection