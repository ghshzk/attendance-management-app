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

<body>
    <div class="app">
        <header class="header">
            <a href="{{ url('/') }}">
                <img class="header-logo" src="{{ asset('img/logo.svg') }}" alt="ロゴ">
            </a>

            @if(!in_array(Route::currentRouteName(),['login','register']))
                <nav class="header-nav">
                    <ul class="header-nav__list">
                        @auth
                            {{-- 管理者用 --}}
                            @if(Auth::user()->role === 'admin')
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/">勤怠一覧</a>
                                </li>
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/">スタッフ一覧</a>
                                </li>
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/">申請一覧</a>
                                </li>
                            @else
                            {{-- 一般ユーザー用 --}}
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/attendance">勤怠</a>
                                </li>
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/attendance/list">勤怠一覧</a>
                                </li>
                                <li class="header-nav__item">
                                    <a class="header-nav__link" href="/">申請</a>
                                </li>
                            @endif
                            <li class="header-nav__item">
                                <form action="{{ Auth::user()->role === 'admin' ? route('admin.logout') : route('logout') }}" method="post">
                                    @csrf
                                    <input class="header-nav__link" type="submit" value="ログアウト">
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