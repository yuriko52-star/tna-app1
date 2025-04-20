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
                <!--値を入れる-->
            </td>
            
        </tr>
        <tr>
            
            <th class="data-label">日付</th>
            <td class="data-item">
                <style>
                    .date-select-wrapper select {
                        margin-right: 8px;
                        padding: 5px;
                        font-size: 1rem;
                        appearance: none;
                         border-radius: 4px;
                        border: 1px solid #E1E1E1;
                        width:100px;
                        font-family: Inter;
                        font-weight: 700;
                        font-size: 16px;
                        
                        }
                    /* .date-display { 
                        display: flex;
                        gap: 2px;
                         align-items: center;
                    }
                    .date-display input {
                        margin-left:25px;
                        border: none;
                        background: none;
                        font-weight: bold;
                        font-size: 1em;
                    }
                    .admin-date-space {
                       
                        width:80px;

                    }
                        */
                </style>
                <div class="date-select-wrapper">
                    @php
    $parsedDate = \Carbon\Carbon::parse($attendance->date);
@endphp

    {{-- 年 --}}
    <select name="target_year">
        @for ($y = 2023; $y <= 2026; $y++)
            <option value="{{ $y }}" {{ (old('target_year', $parsedDate->year) == $y) ? 'selected' : '' }}>
                {{ $y }}年
            </option>
        @endfor
    </select>

    {{-- 月 --}}
    <select name="target_month">
        @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ (old('target_month', $parsedDate->month) == $m) ? 'selected' : '' }}>
                {{ $m }}月
            </option>
        @endfor
    </select>

    {{-- 日 --}}
    <select name="target_day">
        @for ($d = 1; $d <= 31; $d++)
            <option value="{{ $d }}" {{ (old('target_day', $parsedDate->day) == $d) ? 'selected' : '' }}>
                {{ $d }}日
            </option>
        @endfor
    </select>
</div>

                {{--<div class="date-display">
                  <input type="text" value="{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}">
                <span class="admin-date-space"></span>
                    <input type="text"  value="{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}">
  
                </div>
                <input type="date" name="target_date" value="{{ old('target_date', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')) }}">--}}
                    {{--<input type="text" name="date_year"class="time-input" value="{{old('date_year',$year)}}">
                    <!--  valueに値を入れる-->
                    <span class="date-space"></span>
                    <input type="text" class="time-input"name="date_month_day" value="{{ old('date_month_day' ,$monthDay}}">--}}
                    <!--  valueに値を入れる-->

                    
                
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
          @php
    // 出勤情報があるかチェック
    $hasAttendance = isset($attendance) && $attendance->id !== null;

    // 出勤しているかどうか（時間が入っているか）
    $hasWorked = $hasAttendance && ($attendance->clock_in || $attendance->clock_out);

    // 新規 or 土日（時間がすべてnull）の場合 → 休憩欄を2つ出す
    $showEmptyBreaks = !$hasWorked;

    // 既存休憩数（あっても null の日なら 0 に扱う）
    $existingCount = $showEmptyBreaks ? 0 : $attendance->breakTimes->count();

    // 追加する休憩欄の数
    $additional = $showEmptyBreaks ? 2 : 1;
@endphp

{{-- 既存の休憩表示（時間がある人のみ） --}}
@if ($hasWorked)
        @foreach($attendance->breakTimes as $i => $break)
        <tr>
            <th class="data-label">休憩{{ $i> 0 ? $i+1 : '' }}</th>
            <td class="data-item">
            <div class="time-wrapper">
                <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                <input type="text" name="breaks[{{ $i}}][clock_in]"class="time-input" value="{{ old("breaks.$i.clock_in",$break->clock_in  ? \Carbon\Carbon::parse($break->clock_in)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
                <span class="time-separator">~</span> 
                <input type="text" class="time-input" name="breaks[{{$i}}][clock_out]"value="{{ old("breaks.$i.clock_out",$break->clock_out ? \Carbon\Carbon::parse($break->clock_out)->format('H:i') : '') }}">
                <!--  valueに値を入れる-->
            </div>
                <p>
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
                </p>
            </td>
        </tr>
        @endforeach
        @endif
{{-- 追加の空の休憩欄 --}}
@for ($j = 0; $j < $additional; $j++)
    @php $i = $existingCount + $j; @endphp
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
               <textarea class="reason-input" name="reason">{{old('reason')}}</textarea>
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