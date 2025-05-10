@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="content">
    <h1>会員登録</h1>
    <form action="/register" class="" method="post" novalidate>
        @csrf
   
    <label for="name" >
        <h2 class="label-title">名前</h2>
    </label>
    <input type="text" class="text" name="name" id="name" value="{{ old('name') }}">
    
    <p class="form_error">
       @error('name')
        {{ $message }}
        @enderror
    </p>
    <label for="mail" >
        <h2 class="label-title">メールアドレス</h2>
    </label>
   <input type="text" name="email" id="mail" class="text" value="{{ old('email') }}">
   
    <p class="form_error">
       @error('email')
        {{ $message }}
        @enderror
    </p>
    <label for="password" >
        <h2 class="label-title">パスワード</h2>
    </label>
    <input type="password" name="password" id="password"class="text">
    <p class="form_error">
        @error('password')
        {{ $message }}
        @enderror
    <label for="password_confirm" >
        <h2 class="label-title">パスワード確認</h2>
    </label>
    <input type="password" name="password_confirmation" id="password_confirm"class="text" >
    <button class="register-btn" type="submit">登録する</button>
    <a href="/login" class="link">ログインはこちら</a>
    </form>
    
</div>
@endsection