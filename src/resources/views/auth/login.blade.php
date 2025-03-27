@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}" class="">
@endsection

@section('content')

    <div class="content">
    <!-- <h1>ログイン</h1> -->
     <h1>管理者ログイン</h1>
    <form action="/login" class="" method="post" novalidate >
        @csrf
    <label for="mail" >
        <h2 class="label-title">メールアドレス</h2>
    </label>
    {{--<input type="email" class="text" name="email" value="{{old('email')}}">--}}
    <input type="text" class="text"name="email" id="mail">
    <p class="form_error">
        {{--@error('email')
        {{ $message}}
        @enderror--}}
    </p>

    <label for="password" >
        <h2 class="label-title">パスワード</h2>
    </label>
    <input type="password" class="text" name="password" id="password">
    <p class="form_error">
        {{--@error('password')
        {{ $message}}
        @enderror--}}
    </p>
    
    <!-- <button class="login-btn" type="submit">ログインする -->
    <button class="login-btn" type="submit">管理者ログインする
        
    </button>
    
    
    <!-- <a href="/register" class="link">会員登録はこちら</a> -->
    </form>
    </div>

@endsection