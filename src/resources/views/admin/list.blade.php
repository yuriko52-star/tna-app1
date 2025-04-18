@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <label class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
         <h1>{{ $today}}の勤怠</h1>
      </label>    
        <div class="top-content"> 
             <div class="days">
              <label class="last-day">
                <a href="{{ route('admin.attendance.index') }}?day={{$previousDay}}" class="day-link"><img src="{{asset('img/image 2.png')}} "style="height:15px; width:20px;" alt="" class="img">
                前日</a>
              </label>
              <label class="today">
                <div class="image">
                <img src="{{ asset('img/image 1 (1).png')}}" style="height: 25px; width: 25px;"alt="" class="img">
                </div>
                <p class="date">{{ $thisDay}}</p>
              </label>
              <label class="next-day">
                 <a href="{{ route('admin.attendance.index') }}?day={{$nextDay}}" class="day-link">翌日<img src="{{ asset('img/image 3.png')}}"style="height:15px; width:20px;" alt="" class="img"></a>
              </label>
            </div>
        </div>
        <div class="under-content">
           <table>
             <colgroup>  
              <col style="width: 200px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 100px;">
              <col style="width: 150px;">
            </colgroup>
            <tr class="row">
              <th class="data-label">名前</th>
              <th class="data-label">出勤</th>
              <th class="data-label">退勤</th>
              <th class="data-label">休憩</th>
              <th class="data-label">合計</th>
              <th class="data-label">詳細</th>
            </tr>
            @foreach($attendanceData as $day)
            <tr class="row">
             <td class="data-item">{{ $day['user_name']}}</td>
              <td class="data-item">{{$day['clockIn']}}</td>
              <td class="data-item">{{ $day['clockOut']}}</td>
             <td class="data-item">{{$day['breakTime'] }}</td>
             <td class="data-item">{{ $day['workingTime'] }}</td>
              <td class="data-item">
               <a href="{{route('admin.attendance.detail',['id' => $day['id']])}}" class="data-link">詳細</a>
              </td>
            </tr>
            @endforeach
            
          </table>
        </div>    
</div>
@endsection