<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠アプリ</title>
    
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
   
    <div class="container">
        
    <header class="header">
        <div class="header__inner">
             <div class="header-utilities">
            <a href="" class="header__logo">
                <img src="{{ asset('img/CoachTech_White 1 (1).png') }}" > 
            </a>

        <nav>
            <ul class="header-nav">
                <li>
                    <a href="" class="header-nav-link">勤怠一覧</a>
                    <!-- <a href="" class="header-nav-link">勤怠</a> -->
                </li>
                <li>
                    <!-- <a href="" class="header-nav-link"> スタッフ一覧-->
                        
                    <a href="" class="header-nav-link">
                        勤怠一覧
                    </a>
                </li>
                <li>
                    <a href="" class="header-nav-link">申請一覧 </a>
                        
                    
                    <!-- <a href="" class="header-nav-link"> -->
                        <!-- 申請 -->
                    <!-- </a> -->
                </li>
                <li>
                <form action="{{ Request::is('admin/*') ? url('/admin.logout') : url('/logout') }}" class="" method="post" novalidate >
                @csrf
                <button type="submit" class="btn logout-btn">ログアウト</button>
                </form>
                
                </li>
            </ul>
        </nav>
            </div> 
        </div>
    </header>

    <main>

        
        @yield('content')

        
    </main>    
   
</div>

</body>
</html>