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
        <form action="{{ route('admin.attendance.store.new',['id' => $user->id]) }}" method="POST">
        @endif
            @csrf
        
        <tr>
            <th class="data-label">名前</th>
            <td class="data-item">
                <span class="name">{{ $attendance->user->name}}</span>
            </td>
        </tr>
        <tr>
            <th class="data-label">日付</th>
            <td class="data-item">
                <div class="date-wrapper">
                    @php
                        $parsedDate = \Carbon\Carbon::parse($attendance->date);
                    @endphp
                     <input type="text"name="target_year" class="date-input" value="{{ old('target_year', $parsedDate->format('Y') . '年') }}">
                     <span class="date-space"></span>
                     <input type="text" class="date-input" name="target_month_day" value="{{ old('target_month_day', $parsedDate->format('n月j日')) }}">
                    
                </div>
                <p class="form_error">
                @error('target_year')
                     {{$message}}
                     @enderror
                </p>
                <p class="form_error">
                     @error('target_month_day')
                      {{ $message }} 
                      @enderror
                </p>
            </td>
        </tr>
        <tr>
            <th class="data-label">
                <span class="work">出勤・退勤</span>
            </th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="text" class="time-input" name="clock_in"value="{{ old('clock_in' ,$attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                
                <span class="time-separator">~</span>
                <input type="text" class="time-input" name="clock_out"value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
            </div> 
            @foreach (['clock_time_invalid', 'clock_in', 'clock_out'] as $field)
                 <p class="form_error">
                    @error($field)
                    {{ $message}}
                    @enderror
                </p>
            @endforeach 
            </td>
        </tr>
@php
    $hasAttendance = isset($attendance) && $attendance->id !== null;
    $hasWorked = $hasAttendance && ($attendance->clock_in || $attendance->clock_out);
    $showEmptyBreaks = !$hasWorked;
    $existingCount = $showEmptyBreaks ? 0 : $attendance->breakTimes->count();
    $additional = $showEmptyBreaks ? 2 : 1;
@endphp
@php $i = 0; @endphp
@if ($hasWorked)
<!--変数iに統一してみた  -->

    @foreach($attendance->breakTimes as $break)
        @php
            $hasTime = $break->clock_in || $break->clock_out;
        @endphp

        @if ($hasTime)

        <tr>
            <th class="data-label">休憩{{ $i === 0 ? '' : $i + 1 }}
            </th>

            <td class="data-item">
                <div class="time-wrapper">
                    <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id ?? ' '}}">
                    <input type="text" name="breaks[{{$i }}][clock_in]"class="time-input" value="{{ old("breaks.$i.clock_in",$break->clock_in  ? \Carbon\Carbon::parse($break->clock_in)->format('H:i') : '') }}">
                
                    <span class="time-separator">~</span> 
                    <input type="text" class="time-input" name="breaks[{{$i}}][clock_out]"value="{{ old("breaks.$i.clock_out",$break->clock_out ? \Carbon\Carbon::parse($break->clock_out)->format('H:i') : '') }}">
                </div>
                @foreach (['break_time_invalid', 'outside_working_time', 'clock_in', 'clock_out'] as $field)
                    <p class="form_error">
                        @error("breaks.$i.$field")
                        {{$message}}
                        @enderror
                    </p>
                @endforeach
            </td>
        </tr>
        @php $i++; @endphp
        @endif
    @endforeach
@endif

@for ($j = 0; $j < $additional; $j++)
    <tr>
        <th class="data-label">休憩{{ $i === 0 ? '' :$i + 1}} </th>
        <td class="data-item">
            <div class="time-wrapper">
                <input type="text" name="breaks[{{$i}}][clock_in]"class="time-input" value="{{ old("breaks.$i.clock_in") }}">
            
                <span class="time-separator">~</span> 
                <input type="text" name ="breaks[{{$i}}][clock_out]"class="time-input" value="{{ old("breaks.$i.clock_out") }}">
            </div>
            @foreach (['outside_working_time', 'clock_in', 'clock_out', 'break_time_invalid'] as $field)
                <p class="form_error">
                    @error("breaks.$i.$field")
                    {{$message}}
                    @enderror
                </p>
            @endforeach
        </td>
    </tr>
    @php $i++; @endphp
@endfor
        <tr>
            <th class="data-label">備考</th>
            <td class="data-item">
               <textarea class="reason-input" name="reason">{{old('reason')}}</textarea>
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