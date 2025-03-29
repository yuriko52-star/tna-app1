@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
          <h1>勤怠詳細</h1> 
    </div> 
    <table>
        <!-- 管理者用 -->
        <form action="" class="">
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <span class="name">石黒 ゆりこ</span>
            </td>
            
        </tr>
        <tr>
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    <span class="year">2025年</span>
                    <span class="date-space"></span>
                    <span>12月11日</span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                <span class="time-in">09:00</span>
                <span class="time-separator">~</span>
                <span class="time-out">18:00</span>
            </div> 
            </td>
        </tr>
        <tr>
            <th class="data-label">休憩</th>
            <td class="data-item">
            <div class="time-wrapper">
                <span class="time-in">12:00</span>
                <span class="time-separator">~</span>
                <span class="time-out">13:00</span> 
            </div>
            </td>
        </tr>
         <tr>
            <!-- userも必要、figmaにはないので要注意！基本設計にあり。 -->
            <th class="data-label">休憩2</th>
            <td class="data-item">
            <div class="time-wrapper">
                <span class="time-in"></span>
                <span class="time-separator">~</span> 
                <span class="time-out"></span>
            </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">備考</th>
            <td class="data-item">
                <span class="reason">電車遅延のため</span>
            </td>
        </tr>
    </table>
    <!-- <div class="button"> -->
        <!-- <button class="approve-btn" type="submit">承認</button> -->
<!-- 承認されたら・・ -->
 <!-- <button class="ok-btn" type="submit">承認済み</button> -->
    <!-- </div> -->
    <!-- 一般ユーザー -->
    <p class="attention">
        *承認待ちのため修正はできません。
    </p>
     

    </form>
</div>
@endsection   