@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}" class="">
@endsection

@section('content')
<div class="content">
  
    <div class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
          <h1>申請一覧</h1> 
    </div>    
        <div class="top-content">
            <nav>
                <ul>
                  <li>
                  <a href="{{ route('admin.stamp_correction_request.list',['tab'=> 'waiting']) }}" class="tab-link {{ request('tab') === 'waiting' ? 'active-tab' : '' }}">承認待ち</a>
                  </li>
                  <li>
                    <a href="{{ route('admin.stamp_correction_request.list',['tab' => 'approved']) }}" class="tab-link {{ request('tab') === 'approved' ? 'active-tab' : '' }}">承認済み</a>
                  </li>
                </ul>
            </nav>
        </div> 
        <div class="under-content">
            <table>
             <colgroup>  
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
              <col style="width: 150px;">
            </colgroup>
            <tr class="row">
              <th class="data-label">状態</th>
              <th class="data-label">名前</th>
              <th class="data-label">対象日時</th>
              <th class="data-label">申請理由</th>
              <th class="data-label">申請日時</th>
              <th class="data-label">詳細</th>
            </tr>
        <tr class="row">
          @foreach($datas as $data)
            @php
              $attendanceEdit = $data['attendance_edits']->first();
              $breakEdit = $data['break_time_edits']->first();

              
              $edit = null;
              if ($attendanceEdit && $attendanceEdit->approved_at) {
              $edit = $attendanceEdit;
              } elseif ($breakEdit && $breakEdit->approved_at) {
              $edit = $breakEdit;
              } else {
              $edit = $attendanceEdit ?? $breakEdit;
              }
            @endphp
            <td class="data-item">
                {{is_null($edit->approved_at) ? '承認待ち' : '承認済み' }}
            </td>  
            <td class="data-item">{{$data['user']->name}}</td>
            <td class="data-item">{{\Carbon\Carbon::parse($data['target_date'])->format('Y/m/d') }}</td>
            <td class="data-item">{{$data['reason']}}</td>
            <td class="data-item">{{\Carbon\Carbon::parse($data['request_date'])->format('Y/m/d') }}</td>
            <td class="data-item">
            @php
              $attendanceEdit = $data['attendance_edits']->first();
              $hasAttendanceChange = $attendanceEdit && ($attendanceEdit->new_clock_in || $attendanceEdit->new_clock_out);
            @endphp
            @if($hasAttendanceChange)
                <a href="{{ route('admin.approvePage', ['attendance_correct_request' => $attendanceEdit->id]) }}" class="data-link">詳細</a>
            @else
                <a href="{{ route('admin.approveOnlyBreak', ['user_id' => $data['user']->id, 'date' => $data['target_date'] ]) }}" class="data-link">詳細
                </a>
            @endif
            </td>
        </tr>
          @endforeach
            </table>
          </div>
    </div> 
</div>
@endsection