<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body class="@yield('body_class')">
    <div class="app">
        <header class="header">
            <img class="header-logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">

            @if(!in_array(Route::currentRouteName(),['login','register']))
                <nav class="header-nav">
                    <ul class="header-nav-list">
                        @auth
                            {{-- 管理者用 --}}
                            @if(Auth::user()->role === 'admin')
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/">勤怠一覧</a>
                                </li>
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/">スタッフ一覧</a>
                                </li>
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/">申請一覧</a>
                                </li>
                            @else
                            {{-- 一般ユーザー用 --}}
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/attendance">勤怠</a>
                                </li>
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/attendance/list">勤怠一覧</a>
                                </li>
                                <li class="header-nav-item">
                                    <a class="header-nav-link" href="/stamp_correction_request/list">申請</a>
                                </li>
                            @endif
                            <li class="header-nav-item">
                                <form action="{{ Auth::user()->role === 'admin' ? route('admin.logout') : route('logout') }}" method="post">
                                    @csrf
                                    <input class="header-nav-btn" type="submit" value="ログアウト">
                                </form>
                            </li>
                        @endauth
                    </ul>
                </nav>
            @endif
        </header>

        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>