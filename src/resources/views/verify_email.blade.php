@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css')  }}">
@endsection

@section('content')
<div class="verification-container">
    <div class="verification-content">
        <p class="verification-content__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
    </div>

    <div class="verification-btn">
        <a href="https://mailtrap.io/home" class="verification-btn__link">
            認証はこちらから
        </a>
    </div>

    <div class="verification-resend">
        <form class="resend-form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-form__btn">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection