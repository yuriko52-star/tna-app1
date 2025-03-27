@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/record.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <form action="" class="">
  <label for="status" class="label">
    出勤外
    <!--出勤中  -->
    <!-- 休憩中 -->
    <!-- 退勤済 -->
  </label> 
   <div class="date">
    <!-- 現在の日付が入る -->
     2025年12月31日(金)
    </div>
    <div class="time">
    <!-- 現在の時間が入る -->
     08：00
    </div>
    <!-- <button class="work-btn" type="submit">出勤</button> -->
     
        <!-- <button class="out-btn" type="submit">退勤</button> -->
        <!-- <button class="rest-btn" type="submit">休憩入</button> -->
     
    
    <!-- <button class="back-btn" type="submit">休憩戻</button> -->
    <p class="message">お疲れ様でした。</p>
    

    </form>
</div>
@endsection