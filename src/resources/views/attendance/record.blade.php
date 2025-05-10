@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/record.css') }}" class="">
@endsection

@section('content')
  <div class="content">
    
  <label for="status" class="label">
    {{ $status }}
  </label> 
    <div class="date">
    {{ $today}}
    </div>
    <div class="time">
    {{ $currentTime }}
    </div>

  @if($status === '勤務外')
    <form action="{{ route('attendance.clockIn') }}" method="POST">
      @csrf
    <button class="work-btn" type="submit">出勤</button>
    </form>
  @endif
  @if($status === '出勤中')
  <div class="working">
    <form action="{{ route('attendance.clockOut') }}" method="POST">
      @csrf
      <div>
      <button class="work-btn" type="submit">退勤</button>
      </div>
    </form>
    <form action="{{ route('attendance.breakStart') }}" method="POST">
      @csrf
      <div>
      <button class="rest-btn" type="submit">休憩入</button>
      </div>
    </form>
    </div>
  @endif
  @if($status === '休憩中')
    <form action="{{ route('attendance.breakEnd') }}" method="POST">
      @csrf
      <button class="back-btn" type="submit">休憩戻</button>
    </form>
  @endif  
  @if($status === '退勤済')
    <p class="message">お疲れ様でした。</p>
  </div>
  @endif
@endsection