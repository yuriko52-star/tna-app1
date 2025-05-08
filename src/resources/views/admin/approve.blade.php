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
       <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <span class="name">{{ $user->name}}</span>
            </td>
            
        </tr>
        <tr>
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    <span class="year">{{$year}}</span>
                    <span class="date-space"></span>
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
                
                <span class="time-in">{{\Carbon\Carbon::parse($workclockIn)->format('H:i')}}</span>
               
                <span class="time-separator">~</span>
                
                <span class="time-out">{{\Carbon\Carbon::parse($workclockOut)->format('H:i')}}</span>
            </div> 
            </td>
        </tr>
    @php $displayedIndex = 0; @endphp

   @foreach($mergedBreaks as $break)
    @php
        $hasTime = $break['clock_in'] || $break['clock_out'];
    @endphp

    @if ($hasTime)    
        <tr>
    <th class="data-label">{{  $displayedIndex === 0 ? '休憩' : '休憩' . ($displayedIndex + 1) }}</th>
            <td class="data-item">
            <div class="time-wrapper">
                <span class="time-in">{{ $break['clock_in'] ? \Carbon\Carbon::parse($break['clock_in'])->format('H:i') : '-' }}</span>
                <span class="time-separator">~</span>
                <span class="time-out">{{ $break['clock_out'] ? \Carbon\Carbon::parse($break['clock_out'])->format('H:i') : '-' }}</span>
            </div>
            </td>
        </tr>
        @php $displayedIndex++; @endphp
        @endif
         @endforeach
         
         <tr>
            <th class="data-label">{{ $displayedIndex === 0 ? '休憩' : '休憩' . ($displayedIndex + 1)}}</th>
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
               <span class="reason">{{ $reason }}</span>
            </td>
        </tr>
    </table>
    @php
    $isBreakEdit = $edit instanceof \App\Models\BreakTimeEdit;
    $formAction = $isBreakEdit
        ? route('admin.breakEdit.approve', ['id' => $edit->id])
        : route('admin.attendanceEdit.approve', ['id' => $edit->id]);
    @endphp
    <div class="button">
    @if($edit->approved_at)
      <button class="ok-btn"type="submit" disabled>承認済み</button>
    @else
       <form action="{{ $formAction}}" method="POST">
        @csrf
        <button class="approve-btn" type="submit">承認</button>
    </form>
    @endif
                
            
       
    </div>
</div>
@endsection   