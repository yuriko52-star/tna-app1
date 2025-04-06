@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" class="">
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
        <form action="" class="">
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <span class="name">石黒 ゆりこ</span>
            </td>
            
        </tr>
        <tr>
            <!-- コメントは一般ユーザー -->
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    <!-- <input type="text" class="time-input" value="2025年"> -->
                    <span class="year">2025年</span>
                    <span class="date-space"></span>
                    <!-- <input type="text" class="time-input" value="6月11日"> -->

                    <span class="date">6月11日</span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" class="time-input" value="09:00">
                <span class="time-separator">~</span>
                <input type="text" class="time-input" value="18:00">
            </div> 
            </td>
            
        </tr>
        <tr>
            <th class="data-label">休憩</th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" class="time-input" value="12:00">
                <span class="time-separator">~</span> 
                <input type="text" class="time-input" value="13:00">
            </div>
            </td>
        </tr>
         <tr>
            <!-- userも必要、figmaにはないので要注意！基本設計にあり。 -->
            <th class="data-label">休憩2</th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" class="time-input" value="">
                <span class="time-separator">~</span> 
                <input type="text" class="time-input" value="">
            </div>
            </td>
        </tr>

        <tr>
            <th class="data-label">備考</th>
            <td class="data-item">
               <textarea class="reason-input"></textarea>
               <!-- <textarea class="reason-input">電車遅延のため</textarea> -->
            </td>
           
        </tr>
    </table>
    <div class="button">
        <button class="edit-btn" type="submit">修正</button>
    </div>
    </form>
</div>
@endsection   