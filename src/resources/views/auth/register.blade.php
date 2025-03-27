@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="content">
    <h1>会員登録</h1>
    {{--<form action="{{ route('register.process') }}" class="" method="post" novalidate>
        @csrf--}}
    <form action="" class="">  
    <label for="name" >
        <h2 class="label-title">名前</h2>
    </label>
    {{--<input type="text" class="text" name="name" value="{{ old('name') }}">--}}
    <input type="text" name="name" id="name"class="text">
    <p class="form_error">
        {{--@error('name')
        {{ $message }}
        @enderror--}}
    </p>
    <label for="mail" >
        <h2 class="label-title">メールアドレス</h2>
    </label>
   {{--<input type="text" name="email" class="text" value="{{ old('email') }}">--}} 
   <input type="text" name="email" id="mail"class="text">
    <p class="form_error">
        {{--@error('email')
        {{ $message }}
        @enderror--}}
    </p>
    <label for="password" >
        <h2 class="label-title">パスワード</h2>
    </label>
    <input type="password" name="password" id="password"class="text">
    <p class="form_error">
        {{--@error('password')
        {{ $message }}
        @enderror--}}
    </p>
    <label for="password_confirm" >
        <h2 class="label-title">パスワード確認</h2>
    </label>
    <input type="password" name="password_confirmation" id="password_confirm"class="text" >
          
        <!-- <div> -->
    <button class="register-btn" type="submit">登録する</button>
        <!-- </div> -->
    <a href="/login" class="link">ログインはこちら</a>
    </form>
    
</div>
@endsection