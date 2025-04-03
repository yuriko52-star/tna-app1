@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}" class="">
@endsection

@section('content')

    <div class="content">
        <h1>
        @if(Request::is('admin/login'))
            管理者ログイン
        @else
            ログイン
        @endif
        </h1>
     
    <form action="{{ Request::is('admin/login') ? route('admin.login') : route('login') }}" class="" method="post" novalidate >
        @csrf
    <label for="mail" >
        <h2 class="label-title">メールアドレス</h2>
    </label>
    <input type="email" class="text" name="email" id="mail" value="{{old('email')}}">
   
    <p class="form_error">
        @error('email')
        {{ $message}}
        @enderror
    </p>

    <label for="password" >
        <h2 class="label-title">パスワード</h2>
    </label>
    <input type="password" class="text" name="password" id="password">
    <p class="form_error">
        @error('password')
        {{ $message}}
        @enderror
    </p>
    
    <button class="login-btn" type="submit">
        @if (Request::is('admin/login'))
            管理者ログインする
        @else
            ログインする
        @endif
    </button>

    @if(!Request::is('admin/login'))
    <a href="/register" class="link">会員登録はこちら</a>
    @endif
    </form>
    </div>

@endsection