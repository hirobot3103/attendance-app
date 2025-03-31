@extends('layouts.app')

@section('subtitle','会員登録画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/reg.css') }}" />
@endsection

@section('header-contents')
  <x-header-nolink></x-header-nolink>
@endsection

@section('main-contents')
<main class="contents">
  <div class="contents-area">
    <p class="sell-title">会員登録</p>
    <form class="sell-form" action="/register" method="POST">
      @csrf
      <section class="item-detail-area">
        <p class="iteme-name-title">名前</p>
        <input type="text" class="item-name" name="name" value="{{ old('name')}}"/>
        @if ($errors->has('name'))
          @foreach($errors->get('name') as $errorMassage )
              <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
          @endforeach
        @endif
        <p class="iteme-name-title">メールアドレス</p>
        <input type="text" class="item-name" name="email" value="{{ old('email') }}"/>
        @if ($errors->has('email'))
          @foreach($errors->get('email') as $errorMassage )
              <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
          @endforeach
        @endif
        <p class="iteme-name-title">パスワード</p>
        <input type="password" class="item-name" name="password" />
        @if ($errors->has('password'))
          @foreach($errors->get('password') as $errorMassage )
              <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
          @endforeach
        @endif
        <p class="iteme-name-title">確認用パスワード</p>
        <input type="password" class="item-prace" name="password_confirmation" />
        @if ($errors->has('password_confirmation'))
          @foreach($errors->get('password_confirmation') as $errorMassage )
              <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
          @endforeach
        @endif
      </section>
      <button class="item-post-btn" type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}" class="register-link">ログインはこちら</a>
  </div>
</main>
@endsection
