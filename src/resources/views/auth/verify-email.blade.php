@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection
@section('content')
<div class="content">
    <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
    <p>メール認証を完了してください。</p>
   <a href="http://localhost:8025" class="verify-btn">
    認証はこちらから
   </a>
  @if (session('message'))
            <div class="alert alert-success">
               {{ session('message') }}
            </div>
    @endif
    <form action="{{ route('verification.resend') }}" method="post">
        @csrf
        <button class="btn btn-primary" type="submit">認証メールを再送する</button>
    </form> 
</div>
@endsection