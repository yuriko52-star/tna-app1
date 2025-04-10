@extends('layouts.app')
<!-- userとadminで 画面を変える-->
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
                  
                    <a href="{{ route('attendance.editRequest',['tab'=> 'waiting']) }}" class="page-title">承認待ち</a>
                    <li>
                    <a href="{{ route('attendance.editRequest',['tab' => 'approved']) }}" class="page-title">承認済み</a>
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
            <tr class="row">
              {{--{{is_null($data['approved_at']) ? '承認待ち' : '承認済み' }}--}}
              
             <td class="data-item">承認待ち</td>
             
             <!-- <td class="data-item">承認済み</td> -->
            
              <td class="data-item">{{$data['user']->name}}</td>
              <td class="data-item">{{\Carbon\Carbon::parse($data['target_date'])->format('Y/m/d') }}</td>
             <td class="data-item">{{$data['reason']}}</td>
             <td class="data-item">{{\Carbon\Carbon::parse($data['request_date'])->format('Y/m/d') }}</td>
              <td class="data-item">
               <a href="{{ route('attendance.editDetail',['id' => $data['id']])}}" class="data-link">詳細</a>
               <!-- チャットにきく -->
              </td>
            </tr>
            @endforeach
            <!-- <tr class="row">
             <td class="data-item">承認待ち</td>
              <td class="data-item">石黒 ゆりこ</td>
              <td class="data-item">2025/11/02</td>
             <td class="data-item">寝坊のため</td>
             <td class="data-item">2025/11/05</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
            <tr class="row">
             <td class="data-item">承認待ち</td>
              <td class="data-item">石黒 ゆりこ</td>
              <td class="data-item">2025/11/03</td>
             <td class="data-item">残業のため</td>
             <td class="data-item">2025/12/03</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
             -->
           </table>
          </div>
        </div> 
</div>
@endsection