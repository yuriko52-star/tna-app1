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
        <!-- <form action="" class=""> -->
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <!-- 値入れる -->
                <span class="name">{{ $user->name}}</span>
            </td>
            
        </tr>
        <tr>
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    <!-- 値入れる -->
                    <span class="year">{{$year}}</span>
                    <span class="date-space"></span>
                    <!-- 値入れる -->
                    <span class="day">{{ $monthDay}}</span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                <!-- 値入れる -->
                <span class="time-in">{{ $clockIn ? \Carbon\Carbon::parse($clockIn)->format('H:i') : '-' }}</span>
                <span class="time-separator">~</span>
                <!-- 値入れる -->
                <span class="time-out">{{ $clockOut ? \Carbon\Carbon::parse($clockOut)->format('H:i') : '-' }}</span>
            </div> 
            </td>
        </tr>
        @foreach($mergedBreaks as $i => $break)
        <tr>
             {{-- @if($break['clock_in'] || $break['clock_out'])--}} {{-- どちらかに値があれば表示 --}}
            <th class="data-label">休憩{{ $i > 0 ? $i+1 : ' ' }}</th>
            <td class="data-item">
            <div class="time-wrapper">
                <!-- 値入れる -->
                <span class="time-in">{{ $break['clock_in'] ? \Carbon\Carbon::parse($break['clock_in'])->format('H:i') : '-' }}</span>
                <span class="time-separator">~</span>
                <!-- 値入れる -->
                <span class="time-out">{{ $break['clock_out'] ? \Carbon\Carbon::parse($break['clock_out'])->format('H:i') : '-' }}</span> 
            </div>
            </td>
        </tr>
            {{--@endif--}}
         @endforeach
         <tr>
            <!-- userも必要、figmaにはないので要注意！基本設計にあり。 -->
             <!-- edit.blade.phpさんしょうにしていれる -->
            <th class="data-label">休憩{{ count($mergedBreaks) + 1 }}</th>
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
                <!-- 値入れる -->
                <span class="reason">{{ $reason }}</span>
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
     

    <!-- </form> -->
</div>
@endsection   