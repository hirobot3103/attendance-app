@extends('layouts.app')

@section('subtitle', '認証メール送付完了')

@section('css')
    <link rel="stylesheet" href="{{ asset('/assets/css/verify-mail.css') }}">
@endsection

@section('header-contents')
<x-header-mail></x-header-mail>
@endsection

@section('main-contents')
    <main class="page-main">
        <div class="container__message">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        </div>
        @if (session('status_done'))
            <div class="status__area">&#x2757;&emsp;{{ session('status_done') }}</div>
        @endif
        @if (session('status_resend'))
            <div class="status__area">&#x2B55;&emsp;{{ session('status_resend') }}</div>
        @endif
        <a href="{{ $verificationUrl }}" class="container__verify">認証はこちらから</a>
        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button class="container__mail-resend" type="submit">認証メールを再送信する</button>
        </form>
    </main>  
@endsection