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
                  
                    <a href="" class="page-title">承認待ち</a>
                    <li>
                    <a href="" class="page-title">承認済み</a>
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

            <tr class="row">
             <td class="data-item">承認待ち</td>
              <td class="data-item">石黒 ゆりこ</td>
              <td class="data-item">2025/11/01</td>
             <td class="data-item">遅延のため</td>
             <td class="data-item">2025/12/25</td>
              <td class="data-item">
               <a href="" class="data-link">詳細</a>
              </td>
            </tr>
            <tr class="row">
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
           </table>
          </div>
        </div> 
</div>
@endsection