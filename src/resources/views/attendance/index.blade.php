@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <label class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
       <h1>勤怠一覧</h1>
    </label>    
        <div class="top-content"> 
          <div class="months">
            <label class="last-month">
              <a href="{{ url('/attendance/list?month=' . $previousMonth) }}" class="month-link"><img src="{{asset('img/image 2.png')}} "style="height:15px; width:20px;" alt="" class="img">前月</a>
            </label>
            <label class="this-month">
                <div class="image">
                <img src="{{ asset('img/image 1 (1).png')}}" style="height: 25px; width: 25px;"alt="" class="img">
                </div>
                <p class="date">{{ $thisMonth}}</p>
              </label>
              <label class="next-month">
                 <a href="{{ url('/attendance/list?month=' . $nextMonth) }}" class="month-link">翌月<img src="{{ asset('img/image 3.png')}}"style="height:15px; width:20px;" alt="" class="img"></a>
              </label>
          </div>
        </div>
        <div class="under-content">
           <table>
            <thead>
             <colgroup>  
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
            </colgroup>
            <tr class="row">
              <th class="data-label">日付</th>
              <th class="data-label">出勤</th>
              <th class="data-label">退勤</th>
              <th class="data-label">休憩</th>
              <th class="data-label">合計</th>
              <th class="data-label">詳細</th>
            </tr>
          </thead>
          <tbody>
            @foreach($attendanceData as $day)
            <tr class="row">
              <td class="data-item">{{$day['date'] }}</td>
              <td class="data-item">{{$day['clockIn']}}</td>
              <td class="data-item">{{ $day['clockOut']}}</td>
              <td class="data-item">{{$day['breakTime'] }}</td>
              <td class="data-item">{{ $day['workingTime'] }}</td>
              <td class="data-item">
              @if(!empty($day['id']))
                  @if(!empty($day['has_pending_edit']))
                  <a href="{{route('attendance.editDetail',['date' => \Carbon\Carbon::parse($day['raw_date'])->format('Y-m-d')]) }}" class="data-link">詳細</a>
                  @else
                  <a href="{{route('user.attendance.detail', ['id' => $day['id']]) }}" class="data-link">詳細</a>
                  @endif
               @else
               <a href="{{route('user.attendance.detailByDate',['date'=> $day['raw_date']])}}" class="data-link">詳細</a>
               @endif
              </td>
            </tr>
            @endforeach
          </tbody>
          </table>
        </div>
</div>
@endsection