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
         @if($attendance->id)
        <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST">
            @method('PATCH')
        @else
        <form action="{{ route('admin.attendance.store') }}" method="POST">
        @endif
        @csrf
        
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <span class="name">{{ $attendance->user->name}}</span>
                <!--値を入れる-->
            </td>
            
        </tr>
        <tr>
            
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    <input type="text" name="date_year"class="time-input" value="{{old('date_year',$year)}}">
                    <!--  valueに値を入れる-->
                    <span class="date-space"></span>
                    <input type="text" class="time-input"name="date_month_day" value="{{ old('date_month_day' ,$monthDay}}">
                    <!--  valueに値を入れる-->

                    
                </div>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" class="time-input" name="clock_in"value="{{ old('clock_in' ,$attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
                <span class="time-separator">~</span>
                <input type="text" class="time-input" name="clock_out"value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
            </div> 
                 <p class="form_error">
                    @error('clock_time_invalid')
                    {{ $message}}
                    @enderror
                </p>
                 <p class="form_error">
                    @error('clock_in')
                    {{ $message}} 
                    @enderror
                </p>    
                <p class="form_error">
                    @error('clock_out')
                    {{ $message}} 
                    @enderror
                </p>    
                
            </td>
            
        </tr>
        @foreach($attendance->breakTimes as $i => $break)
        <tr>
            <th class="data-label">休憩{{ $i> 0 ? $i+1 : '' }}</th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" name="breaks[{{ $i}}][clock_in]"class="time-input" value="{{ old("breaks.$i.clock_in",$break->clock_in  ? \Carbon\Carbon::parse($break->clock_in)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
                <span class="time-separator">~</span> 
                <input type="text" class="time-input" name="breaks[{{$i}}][clock_out]"value="{{ old("breaks.$i.clock_out",$break->clock_out ? \Carbon\Carbon::parse($break->clock_out)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
            </div>
                    @error("breaks.$i.outside_working_time")
                    {{$message}}
                    @enderror
                </p>
                 <p class="form_error">
                    @error('breaks.*.clock_in')
                    {{ $message}} 
                    @enderror
                </p>    
                <p class="form_error">
                    @error('breaks.*.clock_out')
                    {{ $message}} 
                    @enderror
            </td>
        </tr>
        @endforeach
         @php
        $existing = count($attendance->breakTimes);
        $additional = 1; // 追加したい休憩欄の数
        @endphp
        @for ($i = $existing; $i < $existing + $additional; $i++)
         <tr>
            
            <th class="data-label">休憩{{ $i === 0 ? '' :$i+ 1}} </th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" name="breaks[{{$i}}][clock_in]"class="time-input" value="{{ old("breaks.$i.clock_in") }}">
            
                <span class="time-separator">~</span> 
                <input type="text" name ="breaks[{{$i}}][clock_out]"class="time-input" value="{{ old("breaks.$i.clock_out") }}">
            </div>
            <!-- バリデはあと -->
            </td>
        </tr>
        @endfor
        <tr>
            <th class="data-label">備考</th>
            <td class="data-item">
               <textarea class="reason-input" name="reason"></textarea>
               <!--  値を入れる-->
              <p class="form_error">
                @error('reason')
                {{ $message}}
                @enderror
            </p>
            </td>
          
        </tr>
    </table>
    <div class="button">
        <button class="edit-btn" type="submit">修正</button>
    </div>
    </form>
</div>
@endsection   