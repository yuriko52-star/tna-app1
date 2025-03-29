@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" class="">
@endsection

@section('content')
<div class="content">
    <form action="" class="">
       
      <label class="title">
        <div class="image">
          <img src="{{asset('img/Line 2.png')}}" style="height:40px;width:8px;"alt="" class="img">
        </div>
          <!-- <h1>勤怠一覧</h1> -->
          <h1>石黒さんの勤怠</h1>
      </label>    
        <div class="top-content"> 
          <div class="months">
            <label class="last-month">
              <a href="" class="month-link"><img src="{{asset('img/image 2.png')}} "style="height:15px; width:20px;" alt="" class="img">前月</a>
            </label>
            <label class="this-month">
                <div class="image">
                <img src="{{ asset('img/image 1 (1).png')}}" style="height: 25px; width: 25px;"alt="" class="img">
                </div>
                <p class="date">2025/11</p>
              </label>
              <label class="next-month">
                 <a href="" class="month-link">翌月<img src="{{ asset('img/image 3.png')}}"style="height:15px; width:20px;" alt="" class="img"></a>
              </label>
          </div>
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
              <th class="data-label">日付</th>
              <th class="data-label">出勤</th>
              <th class="data-label">退勤</th>
              <th class="data-label">休憩</th>
              <th class="data-label">合計</th>
              <th class="data-label">詳細</th>
            </tr>

            <tr class="row">
             <td class="data-item">11/01（木）</td>
              <td class="data-item">09:00</td>
              <td class="data-item">18:00</td>
             <td class="data-item">1:00</td>
             <td class="data-item">8:00</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
            <tr class="row">
             <td class="data-item">11/01（木）</td>
              <td class="data-item">09:00</td>
              <td class="data-item">18:00</td>
             <td class="data-item">1:00</td>
             <td class="data-item">8:00</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
            <tr class="row">
             <td class="data-item">11/01（木）</td>
              <td class="data-item">09:00</td>
              <td class="data-item">18:00</td>
             <td class="data-item">1:00</td>
             <td class="data-item">8:00</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
           </table>
          </div>
          <div class="button">
            <button class="csv-btn" type="submit">CSV出力</button>
          </div>
    </form>
</div>
@endsection