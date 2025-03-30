@extends('layouts.app')

@section('subtitle','ログイン画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
@endsection

<x-header-nolink>
</x-header-nolink>

@section('main-contents')
    <main class="contents">
      <div class="contents-area">
        <p class="sell-title">ログイン</p>
        <form class="sell-form" action="{{ route('login') }}" method="POST">
          @csrf
          <section class="item-detail-area">
            <p class="iteme-name-title">メールアドレス</p>
            <input type="text" class="item-name" name="email"  value="{{ old( 'email' ) }}"/>
            @if ($errors->has('email'))
              @foreach($errors->get('email') as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
            <p class="iteme-name-title">パスワード</p>
            <input type="password" class="item-prace" name="password" />
            @if ($errors->has('password'))
              @foreach($errors->get('password') as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
          </section>
          <button class="item-post-btn" type="submit">ログインする</button>
        </form>
        <a href="/register" class="register-link">会員登録はこちら</a>
      </div>
    </main>
@endsection