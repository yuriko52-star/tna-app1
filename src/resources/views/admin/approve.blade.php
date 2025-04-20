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
       
        <!-- <form action="" class=""> -->
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                {{--<span class="name">{{ $user->name}}</span>--}
            </td>
            
        </tr>
        <tr>
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    {{--<span class="year">{{$year}}</span>--}}
                    <span class="date-space"></span>
                    {{--$_COOKIE<span class="day">{{ $monthDay}}</span>--}}
                </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                
                {{--<span class="time-in">{{\Carbon\Carbon::parse($workclockIn)->format('H:i')}}</span>--}}
               
                <span class="time-separator">~</span>
                
                {{--<span class="time-out">{{\Carbon\Carbon::parse($workclockOut)->format('H:i')}}</span>--}}
            </div> 
            </td>
        </tr>
   {{-- @foreach($mergedBreaks as $i => $break)--}}
        
        <tr>
    {{--<th class="data-label">休憩{{ $i > 0 ? $i+1 : ' ' }}</th>--}}
            <td class="data-item">
            <div class="time-wrapper">
                {{--<span class="time-in">{{ $break['clock_in'] ? \Carbon\Carbon::parse($break['clock_in'])->format('H:i') : '-' }}</span>--}}
                <span class="time-separator">~</span>
                {{--<span class="time-out">{{ $break['clock_out'] ? \Carbon\Carbon::parse($break['clock_out'])->format('H:i') : '-' }}</span>--}} 
            </div>
            </td>
        </tr>
        {{-- @endforeach--}}
         
         <tr>
            {{--<th class="data-label">休憩{{ count($mergedBreaks) + 1 }}</th>--}}
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
               {{--<span class="reason">{{ $reason }}</span>--}}
            </td>
        </tr>
    </table>
    <div class="button">
        <button class="approve-btn" type="submit">承認</button>
<!-- 承認されたら・・ -->
 <!-- <button class="ok-btn" type="submit">承認済み</button> -->
    </div>
    
     

    <!-- </form> -->
</div>
@endsection   