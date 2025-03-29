@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-list.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
          <h1>スタッフ一覧</h1> 
    </div>    
    <table>
        <colgroup>  
            <col style="width: 100px;">
            <col style="width: 200px;">
            <col style="width: 300px;">
            <col style="width: 200px;">
            <col style="width: 100px;">
        </colgroup>
        <tr class="row">
            <th class="data-label"></th>
            <th class="data-label">名前</th>
            <th class="data-label">メールアドレス</th>
            <th class="data-label">月次勤怠</th>
            <th class="data-label"></th>
        </tr>
        <tr class="row">
            <td class="data-item"></td>
            <td class="data-item">石黒 ゆりこ</td>
            <td class="data-item">test@example.com</td>
            <td class="data-item"> <a href="" class="data-link">詳細</a></td>
            <td class="data-item"></td>
        </tr>

        <tr class="row">
            <td class="data-item"></td>
            <td class="data-item">クレオパトラ</td>
            <td class="data-item">cleo@patra.com</td>
            <td class="data-item"> <a href="" class="data-link">詳細</a></td>
            <td class="data-item"></td>
        </tr>

        <tr class="row">
            <td class="data-item"></td>
            <td class="data-item">ポンペイウス</td>
            <td class="data-item">pon@romaroma.com</td>
            <td class="data-item"> <a href="" class="data-link">詳細</a></td>
            <td class="data-item"></td>
        </tr>
    </table>
</div>
@endsection