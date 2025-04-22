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
                  
                    <a href="{{ route('admin.stamp_correction_request.list',['tab'=> 'waiting']) }}" class="page-title">承認待ち</a>
                    <li>
                    <a href="{{ route('admin.stamp_correction_request.list',['tab' => 'approved']) }}" class="page-title">承認済み</a>
   <!-- 表示しているページのタブの太さが変わるように設定する。してないものは細目で。
 -->
   <!-- 管理者ページはデータの内容が一人一人になる。-->
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
            @foreach($datas as $data)
             @php
        $attendanceEdit = $data['attendance_edits']->first(); // 最初の1件を仮に取り出す
    @endphp
            <tr class="row">
              <!-- 後々必要 -->
              {{--{{is_null($data['approved_at']) ? '承認待ち' : '承認済み' }}--}}
              
             <td class="data-item">承認待ち</td>
             
             <!-- <td class="data-item">承認済み</td> -->
            
              <td class="data-item">{{$data['user']->name}}</td>
              <td class="data-item">{{\Carbon\Carbon::parse($data['target_date'])->format('Y/m/d') }}</td>
                {{--<pre>{{ dd($data['break_time_edits']) }}</pre>--}}
             <td class="data-item">{{$data['reason']}}</td>
             <td class="data-item">{{\Carbon\Carbon::parse($data['request_date'])->format('Y/m/d') }}</td>
              <td class="data-item">
                <!--$attendanceEdit->idでいいのか疑問 -->


                @if($attendanceEdit && $attendanceEdit->id)
                    <a href="{{ route('admin.approvePage', ['attendance_correct_request' => $attendanceEdit->id]) }}" class="data-link">詳細</a>
               @else
                    <a href="{{ route('admin.approveOnlyBreak', [
                'user_id' => $data['user']->id,
                 'date' => $data['target_date']
                 ]) }}" class="data-link">詳細</a>
               @endif
              
              </td>
            </tr>
            @endforeach
            
           </table>
          </div>
        </div> 
</div>
@endsection